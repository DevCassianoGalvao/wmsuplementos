<?php use Maia\Helpers\Auth; use Maia\Helpers\CSRF; use Maia\Helpers\Sanitizer; ?>
<?php $canViewFinancials = Auth::isAdmin(); ?>

<div class="page-header">
    <h1>Pedidos</h1>
    <?php if ($canViewFinancials): ?>
    <a href="/admin/pedidos/exportar?<?= http_build_query(['de' => $filters['date_from'] ?? '', 'ate' => $filters['date_to'] ?? '']) ?>"
       class="btn btn-outline">Exportar CSV</a>
    <?php endif; ?>
</div>

<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($flash['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form class="filter-bar" method="get" action="/admin/pedidos">
    <select name="status">
        <option value="">Todos os status</option>
        <?php foreach ($statuses as $val => $label): ?>
        <option value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>" <?= ($filters['status'] ?? '') === $val ? 'selected' : '' ?>>
            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
        </option>
        <?php endforeach; ?>
    </select>
    <input type="date" name="de" value="<?= htmlspecialchars($filters['date_from'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <input type="date" name="ate" value="<?= htmlspecialchars($filters['date_to'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <input type="email" name="email" placeholder="E-mail do cliente" value="<?= htmlspecialchars($filters['customer_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit" class="btn btn-outline">Filtrar</button>
</form>

<p class="results-count"><?= number_format((int)$total) ?> pedido(s)</p>

<?php $items = is_array($orders) && isset($orders['items']) ? $orders['items'] : (array)$orders; ?>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Data</th>
            <th>Cliente</th>
            <th>Pagamento</th>
            <?php if ($canViewFinancials): ?>
            <th>Total</th>
            <?php endif; ?>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($items)): ?>
    <tr><td colspan="<?= $canViewFinancials ? 7 : 6 ?>" class="empty-state">Nenhum pedido encontrado.</td></tr>
    <?php else: ?>
    <?php foreach ($items as $order): ?>
    <tr>
        <td><a href="/admin/pedidos/<?= (int)$order['id'] ?>">#<?= (int)$order['id'] ?></a></td>
        <td><?= htmlspecialchars(substr($order['created_at'] ?? '', 0, 16), ENT_QUOTES, 'UTF-8') ?></td>
        <td>
            <?= htmlspecialchars($order['customer_name'], ENT_QUOTES, 'UTF-8') ?><br>
            <small><?= htmlspecialchars($order['customer_email'], ENT_QUOTES, 'UTF-8') ?></small>
        </td>
        <td><?= htmlspecialchars(ucfirst($order['payment_method'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
        <?php if ($canViewFinancials): ?>
        <td>R$ <?= Sanitizer::money((float)$order['total']) ?></td>
        <?php endif; ?>
        <td>
            <span class="badge status-<?= htmlspecialchars($order['status'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($statuses[$order['status'] ?? ''] ?? $order['status'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </span>
        </td>
        <td class="actions">
            <a href="/admin/pedidos/<?= (int)$order['id'] ?>">Ver</a>
            <form method="post" action="/admin/pedidos/<?= (int)$order['id'] ?>/excluir" style="display:inline" data-confirm="Excluir este pedido? O histórico e itens do pedido serão removidos.">
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                <button type="submit" class="btn-link btn-link-danger">Excluir</button>
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
    <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $p])) ?>"
       class="page-link <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
</nav>
<?php endif; ?>
