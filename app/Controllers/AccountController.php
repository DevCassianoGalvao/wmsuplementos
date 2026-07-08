<?php

declare(strict_types=1);

namespace Maia\Controllers;

use Maia\Models\UserModel;
use Maia\Models\OrderModel;
use Maia\Helpers\Auth;
use Maia\Helpers\CSRF;
use Maia\Helpers\Validator;
use Maia\Helpers\Sanitizer;

class AccountController extends BaseController
{
    public function index(array $params = []): void
    {
        Auth::requireUser();

        $user = (new UserModel())->findById(Auth::userId());

        $this->render('account/index', [
            'pageTitle' => 'Minha Conta | WM Suplementos',
            'user'      => $user,
            'flash'     => $this->getFlash(),
        ]);
    }

    public function orders(array $params = []): void
    {
        Auth::requireUser();

        $page   = max(1, (int)($_GET['pagina'] ?? 1));
        $orders = (new OrderModel())->getByUser(Auth::userId(), $page, 10);

        $this->render('account/orders', [
            'pageTitle' => 'Meus Pedidos | WM Suplementos',
            'orders'    => $orders,
            'page'      => $page,
        ]);
    }

    public function orderDetail(array $params): void
    {
        Auth::requireUser();

        $orderId    = (int)($params['id'] ?? 0);
        $orderModel = new OrderModel();
        $order      = $orderModel->findById($orderId);

        if (!$order || (int)$order['user_id'] !== Auth::userId()) {
            http_response_code(403);
            $this->render('errors/404');
            return;
        }

        $this->render('account/order_detail', [
            'pageTitle' => 'Pedido #' . $orderId . ' | WM Suplementos',
            'order'     => $order,
        ]);
    }

    public function updateProfile(array $params = []): void
    {
        Auth::requireUser();
        CSRF::verify();

        $name  = Sanitizer::plainText($_POST['name']  ?? '');
        $email = Sanitizer::email($_POST['email']     ?? '');
        $phone = Sanitizer::onlyDigits($_POST['phone'] ?? '');

        $v = new Validator(['name' => $name, 'email' => $email, 'phone' => $phone]);
        $v->required('name')->maxLen('name', 150)
          ->required('email')->email('email')
          ->uniqueInDb('email', 'users', 'email', Auth::userId(), 'E-mail')
          ->required('phone')->phone('phone', 'Telefone');

        if ($v->fails()) {
            $this->flash('error', implode('<br>', $v->errors()));
            $this->redirect('/minha-conta');
        }

        (new UserModel())->updateProfile(Auth::userId(), [
            'name'  => $name,
            'email' => $email,
            'phone' => $phone,
        ]);

        $_SESSION['user_name']  = $name;
        $_SESSION['user_email'] = $email;

        $this->flash('success', 'Dados atualizados com sucesso.');
        $this->redirect('/minha-conta');
    }

    public function deleteAccount(array $params = []): void
    {
        Auth::requireUser();
        CSRF::verify();

        $confirmation = trim((string)($_POST['confirmation'] ?? ''));
        if ($confirmation !== 'EXCLUIR') {
            $this->flash('error', 'Digite EXCLUIR para confirmar a remocao dos seus dados pessoais.');
            $this->redirect('/minha-conta');
        }

        (new UserModel())->anonymize(Auth::userId());
        Auth::logoutUser();

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Conta anonimizada. Seus dados pessoais foram removidos e o historico financeiro permanece sem identificacao pessoal.',
        ];

        $this->redirect('/entrar');
    }
}
