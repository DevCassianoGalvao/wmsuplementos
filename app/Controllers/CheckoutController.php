<?php

declare(strict_types=1);

namespace Maia\Controllers;

use Maia\Models\OrderModel;
use Maia\Models\UserModel;
use Maia\Models\ProductModel;
use Maia\Models\ComboModel;
use Maia\Services\CartService;
use Maia\Services\MercadoPagoService;
use Maia\Helpers\CSRF;
use Maia\Helpers\Validator;
use Maia\Helpers\Sanitizer;

class CheckoutController extends BaseController
{
    private CartService $cart;

    public function __construct()
    {
        parent::__construct();
        $this->cart = new CartService();
    }

    public function index(array $params = []): void
    {
        if ($this->cart->isEmpty()) {
            $this->redirect('/carrinho');
        }

        // Funil
        $this->trackFunnel('checkout_start');

        $this->render('checkout/index', [
            'pageTitle' => 'Finalizar Compra | WM Suplementos',
            'items'     => $this->cart->getItems(),
            'subtotal'  => $this->cart->subtotal(),
            'discount'  => $this->cart->discount(),
            'shipping'  => $this->cart->shippingFee(),
            'freeShippingRemaining' => $this->cart->freeShippingRemaining(),
            'total'     => $this->cart->total(),
            'coupon'    => $this->cart->getAppliedCoupon(),
            'user'      => $this->getLoggedUser(),
            'flash'     => $this->getFlash(),
        ]);
    }

    public function store(array $params = []): void
    {
        CSRF::verify();

        if ($this->cart->isEmpty()) {
            $this->redirect('/carrinho');
        }

        $name   = Sanitizer::plainText($_POST['name']   ?? '');
        $email  = Sanitizer::email($_POST['email']      ?? '');
        $phone  = Sanitizer::onlyDigits($_POST['phone'] ?? '');
        $method = $_POST['payment_method'] ?? '';
        $address = $this->extractBoletoAddress();

        $v = new Validator(['name' => $name, 'email' => $email, 'phone' => $phone, 'payment_method' => $method]);
        $v->required('name')->maxLen('name', 150)
          ->required('email')->email('email')
          ->required('phone')->phone('phone', 'Telefone')
          ->required('payment_method')->in('payment_method', ['pix', 'cartao', 'boleto'], 'Forma de pagamento');

        if ($method === 'boleto') {
            foreach (['zip_code', 'address', 'address_number', 'neighborhood', 'city', 'state'] as $field) {
                if (($address[$field] ?? '') === '') {
                    $this->flash('error', 'Preencha o endereco completo para pagamento por boleto.');
                    $this->redirect('/finalizar-compra');
                }
            }
        }

        if ($v->fails()) {
            $this->flash('error', implode(' ', $v->errors()));
            $this->redirect('/finalizar-compra');
        }

        $stockError = $this->validateCartStock();
        if ($stockError !== null) {
            $this->flash('error', $stockError);
            $this->redirect('/carrinho');
        }

        // Persiste e-mail no cart_sessions para recuperação de abandono
        $this->cart->persist($email);

        $orderModel = new OrderModel();
        $items      = [];
        foreach ($this->cart->getItems() as $item) {
            $items[] = [
                'product_id'   => $item['product_id'] ?? null,
                'combo_id'     => $item['combo_id'] ?? null,
                'product_name' => $item['product_name'],
                'price'        => $item['price'],
                'quantity'     => $item['quantity'],
                'subtotal'     => round($item['price'] * $item['quantity'], 2),
            ];
        }

        $coupon = $this->cart->getAppliedCoupon();

        $orderId = $orderModel->create([
            'user_id'        => $_SESSION['user_id'] ?? null,
            'customer_name'  => $name,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'subtotal'       => $this->cart->subtotal(),
            'discount'       => $this->cart->discount(),
            'total'          => $this->cart->total(),
            'coupon_id'      => $coupon['id']   ?? null,
            'coupon_code'    => $coupon['code'] ?? null,
            'payment_method' => $method,
            'notes'          => $this->formatOrderNotes($method, $address),
            'items'          => $items,
        ]);

        // Cria preferência no Mercado Pago
        $mpResult = $this->createMercadoPagoPreference($orderId, $email, $name, $method);

        if (empty($mpResult['init_point'])) {
            $orderModel->updateStatus($orderId, OrderModel::STATUS_CANCELLED, 'Falha ao iniciar pagamento Mercado Pago.');
            $this->flash('error', 'Nao foi possivel iniciar o pagamento. Tente novamente em instantes.');
            $this->redirect('/finalizar-compra');
        }

        // Funil
        $this->trackFunnel('purchase', $orderId);
        $_SESSION['last_order_id'] = $orderId;

        // Limpa carrinho após criar pedido com MP
        $this->cart->clear();

        $this->redirect('/pedido/confirmacao/' . $orderId . '?mp=' . urlencode($mpResult['init_point'] ?? ''));
    }

    public function confirmation(array $params): void
    {
        $orderId = (int)($params['id'] ?? 0);

        $orderModel = new OrderModel();
        $order      = $orderModel->findById($orderId);

        if (!$order) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        $orderUserId = (int)($order['user_id'] ?? 0);
        $lastOrderId = (int)($_SESSION['last_order_id'] ?? 0);

        if ($orderUserId > 0 && (int)$userId !== $orderUserId) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        if ($orderUserId === 0 && $lastOrderId !== $orderId) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('checkout/confirmation', [
            'pageTitle' => 'Pedido Confirmado | WM Suplementos',
            'order'     => $order,
            'mpUrl'     => $_GET['mp'] ?? '',
        ]);
    }

    private function getLoggedUser(): ?array
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return null;
        }
        return (new UserModel())->findById((int)$userId);
    }

    private function createMercadoPagoPreference(int $orderId, string $email, string $name, string $method): array
    {
        $orderModel = new OrderModel();
        $order      = $orderModel->findById($orderId);

        if (!$order) {
            return ['init_point' => '', 'id' => ''];
        }

        $mp     = new MercadoPagoService();
        $result = $mp->createPreference($order, $order['items'] ?? []);

        // Persiste preference_id no pedido para rastreio
        if (!empty($result['id'])) {
            db()->prepare('UPDATE orders SET payment_id = ? WHERE id = ?')
                ->execute([$result['id'], $orderId]);
        }

        return $result;
    }

    private function validateCartStock(): ?string
    {
        foreach ($this->cart->getItems() as $item) {
            $quantity = max(1, (int)($item['quantity'] ?? 1));

            if (($item['type'] ?? 'product') === 'combo' && !empty($item['combo_id'])) {
                $combo = (new ComboModel())->findById((int)$item['combo_id']);
                if (!$combo || empty($combo['active'])) {
                    return 'Um combo do carrinho nao esta mais disponivel.';
                }

                foreach (($combo['items'] ?? []) as $comboItem) {
                    $needed = (int)$comboItem['quantity'] * $quantity;
                    if ((int)$comboItem['stock'] < $needed) {
                        return 'Estoque insuficiente para o combo ' . $combo['name'] . '.';
                    }
                }

                continue;
            }

            if (empty($item['product_id'])) {
                return 'Um item do carrinho nao esta mais disponivel.';
            }

            $product = (new ProductModel())->findById((int)$item['product_id']);
            if (!$product || empty($product['active'])) {
                return 'O produto ' . ($item['product_name'] ?? '') . ' nao esta mais disponivel.';
            }

            if ((int)$product['stock'] < $quantity) {
                return 'Estoque insuficiente para ' . $product['name'] . '.';
            }
        }

        return null;
    }

    private function extractBoletoAddress(): array
    {
        return [
            'zip_code'       => Sanitizer::onlyDigits($_POST['zip_code'] ?? ''),
            'address'        => Sanitizer::plainText($_POST['address'] ?? ''),
            'address_number' => Sanitizer::plainText($_POST['address_number'] ?? ''),
            'neighborhood'   => Sanitizer::plainText($_POST['neighborhood'] ?? ''),
            'city'           => Sanitizer::plainText($_POST['city'] ?? ''),
            'state'          => strtoupper(substr(Sanitizer::plainText($_POST['state'] ?? ''), 0, 2)),
        ];
    }

    private function formatAddressNote(array $address): string
    {
        return 'Endereco boleto: '
            . $address['address'] . ', ' . $address['address_number']
            . ' - ' . $address['neighborhood']
            . ' - ' . $address['city'] . '/' . $address['state']
            . ' - CEP ' . $address['zip_code'];
    }

    private function formatOrderNotes(string $method, array $address): ?string
    {
        $notes = [];

        if ($method === 'boleto') {
            $notes[] = $this->formatAddressNote($address);
        }

        $shipping = $this->cart->shippingFee();
        if ($shipping > 0) {
            $notes[] = 'Frete: R$ ' . number_format($shipping, 2, ',', '.');
        }

        return $notes !== [] ? implode("\n", $notes) : null;
    }

    private function trackFunnel(string $step, ?int $orderId = null): void
    {
        $stmt = db()->prepare(
            'INSERT INTO funnel_events (session_id, user_id, step, order_id, ip) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([session_id(), $_SESSION['user_id'] ?? null, $step, $orderId, $this->clientIp()]);
    }
}
