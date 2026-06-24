<?php use Maia\Helpers\{CSRF, Sanitizer}; ?>

<div class="container">
    <nav class="breadcrumb" aria-label="Navegação">
        <a href="/">Home</a><span class="breadcrumb-sep">/</span>
        <a href="/combos">Combos</a><span class="breadcrumb-sep">/</span>
        <span><?= htmlspecialchars($combo['name'], ENT_QUOTES, 'UTF-8') ?></span>
    </nav>

    <div class="product-detail">
        <?php if ($combo['image']): ?>
        <div class="product-gallery">
            <img src="<?= htmlspecialchars($combo['image'], ENT_QUOTES, 'UTF-8') ?>"
                 alt="<?= htmlspecialchars($combo['name'], ENT_QUOTES, 'UTF-8') ?>"
                 loading="eager">
        </div>
        <?php endif; ?>

        <div class="product-info">
            <h1><?= htmlspecialchars($combo['name'], ENT_QUOTES, 'UTF-8') ?></h1>

            <div class="product-price">
                <span class="price-current">R$ <?= Sanitizer::money((float)$combo['price']) ?></span>
            </div>

            <?php if ($combo['description']): ?>
            <p class="combo-detail__desc"><?= nl2br(htmlspecialchars($combo['description'], ENT_QUOTES, 'UTF-8')) ?></p>
            <?php endif; ?>

            <?php if (!empty($items)): ?>
            <div class="combo-detail__items">
                <h3>Produtos inclusos:</h3>
                <ul>
                    <?php foreach ($items as $item): ?>
                    <li>
                        <strong><?= (int)$item['quantity'] ?>x</strong>
                        <a href="/produto/<?= htmlspecialchars($item['slug'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form action="/carrinho/adicionar" method="post">
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                <input type="hidden" name="combo_id" value="<?= (int)$combo['id'] ?>">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-primary btn-block">Adicionar Combo ao Carrinho</button>
            </form>
        </div>
    </div>
</div>
