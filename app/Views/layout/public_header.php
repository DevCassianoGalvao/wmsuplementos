<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Maia Suplementos', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="app-base" content="<?= htmlspecialchars(defined('APP_BASE') ? APP_BASE : '', ENT_QUOTES, 'UTF-8') ?>">
    <?php if (!empty($metaDesc)): ?>
    <meta name="description" content="<?= htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle ?? 'Maia Suplementos', ENT_QUOTES, 'UTF-8') ?>">
    <?php if (!empty($metaDesc)): ?>
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <meta property="og:type" content="<?= !empty($ogType) ? htmlspecialchars($ogType, ENT_QUOTES, 'UTF-8') : 'website' ?>">
    <?php if (!empty($ogImage)): ?>
    <meta property="og:image" content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/design-system.css?v=20260625-6">
    <link rel="stylesheet" href="/assets/css/animations.css?v=20260625-6">
    <link rel="stylesheet" href="/assets/css/main.css?v=20260625-6">
    <?= \Maia\Helpers\ScriptInjector::head() ?>
</head>
<body>
<?= \Maia\Helpers\ScriptInjector::bodyOpen() ?>
<header class="site-header" id="site-header">
    <div class="container header-inner">
        <button class="btn btn--icon mobile-menu-toggle" aria-label="Menu" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>

        <a href="/" class="logo" aria-label="Maia Suplementos">
            <img src="/assets/img/logo.png" alt="Maia Suplementos" height="44" loading="eager" style="height:44px;width:auto;">
        </a>

        <form class="search-form" action="/busca" method="get" role="search">
            <div class="input-group">
                <span class="input-group__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </span>
                <input class="input" type="search" name="q" placeholder="Buscar produtos, marcas..."
                       value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       aria-label="Buscar produtos" autocomplete="off">
            </div>
        </form>

        <nav class="header-nav" id="header-nav" aria-label="Menu principal">
            <a href="/produtos" class="nav-link">Produtos</a>
            <a href="/combos" class="nav-link">Combos</a>
            <?php if (\Maia\Helpers\Auth::isUserLogged()): ?>
                <a href="/minha-conta" class="nav-link">Minha Conta</a>
                <a href="/sair" class="nav-link">Sair</a>
            <?php else: ?>
                <a href="/entrar" class="nav-link">Entrar</a>
            <?php endif; ?>
        </nav>

        <a href="/carrinho" class="cart-btn btn btn--icon" aria-label="Carrinho">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            <?php $cartCount = (int)(\Maia\Services\CartService::countStatic()); ?>
            <?php if ($cartCount > 0): ?>
            <span class="cart-count" id="cart-count"><?= $cartCount ?></span>
            <?php endif; ?>
        </a>
    </div>
    <!-- Mobile nav overlay -->
    <div class="mobile-nav" id="mobile-nav">
        <nav>
            <a href="/produtos">Produtos</a>
            <a href="/combos">Combos</a>
            <?php if (\Maia\Helpers\Auth::isUserLogged()): ?>
                <a href="/minha-conta">Minha Conta</a>
                <a href="/sair">Sair</a>
            <?php else: ?>
                <a href="/entrar">Entrar</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main id="main-content">
