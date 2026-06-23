<?php use Maia\Helpers\Sanitizer; ?>

<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<!-- Hero Banner -->
<section class="hero">
    <div class="container hero-inner">
        <h1>Suplementos premium para sua performance</h1>
        <p>Qualidade que você sente. Resultados que você vê.</p>
        <a href="/produtos" class="btn btn-primary btn-lg">Ver Produtos</a>
    </div>
</section>

<!-- Categorias -->
<?php if (!empty($categories)): ?>
<section class="section categories-section">
    <div class="container">
        <h2 class="section-title">Categorias</h2>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
            <a href="/categoria/<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>" class="category-card">
                <?php if ($cat['image']): ?>
                <img src="/uploads/categories/<?= htmlspecialchars($cat['image'], ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>"
                     width="200" height="200" loading="lazy">
                <?php endif; ?>
                <span><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Destaques -->
<?php if (!empty($featured)): ?>
<section class="section products-section">
    <div class="container">
        <h2 class="section-title">Produtos em Destaque</h2>
        <div class="products-grid">
            <?php foreach ($featured as $product): ?>
            <?php include __DIR__ . '/../partials/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Mais Vendidos -->
<?php if (!empty($bestsellers)): ?>
<section class="section products-section bg-light">
    <div class="container">
        <h2 class="section-title">Mais Vendidos</h2>
        <div class="products-grid">
            <?php foreach ($bestsellers as $product): ?>
            <?php include __DIR__ . '/../partials/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Combos -->
<?php if (!empty($combos)): ?>
<section class="section combos-section">
    <div class="container">
        <h2 class="section-title">Combos Especiais</h2>
        <div class="combos-grid">
            <?php foreach ($combos as $combo): ?>
            <div class="combo-card">
                <h3><?= htmlspecialchars($combo['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="combo-price">R$ <?= Sanitizer::money((float)$combo['total_price']) ?></p>
                <a href="/combo/<?= htmlspecialchars($combo['slug'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline">Ver Combo</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Avaliações -->
<?php if (!empty($reviews)): ?>
<section class="section reviews-section">
    <div class="container">
        <h2 class="section-title">O que dizem nossos clientes</h2>
        <div class="reviews-grid">
            <?php foreach ($reviews as $review): ?>
            <div class="review-card">
                <div class="stars" aria-label="<?= (int)$review['rating'] ?> de 5 estrelas">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="star <?= $i <= (int)$review['rating'] ? 'filled' : '' ?>">&#9733;</span>
                    <?php endfor; ?>
                </div>
                <p class="review-comment"><?= htmlspecialchars($review['comment'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                <p class="review-author"><?= htmlspecialchars($review['customer_name'] ?? 'Cliente', ENT_QUOTES, 'UTF-8') ?></p>
                <p class="review-product"><small><?= htmlspecialchars($review['product_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></small></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
