<?php

declare(strict_types=1);

namespace Maia\Controllers;

use Maia\Services\MercadoPagoService;

class WebhookController extends BaseController
{
    public function mercadopago(array $params = []): void
    {
        $raw = file_get_contents('php://input');

        if (empty($raw)) {
            http_response_code(400);
            exit;
        }

        // Valida assinatura antes de processar
        $signature = $_SERVER['HTTP_X_SIGNATURE']    ?? '';
        $requestId = $_SERVER['HTTP_X_REQUEST_ID']   ?? '';
        $data      = json_decode($raw, true)          ?? [];
        $dataId    = (string)($data['data']['id']     ?? $data['id'] ?? '');

        $mp = new MercadoPagoService();

        if (!$mp->validateSignature($raw, $signature, $requestId, $dataId)) {
            error_log('[Webhook] Assinatura MP inválida. IP: ' . $this->clientIp());
            http_response_code(401);
            exit;
        }

        if (!isset($data['type']) && !isset($data['topic'])) {
            http_response_code(200);
            echo 'OK';
            exit;
        }

        try {
            $mp->processWebhook($data);
        } catch (\Throwable $e) {
            error_log('[Webhook] Erro ao processar: ' . $e->getMessage() . ' Data: ' . $raw);
            // Retorna 200 para o MP não reenviar — erro já logado
        }

        http_response_code(200);
        echo 'OK';
        exit;
    }
}
