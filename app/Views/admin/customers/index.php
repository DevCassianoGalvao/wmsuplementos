<?php use Maia\Helpers\Sanitizer; ?>

<div class="page-header">
    <h1>Clientes</h1>
    <a class="btn btn-outline" href="/admin/clientes/exportar?<?= http_build_query($_GET) ?>">Exportar CSV</a>
</div>

<form class="filter-bar" method="get" action="/admin/clientes">
    <input type="text" name="busca" placeholder="Nome, e-mail ou telefone..." value="<?= htmlspecialchars($filters['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <select name="segmento">
        <option value="">Todos</option>
        <option value="never_bought"  <?= ($filters['segment'] ?? '') === 'never_bought'  ? 'selected' : '' ?>>Nunca compraram</option>
        <option value="bought_once"   <?= ($filters['segment'] ?? '') === 'bought_once'   ? 'selected' : '' ?>>Compraram 1x</option>
        <option value="bought_3plus"  <?= ($filters['segment'] ?? '') === 'bought_3plus'  ? 'selected' : '' ?>>3+ compras</option>
        <option value="inactive_30"   <?= ($filters['segment'] ?? '') === 'inactive_30'   ? 'selected' : '' ?>>Inativos 30d</option>
        <option value="inactive_60"   <?= ($filters['segment'] ?? '') === 'inactive_60'   ? 'selected' : '' ?>>Inativos 60d</option>
        <option value="inactive_90"   <?= ($filters['segment'] ?? '') === 'inactive_90'   ? 'selected' : '' ?>>Inativos 90d</option>
    </select>
    <select name="tag">
        <option value="">Todas as tags</option>
        <option value="vip" <?= ($filters['tag'] ?? '') === 'vip' ? 'selected' : '' ?>>VIP</option>
        <option value="atacado" <?= ($filters['tag'] ?? '') === 'atacado' ? 'selected' : '' ?>>Atacado</option>
        <option value="bloqueado" <?= ($filters['tag'] ?? '') === 'bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
    </select>
    <select name="opt_in">
        <option value="">Opt-in: todos</option>
        <option value="1" <?= ($filters['opt_in'] ?? '') === '1' ? 'selected' : '' ?>>Aceita e-mails</option>
        <option value="0" <?= ($filters['opt_in'] ?? '') === '0' ? 'selected' : '' ?>>Não aceita</option>
    </select>
    <input type="number" name="gastou_acima" min="0" step="1" placeholder="Gastou acima de R$"
           value="<?= htmlspecialchars((string)($filters['spent_above_value'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit" class="btn btn-outline">Filtrar</button>
</form>

<p class="results-count"><?= number_format((int)$total) ?> cliente(s)</p>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Tag</th>
            <th>Pedidos</th>
            <th>Total Gasto</th>
            <th>Cadastro</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($customers)): ?>
    <tr><td colspan="8" class="empty-state">Nenhum cliente encontrado.</td></tr>
    <?php else: ?>
    <?php foreach ($customers as $c): ?>
    <?php if (!is_array($c)) { continue; } ?>
    <tr>
        <td><?= (int)($c['id'] ?? 0) ?></td>
        <td><?= htmlspecialchars($c['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($c['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars(($c['tag'] ?? '') !== '' ? ucfirst((string)$c['tag']) : '-', ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= (int)($c['total_orders'] ?? 0) ?></td>
        <td>R$ <?= Sanitizer::money((float)($c['total_spent'] ?? 0)) ?></td>
        <td><?= htmlspecialchars(substr($c['created_at'] ?? '', 0, 10), ENT_QUOTES, 'UTF-8') ?></td>
        <td><a href="/admin/clientes/<?= (int)($c['id'] ?? 0) ?>">Ver</a></td>
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
