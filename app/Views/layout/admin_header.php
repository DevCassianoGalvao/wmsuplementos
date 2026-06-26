<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin | Maia Suplementos', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/design-system.css?v=20260625-6">
    <link rel="stylesheet" href="/assets/css/admin.css?v=20260625-6">
    <meta name="robots" content="noindex, nofollow">
</head>
<body class="admin-body">

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="sidebar-logo">
            <a href="<?= \Maia\Helpers\Auth::isAdmin() ? '/admin/dashboard' : '/admin/pedidos' ?>">
                <img src="/assets/img/logo.png" alt="Maia Suplementos" height="38" style="height:38px;width:auto;">
            </a>
        </div>

        <?php $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH) ?: '/admin'; ?>
        <?php $isAdminRole = \Maia\Helpers\Auth::isAdmin(); ?>
        <nav class="sidebar-nav" aria-label="Menu administrativo">
            <?php if ($isAdminRole): ?>
            <a href="/admin/dashboard" class="nav-item <?= str_starts_with($currentPath, '/admin/dashboard') || $currentPath === '/admin' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                <span>Dashboard</span>
            </a>
            <?php endif; ?>
            <a href="/admin/pedidos" class="nav-item <?= str_starts_with($currentPath, '/admin/pedidos') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <span>Pedidos</span>
            </a>
            <?php if ($isAdminRole): ?>
            <a href="/admin/produtos" class="nav-item <?= str_starts_with($currentPath, '/admin/produtos') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 16-9 5-9-5V8l9-5 9 5z"/><path d="M3.3 7.7 12 13l8.7-5.3"/><path d="M12 13v8"/></svg>
                <span>Produtos</span>
            </a>
            <a href="/admin/marcas" class="nav-item <?= str_starts_with($currentPath, '/admin/marcas') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/><circle cx="12" cy="12" r="4"/></svg>
                <span>Marcas</span>
            </a>
            <a href="/admin/categorias" class="nav-item <?= str_starts_with($currentPath, '/admin/categorias') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                <span>Categorias</span>
            </a>
            <a href="/admin/combos" class="nav-item <?= str_starts_with($currentPath, '/admin/combos') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                <span>Combos</span>
            </a>
            <?php endif; ?>
            <a href="/admin/estoque" class="nav-item <?= str_starts_with($currentPath, '/admin/estoque') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>
                <span>Estoque</span>
            </a>
            <?php if ($isAdminRole): ?>
            <a href="/admin/clientes" class="nav-item <?= str_starts_with($currentPath, '/admin/clientes') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Clientes</span>
            </a>
            <a href="/admin/cupons" class="nav-item <?= str_starts_with($currentPath, '/admin/cupons') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                <span>Cupons</span>
            </a>
            <a href="/admin/avaliacoes" class="nav-item <?= str_starts_with($currentPath, '/admin/avaliacoes') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6-5.4-2.9-5.4 2.9 1-6-4.4-4.3 6.1-.9z"/></svg>
                <span>Avaliações</span>
            </a>
            <a href="/admin/scripts" class="nav-item <?= str_starts_with($currentPath, '/admin/scripts') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                <span>Scripts</span>
            </a>
            <a href="/admin/configuracoes" class="nav-item <?= str_starts_with($currentPath, '/admin/configuracoes') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                <span>Configurações</span>
            </a>
            <a href="/admin/usuarios" class="nav-item <?= str_starts_with($currentPath, '/admin/usuarios') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Usuários</span>
            </a>
            <a href="/admin/utm" class="nav-item <?= str_starts_with($currentPath, '/admin/utm') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                <span>UTM Builder</span>
            </a>
            <?php endif; ?>
            <?php
            $unreadCount = \Maia\Services\NotificationService::countUnread();
            ?>
            <a href="/admin/notificacoes" class="nav-item <?= str_starts_with($currentPath, '/admin/notificacoes') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span>Notificações</span>
                <?php if ($unreadCount > 0): ?>
                <span class="nav-badge"><?= $unreadCount ?></span>
                <?php endif; ?>
            </a>
        </nav>

        <div class="sidebar-footer">
            <span class="admin-name"><?= htmlspecialchars($_SESSION['admin_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
            <a href="/admin/logout" class="logout-link">Sair</a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="sidebar-toggle" aria-label="Menu" aria-controls="admin-sidebar">☰</button>
            <div class="topbar-right">
                <a href="/admin/notificacoes" class="notif-bell" aria-label="Notificações">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <?php if ($unreadCount > 0): ?>
                    <span class="notif-dot"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="/" target="_blank" rel="noopener" class="view-store-link">Ver loja ↗</a>
            </div>
        </header>
        <div class="admin-content">
