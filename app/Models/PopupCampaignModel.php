<?php

declare(strict_types=1);

namespace Maia\Models;

use PDOException;

class PopupCampaignModel extends BaseModel
{
    public function getAll(): array
    {
        return $this->fetchAll(
            'SELECT *
               FROM popup_campaigns
              ORDER BY active DESC, created_at DESC, id DESC'
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetch('SELECT * FROM popup_campaigns WHERE id = ?', [$id]);
    }

    public function getActiveForPublic(): ?array
    {
        try {
            return $this->fetch(
                'SELECT *
                   FROM popup_campaigns
                  WHERE active = 1
                    AND (start_at IS NULL OR start_at <= NOW())
                    AND (end_at IS NULL OR end_at >= NOW())
                  ORDER BY created_at DESC, id DESC
                  LIMIT 1'
            );
        } catch (PDOException $e) {
            error_log('[PopupCampaignModel] tabela ausente ou invalida: ' . $e->getMessage());
            return null;
        }
    }

    public function create(array $data): int
    {
        $this->query(
            'INSERT INTO popup_campaigns
                (title, slug, mode, image, target_url, message, coupon_code, cta_label, active, start_at, end_at, show_once)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            $this->params($data)
        );

        return $this->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $params = $this->params($data);
        $params[] = $id;

        $this->query(
            'UPDATE popup_campaigns
                SET title = ?,
                    slug = ?,
                    mode = ?,
                    image = ?,
                    target_url = ?,
                    message = ?,
                    coupon_code = ?,
                    cta_label = ?,
                    active = ?,
                    start_at = ?,
                    end_at = ?,
                    show_once = ?
              WHERE id = ?',
            $params
        );

        return true;
    }

    public function toggleActive(int $id): bool
    {
        $this->query('UPDATE popup_campaigns SET active = NOT active WHERE id = ?', [$id]);
        return true;
    }

    public function delete(int $id): ?array
    {
        $campaign = $this->findById($id);
        if (!$campaign) {
            return null;
        }

        $this->query('DELETE FROM popup_campaigns WHERE id = ?', [$id]);
        return $campaign;
    }

    public function tableExists(): bool
    {
        try {
            $this->fetchColumn('SELECT 1 FROM popup_campaigns LIMIT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    private function params(array $data): array
    {
        return [
            $data['title'],
            $data['slug'],
            $data['mode'],
            $data['image'] ?: null,
            $data['target_url'] ?: null,
            $data['message'] ?: null,
            $data['coupon_code'] ?: null,
            $data['cta_label'] ?: null,
            (int)$data['active'],
            $data['start_at'] ?: null,
            $data['end_at'] ?: null,
            (int)$data['show_once'],
        ];
    }
}
