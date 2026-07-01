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
    <link rel="stylesheet" href="/assets/css/admin.css?v=20260630-11">
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

        <?php
        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH) ?: '/admin';
        $basePath = defined('APP_BASE') ? APP_BASE : '';
        if ($basePath !== '' && str_starts_with($currentPath, $basePath . '/')) {
            $currentPath = substr($currentPath, strlen($basePath));
        }
        if ($currentPath === '') {
            $currentPath = '/admin';
        }

        $isAdminRole = \Maia\Helpers\Auth::isAdmin();
        $unreadCount = \Maia\Services\NotificationService::countUnread();
        $productsGroupActive = str_starts_with($currentPath, '/admin/produtos')
            || str_starts_with($currentPath, '/admin/categorias')
            || str_starts_with($currentPath, '/admin/marcas')
            || str_starts_with($currentPath, '/admin/combos')
            || str_starts_with($currentPath, '/admin/estoque');
        $marketingGroupActive = str_starts_with($currentPath, '/admin/cupons')
            || str_starts_with($currentPath, '/admin/avaliacoes');
        $settingsGroupActive = str_starts_with($currentPath, '/admin/configuracoes')
            || str_starts_with($currentPath, '/admin/scripts')
            || str_starts_with($currentPath, '/admin/usuarios')
            || str_starts_with($currentPath, '/admin/utm')
            || str_starts_with($currentPath, '/admin/notificacoes');
        ?>

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
            <a href="/admin/clientes" class="nav-item <?= str_starts_with($currentPath, '/admin/clientes') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Clientes</span>
            </a>

            <div class="nav-group <?= $productsGroupActive ? 'is-active is-open' : '' ?>">
                <button type="button" class="nav-group__label" aria-expanded="<?= $productsGroupActive ? 'true' : 'false' ?>">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 16-9 5-9-5V8l9-5 9 5z"/><path d="M3.3 7.7 12 13l8.7-5.3"/><path d="M12 13v8"/></svg>
                    <span>Produtos</span>
                    <span class="nav-group__chevron" aria-hidden="true"></span>
                </button>
                <div class="nav-group__items">
                    <a href="/admin/produtos" class="nav-subitem <?= str_starts_with($currentPath, '/admin/produtos') ? 'active' : '' ?>">Todos os produtos</a>
                    <a href="/admin/categorias" class="nav-subitem <?= str_starts_with($currentPath, '/admin/categorias') ? 'active' : '' ?>">Categorias</a>
                    <a href="/admin/marcas" class="nav-subitem <?= str_starts_with($currentPath, '/admin/marcas') ? 'active' : '' ?>">Marcas</a>
                    <a href="/admin/combos" class="nav-subitem <?= str_starts_with($currentPath, '/admin/combos') ? 'active' : '' ?>">Combos</a>
                    <a href="/admin/estoque" class="nav-subitem <?= str_starts_with($currentPath, '/admin/estoque') ? 'active' : '' ?>">Estoque</a>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($isAdminRole): ?>
            <div class="nav-group <?= $marketingGroupActive ? 'is-active is-open' : '' ?>">
                <button type="button" class="nav-group__label" aria-expanded="<?= $marketingGroupActive ? 'true' : 'false' ?>">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                    <span>Marketing</span>
                    <span class="nav-group__chevron" aria-hidden="true"></span>
                </button>
                <div class="nav-group__items">
                    <a href="/admin/cupons" class="nav-subitem <?= str_starts_with($currentPath, '/admin/cupons') ? 'active' : '' ?>">Cupons</a>
                    <a href="/admin/avaliacoes" class="nav-subitem <?= str_starts_with($currentPath, '/admin/avaliacoes') ? 'active' : '' ?>">Avaliações</a>
                </div>
            </div>

            <div class="nav-group <?= $settingsGroupActive ? 'is-active is-open' : '' ?>">
                <button type="button" class="nav-group__label" aria-expanded="<?= $settingsGroupActive ? 'true' : 'false' ?>">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    <span>Configurações</span>
                    <?php if ($unreadCount > 0): ?>
                    <span class="nav-badge"><?= $unreadCount ?></span>
                    <?php endif; ?>
                    <span class="nav-group__chevron" aria-hidden="true"></span>
                </button>
                <div class="nav-group__items">
                    <a href="/admin/configuracoes" class="nav-subitem <?= str_starts_with($currentPath, '/admin/configuracoes') ? 'active' : '' ?>">Geral</a>
                    <a href="/admin/scripts" class="nav-subitem <?= str_starts_with($currentPath, '/admin/scripts') ? 'active' : '' ?>">Scripts</a>
                    <a href="/admin/utm" class="nav-subitem <?= str_starts_with($currentPath, '/admin/utm') ? 'active' : '' ?>">UTM Builder</a>
                    <a href="/admin/notificacoes" class="nav-subitem <?= str_starts_with($currentPath, '/admin/notificacoes') ? 'active' : '' ?>">Notificações</a>
                    <a href="/admin/usuarios" class="nav-subitem <?= str_starts_with($currentPath, '/admin/usuarios') ? 'active' : '' ?>">Usuários</a>
                    <?php /* <a href="/admin/diagnostico-uploads" class="nav-subitem <?= str_starts_with($currentPath, '/admin/diagnostico-uploads') ? 'active' : '' ?>">Diagnóstico Imagens</a> */ ?>
                </div>
            </div>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <span class="admin-name"><?= htmlspecialchars($_SESSION['admin_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
            <a href="/admin/logout" class="logout-link">Sair</a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="sidebar-toggle" aria-label="Menu" aria-controls="admin-sidebar">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <div class="topbar-right">
                <a href="/admin/notificacoes" class="notif-bell" aria-label="Notificações">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <?php if ($unreadCount > 0): ?>
                    <span class="notif-dot"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="/" target="_blank" rel="noopener" class="view-store-link">Ver loja</a>
            </div>
        </header>
        <div class="admin-content">
