<?php use Maia\Helpers\Sanitizer; use Maia\Helpers\CSRF; ?>

<?php
$images = $product['images'] ?? [];
$nutrition = [];
if (!empty($product['nutrition_table'])) {
    $decodedNutrition = is_array($product['nutrition_table'])
        ? $product['nutrition_table']
        : json_decode((string)$product['nutrition_table'], true);
    $nutrition = is_array($decodedNutrition) ? $decodedNutrition : [];
}
$effectivePrice = !empty($product['price_sale']) ? (float)$product['price_sale'] : (float)$product['price'];
$discountPercent = !empty($product['price_sale']) && (float)$product['price'] > 0
    ? (int)round((1 - ((float)$product['price_sale'] / (float)$product['price'])) * 100)
    : 0;
$siteUrl = rtrim((string)(getenv('APP_URL') ?: ''), '/');
$mainImage = $images[0]['filename_webp'] ?? $images[0]['filename'] ?? '';
$schemaImage = $mainImage;
if ($siteUrl !== '' && $schemaImage !== '' && !preg_match('#^https?://#', $schemaImage)) {
    $schemaImage = $siteUrl . '/' . ltrim($schemaImage, '/');
}
$ratingValue = (float)($product['avg_rating'] ?? $avgRating ?? 0);
$ratingCount = (int)($product['review_count'] ?? $reviewCount ?? 0);
?>

<div class="container">
    <?php if (!empty($flash)): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type'] ?? 'success', ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($flash['message'] ?? '', ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>

    <nav class="breadcrumb" aria-label="Navegacao">
        <ol>
            <li><a href="/">Home</a></li>
            <?php if (!empty($product['category_slug'])): ?>
            <li><a href="/categoria/<?= htmlspecialchars($product['category_slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($product['category_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></a></li>
            <?php endif; ?>
            <li aria-current="page"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></li>
        </ol>
    </nav>

    <div class="product-detail">
        <div class="product-gallery">
            <?php if (!empty($images)): ?>
            <div class="gallery-main">
                <img id="main-img"
                     src="<?= htmlspecialchars($images[0]['filename_webp'] ?? $images[0]['filename'], ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                     width="500" height="500">
            </div>
            <?php if (count($images) > 1): ?>
            <div class="gallery-thumbs">
                <?php foreach ($images as $img): ?>
                <button type="button" class="thumb-btn"
                        data-src="<?= htmlspecialchars($img['filename_webp'] ?? $img['filename'], ENT_QUOTES, 'UTF-8') ?>"
                        onclick="document.getElementById('main-img').src=this.dataset.src">
                    <img src="<?= htmlspecialchars($img['filename_webp'] ?? $img['filename'], ENT_QUOTES, 'UTF-8') ?>"
                         alt="" width="80" height="80" loading="lazy">
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div class="product-gallery__placeholder" aria-hidden="true"></div>
            <?php endif; ?>

        </div>

        <div class="product-info">
            <?php if (!empty($product['brand_name'])): ?>
            <p class="product-brand"><?= htmlspecialchars($product['brand_name'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <h1 class="product-name"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h1>

            <div class="product-rating" aria-label="Avaliacao: <?= number_format($ratingValue, 1) ?> de 5">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="star <?= $i <= round($ratingValue) ? 'filled' : '' ?>">&#9733;</span>
                <?php endfor; ?>
                <span class="rating-count">(<?= $ratingCount ?> avaliações)</span>
            </div>

            <div class="product-price">
                <?php if (!empty($product['price_sale'])): ?>
                <span class="price-original">R$ <?= Sanitizer::money((float)$product['price']) ?></span>
                <span class="price-current">R$ <?= Sanitizer::money((float)$product['price_sale']) ?></span>
                <?php if ($discountPercent > 0): ?>
                <span class="badge badge--red">-<?= $discountPercent ?>%</span>
                <?php endif; ?>
                <?php else: ?>
                <span class="price-current">R$ <?= Sanitizer::money((float)$product['price']) ?></span>
                <?php endif; ?>
            </div>

            <?php if ((int)$product['stock'] > 0 && (int)$product['stock'] <= 5): ?>
            <p class="stock-warning">Apenas <?= (int)$product['stock'] ?> unidade(s) em estoque.</p>
            <?php endif; ?>

            <?php if (!empty($product['description'])): ?>
            <div class="product-description">
                <h2>Descrição</h2>
                <div class="rich-text"><?= nl2br(htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8')) ?></div>
            </div>
            <?php endif; ?>

            <?php if ((int)$product['stock'] > 0): ?>
            <form action="/carrinho/adicionar" method="post" class="add-to-cart-form"
                  data-product-id="<?= (int)$product['id'] ?>"
                  data-product-name="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                  data-product-value="<?= htmlspecialchars(number_format($effectivePrice, 2, '.', ''), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                <div class="product-qty-wrap">
                    <div class="cart-item__qty">
                        <button type="button" class="qty-btn qty-minus-prod" aria-label="Diminuir">−</button>
                        <input type="number" id="qty" name="quantity" value="1" min="1" max="<?= (int)$product['stock'] ?>" class="qty-input qty-input-prod" readonly>
                        <button type="button" class="qty-btn qty-plus-prod" aria-label="Aumentar">+</button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block">Adicionar ao Carrinho</button>
            </form>
            <?php else: ?>
            <p class="out-of-stock-msg">Produto temporariamente indisponível</p>
            <form action="/produto/<?= htmlspecialchars($product['slug'], ENT_QUOTES, 'UTF-8') ?>/avisar" method="post" class="stock-notify-form">
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                <?php if (!\Maia\Helpers\Auth::isUserLogged()): ?>
                <label for="stock-email">Receber aviso por e-mail</label>
                <input type="email" id="stock-email" name="email" placeholder="voce@email.com" required>
                <?php else: ?>
                <p class="stock-notify-copy">Vamos avisar no e-mail da sua conta.</p>
                <?php endif; ?>
                <button type="submit" class="btn btn-outline btn-block">Avise-me quando voltar</button>
            </form>
            <?php endif; ?>

            <?php if (!empty($product['benefits'])): ?>
            <div class="product-description">
                <h2>Benefícios</h2>
                <div class="rich-text"><?= nl2br(htmlspecialchars($product['benefits'], ENT_QUOTES, 'UTF-8')) ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($nutrition)): ?>
            <div class="product-description">
                <h2>Tabela Nutricional</h2>
                <dl class="nutrition-list">
                    <?php foreach ($nutrition as $label => $value): ?>
                    <div>
                        <dt><?= htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8') ?></dt>
                        <dd><?= htmlspecialchars(is_scalar($value) ? (string)$value : json_encode($value, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?></dd>
                    </div>
                    <?php endforeach; ?>
                </dl>
            </div>
            <?php endif; ?>

            <?php if (!empty($product['description']) || !empty($product['benefits']) || !empty($nutrition)): ?>
            <div class="product-tabs" data-tabs>
                <div class="product-tabs__nav" role="tablist" aria-label="Detalhes do produto">
                    <?php if (!empty($product['description'])): ?>
                    <button type="button" class="product-tabs__btn is-active" data-tab-target="description" role="tab">Descricao</button>
                    <?php endif; ?>
                    <?php if (!empty($product['benefits'])): ?>
                    <button type="button" class="product-tabs__btn <?= empty($product['description']) ? 'is-active' : '' ?>" data-tab-target="benefits" role="tab">Beneficios</button>
                    <?php endif; ?>
                    <?php if (!empty($nutrition)): ?>
                    <button type="button" class="product-tabs__btn <?= empty($product['description']) && empty($product['benefits']) ? 'is-active' : '' ?>" data-tab-target="nutrition" role="tab">Tabela Nutricional</button>
                    <?php endif; ?>
                </div>

                <?php if (!empty($product['description'])): ?>
                <div class="product-tabs__panel is-active" data-tab-panel="description" role="tabpanel">
                    <div class="rich-text"><?= nl2br(htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8')) ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($product['benefits'])): ?>
                <div class="product-tabs__panel <?= empty($product['description']) ? 'is-active' : '' ?>" data-tab-panel="benefits" role="tabpanel">
                    <div class="rich-text"><?= nl2br(htmlspecialchars($product['benefits'], ENT_QUOTES, 'UTF-8')) ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($nutrition)): ?>
                <div class="product-tabs__panel <?= empty($product['description']) && empty($product['benefits']) ? 'is-active' : '' ?>" data-tab-panel="nutrition" role="tabpanel">
                    <dl class="nutrition-list">
                        <?php foreach ($nutrition as $label => $value): ?>
                        <div>
                            <dt><?= htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8') ?></dt>
                            <dd><?= htmlspecialchars(is_scalar($value) ? (string)$value : json_encode($value, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?></dd>
                        </div>
                        <?php endforeach; ?>
                    </dl>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($reviews)): ?>
    <section class="product-reviews">
        <h2>Avaliações dos clientes</h2>
        <?php foreach ($reviews as $review): ?>
        <article class="review-item">
            <div class="stars" aria-label="<?= (int)$review['rating'] ?> de 5">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="star <?= $i <= (int)$review['rating'] ? 'filled' : '' ?>">&#9733;</span>
                <?php endfor; ?>
            </div>
            <p class="review-comment"><?= htmlspecialchars($review['comment'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            <?php if (!empty($review['photo'])): ?>
            <img src="<?= htmlspecialchars($review['photo'], ENT_QUOTES, 'UTF-8') ?>" alt="" class="review-photo" loading="lazy">
            <?php endif; ?>
            <p class="review-meta">
                <strong><?= htmlspecialchars($review['customer_name'] ?? $review['user_name'] ?? 'Cliente', ENT_QUOTES, 'UTF-8') ?></strong>
                &middot; <?= htmlspecialchars($review['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </p>
        </article>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($related)): ?>
    <section class="related-products">
        <h2>Produtos Relacionados</h2>
        <div class="products-grid">
            <?php foreach ($related as $relatedProduct): ?>
            <?php $cardProduct = $product; $product = $relatedProduct; ?>
            <?php include __DIR__ . '/../partials/product_card.php'; ?>
            <?php $product = $cardProduct; unset($cardProduct); ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product['name'],
    'description' => $product['seo_description'] ?: $product['description'],
    'sku' => $product['sku'] ?? null,
    'brand' => !empty($product['brand_name']) ? ['@type' => 'Brand', 'name' => $product['brand_name']] : null,
    'image' => $schemaImage ?: null,
    'offers' => [
        '@type' => 'Offer',
        'priceCurrency' => 'BRL',
        'price' => number_format($effectivePrice, 2, '.', ''),
        'availability' => ((int)$product['stock'] > 0) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
    ],
    'aggregateRating' => ($ratingCount > 0) ? [
        '@type' => 'AggregateRating',
        'ratingValue' => $ratingValue,
        'reviewCount' => $ratingCount,
    ] : null,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
</script>
<script>
window.addEventListener('load', function() {
    if (typeof fbq !== 'function') return;
    fbq('track', 'ViewContent', <?= json_encode([
        'content_ids' => [(string)$product['id']],
        'content_name' => $product['name'],
        'content_type' => 'product',
        'value' => $effectivePrice,
        'currency' => 'BRL',
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>);
});
</script>
