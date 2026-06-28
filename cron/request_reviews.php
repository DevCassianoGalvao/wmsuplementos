<?php

/**
 * Envia solicitações de avaliação para pedidos entregues (status='entregue')
 * que ainda não possuem review records criados.
 * Sugerido: 1x por dia às 10h.
 *   0 10 * * * php /var/www/cron/request_reviews.php >> /var/log/maia-cron.log 2>&1
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('CRON_CONTEXT', true);

require ROOT_PATH . '/config/env.php';
load_env(ROOT_PATH);
require ROOT_PATH . '/app/autoload.php';
require ROOT_PATH . '/config/database.php';

use Maia\Services\BrevoService;
use Maia\Services\NotificationService;
use Maia\Models\OrderModel;

try {
    $appUrl = rtrim(getenv('APP_URL') ?: 'https://maiasuplementos.com.br', '/');
    $brevo  = new BrevoService();

    // Pedidos entregues há 2-7 dias sem nenhum review criado para o order_id
    $stmt = db()->prepare(
        "SELECT o.id, o.customer_name, o.customer_email, o.user_id
           FROM orders o
          WHERE o.status = ?
            AND o.updated_at BETWEEN DATE_SUB(NOW(), INTERVAL 7 DAY)
                                 AND DATE_SUB(NOW(), INTERVAL 2 DAY)
            AND NOT EXISTS (
                SELECT 1 FROM reviews r WHERE r.order_id = o.id
            )
          LIMIT 50"
    );
    $stmt->execute([OrderModel::STATUS_DELIVERED]);
    $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    $emailsSent = 0;

    foreach ($orders as $order) {
        $orderId = (int)$order['id'];

        // Busca itens do pedido
        $items = db()->prepare(
            'SELECT oi.product_id, oi.product_name
               FROM order_items oi
              WHERE oi.order_id = ? AND oi.product_id IS NOT NULL'
        );
        $items->execute([$orderId]);
        $products = $items->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($products)) {
            continue;
        }

        foreach ($products as $product) {
            $token = bin2hex(random_bytes(32)); // 64 chars hex

            // Cria review placeholder (rating=5 será sobrescrito pelo cliente)
            try {
                db()->prepare(
                    'INSERT INTO reviews (product_id, user_id, order_id, rating, status, review_token)
                     VALUES (?, ?, ?, 5, ?, ?)'
                )->execute([
                    (int)$product['product_id'],
                    $order['user_id'] ? (int)$order['user_id'] : null,
                    $orderId,
                    'pending',
                    $token,
                ]);
            } catch (\PDOException $e) {
                // Token duplicado (improvável com 64 chars hex) — pula
                error_log('[cron:request_reviews] Token collision: ' . $e->getMessage());
                continue;
            }

            $reviewUrl = $appUrl . '/avaliar/' . $token;

            $brevo->queue('solicitar_avaliacao', $order['customer_email'], $order['customer_name'], [
                'customer_name' => $order['customer_name'],
                'product_name'  => $product['product_name'],
                'review_url'    => $reviewUrl,
                'order_id'      => $orderId,
            ]);

            $emailsSent++;
        }
    }

    // Notifica admin se houver muitos pedidos prontos para avaliação
    if ($emailsSent > 0) {
        NotificationService::create(
            'review_requests',
            'Solicitações de avaliação enviadas',
            "{$emailsSent} e-mail(s) de avaliação enfileirado(s) para " . count($orders) . ' pedido(s) entregue(s).'
        );
    }

    $ts = date('Y-m-d H:i:s');
    echo "[{$ts}] request_reviews: {$emailsSent} e-mail(s) enfileirado(s) para " . count($orders) . " pedido(s)\n";
} catch (\Throwable $e) {
    $ts = date('Y-m-d H:i:s');
    echo "[{$ts}] ERRO request_reviews: " . $e->getMessage() . "\n";
    error_log('[cron:request_reviews] ' . $e->getMessage());
    exit(1);
}
