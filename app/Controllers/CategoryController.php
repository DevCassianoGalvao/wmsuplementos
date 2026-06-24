<?php

declare(strict_types=1);

namespace Maia\Controllers;

use Maia\Models\CategoryModel;
use Maia\Models\ProductModel;
use Maia\Helpers\Cache;

class CategoryController extends BaseController
{
    public function show(array $params): void
    {
        $slug = $params['slug'] ?? '';

        $categoryModel = new CategoryModel();

        // Categoria sem filtros ativos: cacheável
        $category = Cache::remember('category_meta_' . $slug, 600, fn() => $categoryModel->findBySlug($slug));

        if (!$category) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $brandIds = array_filter(array_map('intval', (array)($_GET['marca'] ?? [])));
        $minPrice = !empty($_GET['preco_min']) ? (float)$_GET['preco_min'] : null;
        $maxPrice = !empty($_GET['preco_max']) ? (float)$_GET['preco_max'] : null;
        $order    = $_GET['ordem'] ?? 'relevancia';
        $page     = max(1, (int)($_GET['pagina'] ?? 1));
        $perPage  = $this->config['pagination']['per_page'];

        $filters = [
            'active'      => 1,
            'category_id' => $category['id'],
            'brand_ids'   => $brandIds,
            'min_price'   => $minPrice,
            'max_price'   => $maxPrice,
            'order_by'    => $order,
        ];

        // Sem filtros e página 1 → cacheia lista por 5 min
        $hasFilters = !empty($brandIds) || $minPrice || $maxPrice || $order !== 'relevancia' || $page > 1;

        $productModel = new ProductModel();

        if ($hasFilters) {
            $result = $productModel->getList($filters, $page, $perPage);
        } else {
            $cacheKey = 'category_list_' . $slug . '_p' . $page;
            $result   = Cache::remember($cacheKey, 300, fn() => $productModel->getList($filters, $page, $perPage));
        }

        $brands = Cache::remember('category_brands_' . $category['id'], 600, function () use ($category) {
            $stmt = db()->prepare(
                'SELECT DISTINCT b.id, b.name FROM brands b
                   JOIN products p ON p.brand_id = b.id
                  WHERE p.category_id = ? AND p.active = 1 AND b.active = 1
                  ORDER BY b.name ASC'
            );
            $stmt->execute([$category['id']]);
            return $stmt->fetchAll();
        });

        $this->render('category/show', [
            'pageTitle'  => $category['seo_title']       ?: $category['name'] . ' | Maia Suplementos',
            'metaDesc'   => $category['seo_description'] ?: '',
            'category'   => $category,
            'products'   => $result['items'],
            'total'      => $result['total'],
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => (int)ceil($result['total'] / $perPage),
            'brands'     => $brands,
            'filters'    => $filters,
        ]);
    }
}
