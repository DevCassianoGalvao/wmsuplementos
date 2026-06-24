<?php use Maia\Helpers\CSRF; use Maia\Helpers\Sanitizer; ?>

<div class="page-header">
    <h1>Combos</h1>
    <a href="/admin/combos/novo" class="btn btn-primary">Novo Combo</a>
</div>

<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($flash['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<table class="admin-table">
    <thead>
        <tr>
            <th>Combo</th>
            <th>Slug</th>
            <th>Itens</th>
            <th>Preco</th>
            <th>Status</th>
            <th>Acoes</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($combos)): ?>
        <tr><td colspan="6" class="empty-state">Nenhum combo cadastrado.</td></tr>
    <?php else: ?>
        <?php foreach ($combos as $combo): ?>
        <tr>
            <td><strong><?= htmlspecialchars($combo['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></td>
            <td><?= htmlspecialchars($combo['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (int)($combo['item_count'] ?? 0) ?></td>
            <td>R$ <?= Sanitizer::money((float)($combo['price'] ?? 0)) ?></td>
            <td>
                <span class="<?= !empty($combo['active']) ? 'status-pago' : 'status-cancelado' ?>">
                    <?= !empty($combo['active']) ? 'Ativo' : 'Inativo' ?>
                </span>
            </td>
            <td>
                <div class="table-actions">
                    <a href="/admin/combos/<?= (int)$combo['id'] ?>" class="btn btn-sm btn-outline">Editar</a>
                    <form action="/admin/combos/<?= (int)$combo['id'] ?>/toggle" method="post">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                        <button type="submit" class="btn btn-sm btn-outline"><?= !empty($combo['active']) ? 'Ocultar' : 'Ativar' ?></button>
                    </form>
                    <form action="/admin/combos/<?= (int)$combo['id'] ?>/excluir" method="post" data-confirm="Remover este combo?">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                        <button type="submit" class="btn btn-sm btn-outline">Remover</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
