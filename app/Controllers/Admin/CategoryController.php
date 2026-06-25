<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;
use Maia\Helpers\CSRF;
use Maia\Helpers\Sanitizer;
use Maia\Models\CategoryModel;
use Maia\Helpers\Cache;

class CategoryController extends BaseController
{
    private CategoryModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new CategoryModel();
    }

    public function index(array $params = []): void
    {
        Auth::requireAdminRole();

        $this->render('admin/categories/index', [
            'pageTitle'  => 'Categorias | Admin Maia',
            'categories' => $this->model->getAllWithProductCount(),
            'flash'      => $this->getFlash(),
        ], 'admin');
    }

    public function create(array $params = []): void
    {
        Auth::requireAdminRole();

        $this->render('admin/categories/form', [
            'pageTitle' => 'Nova Categoria | Admin Maia',
            'category'  => null,
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function store(array $params = []): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $data = $this->extractData();
        if ($data['name'] === '') {
            $this->flash('error', 'Informe o nome da categoria.');
            $this->redirect('/admin/categorias/novo');
        }

        if ($data['slug'] === '') {
            $data['slug'] = Sanitizer::slug($data['name']);
        }

        $this->model->create($data);
        Cache::flush('home_');
        Cache::flush('category_');

        $this->flash('success', 'Categoria criada.');
        $this->redirect('/admin/categorias');
    }

    public function edit(array $params): void
    {
        Auth::requireAdminRole();

        $id = (int)($params['id'] ?? 0);
        $category = $this->model->findById($id);

        if (!$category) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('admin/categories/form', [
            'pageTitle' => 'Editar Categoria | Admin Maia',
            'category'  => $category,
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function update(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        $category = $this->model->findById($id);

        if (!$category) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $data = $this->extractData();
        if ($data['name'] === '') {
            $this->flash('error', 'Informe o nome da categoria.');
            $this->redirect('/admin/categorias/' . $id);
        }
        if ($data['slug'] === '') {
            $data['slug'] = Sanitizer::slug($data['name']);
        }

        $this->model->update($id, $data);
        Cache::flush('home_');
        Cache::flush('category_');

        $this->flash('success', 'Categoria atualizada.');
        $this->redirect('/admin/categorias');
    }

    public function delete(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        $count = $this->model->countProducts($id);

        if ($count > 0) {
            $this->flash('error', 'Categoria possui produtos vinculados. Mova ou edite os produtos antes de remover.');
            $this->redirect('/admin/categorias');
        }

        $this->model->delete($id);
        Cache::flush('home_');
        Cache::flush('category_');

        $this->flash('success', 'Categoria removida.');
        $this->redirect('/admin/categorias');
    }

    public function toggle(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $this->model->toggleActive((int)($params['id'] ?? 0));
        Cache::flush('home_');
        Cache::flush('category_');

        $this->flash('success', 'Status da categoria alterado.');
        $this->redirect('/admin/categorias');
    }

    private function extractData(): array
    {
        return [
            'name'            => Sanitizer::plainText($_POST['name'] ?? ''),
            'slug'            => Sanitizer::slug($_POST['slug'] ?? ''),
            'seo_title'       => Sanitizer::plainText($_POST['seo_title'] ?? ''),
            'seo_description' => Sanitizer::plainText($_POST['seo_description'] ?? ''),
            'image'           => null,
            'active'          => isset($_POST['active']) ? 1 : 0,
            'sort_order'      => (int)($_POST['sort_order'] ?? 0),
        ];
    }
}
