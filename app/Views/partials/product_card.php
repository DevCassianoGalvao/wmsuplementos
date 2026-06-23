<?php
// Espera $product em escopo
$img   = $product['images'][0]['filename_webp'] ?? $product['image'] ?? '';
$price = (float)($product['price_sale'] ?? $product['price'] ?? 0);
$orig  = !empty($product['price_sale']) ? (float)$product['price'] : 0;
?>
<article class="product-card">
    <a href="/produto/<?= htmlspecialchars($product['slug'], ENT_QUOTES, 'UTF-8') ?>" class="product-card__link">
        <?php if ($img): ?>
        <img src="/uploads/products/<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
             alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
             width="300" height="300" loading="lazy" class="product-card__img">
        <?php else: ?>
        <div class="product-card__img-placeholder" aria-hidden="true"></div>
        <?php endif; ?>

        <div class="product-card__body">
            <h3 class="product-card__name"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <?php if (!empty($product['brand_name'])): ?>
            <p class="product-card__brand"><?= htmlspecialchars($product['brand_name'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <div class="product-card__price">
                <?php if ($orig > 0): ?>
                <span class="price-original">R$ <?= \Maia\Helpers\Sanitizer::money($orig) ?></span>
                <?php endif; ?>
                <span class="price-current">R$ <?= \Maia\Helpers\Sanitizer::money($price) ?></span>
            </div>
        </div>
    </a>
    <form class="add-to-cart-form" action="/carrinho/adicionar" method="post">
        <input type="hidden" name="csrf_token" value="<?= \Maia\Helpers\CSRF::token() ?>">
        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
        <input type="hidden" name="quantity" value="1">
        <?php if ((int)($product['stock'] ?? 0) > 0): ?>
        <button type="submit" class="btn btn-primary btn-sm add-to-cart-btn">Adicionar ao Carrinho</button>
        <?php else: ?>
        <span class="out-of-stock">Esgotado</span>
        <?php endif; ?>
    </form>
</article>
