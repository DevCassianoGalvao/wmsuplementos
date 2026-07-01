<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;
use Maia\Models\OrderModel;

class DashboardController extends BaseController
{
    public function index(array $params = []): void
    {
        Auth::requireAdmin();
        if (!Auth::isAdmin()) {
            $this->redirect('/admin/pedidos');
        }

        $model   = new OrderModel();
        $summary = $model->getSalesSummary('month');
        $daily   = $model->getDailySales(90);
        $top     = $model->getTopProducts((int)date('m'), (int)date('Y'));

        // Contagens rápidas
        $counts = $this->getCounts();

        $this->render('admin/dashboard/index', [
            'pageTitle' => 'Dashboard | Admin Maia',
            'summary'   => $summary,
            'daily'     => $daily,
            'top'       => $top,
            'counts'    => $counts,
            'funnel'    => $this->getFunnelStats(),
        ], 'admin');
    }

    private function getCounts(): array
    {
        $today = date('Y-m-d');

        $stmt = db()->prepare('SELECT COUNT(*) FROM orders WHERE DATE(created_at) = ?');
        $stmt->execute([$today]);
        $ordersToday = (int)$stmt->fetchColumn();

        $stmt = db()->prepare('SELECT COUNT(*) FROM orders WHERE status = ?');
        $stmt->execute(['aguardando_pagamento']);
        $pendingOrders = (int)$stmt->fetchColumn();

        $totalUsers = (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();

        $stmt = db()->prepare('SELECT COUNT(*) FROM reviews WHERE status = ?');
        $stmt->execute(['pending']);
        $pendingReviews = (int)$stmt->fetchColumn();

        $stmt = db()->query('SELECT COUNT(*) FROM products WHERE stock <= stock_alert_threshold AND active = 1');
        $lowStock = (int)$stmt->fetchColumn();

        return [
            'orders_today'    => $ordersToday,
            'pending_orders'  => $pendingOrders,
            'total_users'     => $totalUsers,
            'pending_reviews' => $pendingReviews,
            'low_stock'       => $lowStock,
        ];
    }

    private function getFunnelStats(): array
    {
        // funnel_events pode não existir — retorna vazio sem quebrar o dashboard
        try {
            $steps = [
                'visit'          => 'Visitas',
                'product_view'   => 'Produto',
                'add_to_cart'    => 'Carrinho',
                'checkout_start' => 'Checkout',
                'purchase'       => 'Compra',
            ];

            $stmt = db()->query(
                "SELECT step, COUNT(DISTINCT COALESCE(session_id, CONCAT('evt-', id))) AS total
                   FROM funnel_events
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                  GROUP BY step"
            );

            $counts = [];
            foreach ($stmt->fetchAll() as $row) {
                $counts[$row['step']] = (int)$row['total'];
            }

            $visitTotal = max(1, $counts['visit'] ?? 0);
            $funnel = [];
            foreach ($steps as $step => $label) {
                $total = $counts[$step] ?? 0;
                $funnel[] = [
                    'step'       => $step,
                    'label'      => $label,
                    'total'      => $total,
                    'conversion' => round(($total / $visitTotal) * 100, 1),
                ];
            }

            return $funnel;
        } catch (\Throwable) {
            return [];
        }
    }
}
