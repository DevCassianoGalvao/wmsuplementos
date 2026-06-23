<?php use Maia\Helpers\Sanitizer; use Maia\Helpers\CSRF; ?>

<div class="page-header">
    <h1>Produtos</h1>
    <a href="/admin/produtos/novo" class="btn btn-primary">+ Novo Produto</a>
</div>

<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($flash['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form class="filter-bar" method="get" action="/admin/produtos">
    <input type="text" name="busca" placeholder="Buscar produtos..." value="<?= htmlspecialchars($filters['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <select name="categoria">
        <option value="">Todas as categorias</option>
        <?php foreach ($categories as $cat): ?>
        <option value="<?= (int)$cat['id'] ?>" <?= (int)($filters['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
        </option>
        <?php endforeach; ?>
    </select>
    <select name="ativo">
        <option value="">Todos</option>
        <option value="1" <?= ($filters['active'] ?? '') === '1' ? 'selected' : '' ?>>Ativos</option>
        <option value="0" <?= ($filters['active'] ?? '') === '0' ? 'selected' : '' ?>>Inativos</option>
    </select>
    <label><input type="checkbox" name="estoque_baixo" value="1" <?= !empty($filters['low_stock']) ? 'checked' : '' ?>> Estoque baixo</label>
    <button type="submit" class="btn btn-outline">Filtrar</button>
</form>

<p class="results-count"><?= number_format((int)$total) ?> produto(s)</p>

<?php $items = is_array($products) && isset($products['items']) ? $products['items'] : (array)$products; ?>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Categoria</th>
            <th>Preço</th>
            <th>Estoque</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($items)): ?>
    <tr><td colspan="7" class="empty-state">Nenhum produto encontrado.</td></tr>
    <?php else: ?>
    <?php foreach ($items as $prod): ?>
    <tr class="<?= (int)$prod['stock'] <= (int)($prod['stock_alert_threshold'] ?? 5) ? 'row-warning' : '' ?>">
        <td><?= (int)$prod['id'] ?></td>
        <td><a href="/admin/produtos/<?= (int)$prod['id'] ?>"><?= htmlspecialchars($prod['name'], ENT_QUOTES, 'UTF-8') ?></a></td>
        <td><?= htmlspecialchars($prod['category_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
        <td>R$ <?= Sanitizer::money((float)($prod['price_sale'] ?? $prod['price'])) ?></td>
        <td class="<?= (int)$prod['stock'] <= 0 ? 'stock-zero' : ((int)$prod['stock'] <= (int)($prod['stock_alert_threshold'] ?? 5) ? 'stock-low' : '') ?>">
            <?= (int)$prod['stock'] ?>
        </td>
        <td>
            <span class="badge <?= $prod['active'] ? 'badge-success' : 'badge-muted' ?>">
                <?= $prod['active'] ? 'Ativo' : 'Inativo' ?>
            </span>
        </td>
        <td class="actions">
            <a href="/admin/produtos/<?= (int)$prod['id'] ?>">Editar</a>
            <form method="post" action="/admin/produtos/<?= (int)$prod['id'] ?>/toggle" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                <button type="submit" class="btn-link"><?= $prod['active'] ? 'Desativar' : 'Ativar' ?></button>
            </form>
            <form method="post" action="/admin/produtos/<?= (int)$prod['id'] ?>/duplicar" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                <button type="submit" class="btn-link">Duplicar</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<?php if ($totalPages > 1): ?>
<nav class="pagination">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <?php $qs = http_build_query(array_merge($_GET, ['pagina' => $p])); ?>
    <a href="?<?= $qs ?>" class="page-link <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
</nav>
<?php endif; ?>
