<?php

declare(strict_types=1);

namespace Maia\Controllers;

use Maia\Models\UserModel;
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

    public function brevo(array $params = []): void
    {
        $secret = getenv('BREVO_WEBHOOK_SECRET') ?: '';
        if ($secret !== '') {
            $provided = $_SERVER['HTTP_X_BREVO_WEBHOOK_SECRET'] ?? ($_GET['token'] ?? '');
            if (!hash_equals($secret, (string)$provided)) {
                error_log('[Webhook] Brevo secret invalido. IP: ' . $this->clientIp());
                http_response_code(401);
                echo 'Unauthorized';
                exit;
            }
        }

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw ?: '{}', true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo 'Invalid JSON';
            exit;
        }

        $event = strtolower((string)($payload['event'] ?? $payload['event_type'] ?? ''));
        $email = (string)($payload['email'] ?? $payload['recipient'] ?? '');

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)
            && in_array($event, ['unsubscribed', 'unsubscribe', 'spam', 'complaint'], true)) {
            (new UserModel())->setEmailOptOut($email);
        }

        http_response_code(200);
        echo 'OK';
        exit;
    }
}
