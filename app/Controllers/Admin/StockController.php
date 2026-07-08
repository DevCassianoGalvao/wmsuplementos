<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;
use Maia\Helpers\CSRF;
use Maia\Helpers\Sanitizer;
use Maia\Models\ProductModel;

class StockController extends BaseController
{
    public function index(array $params = []): void
    {
        Auth::requireAdmin();

        $page  = max(1, (int)($_GET['pagina'] ?? 1));
        $filter = $_GET['filtro'] ?? '';

        $sql    = 'SELECT p.id, p.name, p.slug, p.stock, p.stock_alert_threshold AS stock_alert, p.active,
                          c.name AS category_name
                     FROM products p
                     LEFT JOIN categories c ON c.id = p.category_id
                    WHERE 1=1';
        $params = [];

        if ($filter === 'baixo') {
            $sql .= ' AND p.stock <= p.stock_alert_threshold';
        } elseif ($filter === 'zerado') {
            $sql .= ' AND p.stock = 0';
        }

        $sql .= ' ORDER BY p.stock ASC, p.name ASC';

        $perPage = 30;
        $offset  = ($page - 1) * $perPage;
        $stmt    = db()->prepare($sql . ' LIMIT ? OFFSET ?');
        $stmt->execute(array_merge($params, [$perPage, $offset]));
        $products = $stmt->fetchAll();

        $stmt = db()->prepare(str_replace('SELECT p.id, p.name, p.slug, p.stock, p.stock_alert_threshold AS stock_alert, p.active,
                          c.name AS category_name', 'SELECT COUNT(*)', $sql));
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $movements = $this->getRecentMovements(20);

        $this->render('admin/stock/index', [
            'pageTitle'  => 'Estoque | Admin WM',
            'products'   => $products,
            'total'      => $total,
            'page'       => $page,
            'totalPages' => (int)ceil($total / $perPage),
            'filter'     => $filter,
            'movements'  => $movements,
            'flash'      => $this->getFlash(),
        ], 'admin');
    }

    public function adjust(array $params = []): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity  = (int)($_POST['quantity']   ?? 0);
        $type      = $_POST['type']             ?? '';
        $reason    = Sanitizer::plainText($_POST['reason'] ?? '');

        if (!$productId || !in_array($type, ['entrada', 'saida', 'ajuste'], true)) {
            $this->flash('error', 'Dados inválidos.');
            $this->redirect('/admin/estoque');
        }

        (new ProductModel())->adjustStock(
            $productId,
            abs($quantity),
            $type,
            $reason,
            null,
            $_SESSION['admin_id'] ?? null
        );

        $this->flash('success', 'Estoque ajustado com sucesso.');
        $this->redirect('/admin/estoque');
    }

    private function getRecentMovements(int $limit): array
    {
        $stmt = db()->prepare(
            'SELECT sm.*, p.name AS product_name
               FROM stock_movements sm
               JOIN products p ON p.id = sm.product_id
              ORDER BY sm.created_at DESC
              LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
