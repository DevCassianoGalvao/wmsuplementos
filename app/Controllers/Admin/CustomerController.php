<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;
use Maia\Models\UserModel;
use Maia\Models\OrderModel;

class CustomerController extends BaseController
{
    public function index(array $params = []): void
    {
        Auth::requireAdmin();

        $page    = max(1, (int)($_GET['pagina'] ?? 1));
        $filters = [
            'search'  => $_GET['busca']     ?? '',
            'segment' => $_GET['segmento']  ?? '',
            'opt_in'  => $_GET['opt_in']    ?? '',
        ];

        $model     = new UserModel();
        $customerList = $model->getList($filters, $page, 30);
        $customers    = $customerList['items'] ?? [];
        $total        = (int)($customerList['total'] ?? $model->countList($filters));

        $this->render('admin/customers/index', [
            'pageTitle'  => 'Clientes | Admin Maia',
            'customers'  => $customers,
            'total'      => $total,
            'page'       => $page,
            'totalPages' => (int)ceil($total / 30),
            'filters'    => $filters,
            'flash'      => $this->getFlash(),
        ], 'admin');
    }

    public function show(array $params): void
    {
        Auth::requireAdmin();

        $id   = (int)($params['id'] ?? 0);
        $user = (new UserModel())->findById($id);

        if (!$user) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $orders = (new OrderModel())->getByUser($id, 1, 20);

        $this->render('admin/customers/show', [
            'pageTitle' => $user['name'] . ' | Clientes | Admin Maia',
            'customer'  => $user,
            'orders'    => $orders,
        ], 'admin');
    }

    public function export(array $params = []): void
    {
        Auth::requireAdmin();

        $filters = [
            'search'  => $_GET['busca']     ?? '',
            'segment' => $_GET['segmento']  ?? '',
            'opt_in'  => $_GET['opt_in']    ?? '',
        ];

        $result = (new UserModel())->getList($filters, 1, 100000);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="clientes-' . date('Ymd') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Nome', 'Email', 'Telefone', 'Cidade', 'Estado', 'Tag', 'Pedidos', 'Total gasto', 'Ultima compra', 'Opt-in', 'Cadastro']);

        foreach ($result['items'] as $row) {
            fputcsv($out, [
                $row['id'],
                $row['name'],
                $row['email'],
                $row['phone'],
                $row['city'],
                $row['state'],
                $row['tag'],
                $row['total_orders'],
                number_format((float)$row['total_spent'], 2, ',', '.'),
                $row['last_purchase_at'],
                !empty($row['email_opt_in']) ? 'sim' : 'nao',
                $row['created_at'],
            ]);
        }

        fclose($out);
        exit;
    }
}
