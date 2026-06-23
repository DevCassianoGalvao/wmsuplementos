<?php use Maia\Helpers\CSRF; ?>

<div class="page-header">
    <h1>Avaliações</h1>
</div>

<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="filter-bar">
    <a href="/admin/avaliacoes?status=pending"  class="btn btn-sm <?= $status === 'pending'  ? 'btn-primary' : 'btn-outline' ?>">Pendentes</a>
    <a href="/admin/avaliacoes?status=approved" class="btn btn-sm <?= $status === 'approved' ? 'btn-primary' : 'btn-outline' ?>">Aprovadas</a>
    <a href="/admin/avaliacoes?status=rejected" class="btn btn-sm <?= $status === 'rejected' ? 'btn-primary' : 'btn-outline' ?>">Rejeitadas</a>
</div>

<p class="results-count"><?= (int)$total ?> avaliação(ões)</p>

<table class="admin-table">
    <thead>
        <tr>
            <th>Produto</th>
            <th>Cliente</th>
            <th>Nota</th>
            <th>Comentário</th>
            <th>Data</th>
            <?php if ($status === 'pending'): ?>
            <th>Ações</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($reviews)): ?>
    <tr><td colspan="<?= $status === 'pending' ? 6 : 5 ?>" class="empty-state">Nenhuma avaliação encontrada.</td></tr>
    <?php else: ?>
    <?php foreach ($reviews as $r): ?>
    <tr>
        <td><?= htmlspecialchars($r['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($r['customer_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
        <td>
            <?php for ($i = 1; $i <= 5; $i++): ?>
            <span class="star <?= $i <= (int)$r['rating'] ? 'filled' : '' ?>">&#9733;</span>
            <?php endfor; ?>
        </td>
        <td><?= htmlspecialchars($r['comment'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars(substr($r['created_at'] ?? '', 0, 10), ENT_QUOTES, 'UTF-8') ?></td>
        <?php if ($status === 'pending'): ?>
        <td class="actions">
            <form method="post" action="/admin/avaliacoes/<?= (int)$r['id'] ?>/aprovar" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                <button type="submit" class="btn btn-sm btn-success">Aprovar</button>
            </form>
            <form method="post" action="/admin/avaliacoes/<?= (int)$r['id'] ?>/rejeitar" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                <button type="submit" class="btn btn-sm btn-danger">Rejeitar</button>
            </form>
        </td>
        <?php endif; ?>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<?php if ($totalPages > 1): ?>
<nav class="pagination">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <a href="?status=<?= urlencode($status) ?>&pagina=<?= $p ?>"
       class="page-link <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
</nav>
<?php endif; ?>
