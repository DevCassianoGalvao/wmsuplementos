<?php use Maia\Helpers\Sanitizer; use Maia\Helpers\CSRF; ?>

<div class="page-header">
    <h1>Cupons</h1>
    <a href="/admin/cupons/novo" class="btn btn-primary">+ Novo Cupom</a>
</div>

<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<table class="admin-table">
    <thead>
        <tr>
            <th>Código</th>
            <th>Tipo</th>
            <th>Valor</th>
            <th>Mín. Pedido</th>
            <th>Usos</th>
            <th>Validade</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($coupons)): ?>
    <tr><td colspan="8" class="empty-state">Nenhum cupom cadastrado.</td></tr>
    <?php else: ?>
    <?php foreach ($coupons as $c): ?>
    <tr>
        <td><strong><?= htmlspecialchars($c['code'], ENT_QUOTES, 'UTF-8') ?></strong></td>
        <td><?= $c['type'] === 'percent' ? 'Percentual' : 'Fixo' ?></td>
        <td><?= $c['type'] === 'percent' ? (float)$c['value'] . '%' : 'R$ ' . Sanitizer::money((float)$c['value']) ?></td>
        <td><?= $c['min_order'] ? 'R$ ' . Sanitizer::money((float)$c['min_order']) : '—' ?></td>
        <td><?= (int)$c['used_count'] ?><?= $c['max_uses'] ? '/' . (int)$c['max_uses'] : '' ?></td>
        <td><?= $c['expires_at'] ? htmlspecialchars(substr($c['expires_at'], 0, 10), ENT_QUOTES, 'UTF-8') : '—' ?></td>
        <td><span class="badge <?= $c['active'] ? 'badge-success' : 'badge-muted' ?>"><?= $c['active'] ? 'Ativo' : 'Inativo' ?></span></td>
        <td class="actions">
            <a href="/admin/cupons/<?= (int)$c['id'] ?>">Editar</a>
            <form method="post" action="/admin/cupons/<?= (int)$c['id'] ?>/toggle" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                <button type="submit" class="btn-link"><?= $c['active'] ? 'Desativar' : 'Ativar' ?></button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
