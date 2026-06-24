<?php

declare(strict_types=1);

namespace Maia\Models;

class BrandModel extends BaseModel
{
    public function getAll(): array
    {
        return $this->fetchAll('SELECT * FROM brands ORDER BY name ASC');
    }

    public function getActive(): array
    {
        return $this->fetchAll('SELECT * FROM brands WHERE active = 1 ORDER BY name ASC');
    }

    public function getAllWithProductCount(): array
    {
        return $this->fetchAll(
            'SELECT b.*, COUNT(p.id) AS product_count
               FROM brands b
               LEFT JOIN products p ON p.brand_id = b.id
              GROUP BY b.id
              ORDER BY b.name ASC'
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetch('SELECT * FROM brands WHERE id = ?', [$id]);
    }

    public function create(array $data): int
    {
        $this->query(
            'INSERT INTO brands (name, slug, logo, description, active)
             VALUES (?, ?, ?, ?, ?)',
            [
                $data['name'],
                $data['slug'],
                $data['logo'] ?? null,
                $data['description'] ?? null,
                (int)($data['active'] ?? 1),
            ]
        );

        return $this->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $this->query(
            'UPDATE brands
                SET name = ?, slug = ?, logo = ?, description = ?, active = ?
              WHERE id = ?',
            [
                $data['name'],
                $data['slug'],
                $data['logo'] ?? null,
                $data['description'] ?? null,
                (int)($data['active'] ?? 1),
                $id,
            ]
        );

        return true;
    }

    public function toggleActive(int $id): bool
    {
        $this->query('UPDATE brands SET active = NOT active WHERE id = ?', [$id]);
        return true;
    }

    public function delete(int $id): bool
    {
        $this->query('DELETE FROM brands WHERE id = ?', [$id]);
        return true;
    }

    public function countProducts(int $id): int
    {
        return (int)$this->fetchColumn('SELECT COUNT(*) FROM products WHERE brand_id = ?', [$id]);
    }
}
