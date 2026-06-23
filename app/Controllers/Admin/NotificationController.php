<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;
use Maia\Helpers\CSRF;
use Maia\Services\NotificationService;

class NotificationController extends BaseController
{
    public function index(array $params = []): void
    {
        Auth::requireAdmin();

        $page   = max(1, (int)($_GET['pagina'] ?? 1));
        $limit  = 30;
        $offset = ($page - 1) * $limit;
        $filter = $_GET['filtro'] ?? '';

        $where  = '';
        $params = [];

        if ($filter === 'nao_lidas') {
            $where    = 'WHERE `read` = 0';
        } elseif ($filter === 'lidas') {
            $where    = 'WHERE `read` = 1';
        }

        $stmt = db()->prepare(
            "SELECT * FROM notifications {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        $notifications = $stmt->fetchAll();

        $countStmt = db()->query("SELECT COUNT(*) FROM notifications {$where}");
        $total     = (int)$countStmt->fetchColumn();
        $unread    = NotificationService::countUnread();

        $this->render('admin/notifications/index', [
            'pageTitle'     => 'Notificações | Admin Maia',
            'notifications' => $notifications,
            'total'         => $total,
            'unread'        => $unread,
            'page'          => $page,
            'totalPages'    => (int)ceil($total / $limit),
            'filter'        => $filter,
            'flash'         => $this->getFlash(),
        ], 'admin');
    }

    public function markRead(array $params): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        if ($id > 0) {
            NotificationService::markRead($id);
        }

        $this->redirect('/admin/notificacoes');
    }

    public function markAllRead(array $params = []): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        NotificationService::markAllRead();

        $this->flash('success', 'Todas as notificações marcadas como lidas.');
        $this->redirect('/admin/notificacoes');
    }
}
