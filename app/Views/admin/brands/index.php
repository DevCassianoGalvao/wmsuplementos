<?php use Maia\Helpers\CSRF; ?>

<div class="page-header">
    <h1>Marcas</h1>
    <a href="/admin/marcas/novo" class="btn btn-primary">Nova Marca</a>
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
            <th>Marca</th>
            <th>Slug</th>
            <th>Produtos</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($brands)): ?>
        <tr><td colspan="5" class="empty-state">Nenhuma marca cadastrada.</td></tr>
    <?php else: ?>
        <?php foreach ($brands as $brand): ?>
        <tr>
            <td>
                <strong><?= htmlspecialchars($brand['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                <?php if (!empty($brand['logo'])): ?>
                <div class="muted"><?= htmlspecialchars($brand['logo'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($brand['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (int)($brand['product_count'] ?? 0) ?></td>
            <td>
                <span class="<?= !empty($brand['active']) ? 'status-pago' : 'status-cancelado' ?>">
                    <?= !empty($brand['active']) ? 'Ativa' : 'Inativa' ?>
                </span>
            </td>
            <td>
                <div class="table-actions">
                    <a href="/admin/marcas/<?= (int)$brand['id'] ?>" class="btn btn-sm btn-outline">Editar</a>
                    <form action="/admin/marcas/<?= (int)$brand['id'] ?>/toggle" method="post">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                        <button type="submit" class="btn btn-sm btn-outline"><?= !empty($brand['active']) ? 'Ocultar' : 'Ativar' ?></button>
                    </form>
                    <form action="/admin/marcas/<?= (int)$brand['id'] ?>/excluir" method="post" data-confirm="Remover esta marca?">
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
