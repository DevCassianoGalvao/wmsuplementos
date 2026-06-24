<?php

declare(strict_types=1);

namespace Maia\Models;

class OrderModel extends BaseModel
{
    public const STATUS_WAITING  = 'aguardando_pagamento';
    public const STATUS_PAID     = 'pago';
    public const STATUS_PREPARING = 'em_preparacao';
    public const STATUS_SHIPPED  = 'enviado';
    public const STATUS_DELIVERED = 'entregue';
    public const STATUS_CANCELLED = 'cancelado';

    // ─── Leitura ─────────────────────────────────────────────────────────────

    public function findById(int $id): ?array
    {
        $order = $this->fetch('SELECT * FROM orders WHERE id = ?', [$id]);
        if ($order) {
            $order['items']   = $this->getItems($id);
            $order['history'] = $this->getStatusHistory($id);
        }
        return $order;
    }

    public function findByPaymentId(string $paymentId): ?array
    {
        return $this->fetch('SELECT * FROM orders WHERE payment_id = ?', [$paymentId]);
    }

    /**
     * Listagem com filtros para painel admin (seção 4.4 PRD).
     *
     * @param array $filters  status, date_from, date_to, customer_email, min_total, max_total
     */
    public function getList(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]  = 'status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $where[]  = 'DATE(created_at) >= ?';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[]  = 'DATE(created_at) <= ?';
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['customer_email'])) {
            $where[]  = 'customer_email LIKE ?';
            $params[] = '%' . $filters['customer_email'] . '%';
        }

        if (!empty($filters['min_total'])) {
            $where[]  = 'total >= ?';
            $params[] = (float)$filters['min_total'];
        }

        if (!empty($filters['max_total'])) {
            $where[]  = 'total <= ?';
            $params[] = (float)$filters['max_total'];
        }

        $whereStr = implode(' AND ', $where);

        $total = (int)$this->fetchColumn("SELECT COUNT(*) FROM orders WHERE {$whereStr}", $params);

        [$limit, $offset] = $this->limitOffset($page, $perPage);
        $params[]         = $limit;
        $params[]         = $offset;

        $rows = $this->fetchAll(
            "SELECT * FROM orders WHERE {$whereStr} ORDER BY created_at DESC LIMIT ? OFFSET ?",
            $params
        );

        return ['items' => $rows, 'total' => $total];
    }

    public function getByUser(int $userId, int $page = 1, int $perPage = 10): array
    {
        [$limit, $offset] = $this->limitOffset($page, $perPage);
        return $this->fetchAll(
            'SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?',
            [$userId, $limit, $offset]
        );
    }

    public function getItems(int $orderId): array
    {
        return $this->fetchAll(
            'SELECT oi.*, p.slug AS product_slug
               FROM order_items oi
               LEFT JOIN products p ON p.id = oi.product_id
              WHERE oi.order_id = ?',
            [$orderId]
        );
    }

    public function getStatusHistory(int $orderId): array
    {
        return $this->fetchAll(
            'SELECT h.*, a.name AS admin_name
               FROM order_status_history h
               LEFT JOIN admin_users a ON a.id = h.created_by
              WHERE h.order_id = ?
              ORDER BY h.created_at ASC',
            [$orderId]
        );
    }

    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function getSalesSummary(string $period = 'day'): array
    {
        // MariaDB não suporta "INTERVAL n DAY * 2" — usamos pares explícitos
        $intervals = [
            'day'   => ['cur' => 'INTERVAL 1 DAY',   'prev' => 'INTERVAL 2 DAY'],
            'week'  => ['cur' => 'INTERVAL 7 DAY',   'prev' => 'INTERVAL 14 DAY'],
            'month' => ['cur' => 'INTERVAL 30 DAY',  'prev' => 'INTERVAL 60 DAY'],
        ];
        $int      = $intervals[$period] ?? $intervals['day'];
        $cur      = $int['cur'];
        $prev     = $int['prev'];

        $current = $this->fetch(
            "SELECT COUNT(*) AS orders, COALESCE(SUM(total), 0) AS revenue,
                    COALESCE(AVG(total), 0) AS avg_ticket
               FROM orders
              WHERE status != ? AND created_at >= DATE_SUB(NOW(), {$cur})",
            [self::STATUS_CANCELLED]
        );

        $previous = $this->fetch(
            "SELECT COUNT(*) AS orders, COALESCE(SUM(total), 0) AS revenue
               FROM orders
              WHERE status != ?
                AND created_at >= DATE_SUB(NOW(), {$prev})
                AND created_at <  DATE_SUB(NOW(), {$cur})",
            [self::STATUS_CANCELLED]
        );

        return ['current' => $current, 'previous' => $previous];
    }

    public function countByStatus(): array
    {
        return $this->fetchAll(
            'SELECT status, COUNT(*) AS total FROM orders GROUP BY status'
        );
    }

    public function getDailySales(int $days = 30): array
    {
        return $this->fetchAll(
            'SELECT DATE(created_at) AS date,
                    COUNT(*) AS orders,
                    COALESCE(SUM(total), 0) AS revenue
               FROM orders
              WHERE status != ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
              GROUP BY DATE(created_at)
              ORDER BY date ASC',
            [self::STATUS_CANCELLED, $days]
        );
    }

    public function getTopProducts(int $month, int $year, int $limit = 5): array
    {
        return $this->fetchAll(
            'SELECT oi.product_id, oi.product_name,
                    SUM(oi.quantity) AS units_sold,
                    SUM(oi.subtotal) AS revenue
               FROM order_items oi
               JOIN orders o ON o.id = oi.order_id
              WHERE o.status != ?
                AND MONTH(o.created_at) = ?
                AND YEAR(o.created_at) = ?
                AND oi.product_id IS NOT NULL
              GROUP BY oi.product_id, oi.product_name
              ORDER BY units_sold DESC
              LIMIT ?',
            [self::STATUS_CANCELLED, $month, $year, $limit]
        );
    }

    // ─── Escrita ──────────────────────────────────────────────────────────────

    public function create(array $data): int
    {
        $this->query(
            'INSERT INTO orders
                (user_id, customer_name, customer_email, customer_phone, status,
                 subtotal, discount, total, coupon_id, coupon_code, payment_method)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                !empty($data['user_id'])   ? (int)$data['user_id'] : null,
                $data['customer_name'],
                $data['customer_email'],
                $data['customer_phone']    ?? null,
                self::STATUS_WAITING,
                (float)$data['subtotal'],
                (float)($data['discount']  ?? 0),
                (float)$data['total'],
                !empty($data['coupon_id']) ? (int)$data['coupon_id'] : null,
                $data['coupon_code']       ?? null,
                $data['payment_method']    ?? null,
            ]
        );

        $orderId = $this->lastInsertId();

        foreach ($data['items'] as $item) {
            $this->query(
                'INSERT INTO order_items
                    (order_id, product_id, combo_id, product_name, price, quantity, subtotal)
                 VALUES (?, ?, ?, ?, ?, ?, ?)',
                [
                    $orderId,
                    !empty($item['product_id']) ? (int)$item['product_id'] : null,
                    !empty($item['combo_id'])   ? (int)$item['combo_id']   : null,
                    $item['product_name'],
                    (float)$item['price'],
                    (int)$item['quantity'],
                    (float)$item['subtotal'],
                ]
            );
        }

        $this->addStatusHistory($orderId, self::STATUS_WAITING);

        return $orderId;
    }

    public function updatePayment(int $id, string $paymentId, string $paymentStatus, string $paymentMethod): bool
    {
        $this->query(
            'UPDATE orders SET payment_id = ?, payment_status = ?, payment_method = ? WHERE id = ?',
            [$paymentId, $paymentStatus, $paymentMethod, $id]
        );
        return true;
    }

    public function updateStatus(int $id, string $status, ?string $note = null, ?int $adminId = null): bool
    {
        $this->query('UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?', [$status, $id]);
        $this->addStatusHistory($id, $status, $note, $adminId);

        if ($status === self::STATUS_PAID) {
            $this->decrementStockForPaidOrder($id);
        }

        return true;
    }

    public function decrementStockForPaidOrder(int $orderId): void
    {
        $pdo = db();
        $startedTransaction = !$pdo->inTransaction();

        if ($startedTransaction) {
            $pdo->beginTransaction();
        }

        try {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*)
                   FROM stock_movements
                  WHERE reference_id = ?
                    AND reason = 'order_paid'
                    AND type = 'out'"
            );
            $stmt->execute([$orderId]);

            if ((int)$stmt->fetchColumn() > 0) {
                if ($startedTransaction) {
                    $pdo->commit();
                }
                return;
            }

            $items = $this->getItems($orderId);
            $order = $this->findById($orderId);
            $productModel = new ProductModel();

            foreach ($items as $item) {
                $orderQuantity = max(1, (int)$item['quantity']);

                if (!empty($item['product_id'])) {
                    $productId = (int)$item['product_id'];
                    $productModel->adjustStock($productId, $orderQuantity, 'out', 'order_paid', $orderId);
                    $productModel->incrementSold($productId, $orderQuantity);
                    continue;
                }

                if (!empty($item['combo_id'])) {
                    $comboItems = $pdo->prepare(
                        'SELECT product_id, quantity FROM combo_items WHERE combo_id = ?'
                    );
                    $comboItems->execute([(int)$item['combo_id']]);

                    foreach ($comboItems->fetchAll(\PDO::FETCH_ASSOC) as $comboItem) {
                        $productId = (int)$comboItem['product_id'];
                        $quantity = max(1, (int)$comboItem['quantity']) * $orderQuantity;
                        $productModel->adjustStock($productId, $quantity, 'out', 'order_paid', $orderId);
                        $productModel->incrementSold($productId, $quantity);
                    }
                }
            }

            if (!empty($order['user_id'])) {
                (new UserModel())->recordPurchase((int)$order['user_id'], (float)$order['total']);
            }

            if (!empty($order['coupon_id'])) {
                $pdo->prepare('UPDATE coupons SET used_count = used_count + 1 WHERE id = ?')
                    ->execute([(int)$order['coupon_id']]);
            }

            if ($startedTransaction) {
                $pdo->commit();
            }
        } catch (\Throwable $e) {
            if ($startedTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public function updateTrackingCode(int $id, string $code): bool
    {
        $this->query('UPDATE orders SET tracking_code = ?, updated_at = NOW() WHERE id = ?', [$code, $id]);
        return true;
    }

    public function addStatusHistory(int $orderId, string $status, ?string $note = null, ?int $createdBy = null): void
    {
        $this->query(
            'INSERT INTO order_status_history (order_id, status, note, created_by) VALUES (?, ?, ?, ?)',
            [$orderId, $status, $note, $createdBy]
        );
    }

    // ─── Exportação CSV ───────────────────────────────────────────────────────

    public function getForExport(array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]  = 'status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $where[]  = 'DATE(created_at) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[]  = 'DATE(created_at) <= ?';
            $params[] = $filters['date_to'];
        }

        return $this->fetchAll(
            'SELECT id, customer_name, customer_email, customer_phone, status,
                    subtotal, discount, total, coupon_code, payment_method,
                    payment_id, tracking_code, created_at
               FROM orders WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at ASC',
            $params
        );
    }

    public function countList(array $filters = []): int
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]  = 'status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $where[]  = 'DATE(created_at) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[]  = 'DATE(created_at) <= ?';
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['customer_email'])) {
            $where[]  = 'customer_email LIKE ?';
            $params[] = '%' . $filters['customer_email'] . '%';
        }

        return (int)$this->fetchColumn(
            'SELECT COUNT(*) FROM orders WHERE ' . implode(' AND ', $where),
            $params
        );
    }

    public static function allStatuses(): array
    {
        return [
            self::STATUS_WAITING   => 'Aguardando Pagamento',
            self::STATUS_PAID      => 'Pago',
            self::STATUS_PREPARING => 'Em Preparação',
            self::STATUS_SHIPPED   => 'Enviado',
            self::STATUS_DELIVERED => 'Entregue',
            self::STATUS_CANCELLED => 'Cancelado',
        ];
    }
}
