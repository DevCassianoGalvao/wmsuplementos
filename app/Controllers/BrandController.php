<?php

declare(strict_types=1);

namespace Maia\Controllers;

use Maia\Models\BrandModel;
use Maia\Models\ProductModel;

class BrandController extends BaseController
{
    public function show(array $params = []): void
    {
        $slug = (string)($params['slug'] ?? '');
        if ($slug === '') {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $brand = (new BrandModel())->findBySlug($slug);
        if (!$brand) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $page    = max(1, (int)($_GET['pagina'] ?? 1));
        $filters = ['brand_id' => $brand['id'], 'active' => '1'];
        $model   = new ProductModel();
        $result  = $model->getList($filters, $page, 20);

        $total = (int)($result['total'] ?? 0);

        $this->render('brand/show', [
            'pageTitle'  => $brand['name'] . ' | WM Suplementos',
            'metaDesc'   => 'Produtos da marca ' . $brand['name'],
            'brand'      => $brand,
            'products'   => $result['items'] ?? [],
            'total'      => $total,
            'page'       => $page,
            'totalPages' => (int)ceil($total / 20),
        ]);
    }
}
