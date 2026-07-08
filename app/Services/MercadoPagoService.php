<?php

declare(strict_types=1);

namespace Maia\Services;

use Maia\Models\OrderModel;
use Maia\Services\NotificationService;

class MercadoPagoService
{
    private string $accessToken;
    private string $apiBase = 'https://api.mercadopago.com';

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/app.php';
        $env    = $config['app']['env'] ?? 'production';

        $this->accessToken = $env === 'production'
            ? ($config['mercadopago']['access_token']         ?? '')
            : ($config['mercadopago']['sandbox_access_token'] ?? '');
    }

    // ─── Preferência de Pagamento ────────────────────────────────────────────

    /**
     * Cria preferência no MP e retorna init_point + preference_id.
     * Chamado pelo CheckoutController após criação do pedido.
     */
    public function createPreference(array $order, array $items): array
    {
        $mpItems = [];
        $itemsTotal = 0.0;
        foreach ($items as $item) {
            $quantity = (int)$item['quantity'];
            $unitPrice = round((float)$item['price'], 2);
            $itemsTotal += $unitPrice * $quantity;
            $mpItems[] = [
                'id'          => !empty($item['product_id'])
                    ? (string)$item['product_id']
                    : 'combo-' . (string)($item['combo_id'] ?? ''),
                'title'       => $item['product_name'],
                'quantity'    => $quantity,
                'unit_price'  => $unitPrice,
                'currency_id' => 'BRL',
            ];
        }

        $orderTotal = round((float)($order['total'] ?? $itemsTotal), 2);
        if ($orderTotal > 0 && abs(round($itemsTotal, 2) - $orderTotal) > 0.01) {
            $mpItems = [[
                'id'          => 'order-' . (string)($order['id'] ?? ''),
                'title'       => 'Pedido #' . (string)($order['id'] ?? '') . ' - WM Suplementos',
                'quantity'    => 1,
                'unit_price'  => $orderTotal,
                'currency_id' => 'BRL',
            ]];
        }

        $appUrl = getenv('APP_URL') ?: 'https://maiasuplementos.com.br';

        $payload = [
            'items'                => $mpItems,
            'payer'                => [
                'name'  => $order['customer_name'],
                'email' => $order['customer_email'],
                'phone' => ['number' => $order['customer_phone'] ?? ''],
            ],
            'external_reference'   => (string)$order['id'],
            'notification_url'     => $appUrl . '/webhook/mercadopago',
            'back_urls'            => [
                'success' => $appUrl . '/pedido/confirmacao/' . $order['id'],
                'pending' => $appUrl . '/pedido/confirmacao/' . $order['id'],
                'failure' => $appUrl . '/finalizar-compra',
            ],
            'auto_return'          => 'approved',
            'statement_descriptor' => 'WM SUPLEMENTOS',
        ];

        // Restringe método de pagamento conforme seleção do cliente
        $method = $order['payment_method'] ?? '';
        if ($method === 'pix') {
            $payload['payment_methods'] = [
                'excluded_payment_types' => [
                    ['id' => 'credit_card'],
                    ['id' => 'debit_card'],
                    ['id' => 'ticket'],
                ],
            ];
        } elseif ($method === 'boleto') {
            $payload['payment_methods'] = [
                'excluded_payment_types' => [
                    ['id' => 'credit_card'],
                    ['id' => 'debit_card'],
                    ['id' => 'bank_transfer'],
                ],
            ];
        } elseif ($method === 'cartao') {
            $payload['payment_methods'] = [
                'excluded_payment_types' => [
                    ['id' => 'ticket'],
                    ['id' => 'bank_transfer'],
                ],
            ];
        }

        $response = $this->post('/checkout/preferences', $payload);

        if (isset($response['error'])) {
            error_log('[MP] createPreference error: ' . json_encode($response));
            return ['init_point' => '', 'id' => ''];
        }

        return [
            'init_point' => $response['init_point']    ?? '',
            'id'         => $response['id']            ?? '',
        ];
    }

    // ─── Processamento de Webhook ────────────────────────────────────────────

    /**
     * Processa notificação IPN/Webhook do Mercado Pago.
     * Retorna true se processado com sucesso.
     */
    public function processWebhook(array $data): bool
    {
        $type = $data['type'] ?? $data['topic'] ?? '';

        if ($type === 'payment') {
            $paymentId = (string)($data['data']['id'] ?? $data['id'] ?? '');
            return $this->handlePayment($paymentId);
        }

        if ($type === 'merchant_order') {
            $orderId = (string)($data['data']['id'] ?? '');
            return $this->handleMerchantOrder($orderId);
        }

        return true;
    }

    private function handlePayment(string $paymentId): bool
    {
        if (empty($paymentId)) {
            return false;
        }

        $payment = $this->get('/v1/payments/' . $paymentId);

        if (isset($payment['error'])) {
            error_log('[MP] handlePayment fetch error: ' . json_encode($payment));
            return false;
        }

        $externalRef = (string)($payment['external_reference'] ?? '');
        $status      = $payment['status']        ?? '';
        $statusDetail = $payment['status_detail'] ?? '';

        if (empty($externalRef)) {
            return false;
        }

        $orderId    = (int)$externalRef;
        $orderModel = new OrderModel();
        $order      = $orderModel->findById($orderId);

        if (!$order) {
            error_log('[MP] handlePayment: pedido não encontrado para external_reference=' . $externalRef);
            return false;
        }

        // Idempotência: evita re-processar mesma notificação
        if ($order['payment_id'] === $paymentId && $order['payment_status'] === $status) {
            return true;
        }

        $newOrderStatus = $this->mapPaymentStatus($status, $statusDetail);

        if ($newOrderStatus === null) {
            return true;
        }

        // Atualiza pedido com dados do pagamento
        db()->prepare(
            'UPDATE orders SET payment_id = ?, payment_status = ?, status = ?, updated_at = NOW()
              WHERE id = ?'
        )->execute([$paymentId, $status, $newOrderStatus, $orderId]);

        // Registra histórico de status
        db()->prepare(
            'INSERT INTO order_status_history (order_id, status, note, created_at) VALUES (?, ?, ?, NOW())'
        )->execute([$orderId, $newOrderStatus, 'MP: ' . $status . ' / ' . $statusDetail]);

        // Enfileira e-mail correspondente
        $this->queueStatusEmail($orderId, $order, $newOrderStatus, $status);

        // Notifica admin em pagamentos aprovados
        if ($newOrderStatus === OrderModel::STATUS_PAID) {
            $orderModel->decrementStockForPaidOrder($orderId);

            NotificationService::create(
                NotificationService::TYPE_PAYMENT,
                'Pagamento aprovado — Pedido #' . $orderId,
                'R$ ' . number_format((float)$order['total'], 2, ',', '.') . ' via ' . $order['payment_method']
            );
        }

        return true;
    }

    private function handleMerchantOrder(string $merchantOrderId): bool
    {
        $mo = $this->get('/merchant_orders/' . $merchantOrderId);

        if (isset($mo['error'])) {
            return false;
        }

        // Processa cada pagamento vinculado ao merchant order
        foreach ($mo['payments'] ?? [] as $payment) {
            $this->handlePayment((string)$payment['id']);
        }

        return true;
    }

    /**
     * Mapeia status do MP para status interno do pedido.
     * Retorna null se o status não requer mudança de pedido.
     */
    private function mapPaymentStatus(string $status, string $detail): ?string
    {
        return match ($status) {
            'approved'     => OrderModel::STATUS_PAID,
            'pending',
            'in_process'   => OrderModel::STATUS_WAITING,
            'rejected',
            'cancelled',
            'refunded',
            'charged_back' => OrderModel::STATUS_CANCELLED,
            default        => null,
        };
    }

    private function queueStatusEmail(int $orderId, array $order, string $newStatus, string $mpStatus): void
    {
        $templateMap = [
            OrderModel::STATUS_PAID      => 'compra_aprovada',
            OrderModel::STATUS_WAITING   => 'pix_gerado',
            OrderModel::STATUS_CANCELLED => null,
        ];

        $template = $templateMap[$newStatus] ?? null;

        // pix_gerado só para PIX pendente; cartão/boleto pendente não envia
        if ($newStatus === OrderModel::STATUS_WAITING
            && ($order['payment_method'] ?? '') !== 'pix') {
            $template = null;
        }

        if ($template === null) {
            return;
        }

        $payload = json_encode([
            'order_id'       => $orderId,
            'customer_name'  => $order['customer_name'],
            'customer_email' => $order['customer_email'],
            'total'          => $order['total'],
            'payment_method' => $order['payment_method'],
        ]);

        db()->prepare(
            'INSERT INTO email_queue (template, to_email, to_name, payload, status, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())'
        )->execute([
            $template,
            $order['customer_email'],
            $order['customer_name'],
            $payload,
            'pending',
        ]);
    }

    // ─── Validação de Assinatura ─────────────────────────────────────────────

    /**
     * Valida assinatura do webhook (x-signature header do MP).
     * Retorna false se assinatura inválida — rejeitar request.
     */
    public function validateSignature(string $rawBody, string $signatureHeader, string $requestId, string $dataId): bool
    {
        $secret = getenv('MP_WEBHOOK_SECRET');

        // Se secret não configurado, pula validação (desenvolvimento)
        if (empty($secret)) {
            return true;
        }

        // Formato: ts=<timestamp>,v1=<hash>
        $parts = [];
        foreach (explode(',', $signatureHeader) as $part) {
            [$k, $v] = explode('=', $part, 2);
            $parts[trim($k)] = trim($v);
        }

        $ts   = $parts['ts']  ?? '';
        $hash = $parts['v1']  ?? '';

        if (empty($ts) || empty($hash)) {
            return false;
        }

        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$ts};";
        $expected = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expected, $hash);
    }

    // ─── HTTP ────────────────────────────────────────────────────────────────

    private function post(string $endpoint, array $body): array
    {
        return $this->request('POST', $endpoint, $body);
    }

    private function get(string $endpoint): array
    {
        return $this->request('GET', $endpoint, []);
    }

    private function request(string $method, string $endpoint, array $body): array
    {
        $url  = $this->apiBase . $endpoint;
        $json = $method === 'POST' ? json_encode($body) : null;

        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
            'X-Idempotency-Key: ' . bin2hex(random_bytes(16)),
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        }

        $responseBody = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log('[MP] cURL error on ' . $method . ' ' . $endpoint . ': ' . $curlError);
            return ['error' => 'curl_error', 'message' => $curlError];
        }

        $decoded = json_decode($responseBody ?: '{}', true) ?? [];

        if ($httpCode >= 400) {
            error_log('[MP] HTTP ' . $httpCode . ' on ' . $method . ' ' . $endpoint . ': ' . $responseBody);
            return array_merge($decoded, ['error' => 'http_' . $httpCode]);
        }

        return $decoded;
    }
}
