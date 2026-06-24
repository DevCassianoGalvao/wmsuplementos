<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;
use Maia\Helpers\CSRF;
use Maia\Models\UserModel;
use Maia\Models\OrderModel;
use Maia\Services\BrevoService;

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
            'tag'     => $_GET['tag']       ?? '',
            'spent_above_value' => $_GET['gastou_acima'] ?? '',
        ];
        if ($filters['spent_above_value'] !== '') {
            $filters['segment'] = 'spent_above';
        }

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
            'tag'     => $_GET['tag']       ?? '',
            'spent_above_value' => $_GET['gastou_acima'] ?? '',
        ];
        if ($filters['spent_above_value'] !== '') {
            $filters['segment'] = 'spent_above';
        }

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

    public function syncBrevo(array $params = []): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $filters = [
            'search' => $_GET['busca'] ?? '',
            'segment' => $_GET['segmento'] ?? '',
            'opt_in' => '1',
            'tag' => $_GET['tag'] ?? '',
            'spent_above_value' => $_GET['gastou_acima'] ?? '',
        ];
        if ($filters['spent_above_value'] !== '') {
            $filters['segment'] = 'spent_above';
        }

        $result = (new UserModel())->getList($filters, 1, 500);
        $sync = (new BrevoService())->syncContacts($result['items'] ?? []);

        $this->flash(
            $sync['failed'] > 0 ? 'error' : 'success',
            'Brevo: ' . (int)$sync['synced'] . ' contato(s) sincronizado(s), '
            . (int)$sync['failed'] . ' falha(s).'
        );
        $this->redirect('/admin/clientes?' . http_build_query($_GET));
    }

    public function anonymize(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            $this->flash('error', 'Cliente invalido.');
            $this->redirect('/admin/clientes');
        }

        (new UserModel())->anonymize($id);

        $this->flash('success', 'Cliente anonimizado conforme LGPD.');
        $this->redirect('/admin/clientes');
    }

    public function updateTag(array $params): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        $tag = (string)($_POST['tag'] ?? '');
        $allowed = ['', 'vip', 'atacado', 'bloqueado'];

        if ($id <= 0 || !in_array($tag, $allowed, true)) {
            $this->flash('error', 'Tag de cliente invalida.');
            $this->redirect('/admin/clientes');
        }

        (new UserModel())->updateTag($id, $tag);

        $this->flash('success', 'Tag do cliente atualizada.');
        $this->redirect('/admin/clientes/' . $id);
    }
}
