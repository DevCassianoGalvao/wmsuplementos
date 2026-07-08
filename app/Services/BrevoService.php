<?php

declare(strict_types=1);

namespace Maia\Services;

/**
 * Envia e-mails transacionais via API Brevo (ex-Sendinblue).
 * Fallback para SMTP se a API falhar (config/mail.php).
 */
class BrevoService
{
    private array $config;
    private array $templates;

    public function __construct()
    {
        $this->config    = require ROOT_PATH . '/config/mail.php';
        $this->templates = $this->loadTemplates();
    }

    // ─── Interface pública ────────────────────────────────────────────────────

    /**
     * Envia e-mail a partir de um slug de template e variáveis.
     * Retorna true em sucesso, false em falha definitiva.
     */
    public function send(string $templateSlug, string $toEmail, string $toName, array $vars = []): bool
    {
        $template = $this->templates[$templateSlug] ?? null;

        if (!$template) {
            error_log("[Brevo] Template não encontrado: {$templateSlug}");
            return false;
        }

        $subject = $this->interpolate($template['subject'], $vars);
        $html    = $this->interpolate($template['html_content'], $vars);

        $result = $this->sendViaApi($toEmail, $toName, $subject, $html);

        if (!$result) {
            $result = $this->sendViaSmtp($toEmail, $toName, $subject, $html);
        }

        return $result;
    }

    /**
     * Enfileira e-mail na tabela email_queue.
     * Usar quando não é necessário envio imediato.
     */
    public function queue(string $templateSlug, string $toEmail, string $toName, array $vars = []): void
    {
        db()->prepare(
            'INSERT INTO email_queue (template, to_email, to_name, payload, status, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())'
        )->execute([
            $templateSlug,
            $toEmail,
            $toName,
            json_encode($vars, JSON_UNESCAPED_UNICODE),
            'pending',
        ]);
    }

    public function syncContact(array $user): bool
    {
        $email = (string)($user['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $nameParts = preg_split('/\s+/', trim((string)($user['name'] ?? ''))) ?: [];
        $payload = [
            'email' => $email,
            'attributes' => [
                'FIRSTNAME' => $nameParts[0] ?? '',
                'LASTNAME' => count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '',
            ],
            'emailBlacklisted' => empty($user['email_opt_in']),
            'updateEnabled' => true,
        ];
        $listId = (int)($this->config['brevo']['list_id'] ?? 0);
        if ($listId > 0) {
            $payload['listIds'] = [$listId];
        }

        return $this->postToBrevo('/contacts', $payload, [201, 204]);
    }

    public function syncContacts(array $users): array
    {
        $synced = 0;
        $failed = 0;

        foreach ($users as $user) {
            if ($this->syncContact($user)) {
                $synced++;
            } else {
                $failed++;
            }
        }

        return ['synced' => $synced, 'failed' => $failed];
    }

    // ─── Processamento da Fila ────────────────────────────────────────────────

    /**
     * Processa até $limit itens pendentes da fila.
     * Chamado pelo cron process_email_queue.php.
     */
    public function processQueue(int $limit = 50): array
    {
        $maxAttempts = $this->config['queue']['max_attempts'] ?? 3;

        $stmt = db()->prepare(
            'SELECT * FROM email_queue
              WHERE status = ? AND attempts < ?
              ORDER BY scheduled_at ASC
              LIMIT ?'
        );
        $stmt->execute(['pending', $maxAttempts, $limit]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $processed = 0;
        $failed    = 0;

        foreach ($rows as $row) {
            $vars = json_decode($row['payload'] ?? '{}', true) ?? [];

            // Para webhooks brutos (type __mp_webhook__), pula
            if (str_starts_with($row['template'] ?? '', '__')) {
                $this->markProcessed((int)$row['id']);
                continue;
            }

            $toEmail = $row['to_email'] ?? ($vars['customer_email'] ?? '');
            $toName  = $row['to_name']  ?? ($vars['customer_name']  ?? 'Cliente');

            if (empty($toEmail)) {
                $this->markFailed((int)$row['id'], 'to_email vazio');
                $failed++;
                continue;
            }

            $this->incrementAttempts((int)$row['id']);

            $ok = $this->send($row['template'], $toEmail, $toName, $vars);

            if ($ok) {
                $this->markProcessed((int)$row['id']);
                $processed++;
            } else {
                $attempts = (int)$row['attempts'] + 1;
                if ($attempts >= $maxAttempts) {
                    $this->markFailed((int)$row['id'], 'max_attempts atingido');
                }
                $failed++;
            }
        }

        return ['processed' => $processed, 'failed' => $failed];
    }

    // ─── Envio via API Brevo ──────────────────────────────────────────────────

    private function sendViaApi(string $toEmail, string $toName, string $subject, string $html): bool
    {
        $apiKey = $this->config['brevo']['api_key'] ?? '';
        if (empty($apiKey)) {
            return false;
        }

        $payload = json_encode([
            'sender'      => [
                'email' => $this->config['brevo']['sender_email'] ?? '',
                'name'  => $this->config['brevo']['sender_name']  ?? 'WM Suplementos',
            ],
            'to'          => [['email' => $toEmail, 'name' => $toName]],
            'subject'     => $subject,
            'htmlContent' => $html,
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init($this->config['brevo']['api_url'] ?? 'https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Content-Type: application/json',
                'api-key: ' . $apiKey,
            ],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $body     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) {
            error_log("[Brevo] cURL error: {$err}");
            return false;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            error_log("[Brevo] HTTP {$httpCode}: {$body}");
            return false;
        }

        return true;
    }

    // ─── Fallback SMTP ────────────────────────────────────────────────────────

    private function postToBrevo(string $endpoint, array $payload, array $successCodes = [200, 201, 204]): bool
    {
        $apiKey = $this->config['brevo']['api_key'] ?? '';
        if ($apiKey === '') {
            return false;
        }

        $ch = curl_init('https://api.brevo.com/v3' . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'api-key: ' . $apiKey,
            ],
            CURLOPT_TIMEOUT => 12,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            error_log('[Brevo] cURL error on ' . $endpoint . ': ' . $err);
            return false;
        }

        if (!in_array($httpCode, $successCodes, true)) {
            error_log('[Brevo] HTTP ' . $httpCode . ' on ' . $endpoint . ': ' . $body);
            return false;
        }

        return true;
    }

    private function sendViaSmtp(string $toEmail, string $toName, string $subject, string $html): bool
    {
        $smtp = $this->config['smtp'] ?? [];
        if (empty($smtp['host'])) {
            return false;
        }

        $from    = $smtp['from_email'] ?? ($this->config['brevo']['sender_email'] ?? '');
        $fromName= $smtp['from_name']  ?? ($this->config['brevo']['sender_name'] ?? 'WM Suplementos');
        $host    = $smtp['host'];
        $port    = (int)($smtp['port'] ?? 587);
        $user    = $smtp['username'] ?? '';
        $pass    = $smtp['password'] ?? '';

        $boundary = bin2hex(random_bytes(8));
        $headers  = implode("\r\n", [
            "From: {$fromName} <{$from}>",
            "To: {$toName} <{$toEmail}>",
            "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=",
            "MIME-Version: 1.0",
            "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
        ]);

        $body = "--{$boundary}\r\n"
              . "Content-Type: text/html; charset=UTF-8\r\n"
              . "Content-Transfer-Encoding: base64\r\n\r\n"
              . chunk_split(base64_encode($html)) . "\r\n"
              . "--{$boundary}--";

        // PHP mail() como último recurso (sem autenticação SMTP)
        $result = mail($toEmail, $subject, $body, $headers);

        if (!$result) {
            error_log("[Brevo] SMTP fallback falhou para: {$toEmail}");
        }

        return $result;
    }

    // ─── Helpers de fila ─────────────────────────────────────────────────────

    private function markProcessed(int $id): void
    {
        db()->prepare(
            "UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE id = ?"
        )->execute([$id]);
    }

    private function markFailed(int $id, string $reason): void
    {
        db()->prepare(
            "UPDATE email_queue SET status = 'failed', error_message = ? WHERE id = ?"
        )->execute([$reason, $id]);
    }

    private function incrementAttempts(int $id): void
    {
        db()->prepare(
            'UPDATE email_queue SET attempts = attempts + 1 WHERE id = ?'
        )->execute([$id]);
    }

    // ─── Templates ───────────────────────────────────────────────────────────

    /**
     * Carrega templates da tabela scripts_config onde type = 'email'.
     * Fallback para templates embutidos se banco não estiver disponível.
     */
    private function loadTemplates(): array
    {
        try {
            $stmt = db()->query(
                "SELECT slug, subject, html_content FROM scripts_config WHERE type = 'email' AND active = 1"
            );
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $templates = [];
            foreach ($rows as $row) {
                $templates[$row['slug']] = $row;
            }

            return $templates ?: $this->builtinTemplates();
        } catch (\Throwable) {
            return $this->builtinTemplates();
        }
    }

    /**
     * Templates mínimos embutidos — usados quando scripts_config não tem registros.
     * Todas variáveis com formato {{VARIAVEL}}.
     */
    private function builtinTemplates(): array
    {
        $brand = 'WM Suplementos';
        return [
            'pix_gerado' => [
                'subject'      => 'Seu PIX foi gerado! | ' . $brand,
                'html_content' => '<p>Olá, {{customer_name}}!</p><p>Seu pedido <strong>#{{order_id}}</strong> foi criado. Pague o PIX para confirmar.</p><p>Total: R$ {{total}}</p>',
            ],
            'compra_aprovada' => [
                'subject'      => 'Compra confirmada! Pedido #{{order_id}} | ' . $brand,
                'html_content' => '<p>Olá, {{customer_name}}!</p><p>Seu pagamento foi aprovado! Pedido <strong>#{{order_id}}</strong> está em processamento.</p>',
            ],
            'em_preparacao' => [
                'subject'      => 'Seu pedido está sendo preparado | ' . $brand,
                'html_content' => '<p>Olá, {{customer_name}}!</p><p>Pedido <strong>#{{order_id}}</strong> está sendo preparado.</p>',
            ],
            'pedido_enviado' => [
                'subject'      => 'Pedido enviado! Rastreie agora | ' . $brand,
                'html_content' => '<p>Olá, {{customer_name}}!</p><p>Seu pedido <strong>#{{order_id}}</strong> foi enviado! Código de rastreio: <strong>{{tracking_code}}</strong></p>',
            ],
            'pedido_entregue' => [
                'subject'      => 'Pedido entregue! | ' . $brand,
                'html_content' => '<p>Olá, {{customer_name}}!</p><p>Seu pedido <strong>#{{order_id}}</strong> foi entregue! Obrigado pela compra.</p>',
            ],
            'solicitar_avaliacao' => [
                'subject'      => 'Como foi seu produto? Avalie agora | ' . $brand,
                'html_content' => '<p>Olá, {{customer_name}}!</p><p>Que tal avaliar <strong>{{product_name}}</strong>? <a href="{{review_url}}">Clique aqui</a></p>',
            ],
            'carrinho_abandonado' => [
                'subject'      => 'Você esqueceu algo... | ' . $brand,
                'html_content' => '<p>Olá!</p><p>Você deixou itens no carrinho. <a href="{{cart_url}}">Conclua sua compra</a>.</p>',
            ],
            'pix_expirado' => [
                'subject'      => 'Seu PIX expirou | ' . $brand,
                'html_content' => '<p>Olá, {{customer_name}}!</p><p>O PIX do pedido <strong>#{{order_id}}</strong> expirou. <a href="{{new_order_url}}">Faça um novo pedido</a>.</p>',
            ],
            'estoque_voltou' => [
                'subject'      => '{{product_name}} voltou ao estoque! | ' . $brand,
                'html_content' => '<p>Olá!</p><p><strong>{{product_name}}</strong> voltou ao estoque. <a href="{{product_url}}">Compre agora</a>!</p>',
            ],
        ];
    }

    private function interpolate(string $template, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $template = str_replace('{{' . strtoupper((string)$key) . '}}', (string)$value, $template);
            $template = str_replace('{{' . $key . '}}', (string)$value, $template);
        }
        return $template;
    }
}
