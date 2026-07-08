<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;
use Maia\Helpers\CSRF;
use Maia\Helpers\Sanitizer;

class AuthController extends BaseController
{
    public function loginForm(array $params = []): void
    {
        if (Auth::isAdminLogged()) {
            $this->redirect(Auth::isAdmin() ? '/admin/dashboard' : '/admin/pedidos');
        }

        $this->render('admin/auth/login', [
            'pageTitle' => 'Admin Login | WM Suplementos',
            'flash'     => $this->getFlash(),
        ], 'admin_bare');
    }

    public function login(array $params = []): void
    {
        CSRF::verify();

        $email    = Sanitizer::email($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';
        $ip       = $this->clientIp();

        if (Auth::isLoginBlocked($email, $ip)) {
            $this->flash('error', 'Muitas tentativas. Aguarde 30 minutos.');
            $this->redirect('/admin/login');
        }

        $stmt = db()->prepare(
            "SELECT * FROM admin_users WHERE email = ? AND active = 1"
        );
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            Auth::recordLoginAttempt($email, $ip);
            $this->flash('error', 'Credenciais inválidas.');
            $this->redirect('/admin/login');
        }

        Auth::clearLoginAttempts($email, $ip);
        Auth::loginAdmin($admin);
        CSRF::rotate();

        $this->redirect(Auth::isAdmin() ? '/admin/dashboard' : '/admin/pedidos');
    }

    public function logout(array $params = []): void
    {
        Auth::logoutAdmin();
        $this->redirect('/admin/login');
    }
}
