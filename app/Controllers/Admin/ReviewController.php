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
        Auth::requireAdminRole();

        $status = $_GET['status'] ?? 'pending';
        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $status = 'pending';
        }

        $page = max(1, (int)($_GET['pagina'] ?? 1));
        $limit = 30;
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
            'pageTitle' => 'Avaliações | Admin Maia',
            'reviews' => $reviews,
            'total' => $total,
            'page' => $page,
            'totalPages' => (int)ceil($total / $limit),
            'status' => $status,
            'flash' => $this->getFlash(),
        ], 'admin');
    }

    public function approve(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        db()->prepare("UPDATE reviews SET status = 'approved', rejection_reason = NULL WHERE id = ?")
            ->execute([$id]);

        $this->flash('success', 'Avaliacao aprovada.');
        $this->redirect('/admin/avaliacoes');
    }

    public function reject(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        $reason = trim((string)($_POST['rejection_reason'] ?? ''));

        db()->prepare("UPDATE reviews SET status = 'rejected', rejection_reason = ? WHERE id = ?")
            ->execute([$reason !== '' ? $reason : null, $id]);

        $this->flash('success', 'Avaliacao rejeitada.');
        $this->redirect('/admin/avaliacoes');
    }

    public function bulk(array $params = []): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $action = (string)($_POST['bulk_action'] ?? '');
        $ids = array_values(array_filter(array_map('intval', (array)($_POST['review_ids'] ?? []))));

        if (!in_array($action, ['approve', 'reject'], true) || empty($ids)) {
            $this->flash('error', 'Selecione avaliacoes e uma acao valida.');
            $this->redirect('/admin/avaliacoes');
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        if ($action === 'approve') {
            db()->prepare("UPDATE reviews SET status = 'approved', rejection_reason = NULL WHERE id IN ({$placeholders})")
                ->execute($ids);
            $this->flash('success', 'Avaliacoes aprovadas.');
        } else {
            $reason = trim((string)($_POST['rejection_reason'] ?? ''));
            db()->prepare("UPDATE reviews SET status = 'rejected', rejection_reason = ? WHERE id IN ({$placeholders})")
                ->execute(array_merge([$reason !== '' ? $reason : null], $ids));
            $this->flash('success', 'Avaliacoes rejeitadas.');
        }

        $this->redirect('/admin/avaliacoes');
    }
}
