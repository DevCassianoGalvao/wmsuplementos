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

        $model   = new OrderModel();
        $summary = $model->getSalesSummary('month');
        $daily   = $model->getDailySales(30);
        $top     = $model->getTopProducts((int)date('m'), (int)date('Y'));

        // Contagens rápidas
        $counts = $this->getCounts();

        $this->render('admin/dashboard/index', [
            'pageTitle' => 'Dashboard | Admin Maia',
            'summary'   => $summary,
            'daily'     => $daily,
            'top'       => $top,
            'counts'    => $counts,
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
}
