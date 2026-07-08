<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;
use Maia\Helpers\CSRF;
use Maia\Helpers\Sanitizer;
use Maia\Models\OrderModel;

class OrderController extends BaseController
{
    private OrderModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new OrderModel();
    }

    public function index(array $params = []): void
    {
        Auth::requireAdmin();

        $page    = max(1, (int)($_GET['pagina'] ?? 1));
        $filters = [
            'status'         => $_GET['status']   ?? '',
            'date_from'      => $_GET['de']        ?? '',
            'date_to'        => $_GET['ate']       ?? '',
            'customer_email' => Sanitizer::email($_GET['email'] ?? ''),
            'min_total'      => (float)($_GET['min'] ?? 0) ?: null,
            'max_total'      => (float)($_GET['max'] ?? 0) ?: null,
        ];

        $orders = $this->model->getList($filters, $page, 25);
        $total  = $this->model->countList($filters);

        $this->render('admin/orders/index', [
            'pageTitle'  => 'Pedidos | Admin WM',
            'orders'     => $orders,
            'total'      => $total,
            'page'       => $page,
            'totalPages' => (int)ceil($total / 25),
            'filters'    => $filters,
            'statuses'   => OrderModel::allStatuses(),
            'flash'      => $this->getFlash(),
        ], 'admin');
    }

    public function show(array $params): void
    {
        Auth::requireAdmin();

        $id    = (int)($params['id'] ?? 0);
        $order = $this->model->findById($id);

        if (!$order) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('admin/orders/show', [
            'pageTitle' => 'Pedido #' . $id . ' | Admin WM',
            'order'     => $order,
            'statuses'  => OrderModel::allStatuses(),
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function updateStatus(array $params): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $id             = (int)($params['id'] ?? 0);
        $newStatus      = $_POST['status']        ?? '';
        $note           = Sanitizer::plainText($_POST['note'] ?? '');
        $trackingCode   = Sanitizer::plainText($_POST['tracking_code'] ?? '');

        $validStatuses = array_keys(OrderModel::allStatuses());
        if (!in_array($newStatus, $validStatuses, true)) {
            $this->flash('error', 'Status inválido.');
            $this->redirect('/admin/pedidos/' . $id);
        }

        if ($trackingCode) {
            $this->model->updateTrackingCode($id, $trackingCode);
        }

        $this->model->updateStatus($id, $newStatus, $note ?: null, $_SESSION['admin_id'] ?? null);

        // Enfileira e-mail de status
        $this->queueStatusEmail($id, $newStatus);

        $this->flash('success', 'Status atualizado.');
        $this->redirect('/admin/pedidos/' . $id);
    }

    public function export(array $params = []): void
    {
        Auth::requireAdminRole();

        $filters = [
            'status'    => $_GET['status']   ?? '',
            'date_from' => $_GET['de']        ?? date('Y-m-01'),
            'date_to'   => $_GET['ate']       ?? date('Y-m-d'),
        ];

        $orders = $this->model->getForExport($filters);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="pedidos-' . date('Ymd') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Data', 'Cliente', 'Email', 'Telefone', 'Pagamento', 'Status', 'Subtotal', 'Desconto', 'Total']);

        foreach ($orders as $row) {
            fputcsv($out, [
                $row['id'],
                $row['created_at'],
                $row['customer_name'],
                $row['customer_email'],
                $row['customer_phone'],
                $row['payment_method'],
                $row['status'],
                number_format((float)$row['subtotal'], 2, ',', '.'),
                number_format((float)$row['discount'], 2, ',', '.'),
                number_format((float)$row['total'], 2, ',', '.'),
            ]);
        }

        fclose($out);
        exit;
    }

    private function queueStatusEmail(int $orderId, string $status): void
    {
        $templateMap = [
            'em_preparacao' => 'em_preparacao',
            'enviado'       => 'pedido_enviado',
            'entregue'      => 'pedido_entregue',
        ];

        $template = $templateMap[$status] ?? null;
        if (!$template) {
            return;
        }

        $order = $this->model->findById($orderId);
        if (!$order) {
            return;
        }

        db()->prepare(
            'INSERT INTO email_queue (template, to_email, to_name, payload, status, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())'
        )->execute([
            $template,
            $order['customer_email'],
            $order['customer_name'],
            json_encode([
                'order_id'      => $orderId,
                'customer_name' => $order['customer_name'],
                'total'         => $order['total'],
                'tracking_code' => $order['tracking_code'] ?? '',
            ]),
            'pending',
        ]);
    }
}
