<?php

declare(strict_types=1);

namespace Maia\Models;

class ComboModel extends BaseModel
{
    public function getAll(): array
    {
        return $this->fetchAll(
            'SELECT c.*, COUNT(ci.id) AS item_count
               FROM combos c
               LEFT JOIN combo_items ci ON ci.combo_id = c.id
              GROUP BY c.id
              ORDER BY c.created_at DESC, c.id DESC'
        );
    }

    public function getActive(): array
    {
        return $this->fetchAll(
            'SELECT c.*, COUNT(ci.id) AS item_count
               FROM combos c
               LEFT JOIN combo_items ci ON ci.combo_id = c.id
              WHERE c.active = 1
              GROUP BY c.id
              ORDER BY c.created_at DESC, c.id DESC'
        );
    }

    public function findById(int $id): ?array
    {
        $combo = $this->fetch('SELECT * FROM combos WHERE id = ?', [$id]);
        if ($combo) {
            $combo['items'] = $this->getItems($id);
        }
        return $combo;
    }

    public function findBySlug(string $slug): ?array
    {
        $combo = $this->fetch('SELECT * FROM combos WHERE slug = ? AND active = 1', [$slug]);
        if ($combo) {
            $combo['items'] = $this->getItems((int)$combo['id']);
        }
        return $combo;
    }

    public function getItems(int $comboId): array
    {
        return $this->fetchAll(
            'SELECT ci.*, p.name, p.slug, p.price, p.price_sale, p.stock,
                    (SELECT filename_webp FROM product_images
                      WHERE product_id = p.id AND is_main = 1 LIMIT 1) AS main_image
               FROM combo_items ci
               JOIN products p ON p.id = ci.product_id
              WHERE ci.combo_id = ?
              ORDER BY ci.id ASC',
            [$comboId]
        );
    }

    public function create(array $data, array $items): int
    {
        $this->query(
            'INSERT INTO combos (name, slug, description, price, image, active)
             VALUES (?, ?, ?, ?, ?, ?)',
            [
                $data['name'],
                $data['slug'],
                $data['description'] ?? null,
                (float)$data['price'],
                $data['image'] ?? null,
                (int)($data['active'] ?? 1),
            ]
        );

        $id = $this->lastInsertId();
        $this->replaceItems($id, $items);
        return $id;
    }

    public function update(int $id, array $data, array $items): bool
    {
        $this->query(
            'UPDATE combos
                SET name = ?, slug = ?, description = ?, price = ?, image = ?, active = ?
              WHERE id = ?',
            [
                $data['name'],
                $data['slug'],
                $data['description'] ?? null,
                (float)$data['price'],
                $data['image'] ?? null,
                (int)($data['active'] ?? 1),
                $id,
            ]
        );

        $this->replaceItems($id, $items);
        return true;
    }

    public function toggleActive(int $id): bool
    {
        $this->query('UPDATE combos SET active = NOT active WHERE id = ?', [$id]);
        return true;
    }

    public function delete(int $id): bool
    {
        $this->query('DELETE FROM combos WHERE id = ?', [$id]);
        return true;
    }

    private function replaceItems(int $comboId, array $items): void
    {
        $this->query('DELETE FROM combo_items WHERE combo_id = ?', [$comboId]);

        foreach ($items as $item) {
            $productId = (int)($item['product_id'] ?? 0);
            $quantity = max(1, (int)($item['quantity'] ?? 1));

            if ($productId <= 0) {
                continue;
            }

            $this->query(
                'INSERT INTO combo_items (combo_id, product_id, quantity)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)',
                [$comboId, $productId, $quantity]
            );
        }
    }
}
