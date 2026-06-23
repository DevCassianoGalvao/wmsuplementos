<?php use Maia\Helpers\CSRF; ?>

<div class="page-header">
    <h1>Notificações <?php if ($unread > 0): ?><span class="badge badge-alert"><?= (int)$unread ?></span><?php endif; ?></h1>
    <?php if ($unread > 0): ?>
    <form action="/admin/notificacoes/marcar-todas" method="post" style="display:inline">
        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
        <button type="submit" class="btn btn-outline">Marcar todas como lidas</button>
    </form>
    <?php endif; ?>
</div>

<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="filter-bar">
    <a href="/admin/notificacoes" class="btn btn-sm <?= $filter === '' ? 'btn-primary' : 'btn-outline' ?>">Todas</a>
    <a href="/admin/notificacoes?filtro=nao_lidas" class="btn btn-sm <?= $filter === 'nao_lidas' ? 'btn-primary' : 'btn-outline' ?>">Não lidas</a>
    <a href="/admin/notificacoes?filtro=lidas" class="btn btn-sm <?= $filter === 'lidas' ? 'btn-primary' : 'btn-outline' ?>">Lidas</a>
</div>

<?php
$typeIcons = [
    'order'           => '🛒',
    'low_stock'       => '⚠️',
    'review_requests' => '⭐',
    'payment'         => '💳',
    'system'          => 'ℹ️',
];
$typeLabels = [
    'order'           => 'Pedido',
    'low_stock'       => 'Estoque',
    'review_requests' => 'Avaliação',
    'payment'         => 'Pagamento',
    'system'          => 'Sistema',
];
?>

<?php if (empty($notifications)): ?>
<p class="empty-state">Nenhuma notificação encontrada.</p>
<?php else: ?>
<ul class="notifications-list">
    <?php foreach ($notifications as $notif): ?>
    <li class="notification-item <?= $notif['read'] ? 'notification-read' : 'notification-unread' ?>">
        <span class="notif-icon" aria-hidden="true">
            <?= $typeIcons[$notif['type']] ?? 'ℹ️' ?>
        </span>
        <div class="notif-body">
            <p class="notif-title"><?= htmlspecialchars($notif['title'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php if ($notif['message']): ?>
            <p class="notif-message"><?= htmlspecialchars($notif['message'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <p class="notif-meta">
                <span class="notif-type"><?= htmlspecialchars($typeLabels[$notif['type']] ?? $notif['type'], ENT_QUOTES, 'UTF-8') ?></span>
                · <?= htmlspecialchars(substr($notif['created_at'] ?? '', 0, 16), ENT_QUOTES, 'UTF-8') ?>
                <?php if ($notif['read'] && $notif['read_at']): ?>
                · lida em <?= htmlspecialchars(substr($notif['read_at'], 0, 16), ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </p>
        </div>
        <?php if (!$notif['read']): ?>
        <form method="post" action="/admin/notificacoes/<?= (int)$notif['id'] ?>/ler" class="notif-action">
            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
            <button type="submit" class="btn btn-sm btn-outline" title="Marcar como lida">✓</button>
        </form>
        <?php endif; ?>
    </li>
    <?php endforeach; ?>
</ul>

<?php if ($totalPages > 1): ?>
<nav class="pagination">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <a href="?filtro=<?= urlencode($filter) ?>&pagina=<?= $p ?>"
       class="page-link <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
</nav>
<?php endif; ?>
<?php endif; ?>
