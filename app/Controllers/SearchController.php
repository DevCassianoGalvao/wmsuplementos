<?php

declare(strict_types=1);

namespace Maia\Controllers;

use Maia\Models\ProductModel;

class SearchController extends BaseController
{
    public function index(array $params = []): void
    {
        $query = trim($_GET['q'] ?? '');
        $page  = max(1, (int)($_GET['pagina'] ?? 1));

        $results    = [];
        $total      = 0;
        $totalPages = 0;

        if (mb_strlen($query) >= 2) {
            $model      = new ProductModel();
            $results    = $model->search($query, $page, 20);
            $total      = $model->searchCount($query);
            $totalPages = (int)ceil($total / 20);
        }

        $this->render('search/results', [
            'pageTitle'  => 'Busca: ' . htmlspecialchars($query, ENT_QUOTES, 'UTF-8') . ' | Maia Suplementos',
            'query'      => $query,
            'results'    => $results,
            'total'      => $total,
            'page'       => $page,
            'totalPages' => $totalPages,
        ]);
    }
}
