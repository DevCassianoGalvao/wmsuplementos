<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;
use Maia\Helpers\Cache;
use Maia\Helpers\CSRF;
use Maia\Helpers\Sanitizer;
use Maia\Models\ComboModel;
use Maia\Models\ProductModel;
use Maia\Services\ImageService;

class ComboController extends BaseController
{
    private ComboModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new ComboModel();
    }

    public function index(array $params = []): void
    {
        Auth::requireAdminRole();

        $this->render('admin/combos/index', [
            'pageTitle' => 'Combos | Admin Maia',
            'combos'    => $this->model->getAll(),
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function create(array $params = []): void
    {
        Auth::requireAdminRole();

        $this->render('admin/combos/form', [
            'pageTitle' => 'Novo Combo | Admin Maia',
            'combo'     => null,
            'products'  => $this->getProducts(),
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function store(array $params = []): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $data = $this->extractData();
        if ($data['name'] === '' || $data['price'] <= 0) {
            $this->flash('error', 'Informe nome e preco valido para o combo.');
            $this->redirect('/admin/combos/novo');
        }

        if ($data['slug'] === '') {
            $data['slug'] = Sanitizer::slug($data['name']);
        }

        $items = $this->extractItems();
        if (empty($items)) {
            $this->flash('error', 'Adicione pelo menos um produto ao combo.');
            $this->redirect('/admin/combos/novo');
        }

        $data['image'] = $this->handleImageUpload();
        $id = $this->model->create($data, $items);
        Cache::flush('home_');

        $this->flash('success', 'Combo criado.');
        $this->redirect('/admin/combos/' . $id);
    }

    public function edit(array $params): void
    {
        Auth::requireAdminRole();

        $combo = $this->model->findById((int)($params['id'] ?? 0));
        if (!$combo) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('admin/combos/form', [
            'pageTitle' => 'Editar Combo | Admin Maia',
            'combo'     => $combo,
            'products'  => $this->getProducts(),
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function update(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        $combo = $this->model->findById($id);
        if (!$combo) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $data = $this->extractData();
        if ($data['name'] === '' || $data['price'] <= 0) {
            $this->flash('error', 'Informe nome e preco valido para o combo.');
            $this->redirect('/admin/combos/' . $id);
        }

        if ($data['slug'] === '') {
            $data['slug'] = Sanitizer::slug($data['name']);
        }

        $items = $this->extractItems();
        if (empty($items)) {
            $this->flash('error', 'Adicione pelo menos um produto ao combo.');
            $this->redirect('/admin/combos/' . $id);
        }

        $data['image'] = $this->handleImageUpload() ?? ($combo['image'] ?? null);
        $this->model->update($id, $data, $items);
        Cache::flush('home_');

        $this->flash('success', 'Combo atualizado.');
        $this->redirect('/admin/combos');
    }

    public function toggle(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $this->model->toggleActive((int)($params['id'] ?? 0));
        Cache::flush('home_');

        $this->flash('success', 'Status do combo alterado.');
        $this->redirect('/admin/combos');
    }

    public function delete(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $this->model->delete((int)($params['id'] ?? 0));
        Cache::flush('home_');

        $this->flash('success', 'Combo removido.');
        $this->redirect('/admin/combos');
    }

    private function extractData(): array
    {
        return [
            'name'        => Sanitizer::plainText($_POST['name'] ?? ''),
            'slug'        => Sanitizer::slug($_POST['slug'] ?? ''),
            'description' => Sanitizer::plainText($_POST['description'] ?? ''),
            'price'       => (float)str_replace(',', '.', $_POST['price'] ?? '0'),
            'active'      => isset($_POST['active']) ? 1 : 0,
        ];
    }

    private function extractItems(): array
    {
        $productIds = $_POST['product_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $items = [];

        foreach ($productIds as $i => $productId) {
            $productId = (int)$productId;
            if ($productId <= 0) {
                continue;
            }
            $items[$productId] = [
                'product_id' => $productId,
                'quantity'   => max(1, (int)($quantities[$i] ?? 1)),
            ];
        }

        return array_values($items);
    }

    private function handleImageUpload(): ?string
    {
        if (empty($_FILES['image']['tmp_name']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
            return null;
        }

        try {
            $paths = ImageService::processUpload($_FILES['image'], 'combos');
            return $paths['medium'] ?? null;
        } catch (\RuntimeException $e) {
            error_log('[ComboController] Imagem invalida: ' . $e->getMessage());
            $this->flash('error', 'Imagem invalida. Envie JPG, PNG ou WebP.');
            return null;
        }
    }

    private function getProducts(): array
    {
        return (new ProductModel())->getList(['active' => 1], 1, 500)['items'] ?? [];
    }
}
