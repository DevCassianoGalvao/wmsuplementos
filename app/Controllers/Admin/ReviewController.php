<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;
use Maia\Helpers\CSRF;

class ReviewController extends BaseController
{
    public function index(array $params = []): void
    {
        Auth::requireAdmin();

        $status = $_GET['status'] ?? 'pending';
        $page   = max(1, (int)($_GET['pagina'] ?? 1));
        $limit  = 30;
        $offset = ($page - 1) * $limit;

        $stmt = db()->prepare(
            'SELECT r.*, p.name AS product_name, o.customer_name
               FROM reviews r
               JOIN products p ON p.id = r.product_id
               LEFT JOIN orders o ON o.id = r.order_id
              WHERE r.status = ?
              ORDER BY r.created_at DESC
              LIMIT ? OFFSET ?'
        );
        $stmt->execute([$status, $limit, $offset]);
        $reviews = $stmt->fetchAll();

        $stmt = db()->prepare('SELECT COUNT(*) FROM reviews WHERE status = ?');
        $stmt->execute([$status]);
        $total = (int)$stmt->fetchColumn();

        $this->render('admin/reviews/index', [
            'pageTitle'  => 'Avaliações | Admin Maia',
            'reviews'    => $reviews,
            'total'      => $total,
            'page'       => $page,
            'totalPages' => (int)ceil($total / $limit),
            'status'     => $status,
            'flash'      => $this->getFlash(),
        ], 'admin');
    }

    public function approve(array $params): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        db()->prepare("UPDATE reviews SET status = 'approved' WHERE id = ?")->execute([$id]);

        $this->flash('success', 'Avaliação aprovada.');
        $this->redirect('/admin/avaliacoes');
    }

    public function reject(array $params): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        db()->prepare("UPDATE reviews SET status = 'rejected' WHERE id = ?")->execute([$id]);

        $this->flash('success', 'Avaliação rejeitada.');
        $this->redirect('/admin/avaliacoes');
    }
}
