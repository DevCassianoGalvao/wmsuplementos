<?php

/**
 * Envia alerta de estoque baixo para admins e e-mail "estoque_voltou" para
 * clientes que solicitaram notificação (caso a tabela stock_alerts exista).
 * Sugerido: diário às 8h.
 *   0 8 * * * php /var/www/cron/stock_alerts.php >> /var/log/maia-cron.log 2>&1
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('CRON_CONTEXT', true);

require ROOT_PATH . '/config/env.php';
load_env(ROOT_PATH);
require ROOT_PATH . '/app/autoload.php';
require ROOT_PATH . '/config/database.php';

use Maia\Services\BrevoService;

try {
    $appUrl = rtrim(getenv('APP_URL') ?: 'https://wmsuplementos.com.br', '/');
    $brevo  = new BrevoService();

    // ── Alerta de estoque baixo para admin ─────────────────────────────────────
    $lowStock = db()->query(
        'SELECT p.id, p.name, p.slug, p.stock, p.stock_alert_threshold AS stock_alert
           FROM products p
          WHERE p.active = 1
            AND p.stock <= p.stock_alert_threshold
          ORDER BY p.stock ASC'
    )->fetchAll(\PDO::FETCH_ASSOC);

    if (!empty($lowStock)) {
        $adminEmails = db()->query(
            "SELECT email, name FROM admin_users WHERE active = 1 AND role IN ('admin', 'manager')"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $productList = implode(', ', array_map(
            fn($p) => $p['name'] . ' (' . $p['stock'] . ' un.)',
            $lowStock
        ));

        foreach ($adminEmails as $admin) {
            $brevo->queue('estoque_baixo_admin', $admin['email'], $admin['name'], [
                'product_list' => $productList,
                'count'        => count($lowStock),
                'stock_url'    => $appUrl . '/admin/estoque?filtro=baixo',
            ]);
        }
    }

    // ── "Estoque voltou" para clientes que pediram notificação ─────────────────
    // Verifica se tabela stock_notifications existe antes de consultar
    $tableExists = db()->query(
        "SELECT COUNT(*) FROM information_schema.tables
          WHERE table_schema = DATABASE()
            AND table_name = 'stock_notifications'"
    )->fetchColumn();

    $notificationsCount = 0;

    if ($tableExists) {
        $notifications = db()->query(
            "SELECT sn.id, sn.email, COALESCE(u.name, 'Cliente') AS customer_name, sn.product_id,
                    p.name AS product_name, p.slug AS product_slug, p.stock
               FROM stock_notifications sn
               JOIN products p ON p.id = sn.product_id
               LEFT JOIN users u ON u.id = sn.user_id
              WHERE sn.notified_at IS NULL
                AND p.stock > 0
              LIMIT 100"
        )->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($notifications as $notif) {
            $brevo->queue('estoque_voltou', $notif['email'], $notif['customer_name'] ?? 'Cliente', [
                'product_name' => $notif['product_name'],
                'product_url'  => $appUrl . '/produto/' . $notif['product_slug'],
            ]);

            db()->prepare(
                'UPDATE stock_notifications SET notified_at = NOW() WHERE id = ?'
            )->execute([$notif['id']]);

            $notificationsCount++;
        }
    }

    $ts = date('Y-m-d H:i:s');
    echo "[{$ts}] stock_alerts: estoque_baixo=" . count($lowStock)
        . ", notificacoes_enviadas={$notificationsCount}\n";
} catch (\Throwable $e) {
    $ts = date('Y-m-d H:i:s');
    echo "[{$ts}] ERRO stock_alerts: " . $e->getMessage() . "\n";
    error_log('[cron:stock_alerts] ' . $e->getMessage());
    exit(1);
}
