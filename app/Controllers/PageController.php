<?php

declare(strict_types=1);

namespace Maia\Controllers;

class PageController extends BaseController
{
    public function show(array $params): void
    {
        $slug = $params['slug'] ?? '';
        $this->renderBySlug($slug);
    }

    public function privacy(array $params = []): void
    {
        $this->renderBySlug('politica-de-privacidade');
    }

    public function terms(array $params = []): void
    {
        $this->renderBySlug('termos-de-uso');
    }

    private function renderBySlug(string $slug): void
    {
        if (empty($slug)) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $stmt = db()->prepare('SELECT * FROM pages WHERE slug = ? AND active = 1');
        $stmt->execute([$slug]);
        $page = $stmt->fetch();

        if (!$page) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('page/show', [
            'pageTitle' => $page['title'] . ' | Maia Suplementos',
            'metaDesc'  => $page['meta_description'] ?? '',
            'page'      => $page,
        ]);
    }
}
