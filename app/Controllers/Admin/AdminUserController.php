<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;
use Maia\Helpers\CSRF;
use Maia\Helpers\Sanitizer;
use Maia\Helpers\Validator;

class AdminUserController extends BaseController
{
    public function index(array $params = []): void
    {
        Auth::requireAdminRole();

        $users = db()->query(
            'SELECT id, name, email, role, active, last_login, created_at
               FROM admin_users
              ORDER BY role ASC, name ASC'
        )->fetchAll();

        $this->render('admin/users/index', [
            'pageTitle' => 'Usuários Admin | Admin WM',
            'users'     => $users,
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function create(array $params = []): void
    {
        Auth::requireAdminRole();

        $this->render('admin/users/form', [
            'pageTitle' => 'Novo Usuário | Admin WM',
            'user'      => null,
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function store(array $params = []): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $data = $this->extractData();
        $password = (string)($_POST['password'] ?? '');
        $validator = $this->validateData($data, $password);

        if ($validator->fails()) {
            $this->flash('error', implode('<br>', $validator->errors()));
            $this->redirect('/admin/usuarios/novo');
        }

        db()->prepare(
            'INSERT INTO admin_users (name, email, password_hash, role, active)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([
            $data['name'],
            $data['email'],
            password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            $data['role'],
            $data['active'],
        ]);

        $this->flash('success', 'Usuario criado.');
        $this->redirect('/admin/usuarios');
    }

    public function edit(array $params): void
    {
        Auth::requireAdminRole();

        $user = $this->find((int)($params['id'] ?? 0));
        if (!$user) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('admin/users/form', [
            'pageTitle' => 'Editar Usuário | Admin WM',
            'user'      => $user,
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function update(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        $user = $this->find($id);
        if (!$user) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $data = $this->extractData();
        $password = (string)($_POST['password'] ?? '');
        $validator = $this->validateData($data, $password, $id, false);

        if ($validator->fails()) {
            $this->flash('error', implode('<br>', $validator->errors()));
            $this->redirect('/admin/usuarios/' . $id);
        }

        if ($id === Auth::adminId()) {
            $data['role'] = 'admin';
            $data['active'] = 1;
        }

        $paramsSql = [$data['name'], $data['email'], $data['role'], $data['active'], $id];
        $passwordSql = '';
        if ($password !== '') {
            $passwordSql = ', password_hash = ?';
            $paramsSql = [$data['name'], $data['email'], $data['role'], $data['active'], password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), $id];
        }

        db()->prepare(
            "UPDATE admin_users
                SET name = ?, email = ?, role = ?, active = ?{$passwordSql}, updated_at = NOW()
              WHERE id = ?"
        )->execute($paramsSql);

        if ($id === Auth::adminId()) {
            $_SESSION['admin_name'] = $data['name'];
            $_SESSION['admin_email'] = $data['email'];
            $_SESSION['admin_role'] = $data['role'];
        }

        $this->flash('success', 'Usuario atualizado.');
        $this->redirect('/admin/usuarios');
    }

    public function delete(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            $this->flash('error', 'Usuário inválido.');
            $this->redirect('/admin/usuarios');
        }

        if ($id === Auth::adminId()) {
            $this->flash('error', 'Você não pode excluir o próprio usuário logado.');
            $this->redirect('/admin/usuarios');
        }

        $user = $this->find($id);
        if (!$user) {
            $this->flash('error', 'Usuário não encontrado.');
            $this->redirect('/admin/usuarios');
        }

        if (($user['role'] ?? '') === 'admin') {
            $stmt = db()->prepare("SELECT COUNT(*) FROM admin_users WHERE role = 'admin' AND active = 1 AND id <> ?");
            $stmt->execute([$id]);
            if ((int)$stmt->fetchColumn() === 0) {
                $this->flash('error', 'Mantenha pelo menos um admin ativo no sistema.');
                $this->redirect('/admin/usuarios');
            }
        }

        $stmt = db()->prepare('DELETE FROM admin_users WHERE id = ?');
        $stmt->execute([$id]);

        $this->flash('success', 'Usuário excluído.');
        $this->redirect('/admin/usuarios');
    }

    private function extractData(): array
    {
        return [
            'name'   => Sanitizer::plainText($_POST['name'] ?? ''),
            'email'  => Sanitizer::email($_POST['email'] ?? ''),
            'role'   => in_array($_POST['role'] ?? 'operator', ['admin', 'operator'], true) ? $_POST['role'] : 'operator',
            'active' => isset($_POST['active']) ? 1 : 0,
        ];
    }

    private function validateData(array $data, string $password, ?int $ignoreId = null, bool $passwordRequired = true): Validator
    {
        $validator = new Validator($data + ['password' => $password]);
        $validator->required('name')->maxLen('name', 150, 'Nome')
            ->required('email')->email('email')
            ->uniqueInDb('email', 'admin_users', 'email', $ignoreId, 'E-mail');

        if ($passwordRequired) {
            $validator->required('password', 'Senha')->minLen('password', 8, 'Senha');
        } elseif ($password !== '') {
            $validator->minLen('password', 8, 'Senha');
        }

        return $validator;
    }

    private function find(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, name, email, role, active, last_login, created_at
               FROM admin_users
              WHERE id = ?'
        );
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }
}
