<?php

declare(strict_types=1);

namespace Maia\Models;

class ProductModel extends BaseModel
{
    // ─── Leitura ─────────────────────────────────────────────────────────────

    public function findById(int $id): ?array
    {
        $product = $this->fetch(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                    b.name AS brand_name
               FROM products p
               LEFT JOIN categories c ON c.id = p.category_id
               LEFT JOIN brands     b ON b.id = p.brand_id
              WHERE p.id = ?',
            [$id]
        );

        if ($product) {
            $product['images'] = $this->getImages($id);
        }

        return $product;
    }

    public function findBySlug(string $slug): ?array
    {
        $product = $this->fetch(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                    b.name AS brand_name
               FROM products p
               LEFT JOIN categories c ON c.id = p.category_id
               LEFT JOIN brands     b ON b.id = p.brand_id
              WHERE p.slug = ? AND p.active = 1',
            [$slug]
        );

        if ($product) {
            $product['images'] = $this->getImages((int)$product['id']);
        }

        return $product;
    }

    /**
     * Listagem com filtros opcionais para área pública e painel.
     *
     * @param array $filters  category_id, brand_id, active, featured, search,
     *                        min_price, max_price, order_by
     */
    public function getList(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = ['1=1'];
        $params = [];

        if (isset($filters['active'])) {
            $where[]  = 'p.active = ?';
            $params[] = (int)$filters['active'];
        }

        if (!empty($filters['category_id'])) {
            $where[]  = 'p.category_id = ?';
            $params[] = (int)$filters['category_id'];
        }

        if (!empty($filters['brand_id'])) {
            $where[]  = 'p.brand_id = ?';
            $params[] = (int)$filters['brand_id'];
        }

        if (!empty($filters['brand_ids']) && is_array($filters['brand_ids'])) {
            $brandIds = array_values(array_filter(array_map('intval', $filters['brand_ids'])));
            if (!empty($brandIds)) {
                $where[] = 'p.brand_id IN (' . implode(',', array_fill(0, count($brandIds), '?')) . ')';
                foreach ($brandIds as $brandId) {
                    $params[] = $brandId;
                }
            }
        }

        if (isset($filters['featured'])) {
            $where[]  = 'p.featured = ?';
            $params[] = (int)$filters['featured'];
        }

        if (isset($filters['bestseller'])) {
            $where[]  = 'p.bestseller = ?';
            $params[] = (int)$filters['bestseller'];
        }

        if (!empty($filters['search'])) {
            $where[]  = '(p.name LIKE ? OR p.sku LIKE ?)';
            $term     = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['min_price'])) {
            $where[]  = 'COALESCE(p.price_sale, p.price) >= ?';
            $params[] = (float)$filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $where[]  = 'COALESCE(p.price_sale, p.price) <= ?';
            $params[] = (float)$filters['max_price'];
        }

        $orderMap = [
            'preco_asc'    => 'COALESCE(p.price_sale, p.price) ASC',
            'preco_desc'   => 'COALESCE(p.price_sale, p.price) DESC',
            'mais_vendidos'=> 'p.total_sold DESC',
            'novidades'    => 'p.created_at DESC',
            'relevancia'   => 'p.featured DESC, p.total_sold DESC',
        ];
        $order = $orderMap[$filters['order_by'] ?? ''] ?? 'p.featured DESC, p.total_sold DESC';

        $whereStr = implode(' AND ', $where);

        $total = (int)$this->fetchColumn(
            "SELECT COUNT(*) FROM products p WHERE {$whereStr}",
            $params
        );

        [$limit, $offset] = $this->limitOffset($page, $perPage);
        $params[]         = $limit;
        $params[]         = $offset;

        $rows = $this->fetchAll(
            "SELECT p.*, c.name AS category_name, b.name AS brand_name,
                    (SELECT filename_webp FROM product_images
                      WHERE product_id = p.id AND is_main = 1 LIMIT 1) AS main_image
               FROM products p
               LEFT JOIN categories c ON c.id = p.category_id
               LEFT JOIN brands     b ON b.id = p.brand_id
              WHERE {$whereStr}
              ORDER BY {$order}
              LIMIT ? OFFSET ?",
            $params
        );

        return ['items' => $rows, 'total' => $total];
    }

    public function getFeatured(int $limit = 8): array
    {
        return $this->fetchAll(
            'SELECT p.*,
                    (SELECT filename_webp FROM product_images
                      WHERE product_id = p.id AND is_main = 1 LIMIT 1) AS main_image
               FROM products p
              WHERE p.active = 1 AND p.featured = 1
              ORDER BY p.total_sold DESC
              LIMIT ?',
            [$limit]
        );
    }

    public function getBestsellers(int $limit = 8): array
    {
        return $this->fetchAll(
            'SELECT p.*,
                    (SELECT filename_webp FROM product_images
                      WHERE product_id = p.id AND is_main = 1 LIMIT 1) AS main_image
               FROM products p
              WHERE p.active = 1
              ORDER BY p.total_sold DESC
              LIMIT ?',
            [$limit]
        );
    }

    public function getRelated(int $categoryId, int $excludeId, int $limit = 4): array
    {
        return $this->fetchAll(
            'SELECT p.*,
                    (SELECT filename_webp FROM product_images
                      WHERE product_id = p.id AND is_main = 1 LIMIT 1) AS main_image
               FROM products p
              WHERE p.active = 1 AND p.category_id = ? AND p.id != ?
              ORDER BY p.total_sold DESC
              LIMIT ?',
            [$categoryId, $excludeId, $limit]
        );
    }

    public function getLowStock(int $threshold = 5): array
    {
        return $this->fetchAll(
            'SELECT * FROM products
              WHERE active = 1 AND stock <= ? AND stock > 0
              ORDER BY stock ASC',
            [$threshold]
        );
    }

    public function getOutOfStock(): array
    {
        return $this->fetchAll(
            'SELECT * FROM products WHERE active = 1 AND stock = 0 ORDER BY name ASC'
        );
    }

    // ─── Imagens ──────────────────────────────────────────────────────────────

    public function getImages(int $productId): array
    {
        return $this->fetchAll(
            'SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC, sort_order ASC',
            [$productId]
        );
    }

    public function addImage(int $productId, string $filenameWebp, bool $isMain = false, int $sortOrder = 0): int
    {
        if ($isMain) {
            $this->query(
                'UPDATE product_images SET is_main = 0 WHERE product_id = ?',
                [$productId]
            );
        }
        $this->query(
            'INSERT INTO product_images (product_id, filename_webp, is_main, sort_order) VALUES (?, ?, ?, ?)',
            [$productId, $filenameWebp, (int)$isMain, $sortOrder]
        );
        return $this->lastInsertId();
    }

    public function deleteImage(int $imageId): ?string
    {
        $img = $this->fetch('SELECT * FROM product_images WHERE id = ?', [$imageId]);
        if (!$img) {
            return null;
        }
        $this->query('DELETE FROM product_images WHERE id = ?', [$imageId]);
        return $img['filename_webp'];
    }

    // ─── Escrita ──────────────────────────────────────────────────────────────

    public function create(array $data): int
    {
        $this->query(
            'INSERT INTO products
                (name, slug, sku, category_id, brand_id, price, price_sale, stock,
                 stock_alert_threshold, description, benefits, nutrition_table,
                 seo_title, seo_description, og_image, active, featured, bestseller)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['name'],
                $data['slug'],
                $data['sku']                  ?? null,
                (int)$data['category_id'],
                !empty($data['brand_id'])      ? (int)$data['brand_id'] : null,
                (float)$data['price'],
                !empty($data['price_sale'])    ? (float)$data['price_sale'] : null,
                (int)($data['stock']           ?? 0),
                (int)($data['stock_alert_threshold'] ?? 5),
                $data['description']           ?? null,
                $data['benefits']              ?? null,
                isset($data['nutrition_table']) ? json_encode($data['nutrition_table'], JSON_UNESCAPED_UNICODE) : null,
                $data['seo_title']             ?? null,
                $data['seo_description']       ?? null,
                $data['og_image']              ?? null,
                (int)($data['active']          ?? 1),
                (int)($data['featured']        ?? 0),
                (int)($data['bestseller']      ?? 0),
            ]
        );
        return $this->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $this->query(
            'UPDATE products
                SET name = ?, slug = ?, sku = ?, category_id = ?, brand_id = ?,
                    price = ?, price_sale = ?, stock = ?, stock_alert_threshold = ?,
                    description = ?, benefits = ?, nutrition_table = ?,
                    seo_title = ?, seo_description = ?, og_image = ?,
                    active = ?, featured = ?, bestseller = ?
              WHERE id = ?',
            [
                $data['name'],
                $data['slug'],
                $data['sku']                  ?? null,
                (int)$data['category_id'],
                !empty($data['brand_id'])      ? (int)$data['brand_id'] : null,
                (float)$data['price'],
                !empty($data['price_sale'])    ? (float)$data['price_sale'] : null,
                (int)($data['stock']           ?? 0),
                (int)($data['stock_alert_threshold'] ?? 5),
                $data['description']           ?? null,
                $data['benefits']              ?? null,
                isset($data['nutrition_table']) ? json_encode($data['nutrition_table'], JSON_UNESCAPED_UNICODE) : null,
                $data['seo_title']             ?? null,
                $data['seo_description']       ?? null,
                $data['og_image']              ?? null,
                (int)($data['active']          ?? 1),
                (int)($data['featured']        ?? 0),
                (int)($data['bestseller']      ?? 0),
                $id,
            ]
        );
        return true;
    }

    /** Ajusta estoque e registra movimentação. */
    public function adjustStock(int $productId, int $quantity, string $type, string $reason = '', ?int $referenceId = null, ?int $createdBy = null): void
    {
        $delta = $type === 'out' ? -abs($quantity) : abs($quantity);

        $this->query(
            'UPDATE products SET stock = stock + ? WHERE id = ?',
            [$delta, $productId]
        );

        $this->query(
            'INSERT INTO stock_movements (product_id, type, quantity, reason, reference_id, created_by)
             VALUES (?, ?, ?, ?, ?, ?)',
            [$productId, $type, $quantity, $reason ?: null, $referenceId, $createdBy]
        );
    }

    public function incrementSold(int $productId, int $quantity): void
    {
        $this->query(
            'UPDATE products SET total_sold = total_sold + ? WHERE id = ?',
            [$quantity, $productId]
        );
    }

    public function delete(int $id): bool
    {
        $this->query('DELETE FROM products WHERE id = ?', [$id]);
        return true;
    }

    public function duplicate(int $id): int
    {
        $p = $this->findById($id);
        if (!$p) {
            return 0;
        }
        unset($p['id'], $p['images'], $p['category_name'], $p['category_slug'], $p['brand_name']);
        $p['name']       = $p['name'] . ' (cópia)';
        $p['slug']       = $p['slug'] . '-copia-' . time();
        $p['sku']        = null;
        $p['active']     = 0;
        $p['total_sold'] = 0;
        if (isset($p['nutrition_table']) && is_string($p['nutrition_table'])) {
            $p['nutrition_table'] = json_decode($p['nutrition_table'], true);
        }
        return $this->create($p);
    }

    // ─── Avaliações ───────────────────────────────────────────────────────────

    public function getAverageRating(int $productId): float
    {
        $avg = $this->fetchColumn(
            'SELECT AVG(rating) FROM reviews WHERE product_id = ? AND status = ?',
            [$productId, 'approved']
        );
        return $avg ? round((float)$avg, 1) : 0.0;
    }

    public function countReviews(int $productId): int
    {
        return (int)$this->fetchColumn(
            'SELECT COUNT(*) FROM reviews WHERE product_id = ? AND status = ?',
            [$productId, 'approved']
        );
    }

    public function search(string $query, int $page = 1, int $perPage = 20): array
    {
        [$limit, $offset] = $this->limitOffset($page, $perPage);
        $like = '%' . $query . '%';
        return $this->fetchAll(
            'SELECT p.id, p.name, p.slug, p.price, p.price_sale, p.stock,
                    b.name AS brand_name
               FROM products p
               LEFT JOIN brands b ON b.id = p.brand_id
              WHERE p.active = 1
                AND (p.name LIKE ? OR p.description LIKE ? OR b.name LIKE ?)
              ORDER BY p.name ASC
              LIMIT ? OFFSET ?',
            [$like, $like, $like, $limit, $offset]
        );
    }

    public function searchCount(string $query): int
    {
        $like = '%' . $query . '%';
        return (int)$this->fetchColumn(
            'SELECT COUNT(*) FROM products p
               LEFT JOIN brands b ON b.id = p.brand_id
              WHERE p.active = 1
                AND (p.name LIKE ? OR p.description LIKE ? OR b.name LIKE ?)',
            [$like, $like, $like]
        );
    }

    public function countList(array $filters = []): int
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[]  = 'p.name LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['category_id'])) {
            $where[]  = 'p.category_id = ?';
            $params[] = (int)$filters['category_id'];
        }
        if ($filters['active'] !== '' && $filters['active'] !== null) {
            $where[]  = 'p.active = ?';
            $params[] = (int)$filters['active'];
        }
        if (!empty($filters['low_stock'])) {
            $where[] = 'p.stock <= p.stock_alert_threshold';
        }

        return (int)$this->fetchColumn(
            'SELECT COUNT(*) FROM products p WHERE ' . implode(' AND ', $where),
            $params
        );
    }

    public function toggleActive(int $id): void
    {
        $this->query('UPDATE products SET active = NOT active WHERE id = ?', [$id]);
    }
}
