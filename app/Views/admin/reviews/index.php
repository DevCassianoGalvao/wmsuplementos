<?php use Maia\Helpers\CSRF; ?>

<div class="page-header">
    <h1>Avaliações</h1>
</div>

<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($flash['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="filter-bar">
    <a href="/admin/avaliacoes?status=pending" class="btn btn-sm <?= $status === 'pending' ? 'btn-primary' : 'btn-outline' ?>">Pendentes</a>
    <a href="/admin/avaliacoes?status=approved" class="btn btn-sm <?= $status === 'approved' ? 'btn-primary' : 'btn-outline' ?>">Aprovadas</a>
    <a href="/admin/avaliacoes?status=rejected" class="btn btn-sm <?= $status === 'rejected' ? 'btn-primary' : 'btn-outline' ?>">Rejeitadas</a>
</div>

<p class="results-count"><?= (int)$total ?> avaliacao(oes)</p>

<?php if ($status === 'pending' && !empty($reviews)): ?>
<form method="post" action="/admin/avaliacoes/lote" class="bulk-form">
    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
    <div class="bulk-actions">
        <select name="bulk_action" required>
            <option value="">Ação em lote</option>
            <option value="approve">Aprovar selecionadas</option>
            <option value="reject">Rejeitar selecionadas</option>
        </select>
        <input type="text" name="rejection_reason" placeholder="Motivo da rejeicao, se houver">
        <button type="submit" class="btn btn-outline">Aplicar</button>
    </div>
<?php endif; ?>

<table class="admin-table">
    <thead>
        <tr>
            <?php if ($status === 'pending'): ?><th></th><?php endif; ?>
            <th>Produto</th>
            <th>Cliente</th>
            <th>Nota</th>
            <th>Comentario</th>
            <th>Foto</th>
            <?php if ($status === 'rejected'): ?><th>Motivo</th><?php endif; ?>
            <th>Data</th>
            <?php if ($status === 'pending'): ?><th>Ações</th><?php endif; ?>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($reviews)): ?>
    <tr><td colspan="<?= $status === 'pending' ? 8 : ($status === 'rejected' ? 7 : 6) ?>" class="empty-state">Nenhuma avaliacao encontrada.</td></tr>
    <?php else: ?>
    <?php foreach ($reviews as $review): ?>
    <tr>
        <?php if ($status === 'pending'): ?>
        <td><input type="checkbox" name="review_ids[]" value="<?= (int)$review['id'] ?>"></td>
        <?php endif; ?>
        <td><?= htmlspecialchars($review['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($review['customer_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
        <td>
            <?php for ($i = 1; $i <= 5; $i++): ?>
            <span class="star <?= $i <= (int)$review['rating'] ? 'filled' : '' ?>">&#9733;</span>
            <?php endfor; ?>
        </td>
        <td><?= htmlspecialchars($review['comment'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
        <td>
            <?php if (!empty($review['photo'])): ?>
            <a href="<?= htmlspecialchars($review['photo'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                <img src="<?= htmlspecialchars($review['photo'], ENT_QUOTES, 'UTF-8') ?>" alt="" class="review-thumb">
            </a>
            <?php else: ?>
            -
            <?php endif; ?>
        </td>
        <?php if ($status === 'rejected'): ?>
        <td><?= htmlspecialchars($review['rejection_reason'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
        <?php endif; ?>
        <td><?= htmlspecialchars(substr($review['created_at'] ?? '', 0, 10), ENT_QUOTES, 'UTF-8') ?></td>
        <?php if ($status === 'pending'): ?>
        <td class="actions">
            <button type="submit" name="bulk_action" value="approve" class="btn btn-sm btn-success"
                    onclick="this.form.querySelectorAll('input[name=&quot;review_ids[]&quot;]').forEach(i => i.checked = false); this.closest('tr').querySelector('input[name=&quot;review_ids[]&quot;]').checked = true;">
                Aprovar
            </button>
            <button type="submit" name="bulk_action" value="reject" class="btn btn-sm btn-danger"
                    onclick="this.form.querySelectorAll('input[name=&quot;review_ids[]&quot;]').forEach(i => i.checked = false); this.closest('tr').querySelector('input[name=&quot;review_ids[]&quot;]').checked = true;">
                Rejeitar
            </button>
        </td>
        <?php endif; ?>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<?php if ($status === 'pending' && !empty($reviews)): ?>
</form>
<?php endif; ?>

<?php if ($totalPages > 1): ?>
<nav class="pagination">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <a href="?status=<?= urlencode($status) ?>&pagina=<?= $p ?>"
       class="page-link <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
</nav>
<?php endif; ?>
