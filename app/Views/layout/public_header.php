<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Maia Suplementos', ENT_QUOTES, 'UTF-8') ?></title>
    <?php if (!empty($metaDesc)): ?>
    <meta name="description" content="<?= htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <?= \Maia\Helpers\ScriptInjector::head() ?>
</head>
<body>
<?= \Maia\Helpers\ScriptInjector::bodyOpen() ?>
<header class="site-header">
    <div class="container header-inner">
        <a href="/" class="logo" aria-label="Maia Suplementos">
            <img src="/assets/img/logo.svg" alt="Maia Suplementos" width="160" height="40">
        </a>

        <form class="search-form" action="/busca" method="get" role="search">
            <input type="search" name="q" placeholder="Buscar produtos..."
                   value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   aria-label="Buscar produtos" autocomplete="off">
            <button type="submit" aria-label="Buscar">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
        </form>

        <nav class="header-nav" aria-label="Menu principal">
            <a href="/produtos">Produtos</a>
            <a href="/combos">Combos</a>
            <?php if (\Maia\Helpers\Auth::isUserLogged()): ?>
                <a href="/minha-conta">Minha Conta</a>
                <a href="/sair">Sair</a>
            <?php else: ?>
                <a href="/entrar">Entrar</a>
            <?php endif; ?>
            <a href="/carrinho" class="cart-link" aria-label="Carrinho">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                <span class="cart-count" id="cart-count"><?= (int)(\Maia\Services\CartService::countStatic()) ?></span>
            </a>
        </nav>
    </div>
</header>
<main id="main-content">
