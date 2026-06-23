<?php use Maia\Helpers\Sanitizer; use Maia\Helpers\CSRF; ?>

<div class="container">

    <nav class="breadcrumb" aria-label="Navegação">
        <ol>
            <li><a href="/">Home</a></li>
            <?php if (!empty($product['category_slug'])): ?>
            <li><a href="/categoria/<?= htmlspecialchars($product['category_slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($product['category_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></a></li>
            <?php endif; ?>
            <li aria-current="page"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></li>
        </ol>
    </nav>

    <div class="product-detail">
        <!-- Galeria -->
        <div class="product-gallery">
            <?php $images = $product['images'] ?? []; ?>
            <?php if (!empty($images)): ?>
            <div class="gallery-main">
                <img id="main-img"
                     src="/uploads/products/<?= htmlspecialchars($images[0]['filename_webp'] ?? $images[0]['filename'], ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                     width="500" height="500">
            </div>
            <?php if (count($images) > 1): ?>
            <div class="gallery-thumbs">
                <?php foreach ($images as $img): ?>
                <button type="button" class="thumb-btn"
                        data-src="/uploads/products/<?= htmlspecialchars($img['filename_webp'] ?? $img['filename'], ENT_QUOTES, 'UTF-8') ?>"
                        onclick="document.getElementById('main-img').src=this.dataset.src">
                    <img src="/uploads/products/<?= htmlspecialchars($img['filename_webp'] ?? $img['filename'], ENT_QUOTES, 'UTF-8') ?>"
                         alt="" width="80" height="80" loading="lazy">
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div class="product-gallery__placeholder" aria-hidden="true"></div>
            <?php endif; ?>
        </div>

        <!-- Info -->
        <div class="product-info">
            <?php if (!empty($product['brand_name'])): ?>
            <p class="product-brand"><?= htmlspecialchars($product['brand_name'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <h1 class="product-name"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h1>

            <div class="product-rating" aria-label="Avaliação: <?= number_format((float)$product['avg_rating'], 1) ?> de 5">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="star <?= $i <= round((float)($product['avg_rating'] ?? 0)) ? 'filled' : '' ?>">&#9733;</span>
                <?php endfor; ?>
                <span class="rating-count">(<?= (int)$product['review_count'] ?> avaliações)</span>
            </div>

            <div class="product-price">
                <?php if (!empty($product['price_sale'])): ?>
                <span class="price-original">R$ <?= Sanitizer::money((float)$product['price']) ?></span>
                <span class="price-current">R$ <?= Sanitizer::money((float)$product['price_sale']) ?></span>
                <?php else: ?>
                <span class="price-current">R$ <?= Sanitizer::money((float)$product['price']) ?></span>
                <?php endif; ?>
            </div>

            <?php if ((int)$product['stock'] > 0): ?>
            <form action="/carrinho/adicionar" method="post" class="add-to-cart-form">
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                <div class="qty-input">
                    <label for="qty">Quantidade:</label>
                    <input type="number" id="qty" name="quantity" value="1" min="1" max="<?= (int)$product['stock'] ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block">Adicionar ao Carrinho</button>
            </form>
            <?php else: ?>
            <p class="out-of-stock-msg">Produto temporariamente indisponível</p>
            <?php endif; ?>

            <?php if (!empty($product['description'])): ?>
            <div class="product-description">
                <h2>Descrição</h2>
                <div class="rich-text"><?= nl2br(htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8')) ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Avaliações -->
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
            <p class="review-meta">
                <strong><?= htmlspecialchars($review['customer_name'] ?? 'Cliente', ENT_QUOTES, 'UTF-8') ?></strong>
                · <?= htmlspecialchars($review['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </p>
        </article>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <!-- Relacionados -->
    <?php if (!empty($related)): ?>
    <section class="related-products">
        <h2>Produtos Relacionados</h2>
        <div class="products-grid">
            <?php foreach ($related as $product): ?>
            <?php include __DIR__ . '/../partials/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</div>
