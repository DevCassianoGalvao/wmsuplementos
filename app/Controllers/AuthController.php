<?php

declare(strict_types=1);

namespace Maia\Controllers;

use Maia\Models\UserModel;
use Maia\Helpers\Auth;
use Maia\Helpers\CSRF;
use Maia\Helpers\Validator;
use Maia\Helpers\Sanitizer;

class AuthController extends BaseController
{
    public function loginForm(array $params = []): void
    {
        if (Auth::isUserLogged()) {
            $this->redirect('/minha-conta');
        }

        $this->render('auth/login', [
            'pageTitle' => 'Entrar | Maia Suplementos',
            'flash'     => $this->getFlash(),
        ]);
    }

    public function login(array $params = []): void
    {
        CSRF::verify();

        $email    = Sanitizer::email($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';
        $ip       = $this->clientIp();

        if (Auth::isLoginBlocked($email, $ip)) {
            $this->flash('error', 'Muitas tentativas. Aguarde 30 minutos.');
            $this->redirect('/entrar');
        }

        $model = new UserModel();
        $user  = $model->findByEmail($email);

        if (!$user || !$model->verifyPassword($password, $user['password_hash'])) {
            Auth::recordLoginAttempt($email, $ip);
            // Mensagem genérica — evita enumeração de usuários (seção 5.2 PRD)
            $this->flash('error', 'E-mail ou senha incorretos.');
            $this->redirect('/entrar');
        }

        Auth::clearLoginAttempts($email, $ip);
        Auth::loginUser($user);
        CSRF::rotate();

        $redirect = $_SESSION['redirect_after_login'] ?? '/minha-conta';
        unset($_SESSION['redirect_after_login']);
        $this->redirect($redirect);
    }

    public function registerForm(array $params = []): void
    {
        if (Auth::isUserLogged()) {
            $this->redirect('/minha-conta');
        }

        $this->render('auth/register', [
            'pageTitle' => 'Criar Conta | Maia Suplementos',
            'flash'     => $this->getFlash(),
        ]);
    }

    public function register(array $params = []): void
    {
        CSRF::verify();

        $name     = Sanitizer::plainText($_POST['name']     ?? '');
        $email    = Sanitizer::email($_POST['email']        ?? '');
        $phone    = Sanitizer::onlyDigits($_POST['phone']   ?? '');
        $password = $_POST['password']                      ?? '';
        $confirm  = $_POST['password_confirm']              ?? '';
        $optIn    = isset($_POST['email_opt_in']) ? 1 : 0;

        $v = new Validator([
            'name'             => $name,
            'email'            => $email,
            'phone'            => $phone,
            'password'         => $password,
            'password_confirm' => $confirm,
        ]);

        $v->required('name')->maxLen('name', 150)
          ->required('email')->email('email')->uniqueInDb('email', 'users', 'email', null, 'E-mail')
          ->required('phone')->phone('phone', 'Telefone')
          ->required('password')->minLen('password', 8)
          ->confirmed('password', 'password_confirm', 'Senhas');

        if ($v->fails()) {
            $this->flash('error', implode('<br>', $v->errors()));
            $this->redirect('/cadastro');
        }

        $model  = new UserModel();
        $userId = $model->create([
            'name'          => $name,
            'email'         => $email,
            'password'      => $password,
            'phone'         => $phone,
            'email_opt_in'  => $optIn,
        ]);

        $user = $model->findById($userId);
        Auth::loginUser($user);
        CSRF::rotate();

        $this->flash('success', 'Bem-vindo(a), ' . $name . '!');
        $this->redirect('/minha-conta');
    }

    public function logout(array $params = []): void
    {
        Auth::logoutUser();
        $this->redirect('/');
    }
}
