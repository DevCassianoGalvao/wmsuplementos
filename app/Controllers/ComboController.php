<?php

declare(strict_types=1);

namespace Maia\Controllers;

use Maia\Models\ComboModel;

class ComboController extends BaseController
{
    public function index(array $params = []): void
    {
        $this->render('combo/index', [
            'pageTitle' => 'Combos | Maia Suplementos',
            'combos'    => (new ComboModel())->getActive(),
            'flash'     => $this->getFlash(),
        ]);
    }

    public function show(array $params): void
    {
        $combo = (new ComboModel())->findBySlug($params['slug'] ?? '');

        if (!$combo) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('combo/show', [
            'pageTitle' => $combo['name'] . ' | Maia Suplementos',
            'combo'     => $combo,
            'items'     => $combo['items'] ?? [],
            'flash'     => $this->getFlash(),
        ]);
    }
}
