<?php use Maia\Helpers\Sanitizer; ?>

<?php if (!empty($flash['success'])): ?>
<div class="container"><div class="alert alert--success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div></div>
<?php endif; ?>

<!-- HERO -->
<section class="hero">
    <div class="hero__bg-grid"></div>
    <div class="container">
        <div class="hero__content">
            <p class="hero__label animate-in">Performance &amp; Resultados</p>
            <h1 class="hero__title animate-in">
                Suplementos para quem<br>leva a <em>performance</em> a sério.
            </h1>
            <p class="hero__subtitle animate-in">Qualidade que você sente. Resultados que você vê.</p>
            <div class="hero__actions animate-in">
                <a href="/produtos" class="btn btn--primary">Ver Produtos</a>
                <a href="/combos" class="btn btn--ghost">Ver Combos</a>
            </div>
        </div>
    </div>
</section>

<!-- CATEGORIAS -->
<?php if (!empty($categories)): ?>
<section class="section" style="padding-top:3rem;padding-bottom:2rem;">
    <div class="container">
        <div class="section__header">
            <p class="section__label">Explorar</p>
            <h2 class="section__title">Categorias</h2>
        </div>
        <div class="categories-scroll">
            <?php foreach ($categories as $cat): ?>
            <a href="/categoria/<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>" class="category-card animate-in">
                <div class="category-card__body">
                    <span class="category-card__name"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- PRODUTOS EM DESTAQUE -->
<?php if (!empty($featured)): ?>
<section class="section">
    <div class="container">
        <div class="section__header">
            <p class="section__label">Curadoria</p>
            <h2 class="section__title">Produtos em Destaque</h2>
        </div>
        <div class="grid-products">
            <?php foreach ($featured as $product): ?>
            <?php include __DIR__ . '/../partials/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- MAIS VENDIDOS -->
<?php if (!empty($bestsellers)): ?>
<section class="section" style="padding-top:0;">
    <div class="container">
        <div class="section__header">
            <p class="section__label">Mais Pedidos</p>
            <h2 class="section__title">Mais Vendidos</h2>
        </div>
        <div class="grid-products">
            <?php foreach ($bestsellers as $product): ?>
            <?php include __DIR__ . '/../partials/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- COMBOS -->
<?php if (!empty($combos)): ?>
<section class="section combos-section">
    <div class="container">
        <div class="section__header">
            <p class="section__label">Economia</p>
            <h2 class="section__title">Combos Especiais</h2>
        </div>
        <div class="combos-grid">
            <?php foreach ($combos as $combo): ?>
            <a href="/combo/<?= htmlspecialchars($combo['slug'], ENT_QUOTES, 'UTF-8') ?>" class="combo-card animate-in">
                <div class="combo-card__body">
                    <span class="badge badge--red">Kit</span>
                    <h3 class="combo-card__name"><?= htmlspecialchars($combo['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <?php if (!empty($combo['description'])): ?>
                    <p class="combo-card__desc"><?= htmlspecialchars(mb_substr($combo['description'], 0, 80), ENT_QUOTES, 'UTF-8') ?>...</p>
                    <?php endif; ?>
                    <div class="combo-card__price">
                        R$ <?= Sanitizer::money((float)($combo['total_price'] ?? $combo['price'] ?? 0)) ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- AVALIAÇÕES -->
<?php if (!empty($reviews)): ?>
<section class="section reviews-section">
    <div class="container">
        <div class="section__header">
            <p class="section__label">Clientes</p>
            <h2 class="section__title">O que dizem sobre nós</h2>
        </div>
        <div class="reviews-grid">
            <?php foreach ($reviews as $review): ?>
            <div class="review-card animate-in">
                <div class="review-card__stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="star <?= $i <= (int)$review['rating'] ? 'star--filled' : '' ?>">&#9733;</span>
                    <?php endfor; ?>
                </div>
                <p class="review-card__text">"<?= htmlspecialchars($review['comment'] ?? '', ENT_QUOTES, 'UTF-8') ?>"</p>
                <span class="review-card__author"><?= htmlspecialchars($review['customer_name'] ?? 'Cliente', ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
