<?php use Maia\Helpers\CSRF; ?>

<div class="page-header">
    <h1>Notificações <?php if ($unread > 0): ?><span class="badge badge-alert"><?= (int)$unread ?></span><?php endif; ?></h1>
    <?php if ($unread > 0): ?>
    <form action="/admin/notificacoes/marcar-todas" method="post" style="display:inline">
        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
        <button type="submit" class="btn btn-outline">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Marcar todas como lidas
        </button>
    </form>
    <?php endif; ?>
</div>

<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($flash['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<nav class="notif-tabs">
    <a href="/admin/notificacoes" class="notif-tab <?= $filter === '' ? 'notif-tab--active' : '' ?>">Todas</a>
    <a href="/admin/notificacoes?filtro=nao_lidas" class="notif-tab <?= $filter === 'nao_lidas' ? 'notif-tab--active' : '' ?>">
        Não lidas <?php if ($unread > 0): ?><span class="notif-tab-count"><?= (int)$unread ?></span><?php endif; ?>
    </a>
    <a href="/admin/notificacoes?filtro=lidas" class="notif-tab <?= $filter === 'lidas' ? 'notif-tab--active' : '' ?>">Lidas</a>
</nav>

<?php
$typeIcons = [
    'order' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>',
    'new_order' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>',
    'review_requests' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
    'new_review' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
    'low_stock' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    'payment' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
    'system' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
];
$typeLabels = [
    'order'           => 'Pedido',
    'new_order'       => 'Pedido',
    'low_stock'       => 'Estoque',
    'review_requests' => 'Avaliação',
    'new_review'      => 'Avaliação',
    'payment'         => 'Pagamento',
    'system'          => 'Sistema',
];
$typeColors = [
    'order'           => 'notif-icon--order',
    'new_order'       => 'notif-icon--order',
    'low_stock'       => 'notif-icon--stock',
    'review_requests' => 'notif-icon--review',
    'new_review'      => 'notif-icon--review',
    'payment'         => 'notif-icon--payment',
    'system'          => 'notif-icon--system',
];
$defaultIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
?>

<?php if (empty($notifications)): ?>
<div class="notif-empty">
    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
    <p>Nenhuma notificação encontrada.</p>
</div>
<?php else: ?>
<ul class="notifications-list">
    <?php foreach ($notifications as $notif): ?>
    <?php $iconClass = $typeColors[$notif['type']] ?? 'notif-icon--system'; ?>
    <li class="notification-item <?= $notif['read'] ? 'notification-read' : 'notification-unread' ?>">
        <?php if (!$notif['read']): ?><span class="notif-unread-dot" aria-hidden="true"></span><?php endif; ?>
        <span class="notif-icon-wrap <?= htmlspecialchars($iconClass, ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true">
            <?= $typeIcons[$notif['type']] ?? $defaultIcon ?>
        </span>
        <div class="notif-body">
            <p class="notif-title"><?= htmlspecialchars($notif['title'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php if ($notif['message']): ?>
            <p class="notif-message"><?= htmlspecialchars($notif['message'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <div class="notif-meta">
                <span class="notif-type-badge <?= htmlspecialchars($iconClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($typeLabels[$notif['type']] ?? $notif['type'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="notif-meta-sep">·</span>
                <span><?= htmlspecialchars(substr($notif['created_at'] ?? '', 0, 16), ENT_QUOTES, 'UTF-8') ?></span>
                <?php if ($notif['read'] && $notif['read_at']): ?>
                <span class="notif-meta-sep">·</span>
                <span class="notif-read-at">lida em <?= htmlspecialchars(substr($notif['read_at'], 0, 16), ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="notif-action">
        <?php if (!$notif['read']): ?>
        <form method="post" action="/admin/notificacoes/<?= (int)$notif['id'] ?>/ler">
            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
            <button type="submit" class="notif-read-btn" title="Marcar como lida">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </button>
        </form>
        <?php endif; ?>
        <form method="post" action="/admin/notificacoes/<?= (int)$notif['id'] ?>/excluir" data-confirm="Excluir esta notificação?">
            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
            <button type="submit" class="notif-read-btn notif-delete-btn" title="Excluir notificação">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            </button>
        </form>
        </div>
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
