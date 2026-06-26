<?php

declare(strict_types=1);

namespace Maia\Models;

class UserModel extends BaseModel
{
    // ─── Leitura ─────────────────────────────────────────────────────────────

    public function findById(int $id): ?array
    {
        return $this->fetch('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->fetch('SELECT * FROM users WHERE email = ?', [$email]);
    }

    /**
     * Listagem com filtros de segmentação CRM (seção 4.5 PRD).
     *
     * @param array $filters  search, tag, segment (never_bought | bought_once |
     *                        bought_3plus | inactive_30 | inactive_60 | inactive_90 |
     *                        spent_above), spent_above_value
     */
    public function getList(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $term     = '%' . $filters['search'] . '%';
            $where[]  = '(name LIKE ? OR email LIKE ? OR phone LIKE ?)';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['tag'])) {
            $where[]  = 'tag = ?';
            $params[] = $filters['tag'];
        }

        if (($filters['opt_in'] ?? '') !== '' && ($filters['opt_in'] ?? null) !== null) {
            $where[]  = 'email_opt_in = ?';
            $params[] = (int)$filters['opt_in'];
        }

        if (!empty($filters['segment'])) {
            switch ($filters['segment']) {
                case 'never_bought':
                    $where[] = 'total_orders = 0';
                    break;
                case 'bought_once':
                    $where[] = 'total_orders = 1';
                    break;
                case 'bought_3plus':
                    $where[] = 'total_orders >= 3';
                    break;
                case 'inactive_30':
                    $where[] = 'last_purchase_at < DATE_SUB(NOW(), INTERVAL 30 DAY)';
                    break;
                case 'inactive_60':
                    $where[] = 'last_purchase_at < DATE_SUB(NOW(), INTERVAL 60 DAY)';
                    break;
                case 'inactive_90':
                    $where[] = 'last_purchase_at < DATE_SUB(NOW(), INTERVAL 90 DAY)';
                    break;
                case 'spent_above':
                    $where[]  = 'total_spent >= ?';
                    $params[] = (float)($filters['spent_above_value'] ?? 0);
                    break;
            }
        }

        $whereStr = implode(' AND ', $where);

        $total = (int)$this->fetchColumn("SELECT COUNT(*) FROM users WHERE {$whereStr}", $params);

        [$limit, $offset] = $this->limitOffset($page, $perPage);
        $params[]         = $limit;
        $params[]         = $offset;

        $rows = $this->fetchAll(
            "SELECT id, name, email, phone, city, state, tag, email_opt_in, total_orders,
                    total_spent, last_purchase_at, created_at
               FROM users
              WHERE {$whereStr}
              ORDER BY created_at DESC
              LIMIT ? OFFSET ?",
            $params
        );

        return ['items' => $rows, 'total' => $total];
    }

    public function getRecentCount(int $days = 30): int
    {
        return (int)$this->fetchColumn(
            'SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)',
            [$days]
        );
    }

    // ─── Escrita ──────────────────────────────────────────────────────────────

    public function create(array $data): int
    {
        $this->query(
            'INSERT INTO users (name, email, password_hash, phone, city, state, email_opt_in)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $data['name'],
                $data['email'],
                password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
                $data['phone']        ?? null,
                $data['city']         ?? null,
                $data['state']        ?? null,
                (int)($data['email_opt_in'] ?? 1),
            ]
        );
        return $this->lastInsertId();
    }

    /** Atualização genérica de colunas permitidas (CRUD admin). */
    public function update(int $id, array $data): void
    {
        $sets   = [];
        $params = [];
        $allowed = ['name', 'email', 'phone', 'password_hash', 'tag'];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $sets[]   = "`{$col}` = ?";
                $params[] = $data[$col];
            }
        }
        if (empty($sets)) {
            return;
        }
        $params[] = $id;
        $this->query('UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = ?', $params);
    }

    public function updateProfile(int $id, array $data): bool
    {
        $this->query(
            'UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?',
            [$data['name'], $data['email'], $data['phone'] ?? null, $id]
        );
        return true;
    }

    public function updatePassword(int $id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $this->query('UPDATE users SET password_hash = ? WHERE id = ?', [$hash, $id]);
        return true;
    }

    public function updateTag(int $id, string $tag): bool
    {
        $this->query('UPDATE users SET tag = ? WHERE id = ?', [$tag, $id]);
        return true;
    }

    public function setEmailOptOut(string $email): bool
    {
        $this->query('UPDATE users SET email_opt_in = 0 WHERE email = ?', [$email]);
        return true;
    }

    /** Atualiza contadores após confirmação de pedido. */
    public function recordPurchase(int $id, float $orderTotal): void
    {
        $this->query(
            'UPDATE users
                SET total_orders    = total_orders + 1,
                    total_spent     = total_spent + ?,
                    last_purchase_at = NOW()
              WHERE id = ?',
            [$orderTotal, $id]
        );
    }

    /** Anonimiza dados pessoais mantendo histórico (LGPD seção 5.4). */
    public function anonymize(int $id): bool
    {
        $anon = 'anonimizado_' . $id;
        $hash = password_hash(bin2hex(random_bytes(32)), PASSWORD_BCRYPT, ['cost' => 12]);
        $this->query(
            'UPDATE users
                SET name          = ?,
                    email         = ?,
                    phone         = NULL,
                    city          = NULL,
                    state         = NULL,
                    password_hash = ?,
                    email_opt_in  = 0
              WHERE id = ?',
            [$anon, $anon . '@removido.local', $hash, $id]
        );
        return true;
    }

    // ─── Autenticação ─────────────────────────────────────────────────────────

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function countList(array $filters = []): int
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $term     = '%' . $filters['search'] . '%';
            $where[]  = '(name LIKE ? OR email LIKE ? OR phone LIKE ?)';
            $params   = array_merge($params, [$term, $term, $term]);
        }
        if (!empty($filters['tag'])) {
            $where[]  = 'tag = ?';
            $params[] = $filters['tag'];
        }
        if (($filters['opt_in'] ?? '') !== '' && ($filters['opt_in'] ?? null) !== null) {
            $where[]  = 'email_opt_in = ?';
            $params[] = (int)$filters['opt_in'];
        }
        if (!empty($filters['segment'])) {
            switch ($filters['segment']) {
                case 'never_bought':
                    $where[] = 'total_orders = 0';
                    break;
                case 'bought_once':
                    $where[] = 'total_orders = 1';
                    break;
                case 'bought_3plus':
                    $where[] = 'total_orders >= 3';
                    break;
                case 'inactive_30':
                    $where[] = 'last_purchase_at < DATE_SUB(NOW(), INTERVAL 30 DAY)';
                    break;
                case 'inactive_60':
                    $where[] = 'last_purchase_at < DATE_SUB(NOW(), INTERVAL 60 DAY)';
                    break;
                case 'inactive_90':
                    $where[] = 'last_purchase_at < DATE_SUB(NOW(), INTERVAL 90 DAY)';
                    break;
                case 'spent_above':
                    $where[]  = 'total_spent >= ?';
                    $params[] = (float)($filters['spent_above_value'] ?? 0);
                    break;
            }
        }

        return (int)$this->fetchColumn(
            'SELECT COUNT(*) FROM users WHERE ' . implode(' AND ', $where),
            $params
        );
    }
}
