<?php

/**
 * Detecta carrinhos abandonados e enfileira e-mail de recuperação.
 * Sugerido: a cada hora via crontab.
 *   0 * * * * php /var/www/cron/check_abandoned_carts.php >> /var/log/maia-cron.log 2>&1
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('CRON_CONTEXT', true);

require ROOT_PATH . '/app/autoload.php';
require ROOT_PATH . '/config/database.php';

use Maia\Services\BrevoService;

try {
    $config         = require ROOT_PATH . '/config/app.php';
    $abandonedAfter = (int)($config['cart']['abandoned_after_minutes'] ?? 60);
    $appUrl         = rtrim(getenv('APP_URL') ?: 'https://maiasuplementos.com.br', '/');

    // Carrinhos com e-mail, não notificados, atualizados há X minutos (máx 7 dias)
    $stmt = db()->prepare(
        'SELECT cs.session_id, cs.user_email, cs.items
           FROM cart_sessions cs
          WHERE cs.user_email IS NOT NULL
            AND cs.notified_at IS NULL
            AND cs.updated_at <= DATE_SUB(NOW(), INTERVAL ? MINUTE)
            AND cs.updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
          LIMIT 100'
    );
    $stmt->execute([$abandonedAfter]);
    $carts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    $brevo = new BrevoService();
    $count = 0;

    foreach ($carts as $cart) {
        $email   = $cart['user_email'];
        $cartUrl = $appUrl . '/carrinho?recover=' . urlencode($cart['session_id']);

        $brevo->queue('carrinho_abandonado', $email, 'Cliente', [
            'cart_url' => $cartUrl,
        ]);

        db()->prepare(
            'UPDATE cart_sessions SET notified_at = NOW() WHERE session_id = ?'
        )->execute([$cart['session_id']]);

        $count++;
    }

    $ts = date('Y-m-d H:i:s');
    echo "[{$ts}] check_abandoned_carts: {$count} e-mail(s) enfileirado(s)\n";
} catch (\Throwable $e) {
    $ts = date('Y-m-d H:i:s');
    echo "[{$ts}] ERRO check_abandoned_carts: " . $e->getMessage() . "\n";
    error_log('[cron:check_abandoned_carts] ' . $e->getMessage());
    exit(1);
}
