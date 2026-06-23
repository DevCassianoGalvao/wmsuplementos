<?php

/**
 * Processa fila de e-mails pendentes.
 * Sugerido: a cada 5 minutos via crontab.
 *   * /5 * * * * php /var/www/cron/process_email_queue.php >> /var/log/maia-cron.log 2>&1
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('CRON_CONTEXT', true);

require ROOT_PATH . '/app/autoload.php';
require ROOT_PATH . '/config/database.php';

use Maia\Services\BrevoService;

try {
    $brevo  = new BrevoService();
    $result = $brevo->processQueue(50);

    $ts = date('Y-m-d H:i:s');
    echo "[{$ts}] process_email_queue: processados={$result['processed']}, falhas={$result['failed']}\n";
} catch (\Throwable $e) {
    $ts = date('Y-m-d H:i:s');
    echo "[{$ts}] ERRO process_email_queue: " . $e->getMessage() . "\n";
    error_log('[cron:process_email_queue] ' . $e->getMessage());
    exit(1);
}
