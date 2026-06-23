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

        $settings = Cache::remember('home_settings', 600, fn() => $this->getSettings());

        $this->render('home/index', [
            'pageTitle'   => $settings['store_name'] ?? 'Maia Suplementos',
            'categories'  => Cache::remember('home_categories', 300, fn() => $categories->getAllActive()),
            'featured'    => Cache::remember('home_featured', 300, fn() => $products->getFeatured(8)),
            'bestsellers' => Cache::remember('home_bestsellers', 300, fn() => $products->getBestsellers(8)),
            'combos'      => Cache::remember('home_combos', 300, fn() => $this->getActiveCombos()),
            'reviews'     => Cache::remember('home_reviews', 600, fn() => $this->getApprovedReviews(6)),
            'settings'    => $settings,
            'flash'       => $this->getFlash(),
        ]);
    }

    private function getActiveCombos(): array
    {
        return db()->query('SELECT * FROM combos WHERE active = 1 ORDER BY id DESC')->fetchAll();
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
}
