<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin | Maia Suplementos', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/design-system.css?v=20260624-2">
    <link rel="stylesheet" href="/assets/css/admin.css?v=20260625-1">
    <meta name="robots" content="noindex, nofollow">
</head>
<body class="admin-body">

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="sidebar-logo">
            <a href="/admin/dashboard">
                <img src="/assets/img/logo.png" alt="Maia Suplementos" height="38" style="height:38px;width:auto;">
            </a>
        </div>

        <?php $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH) ?: '/admin'; ?>
        <nav class="sidebar-nav" aria-label="Menu administrativo">
            <a href="/admin/dashboard" class="nav-item <?= str_starts_with($currentPath, '/admin/dashboard') || $currentPath === '/admin' ? 'active' : '' ?>">
                Dashboard
            </a>
            <a href="/admin/pedidos" class="nav-item <?= str_starts_with($currentPath, '/admin/pedidos') ? 'active' : '' ?>">
                Pedidos
            </a>
            <a href="/admin/produtos" class="nav-item <?= str_starts_with($currentPath, '/admin/produtos') ? 'active' : '' ?>">
                Produtos
            </a>
            <a href="/admin/marcas" class="nav-item <?= str_starts_with($currentPath, '/admin/marcas') ? 'active' : '' ?>">
                Marcas
            </a>
            <a href="/admin/categorias" class="nav-item <?= str_starts_with($currentPath, '/admin/categorias') ? 'active' : '' ?>">
                Categorias
            </a>
            <a href="/admin/combos" class="nav-item <?= str_starts_with($currentPath, '/admin/combos') ? 'active' : '' ?>">
                Combos
            </a>
            <a href="/admin/estoque" class="nav-item <?= str_starts_with($currentPath, '/admin/estoque') ? 'active' : '' ?>">
                Estoque
            </a>
            <a href="/admin/clientes" class="nav-item <?= str_starts_with($currentPath, '/admin/clientes') ? 'active' : '' ?>">
                Clientes
            </a>
            <a href="/admin/cupons" class="nav-item <?= str_starts_with($currentPath, '/admin/cupons') ? 'active' : '' ?>">
                Cupons
            </a>
            <a href="/admin/avaliacoes" class="nav-item <?= str_starts_with($currentPath, '/admin/avaliacoes') ? 'active' : '' ?>">
                Avaliações
            </a>
            <a href="/admin/scripts" class="nav-item <?= str_starts_with($currentPath, '/admin/scripts') ? 'active' : '' ?>">
                Scripts
            </a>
            <a href="/admin/configuracoes" class="nav-item <?= str_starts_with($currentPath, '/admin/configuracoes') ? 'active' : '' ?>">
                Configuracoes
            </a>
            <a href="/admin/usuarios" class="nav-item <?= str_starts_with($currentPath, '/admin/usuarios') ? 'active' : '' ?>">
                Usuarios
            </a>
            <a href="/admin/utm" class="nav-item <?= str_starts_with($currentPath, '/admin/utm') ? 'active' : '' ?>">
                UTM Builder
            </a>
            <?php
            $unreadCount = \Maia\Services\NotificationService::countUnread();
            ?>
            <a href="/admin/notificacoes" class="nav-item <?= str_starts_with($currentPath, '/admin/notificacoes') ? 'active' : '' ?>">
                Notificações
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
                <a href="/" target="_blank" rel="noopener" class="view-store-link">Ver loja ↗</a>
            </div>
        </header>
        <div class="admin-content">
