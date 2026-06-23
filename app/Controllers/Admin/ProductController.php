<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;
use Maia\Helpers\CSRF;
use Maia\Helpers\Sanitizer;
use Maia\Helpers\Validator;
use Maia\Models\ProductModel;
use Maia\Models\CategoryModel;
use Maia\Services\ImageService;
use Maia\Helpers\Cache;

class ProductController extends BaseController
{
    private ProductModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new ProductModel();
    }

    public function index(array $params = []): void
    {
        Auth::requireAdmin();

        $page     = max(1, (int)($_GET['pagina'] ?? 1));
        $filters  = [
            'search'      => $_GET['busca']     ?? '',
            'category_id' => (int)($_GET['categoria'] ?? 0) ?: null,
            'active'      => $_GET['ativo']     ?? '',
            'low_stock'   => isset($_GET['estoque_baixo']),
        ];

        $products = $this->model->getList($filters, $page, 30);
        $total    = $this->model->countList($filters);
        $cats     = (new CategoryModel())->getAll();

        $this->render('admin/products/index', [
            'pageTitle' => 'Produtos | Admin Maia',
            'products'  => $products,
            'total'     => $total,
            'page'      => $page,
            'totalPages'=> (int)ceil($total / 30),
            'filters'   => $filters,
            'categories'=> $cats,
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function create(array $params = []): void
    {
        Auth::requireAdmin();

        $this->render('admin/products/form', [
            'pageTitle'  => 'Novo Produto | Admin Maia',
            'product'    => null,
            'categories' => (new CategoryModel())->getAll(),
            'brands'     => $this->getBrands(),
            'flash'      => $this->getFlash(),
        ], 'admin');
    }

    public function store(array $params = []): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $data = $this->extractProductData();
        $v    = $this->validateProduct($data);

        if ($v->fails()) {
            $this->flash('error', implode('<br>', $v->errors()));
            $this->redirect('/admin/produtos/novo');
        }

        $id = $this->model->create($data);
        $this->handleImageUpload($id);

        Cache::flush('category_');
        Cache::flush('home_');

        $this->flash('success', 'Produto criado com sucesso.');
        $this->redirect('/admin/produtos/' . $id);
    }

    public function edit(array $params): void
    {
        Auth::requireAdmin();

        $id      = (int)($params['id'] ?? 0);
        $product = $this->model->findById($id);

        if (!$product) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('admin/products/form', [
            'pageTitle'  => 'Editar: ' . $product['name'] . ' | Admin Maia',
            'product'    => $product,
            'categories' => (new CategoryModel())->getAll(),
            'brands'     => $this->getBrands(),
            'flash'      => $this->getFlash(),
        ], 'admin');
    }

    public function update(array $params): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $id      = (int)($params['id'] ?? 0);
        $product = $this->model->findById($id);

        if (!$product) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $data = $this->extractProductData();
        $v    = $this->validateProduct($data, $id);

        if ($v->fails()) {
            $this->flash('error', implode('<br>', $v->errors()));
            $this->redirect('/admin/produtos/' . $id);
        }

        $this->model->update($id, $data);
        $this->handleImageUpload($id);

        Cache::forget('product_' . $id);
        Cache::flush('category_');
        Cache::flush('home_');

        $this->flash('success', 'Produto atualizado.');
        $this->redirect('/admin/produtos/' . $id);
    }

    public function toggle(array $params): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        $this->model->toggleActive($id);

        $this->flash('success', 'Status do produto alterado.');
        $this->redirect('/admin/produtos');
    }

    public function duplicate(array $params): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $id    = (int)($params['id'] ?? 0);
        $newId = $this->model->duplicate($id);

        $this->flash('success', 'Produto duplicado.');
        $this->redirect('/admin/produtos/' . $newId);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function extractProductData(): array
    {
        return [
            'name'        => Sanitizer::plainText($_POST['name']        ?? ''),
            'slug'        => Sanitizer::slug($_POST['slug']             ?? ''),
            'description' => Sanitizer::plainText($_POST['description'] ?? ''),
            'price'       => (float)str_replace(',', '.', $_POST['price'] ?? '0'),
            'price_sale'  => !empty($_POST['price_sale'])
                                ? (float)str_replace(',', '.', $_POST['price_sale'])
                                : null,
            'stock'       => (int)($_POST['stock']       ?? 0),
            'stock_alert' => (int)($_POST['stock_alert'] ?? 5),
            'category_id' => (int)($_POST['category_id'] ?? 0) ?: null,
            'brand_id'    => (int)($_POST['brand_id']    ?? 0) ?: null,
            'weight_g'    => (int)($_POST['weight_g']    ?? 0) ?: null,
            'active'      => isset($_POST['active']) ? 1 : 0,
            'featured'    => isset($_POST['featured']) ? 1 : 0,
            'meta_title'       => Sanitizer::plainText($_POST['meta_title']       ?? ''),
            'meta_description' => Sanitizer::plainText($_POST['meta_description'] ?? ''),
        ];
    }

    private function validateProduct(array $data, ?int $ignoreId = null): Validator
    {
        $v = new Validator($data);
        $v->required('name')->maxLen('name', 200, 'Nome')
          ->required('price')->positiveNumber('price', 'Preço');

        if (empty($data['slug'])) {
            $data['slug'] = Sanitizer::slug($data['name']);
        }

        $v->uniqueInDb('slug', 'products', 'slug', $ignoreId, 'Slug');

        return $v;
    }

    private function handleImageUpload(int $productId): void
    {
        if (empty($_FILES['images']['name'][0])) {
            return;
        }

        $config  = require ROOT_PATH . '/config/app.php';
        $maxSize = $config['upload']['max_size'] ?? 5242880;

        foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {
            if (!is_uploaded_file($tmpName)) {
                continue;
            }

            if (($_FILES['images']['size'][$i] ?? 0) > $maxSize) {
                continue;
            }

            // Reconstrói entrada de $_FILES individual para ImageService
            $fileEntry = [
                'error'    => $_FILES['images']['error'][$i],
                'tmp_name' => $tmpName,
                'size'     => $_FILES['images']['size'][$i],
                'name'     => $_FILES['images']['name'][$i],
            ];

            try {
                $paths = ImageService::processUpload($fileEntry, 'products');
            } catch (\RuntimeException $e) {
                error_log('[ProductController] Imagem inválida: ' . $e->getMessage());
                continue;
            }

            // sort_order: prepared statement (não concatenação)
            $sortStmt = db()->prepare(
                'SELECT COALESCE(MAX(sort_order), 0) + 1 FROM product_images WHERE product_id = ?'
            );
            $sortStmt->execute([$productId]);
            $sort = (int)$sortStmt->fetchColumn();

            db()->prepare(
                'INSERT INTO product_images (product_id, filename, filename_webp, sort_order)
                 VALUES (?, ?, ?, ?)'
            )->execute([
                $productId,
                $paths['medium'],  // path usado nas listagens
                $paths['medium'],  // campo webp (já é webp)
                $sort,
            ]);
        }
    }

    private function getBrands(): array
    {
        return db()->query('SELECT id, name FROM brands WHERE active = 1 ORDER BY name ASC')->fetchAll();
    }
}
