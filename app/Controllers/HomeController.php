<?php

declare(strict_types=1);

namespace Maia\Controllers;

use Maia\Models\ProductModel;
use Maia\Models\CategoryModel;
use Maia\Helpers\Cache;

class HomeController extends BaseController
{
    public function index(array $params = []): void
    {
        $products   = new ProductModel();
        $categories = new CategoryModel();
        $this->trackVisit();

        $settings = Cache::remember('home_settings', 600, fn() => $this->getSettings());

        $this->render('home/index', [
            'pageTitle'   => $settings['store_name'] ?? 'Maia Suplementos',
            'categories'  => Cache::remember('home_categories', 300, fn() => $categories->getActiveWithProducts()),
            'featured'    => Cache::remember('home_featured', 300, fn() => $products->getFeatured(8)),
            'bestsellers' => Cache::remember('home_bestsellers', 300, fn() => $products->getBestsellers(8)),
            'combos'      => Cache::remember('home_combos', 300, fn() => $this->getActiveCombos()),
            'reviews'     => Cache::remember('home_reviews', 600, fn() => $this->getApprovedReviews(6)),
            'faq'         => Cache::remember('home_faq', 600, fn() => $this->getFaqPage()),
            'settings'    => $settings,
            'flash'       => $this->getFlash(),
        ]);
    }

    private function getActiveCombos(): array
    {
        return db()->query(
            'SELECT c.*, c.price AS total_price
               FROM combos c
              WHERE c.active = 1
              ORDER BY c.id DESC'
        )->fetchAll();
    }

    private function getApprovedReviews(int $limit): array
    {
        $stmt = db()->prepare(
            'SELECT r.*, u.name AS user_name, p.name AS product_name, p.slug AS product_slug
               FROM reviews r
               LEFT JOIN users    u ON u.id = r.user_id
               LEFT JOIN products p ON p.id = r.product_id
              WHERE r.status = ?
              ORDER BY r.created_at DESC
              LIMIT ?'
        );
        $stmt->execute(['approved', $limit]);
        return $stmt->fetchAll();
    }

    private function getSettings(): array
    {
        $rows = db()->query('SELECT `key`, `value` FROM settings')->fetchAll();
        return array_column($rows, 'value', 'key');
    }

    private function getFaqPage(): ?array
    {
        $stmt = db()->prepare('SELECT title, content FROM pages WHERE slug = ? AND active = 1 LIMIT 1');
        $stmt->execute(['faq']);
        $page = $stmt->fetch();

        return $page ?: [
            'title' => 'Perguntas Frequentes',
            'content' => '<h3>Os produtos sao originais?</h3><p>Sim. Trabalhamos apenas com produtos de procedencia e marcas reconhecidas.</p><h3>Entregam para todo o Brasil?</h3><p>Sim. O prazo depende da regiao e da modalidade escolhida.</p><h3>Posso acompanhar meu pedido?</h3><p>Sim. Depois da compra voce acompanha o status pela sua conta e recebe atualizacoes por e-mail.</p>',
        ];
    }

    private function trackVisit(): void
    {
        try {
            $stmt = db()->prepare(
                'INSERT INTO funnel_events (session_id, user_id, step, ip, user_agent)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                session_id(),
                $_SESSION['user_id'] ?? null,
                'visit',
                $this->clientIp(),
                mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            ]);
        } catch (\Throwable $e) {
            error_log('[Funnel] visit track failed: ' . $e->getMessage());
        }
    }
}
