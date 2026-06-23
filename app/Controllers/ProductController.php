<?php

declare(strict_types=1);

namespace Maia\Controllers;

use Maia\Models\ProductModel;

class ProductController extends BaseController
{
    public function show(array $params): void
    {
        $slug    = $params['slug'] ?? '';
        $model   = new ProductModel();
        $product = $model->findBySlug($slug);

        if (!$product) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        // Avaliações aprovadas
        $stmt = db()->prepare(
            'SELECT r.*, u.name AS user_name
               FROM reviews r
               LEFT JOIN users u ON u.id = r.user_id
              WHERE r.product_id = ? AND r.status = ?
              ORDER BY r.created_at DESC
              LIMIT 20'
        );
        $stmt->execute([$product['id'], 'approved']);
        $reviews = $stmt->fetchAll();

        // Funil — registra visualização de produto
        $this->trackFunnelEvent('product_view', (int)$product['id']);

        $this->render('product/show', [
            'pageTitle'  => $product['seo_title']       ?: $product['name'] . ' | Maia Suplementos',
            'metaDesc'   => $product['seo_description'] ?: '',
            'ogImage'    => $product['og_image']        ?: '',
            'product'    => $product,
            'related'    => $model->getRelated((int)$product['category_id'], (int)$product['id']),
            'reviews'    => $reviews,
            'avgRating'  => $model->getAverageRating((int)$product['id']),
            'reviewCount'=> $model->countReviews((int)$product['id']),
        ]);
    }

    private function trackFunnelEvent(string $step, int $productId): void
    {
        $stmt = db()->prepare(
            'INSERT INTO funnel_events (session_id, user_id, step, product_id, ip, user_agent)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            session_id(),
            $_SESSION['user_id'] ?? null,
            $step,
            $productId,
            $this->clientIp(),
            mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ]);
    }
}
