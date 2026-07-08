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
        Auth::requireAdminRole();

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
            'pageTitle' => 'Produtos | Admin WM',
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
        Auth::requireAdminRole();

        $this->render('admin/products/form', [
            'pageTitle'  => 'Novo Produto | Admin WM',
            'product'    => null,
            'categories' => (new CategoryModel())->getAll(),
            'brands'     => $this->getBrands(),
            'flash'      => $this->getFlash(),
        ], 'admin');
    }

    public function store(array $params = []): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $data = $this->extractProductData();
        $v    = $this->validateProduct($data);

        if ($v->fails()) {
            $this->flash('error', implode('<br>', $v->errors()));
            $this->redirect('/admin/produtos/novo');
        }

        $id = $this->model->create($data);
        $imgErrors = $this->handleImageUpload($id);

        Cache::flush('category_');
        Cache::flush('home_');

        if ($imgErrors !== []) {
            $this->flash('error', 'Produto criado, mas ocorreram erros nas imagens:<br>' . implode('<br>', array_map('htmlspecialchars', $imgErrors)));
        } else {
            $this->flash('success', 'Produto criado com sucesso.');
        }
        $this->redirect('/admin/produtos/' . $id);
    }

    public function edit(array $params): void
    {
        Auth::requireAdminRole();

        $id      = (int)($params['id'] ?? 0);
        $product = $this->model->findById($id);

        if (!$product) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->repairProductImagePermissions($product);
        $product['images'] = $this->prepareProductImages($product['images'] ?? []);

        $this->render('admin/products/form', [
            'pageTitle'    => 'Editar: ' . $product['name'] . ' | Admin WM',
            'product'      => $product,
            'categories'   => (new CategoryModel())->getAll(),
            'brands'       => $this->getBrands(),
            'adminRelated' => $this->model->getAdminRelated($id),
            'flash'        => $this->getFlash(),
        ], 'admin');
    }

    public function update(array $params): void
    {
        Auth::requireAdminRole();
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
        $imgErrors = $this->handleImageUpload($id, isset($_POST['new_image_as_main']));

        $relatedIds = array_filter(array_map('intval', (array)($_POST['related_ids'] ?? [])));
        $this->model->setAdminRelated($id, array_values($relatedIds));

        $this->flushProductCaches($id);

        if ($imgErrors !== []) {
            $this->flash('error', 'Produto atualizado, mas ocorreram erros nas imagens:<br>' . implode('<br>', array_map('htmlspecialchars', $imgErrors)));
        } else {
            $this->flash('success', 'Produto atualizado.');
        }
        $this->redirect('/admin/produtos/' . $id);
    }

    public function deleteImage(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $productId = (int)($params['id'] ?? 0);
        $imageId = (int)($params['imageId'] ?? 0);
        $image = $this->model->deleteProductImage($productId, $imageId);

        if (!$image) {
            $this->flash('error', 'Imagem não encontrada para este produto.');
            $this->redirect('/admin/produtos/' . $productId);
        }

        ImageService::deleteAll((string)($image['filename_webp'] ?? $image['filename'] ?? ''));
        $this->flushProductCaches($productId);

        $this->flash('success', 'Imagem removida.');
        $this->redirect('/admin/produtos/' . $productId);
    }

    public function toggle(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        $this->model->toggleActive($id);

        $this->flash('success', 'Status do produto alterado.');
        $this->redirect('/admin/produtos');
    }

    public function duplicate(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $id    = (int)($params['id'] ?? 0);
        $newId = $this->model->duplicate($id);

        $this->flash('success', 'Produto duplicado.');
        $this->redirect('/admin/produtos/' . $newId);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function extractProductData(): array
    {
        $name = Sanitizer::plainText($_POST['name'] ?? '');
        $slug = Sanitizer::slug($_POST['slug'] ?? '');
        if ($slug === '' && $name !== '') {
            $slug = Sanitizer::slug($name);
        }

        return [
            'name'        => $name,
            'slug'        => $slug,
            'sku'         => Sanitizer::plainText($_POST['sku']         ?? ''),
            'description' => Sanitizer::plainText($_POST['description'] ?? ''),
            'benefits'    => Sanitizer::plainText($_POST['benefits']    ?? ''),
            'nutrition_table' => $this->extractNutritionTable($_POST['nutrition_table'] ?? ''),
            'price'       => (float)str_replace(',', '.', $_POST['price'] ?? '0'),
            'price_sale'  => !empty($_POST['price_sale'])
                                ? (float)str_replace(',', '.', $_POST['price_sale'])
                                : null,
            'stock'       => (int)($_POST['stock']       ?? 0),
            'stock_alert_threshold' => (int)($_POST['stock_alert'] ?? 5),
            'category_id' => (int)($_POST['category_id'] ?? 0) ?: null,
            'brand_id'    => (int)($_POST['brand_id']    ?? 0) ?: null,
            'weight_g'    => (int)($_POST['weight_g']    ?? 0) ?: null,
            'active'      => isset($_POST['active']) ? 1 : 0,
            'featured'    => isset($_POST['featured']) ? 1 : 0,
            'bestseller'   => isset($_POST['bestseller']) ? 1 : 0,
            'seo_title'       => Sanitizer::plainText($_POST['seo_title']       ?? $_POST['meta_title'] ?? ''),
            'seo_description' => Sanitizer::plainText($_POST['seo_description'] ?? $_POST['meta_description'] ?? ''),
            'og_image'        => Sanitizer::plainText($_POST['og_image']        ?? ''),
        ];
    }

    private function extractNutritionTable(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $rows = [];
        foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
            $parts = array_map('trim', explode(':', $line, 2));
            if (count($parts) === 2 && $parts[0] !== '') {
                $rows[$parts[0]] = $parts[1];
            }
        }

        return $rows ?: null;
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

    /** @return string[] lista de erros de upload (vazia = tudo OK) */
    private function handleImageUpload(int $productId, bool $makeFirstUploadMain = false): array
    {
        if (empty($_FILES['images']['name'][0])) {
            return [];
        }

        $config  = require ROOT_PATH . '/config/app.php';
        $maxSize = $config['upload']['max_size'] ?? 5242880;
        $countStmt = db()->prepare('SELECT COUNT(*) FROM product_images WHERE product_id = ?');
        $countStmt->execute([$productId]);
        $existingCount = (int)$countStmt->fetchColumn();
        $remainingSlots = max(0, 10 - $existingCount);

        if ($remainingSlots === 0) {
            return ['Limite de 10 imagens por produto atingido.'];
        }

        $uploadedCount = 0;
        $errors = [];
        $hasPublicMain = $this->hasPublicMainImage($productId);

        foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {
            if ($uploadedCount >= $remainingSlots) {
                break;
            }

            if (!is_uploaded_file($tmpName)) {
                continue;
            }

            $fileName = (string)($_FILES['images']['name'][$i] ?? 'arquivo');

            if (($_FILES['images']['size'][$i] ?? 0) > $maxSize) {
                $errors[] = "\"$fileName\" excede o tamanho máximo de " . round($maxSize / 1048576, 1) . ' MB.';
                continue;
            }

            $fileEntry = [
                'error'    => $_FILES['images']['error'][$i],
                'tmp_name' => $tmpName,
                'size'     => $_FILES['images']['size'][$i],
                'name'     => $fileName,
            ];

            try {
                $paths = ImageService::processUpload($fileEntry, 'products');
            } catch (\RuntimeException $e) {
                $msg = $e->getMessage();
                error_log('[ProductController] Upload falhou para produto ' . $productId . ': ' . $msg);
                $errors[] = "\"$fileName\": $msg";
                continue;
            }

            $sortStmt = db()->prepare(
                'SELECT COALESCE(MAX(sort_order), 0) + 1 FROM product_images WHERE product_id = ?'
            );
            $sortStmt->execute([$productId]);
            $sort = (int)$sortStmt->fetchColumn();
            $isMain = $uploadedCount === 0 && ($makeFirstUploadMain || $existingCount === 0 || !$hasPublicMain);
            if ($isMain) {
                db()->prepare('UPDATE product_images SET is_main = 0 WHERE product_id = ?')->execute([$productId]);
            }

            db()->prepare(
                'INSERT INTO product_images (product_id, filename, filename_webp, is_main, sort_order)
                 VALUES (?, ?, ?, ?, ?)'
            )->execute([
                $productId,
                $paths['medium'],
                $paths['medium'],
                $isMain,
                $sort,
            ]);
            $uploadedCount++;
            $existingCount++;
            $hasPublicMain = true;
        }

        return $errors;
    }

    private function repairProductImagePermissions(array $product): void
    {
        foreach (($product['images'] ?? []) as $image) {
            ImageService::repairPublicPermissions((string)($image['filename_webp'] ?? $image['filename'] ?? ''));
        }
    }

    private function prepareProductImages(array $images): array
    {
        foreach ($images as &$image) {
            $path = (string)($image['filename_webp'] ?? $image['filename'] ?? '');
            $image['_file_exists'] = ImageService::publicFileExists($path);
        }
        unset($image);

        return $images;
    }

    private function hasPublicMainImage(int $productId): bool
    {
        foreach ($this->model->getImages($productId) as $image) {
            if ((int)($image['is_main'] ?? 0) !== 1) {
                continue;
            }
            return ImageService::publicFileExists((string)($image['filename_webp'] ?? $image['filename'] ?? ''));
        }
        return false;
    }

    private function flushProductCaches(int $productId): void
    {
        Cache::forget('product_' . $productId);
        Cache::flush('category_');
        Cache::flush('home_');
    }

    public function searchApi(array $params = []): void
    {
        Auth::requireAdminRole();
        $q       = trim($_GET['q'] ?? '');
        $exclude = (int)($_GET['exclude'] ?? 0);

        header('Content-Type: application/json; charset=UTF-8');

        if (strlen($q) < 2) {
            echo '[]';
            return;
        }

        $rows = $this->model->searchForRelated($q, $exclude, 12);
        echo json_encode($rows, JSON_UNESCAPED_UNICODE);
    }

    private function getBrands(): array
    {
        return db()->query('SELECT id, name FROM brands WHERE active = 1 ORDER BY name ASC')->fetchAll();
    }
}
