<?php

/**
 * Cancela pedidos PIX não pagos após 30 minutos e notifica cliente.
 * Sugerido: a cada 15 minutos.
 *   * /15 * * * * php /var/www/cron/check_pix_expired.php >> /var/log/maia-cron.log 2>&1
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('CRON_CONTEXT', true);

require ROOT_PATH . '/app/autoload.php';
require ROOT_PATH . '/config/database.php';

use Maia\Services\BrevoService;
use Maia\Models\OrderModel;

try {
    $appUrl = getenv('APP_URL') ?: 'https://maiasuplementos.com.br';

    // PIX expirado: aguardando_pagamento + método PIX + criado há mais de 30 min
    $stmt = db()->prepare(
        "SELECT id, customer_name, customer_email, total
           FROM orders
          WHERE status = ?
            AND payment_method = 'pix'
            AND payment_id IS NULL
            AND created_at <= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
          LIMIT 50"
    );
    $stmt->execute([OrderModel::STATUS_WAITING]);
    $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    $brevo = new BrevoService();
    $count = 0;

    foreach ($orders as $order) {
        $orderId = (int)$order['id'];

        // Cancela pedido
        db()->prepare(
            "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?"
        )->execute([OrderModel::STATUS_CANCELLED, $orderId]);

        db()->prepare(
            'INSERT INTO order_status_history (order_id, status, note, created_at) VALUES (?, ?, ?, NOW())'
        )->execute([$orderId, OrderModel::STATUS_CANCELLED, 'PIX expirado (cron)']);

        // Notifica cliente
        $brevo->queue('pix_expirado', $order['customer_email'], $order['customer_name'], [
            'order_id'      => $orderId,
            'customer_name' => $order['customer_name'],
            'total'         => number_format((float)$order['total'], 2, ',', '.'),
            'new_order_url' => $appUrl . '/produtos',
        ]);

        $count++;
    }

    $ts = date('Y-m-d H:i:s');
    echo "[{$ts}] check_pix_expired: {$count} pedido(s) cancelado(s)\n";
} catch (\Throwable $e) {
    $ts = date('Y-m-d H:i:s');
    echo "[{$ts}] ERRO check_pix_expired: " . $e->getMessage() . "\n";
    error_log('[cron:check_pix_expired] ' . $e->getMessage());
    exit(1);
}
