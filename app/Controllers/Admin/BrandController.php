<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;
use Maia\Helpers\Cache;
use Maia\Helpers\CSRF;
use Maia\Helpers\Sanitizer;
use Maia\Models\BrandModel;
use Maia\Services\ImageService;

class BrandController extends BaseController
{
    private BrandModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new BrandModel();
    }

    public function index(array $params = []): void
    {
        Auth::requireAdmin();

        $this->render('admin/brands/index', [
            'pageTitle' => 'Marcas | Admin Maia',
            'brands'    => $this->model->getAllWithProductCount(),
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function create(array $params = []): void
    {
        Auth::requireAdmin();

        $this->render('admin/brands/form', [
            'pageTitle' => 'Nova Marca | Admin Maia',
            'brand'     => null,
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function store(array $params = []): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $data = $this->extractData();
        if ($data['name'] === '') {
            $this->flash('error', 'Informe o nome da marca.');
            $this->redirect('/admin/marcas/novo');
        }

        if ($data['slug'] === '') {
            $data['slug'] = Sanitizer::slug($data['name']);
        }

        $data['logo'] = $this->handleLogoUpload();
        $this->model->create($data);
        Cache::flush('home_');
        Cache::flush('category_');

        $this->flash('success', 'Marca criada.');
        $this->redirect('/admin/marcas');
    }

    public function edit(array $params): void
    {
        Auth::requireAdmin();

        $brand = $this->model->findById((int)($params['id'] ?? 0));
        if (!$brand) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('admin/brands/form', [
            'pageTitle' => 'Editar Marca | Admin Maia',
            'brand'     => $brand,
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function update(array $params): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        $brand = $this->model->findById($id);
        if (!$brand) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $data = $this->extractData();
        if ($data['name'] === '') {
            $this->flash('error', 'Informe o nome da marca.');
            $this->redirect('/admin/marcas/' . $id);
        }

        if ($data['slug'] === '') {
            $data['slug'] = Sanitizer::slug($data['name']);
        }

        $data['logo'] = $this->handleLogoUpload() ?? ($brand['logo'] ?? null);
        $this->model->update($id, $data);
        Cache::flush('home_');
        Cache::flush('category_');

        $this->flash('success', 'Marca atualizada.');
        $this->redirect('/admin/marcas');
    }

    public function delete(array $params): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        if ($this->model->countProducts($id) > 0) {
            $this->flash('error', 'Marca possui produtos vinculados. Edite os produtos antes de remover.');
            $this->redirect('/admin/marcas');
        }

        $this->model->delete($id);
        Cache::flush('home_');
        Cache::flush('category_');

        $this->flash('success', 'Marca removida.');
        $this->redirect('/admin/marcas');
    }

    public function toggle(array $params): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $this->model->toggleActive((int)($params['id'] ?? 0));
        Cache::flush('home_');
        Cache::flush('category_');

        $this->flash('success', 'Status da marca alterado.');
        $this->redirect('/admin/marcas');
    }

    private function extractData(): array
    {
        return [
            'name'        => Sanitizer::plainText($_POST['name'] ?? ''),
            'slug'        => Sanitizer::slug($_POST['slug'] ?? ''),
            'description' => Sanitizer::plainText($_POST['description'] ?? ''),
            'active'      => isset($_POST['active']) ? 1 : 0,
        ];
    }

    private function handleLogoUpload(): ?string
    {
        if (empty($_FILES['logo']['tmp_name']) || !is_uploaded_file($_FILES['logo']['tmp_name'])) {
            return null;
        }

        try {
            $paths = ImageService::processUpload($_FILES['logo'], 'brands');
            return $paths['medium'] ?? null;
        } catch (\RuntimeException $e) {
            error_log('[BrandController] Logo invalido: ' . $e->getMessage());
            $this->flash('error', 'Logo invalido. Envie JPG, PNG ou WebP.');
            return null;
        }
    }
}
