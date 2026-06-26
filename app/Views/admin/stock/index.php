<?php use Maia\Helpers\CSRF; ?>

<h1>Estoque</h1>

<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($flash['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="stock-layout">
    <div class="stock-main">
        <div class="filter-bar">
            <a href="/admin/estoque" class="btn btn-sm <?= $filter === '' ? 'btn-primary' : 'btn-outline' ?>">Todos</a>
            <a href="/admin/estoque?filtro=baixo" class="btn btn-sm <?= $filter === 'baixo' ? 'btn-primary' : 'btn-outline' ?>">Estoque Baixo</a>
            <a href="/admin/estoque?filtro=zerado" class="btn btn-sm <?= $filter === 'zerado' ? 'btn-primary' : 'btn-outline' ?>">Zerado</a>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Produto</th>
                    <th>Categoria</th>
                    <th>Estoque Atual</th>
                    <th>Alerta</th>
                    <th>Ajuste Rápido</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($products)): ?>
            <tr><td colspan="6" class="empty-state">Nenhum produto encontrado.</td></tr>
            <?php else: ?>
            <?php foreach ($products as $prod): ?>
            <tr class="<?= (int)$prod['stock'] <= 0 ? 'stock-zero' : ((int)$prod['stock'] <= (int)$prod['stock_alert'] ? 'stock-low' : '') ?>">
                <td><?= (int)$prod['id'] ?></td>
                <td><a href="/admin/produtos/<?= (int)$prod['id'] ?>"><?= htmlspecialchars($prod['name'], ENT_QUOTES, 'UTF-8') ?></a></td>
                <td><?= htmlspecialchars($prod['category_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td><strong><?= (int)$prod['stock'] ?></strong></td>
                <td><?= (int)$prod['stock_alert'] ?></td>
                <td class="stock-adjust-cell">
                    <form action="/admin/estoque/ajuste" method="post" class="inline-adjust-form">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                        <input type="hidden" name="product_id" value="<?= (int)$prod['id'] ?>">
                        <select name="type">
                            <option value="entrada">Entrada</option>
                            <option value="saida">Saída</option>
                            <option value="ajuste">Ajuste</option>
                        </select>
                        <div class="input-row">
                            <input type="number" name="quantity" min="1" value="1">
                            <input type="text" name="reason" placeholder="Motivo" style="width:100px">
                            <button type="submit" class="btn btn-sm btn-outline">OK</button>
                        </div>
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
            <a href="?filtro=<?= urlencode($filter) ?>&pagina=<?= $p ?>"
               class="page-link <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </nav>
        <?php endif; ?>
    </div>

    <aside class="stock-sidebar">
        <section class="detail-card">
            <h2>Últimas Movimentações</h2>
            <table class="admin-table admin-table--sm">
                <thead><tr><th>Produto</th><th>Tipo</th><th>Qtd</th><th>Data</th></tr></thead>
                <tbody>
                <?php foreach ($movements as $mv): ?>
                <tr>
                    <td><?= htmlspecialchars($mv['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($mv['type'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= (int)$mv['quantity'] ?></td>
                    <td><?= htmlspecialchars(substr($mv['created_at'] ?? '', 0, 10), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </aside>
</div>
