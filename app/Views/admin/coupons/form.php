<?php use Maia\Helpers\CSRF; ?>

<?php $isEdit = $coupon !== null; ?>
<div class="page-header">
    <h1><?= $isEdit ? 'Editar Cupom' : 'Novo Cupom' ?></h1>
    <a href="/admin/cupons" class="btn btn-outline">← Voltar</a>
</div>

<?php if (!empty($flash['error'])): ?>
<div class="alert alert-error"><?= nl2br(htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8')) ?></div>
<?php endif; ?>

<form action="<?= $isEdit ? '/admin/cupons/' . (int)$coupon['id'] : '/admin/cupons' ?>" method="post" novalidate>
    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

    <div class="form-card">
        <div class="form-row">
            <div class="form-group">
                <label for="code">Código *</label>
                <input type="text" id="code" name="code" required maxlength="50" style="text-transform:uppercase"
                       value="<?= htmlspecialchars($coupon['code'] ?? $_POST['code'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="type">Tipo *</label>
                <select id="type" name="type">
                    <option value="percent" <?= ($coupon['type'] ?? 'percent') === 'percent' ? 'selected' : '' ?>>Percentual (%)</option>
                    <option value="fixed" <?= ($coupon['type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Valor fixo (R$)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="value">Valor *</label>
                <input type="text" id="value" name="value" required inputmode="decimal"
                       value="<?= htmlspecialchars($coupon['value'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="min_order">Pedido Mínimo (R$)</label>
                <input type="text" id="min_order" name="min_order" inputmode="decimal"
                       value="<?= htmlspecialchars($coupon['min_order'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="max_uses">Máx. de Usos</label>
                <input type="number" id="max_uses" name="max_uses" min="0"
                       value="<?= (int)($coupon['max_uses'] ?? 0) ?>">
                <small>0 = ilimitado</small>
            </div>
            <div class="form-group">
                <label for="expires_at">Validade</label>
                <input type="date" id="expires_at" name="expires_at"
                       value="<?= htmlspecialchars(substr($coupon['expires_at'] ?? '', 0, 10), ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </div>

        <div class="form-check-row">
            <label><input type="checkbox" name="active" value="1" <?= ($coupon['active'] ?? 1) ? 'checked' : '' ?>> Ativo</label>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvar Cupom</button>
        <a href="/admin/cupons" class="btn btn-link">Cancelar</a>
    </div>
</form>
