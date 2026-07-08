<?php

declare(strict_types=1);

namespace Maia\Controllers\Admin;

use Maia\Controllers\BaseController;
use Maia\Helpers\Auth;
use Maia\Helpers\CSRF;
use Maia\Helpers\Sanitizer;
use Maia\Helpers\Validator;

class CouponController extends BaseController
{
    public function index(array $params = []): void
    {
        Auth::requireAdminRole();

        $stmt = db()->query(
            'SELECT * FROM coupons ORDER BY active DESC, created_at DESC'
        );
        $coupons = $stmt->fetchAll();

        $this->render('admin/coupons/index', [
            'pageTitle' => 'Cupons | Admin WM',
            'coupons'   => $coupons,
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function create(array $params = []): void
    {
        Auth::requireAdminRole();

        $this->render('admin/coupons/form', [
            'pageTitle' => 'Novo Cupom | Admin WM',
            'coupon'    => null,
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function store(array $params = []): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $data = $this->extractData();
        $v    = $this->validate($data);

        if ($v->fails()) {
            $this->flash('error', implode('<br>', $v->errors()));
            $this->redirect('/admin/cupons/novo');
        }

        $stmt = db()->prepare(
            'INSERT INTO coupons (code, type, value, min_order, max_uses, max_uses_per_user, expires_at, active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['code'], $data['type'], $data['value'],
            $data['min_order'] ?: null, $data['max_uses'] ?: null, $data['max_uses_per_user'],
            $data['expires_at'] ?: null, $data['active'],
        ]);

        $this->flash('success', 'Cupom criado.');
        $this->redirect('/admin/cupons');
    }

    public function edit(array $params): void
    {
        Auth::requireAdminRole();

        $id   = (int)($params['id'] ?? 0);
        $stmt = db()->prepare('SELECT * FROM coupons WHERE id = ?');
        $stmt->execute([$id]);
        $coupon = $stmt->fetch();

        if (!$coupon) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('admin/coupons/form', [
            'pageTitle' => 'Editar Cupom | Admin WM',
            'coupon'    => $coupon,
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function update(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $id   = (int)($params['id'] ?? 0);
        $data = $this->extractData();
        $v    = $this->validate($data, $id);

        if ($v->fails()) {
            $this->flash('error', implode('<br>', $v->errors()));
            $this->redirect('/admin/cupons/' . $id);
        }

        db()->prepare(
            'UPDATE coupons SET code = ?, type = ?, value = ?, min_order = ?,
             max_uses = ?, max_uses_per_user = ?, expires_at = ?, active = ? WHERE id = ?'
        )->execute([
            $data['code'], $data['type'], $data['value'],
            $data['min_order'] ?: null, $data['max_uses'] ?: null, $data['max_uses_per_user'],
            $data['expires_at'] ?: null, $data['active'], $id,
        ]);

        $this->flash('success', 'Cupom atualizado.');
        $this->redirect('/admin/cupons');
    }

    public function toggle(array $params): void
    {
        Auth::requireAdminRole();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        db()->prepare('UPDATE coupons SET active = NOT active WHERE id = ?')->execute([$id]);

        $this->flash('success', 'Status do cupom alterado.');
        $this->redirect('/admin/cupons');
    }

    private function extractData(): array
    {
        return [
            'code'       => strtoupper(Sanitizer::plainText($_POST['code']   ?? '')),
            'type'       => $_POST['type']       ?? 'percent',
            'value'      => (float)str_replace(',', '.', $_POST['value'] ?? '0'),
            'min_order'  => (float)str_replace(',', '.', $_POST['min_order'] ?? '0'),
            'max_uses'   => (int)($_POST['max_uses']   ?? 0),
            'max_uses_per_user' => max(1, (int)($_POST['max_uses_per_user'] ?? 1)),
            'expires_at' => $_POST['expires_at'] ?? '',
            'active'     => isset($_POST['active']) ? 1 : 0,
        ];
    }

    private function validate(array $data, ?int $ignoreId = null): Validator
    {
        $v = new Validator($data);
        $v->required('code')->maxLen('code', 50, 'Código')
          ->uniqueInDb('code', 'coupons', 'code', $ignoreId, 'Código')
          ->required('type')->in('type', ['percent', 'fixed'], 'Tipo')
          ->required('value')->positiveNumber('value', 'Valor');
        return $v;
    }
}
