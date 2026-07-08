<?php

declare(strict_types=1);

namespace Maia\Controllers;

use Maia\Models\UserModel;

class WebhookController extends BaseController
{
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
