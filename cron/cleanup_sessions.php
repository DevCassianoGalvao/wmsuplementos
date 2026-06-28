<?php

/**
 * Remove sessões expiradas e cart_sessions antigas.
 * Sugerido: diário às 4h.
 *   0 4 * * * php /var/www/cron/cleanup_sessions.php >> /var/log/maia-cron.log 2>&1
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('CRON_CONTEXT', true);

require ROOT_PATH . '/config/env.php';
load_env(ROOT_PATH);
require ROOT_PATH . '/app/autoload.php';
require ROOT_PATH . '/config/database.php';

try {
    // Remove cart_sessions não notificados com mais de 30 dias
    $stmt = db()->prepare(
        'DELETE FROM cart_sessions
          WHERE notified_at IS NULL
            AND updated_at < DATE_SUB(NOW(), INTERVAL 30 DAY)'
    );
    $stmt->execute();
    $cartAbandoned = $stmt->rowCount();

    // Remove cart_sessions notificados com mais de 90 dias
    $stmt = db()->prepare(
        'DELETE FROM cart_sessions
          WHERE notified_at IS NOT NULL
            AND updated_at < DATE_SUB(NOW(), INTERVAL 90 DAY)'
    );
    $stmt->execute();
    $cartNotified = $stmt->rowCount();

    // Remove tentativas de login antigas (mais de 24h)
    $stmt = db()->prepare(
        'DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)'
    );
    $stmt->execute();
    $loginAttempts = $stmt->rowCount();

    // Remove entradas antigas de email_queue (enviadas há mais de 60 dias)
    $stmt = db()->prepare(
        "DELETE FROM email_queue
          WHERE status IN ('sent', 'failed')
            AND created_at < DATE_SUB(NOW(), INTERVAL 60 DAY)"
    );
    $stmt->execute();
    $emailQueue = $stmt->rowCount();

    // Remove funnel_events com mais de 180 dias
    $stmt = db()->prepare(
        'DELETE FROM funnel_events WHERE created_at < DATE_SUB(NOW(), INTERVAL 180 DAY)'
    );
    $stmt->execute();
    $funnel = $stmt->rowCount();

    // Remove arquivos de rate limit com mais de 2h
    $rateFiles = \Maia\Middleware\RateLimiter::cleanup(7200);

    // Remove entradas de cache expiradas
    $cacheGc = \Maia\Helpers\Cache::gc();

    $ts = date('Y-m-d H:i:s');
    echo "[{$ts}] cleanup_sessions: "
        . "cart_abandoned={$cartAbandoned}, "
        . "cart_notified={$cartNotified}, "
        . "login_attempts={$loginAttempts}, "
        . "email_queue={$emailQueue}, "
        . "funnel_events={$funnel}, "
        . "rate_files={$rateFiles}, "
        . "cache_gc={$cacheGc}\n";
} catch (\Throwable $e) {
    $ts = date('Y-m-d H:i:s');
    echo "[{$ts}] ERRO cleanup_sessions: " . $e->getMessage() . "\n";
    error_log('[cron:cleanup_sessions] ' . $e->getMessage());
    exit(1);
}
