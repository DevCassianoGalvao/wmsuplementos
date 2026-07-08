<?php

declare(strict_types=1);

namespace Maia\Controllers;

use Maia\Models\ProductModel;
use Maia\Models\CategoryModel;
use Maia\Helpers\Auth;
use Maia\Helpers\CSRF;
use Maia\Helpers\Sanitizer;
use Maia\Helpers\Validator;
use Maia\Services\ImageService;

class ProductController extends BaseController
{
    public function index(array $params = []): void
    {
        $page    = max(1, (int)($_GET['pagina'] ?? 1));
        $order   = $_GET['ordem'] ?? 'relevancia';
        $perPage = $this->config['pagination']['per_page'];
        $model   = new ProductModel();
        $result  = $model->getList([
            'active'   => 1,
            'search'   => $_GET['busca'] ?? '',
            'order_by' => $order,
        ], $page, $perPage);

        $this->render('product/index', [
            'pageTitle'   => 'Produtos | WM Suplementos',
            'products'    => $result['items'],
            'total'       => $result['total'],
            'page'        => $page,
            'totalPages'  => (int)ceil($result['total'] / $perPage),
            'categories'  => (new CategoryModel())->getActiveWithProducts(),
            'currentSort' => $order,
            'search'      => $_GET['busca'] ?? '',
        ]);
    }

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
        foreach (($product['images'] ?? []) as $image) {
            ImageService::repairPublicPermissions((string)($image['filename_webp'] ?? $image['filename'] ?? ''));
        }

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
            'pageTitle'  => $product['seo_title']       ?: $product['name'] . ' | WM Suplementos',
            'metaDesc'   => $product['seo_description'] ?: '',
            'ogImage'    => $product['og_image']        ?: '',
            'ogType'     => 'product',
            'product'    => $product,
            'related'    => $model->getAdminRelated((int)$product['id'])
                           ?: $model->getRelated((int)$product['category_id'], (int)$product['id']),
            'reviews'    => $reviews,
            'avgRating'  => $model->getAverageRating((int)$product['id']),
            'reviewCount'=> $model->countReviews((int)$product['id']),
            'flash'      => $this->getFlash(),
        ]);
    }

    public function notifyStock(array $params): void
    {
        CSRF::verify();

        $slug = $params['slug'] ?? '';
        $product = (new ProductModel())->findBySlug($slug);

        if (!$product) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $email = Auth::isUserLogged()
            ? (string)($_SESSION['user_email'] ?? '')
            : Sanitizer::email($_POST['email'] ?? '');

        $validator = new Validator(['email' => $email]);
        $validator->required('email')->email('email');
        if ($validator->fails()) {
            $this->flash('error', implode(' ', $validator->errors()));
            $this->redirect('/produto/' . $slug);
        }

        $existing = db()->prepare(
            'SELECT id FROM stock_notifications
              WHERE product_id = ? AND email = ? AND notified_at IS NULL
              LIMIT 1'
        );
        $existing->execute([(int)$product['id'], $email]);

        if (!$existing->fetchColumn()) {
            db()->prepare(
                'INSERT INTO stock_notifications (product_id, user_id, email, created_at)
                 VALUES (?, ?, ?, NOW())'
            )->execute([(int)$product['id'], Auth::userId(), $email]);
        }

        $this->flash('success', 'Avisaremos você quando este produto voltar ao estoque.');
        $this->redirect('/produto/' . $slug);
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
