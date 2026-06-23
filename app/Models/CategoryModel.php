<?php

declare(strict_types=1);

namespace Maia\Models;

class CategoryModel extends BaseModel
{
    public function getAllActive(): array
    {
        return $this->fetchAll(
            'SELECT * FROM categories WHERE active = 1 ORDER BY sort_order ASC, name ASC'
        );
    }

    public function getAll(): array
    {
        return $this->fetchAll('SELECT * FROM categories ORDER BY sort_order ASC, name ASC');
    }

    public function findById(int $id): ?array
    {
        return $this->fetch('SELECT * FROM categories WHERE id = ?', [$id]);
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->fetch('SELECT * FROM categories WHERE slug = ? AND active = 1', [$slug]);
    }

    public function create(array $data): int
    {
        $this->query(
            'INSERT INTO categories (name, slug, seo_title, seo_description, image, active, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $data['name'],
                $data['slug'],
                $data['seo_title']       ?? null,
                $data['seo_description'] ?? null,
                $data['image']           ?? null,
                (int)($data['active']    ?? 1),
                (int)($data['sort_order'] ?? 0),
            ]
        );
        return $this->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $this->query(
            'UPDATE categories
                SET name = ?, slug = ?, seo_title = ?, seo_description = ?,
                    image = ?, active = ?, sort_order = ?
              WHERE id = ?',
            [
                $data['name'],
                $data['slug'],
                $data['seo_title']        ?? null,
                $data['seo_description']  ?? null,
                $data['image']            ?? null,
                (int)($data['active']     ?? 1),
                (int)($data['sort_order'] ?? 0),
                $id,
            ]
        );
        return true;
    }

    public function delete(int $id): bool
    {
        $this->query('DELETE FROM categories WHERE id = ?', [$id]);
        return true;
    }

    public function toggleActive(int $id): bool
    {
        $this->query('UPDATE categories SET active = NOT active WHERE id = ?', [$id]);
        return true;
    }

    public function countProducts(int $id): int
    {
        return (int)$this->fetchColumn(
            'SELECT COUNT(*) FROM products WHERE category_id = ? AND active = 1',
            [$id]
        );
    }
}
