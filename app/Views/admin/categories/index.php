<?php use Maia\Helpers\CSRF; ?>

<div class="page-header">
    <h1>Categorias</h1>
    <a href="/admin/categorias/novo" class="btn btn-primary">Nova Categoria</a>
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
            <th>Ordem</th>
            <th>Nome</th>
            <th>Slug</th>
            <th>Produtos</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($categories)): ?>
        <tr><td colspan="6" class="empty-state">Nenhuma categoria cadastrada.</td></tr>
    <?php else: ?>
        <?php foreach ($categories as $category): ?>
        <tr>
            <td><?= (int)($category['sort_order'] ?? 0) ?></td>
            <td><?= htmlspecialchars($category['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($category['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (int)($category['product_count'] ?? 0) ?></td>
            <td>
                <span class="<?= !empty($category['active']) ? 'status-pago' : 'status-cancelado' ?>">
                    <?= !empty($category['active']) ? 'Ativa' : 'Inativa' ?>
                </span>
            </td>
            <td>
                <div class="table-actions">
                    <a href="/admin/categorias/<?= (int)$category['id'] ?>" class="btn btn-sm btn-outline">Editar</a>
                    <form action="/admin/categorias/<?= (int)$category['id'] ?>/toggle" method="post">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                        <button type="submit" class="btn btn-sm btn-outline"><?= !empty($category['active']) ? 'Ocultar' : 'Ativar' ?></button>
                    </form>
                    <form action="/admin/categorias/<?= (int)$category['id'] ?>/excluir" method="post" data-confirm="Remover esta categoria?">
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
