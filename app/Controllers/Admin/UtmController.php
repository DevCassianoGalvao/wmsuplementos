<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;
use Maia\Helpers\CSRF;
use Maia\Helpers\Sanitizer;

class UtmController extends BaseController
{
    public function index(array $params = []): void
    {
        Auth::requireAdminRole();

        $page  = max(1, (int)($_GET['pagina'] ?? 1));
        $limit = 25;
        $offset = ($page - 1) * $limit;

        $stmt = db()->prepare(
            'SELECT ul.*, a.name AS created_by_name
               FROM utm_links ul
               LEFT JOIN admin_users a ON a.id = ul.created_by
              ORDER BY ul.created_at DESC
              LIMIT ? OFFSET ?'
        );
        $stmt->execute([$limit, $offset]);
        $links = $stmt->fetchAll();

        $total = (int)db()->query('SELECT COUNT(*) FROM utm_links')->fetchColumn();

        $this->render('admin/utm/index', [
            'pageTitle'  => 'UTM Builder | Admin WM',
            'links'      => $links,
            'total'      => $total,
            'page'       => $page,
            'totalPages' => (int)ceil($total / $limit),
            'flash'      => $this->getFlash(),
        ], 'admin');
    }

    public function store(array $params = []): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $baseUrl  = filter_var(trim($_POST['base_url'] ?? ''), FILTER_VALIDATE_URL);
        $source   = Sanitizer::plainText($_POST['source']   ?? '');
        $medium   = Sanitizer::plainText($_POST['medium']   ?? '');
        $campaign = Sanitizer::plainText($_POST['campaign'] ?? '');
        $content  = Sanitizer::plainText($_POST['content']  ?? '');

        if (!$baseUrl) {
            $this->flash('error', 'URL base inválida.');
            $this->redirect('/admin/utm');
        }

        if (empty($source) || empty($medium) || empty($campaign)) {
            $this->flash('error', 'Source, Medium e Campaign são obrigatórios.');
            $this->redirect('/admin/utm');
        }

        $params = array_filter([
            'utm_source'   => $source,
            'utm_medium'   => $medium,
            'utm_campaign' => $campaign,
            'utm_content'  => $content ?: null,
        ]);

        $fullUrl = $baseUrl . (str_contains($baseUrl, '?') ? '&' : '?') . http_build_query($params);

        db()->prepare(
            'INSERT INTO utm_links (base_url, source, medium, campaign, content, full_url, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $baseUrl,
            $source,
            $medium,
            $campaign,
            $content ?: null,
            $fullUrl,
            $_SESSION['admin_id'] ?? null,
        ]);

        $this->flash('success', 'Link UTM gerado com sucesso.');
        $this->redirect('/admin/utm');
    }

    public function delete(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        db()->prepare('DELETE FROM utm_links WHERE id = ?')->execute([$id]);

        $this->flash('success', 'Link removido.');
        $this->redirect('/admin/utm');
    }
}
