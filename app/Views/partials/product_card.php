<?php
// Espera $product em escopo
use Maia\Helpers\{CSRF, Sanitizer};
$img   = $product['images'][0]['filename_webp'] ?? $product['image'] ?? '';
$price = (float)($product['price_sale'] ?? $product['price'] ?? 0);
$orig  = !empty($product['price_sale']) ? (float)$product['price'] : 0;
$inStock = (int)($product['stock'] ?? 0) > 0;
?>
<article class="product-card animate-in">
    <div class="product-card__image-wrap">
        <a href="/produto/<?= htmlspecialchars($product['slug'], ENT_QUOTES, 'UTF-8') ?>" class="product-card__link" aria-label="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($img): ?>
            <img src="/uploads/products/<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                 alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                 loading="lazy" width="400" height="400" class="product-card__img">
            <?php else: ?>
            <div class="skeleton" style="width:100%;height:100%;"></div>
            <?php endif; ?>
        </a>
        <?php if (!empty($product['bestseller'])): ?>
        <span class="product-card__badge badge badge--red">Mais Vendido</span>
        <?php elseif ($orig > 0): ?>
        <span class="product-card__badge badge badge--red">Promoção</span>
        <?php endif; ?>
        <?php if ((int)($product['stock'] ?? 99) < 5): ?>
        <span class="product-card__stock-indicator"><span class="stock-pulse"></span></span>
        <?php endif; ?>
    </div>
    <div class="product-card__body">
        <?php if (!empty($product['brand_name'])): ?>
        <span class="product-card__brand"><?= htmlspecialchars($product['brand_name'], ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
        <a href="/produto/<?= htmlspecialchars($product['slug'], ENT_QUOTES, 'UTF-8') ?>" class="product-card__name-link">
            <h3 class="product-card__name"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h3>
        </a>
        <div class="product-card__pricing">
            <span class="product-card__price">R$ <?= Sanitizer::money($price) ?></span>
            <?php if ($orig > 0): ?>
            <span class="product-card__price-old">R$ <?= Sanitizer::money($orig) ?></span>
            <?php endif; ?>
        </div>
        <form class="add-to-cart-form product-card__form" action="/carrinho/adicionar" method="post">
            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
            <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
            <input type="hidden" name="quantity" value="1">
            <?php if ($inStock): ?>
            <button type="submit" class="btn btn--primary btn--block product-card__btn add-to-cart-btn">
                Adicionar ao Carrinho
            </button>
            <?php else: ?>
            <span class="out-of-stock">Esgotado</span>
            <?php endif; ?>
        </form>
    </div>
</article>
