<?php use Maia\Helpers\Sanitizer; ?>

<?php if (!empty($flash['success'])): ?>
<div class="container"><div class="alert alert--success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div></div>
<?php endif; ?>

<!-- HERO -->
<?php
$heroImage = trim((string)($settings['hero_image'] ?? ''));
if ($heroImage !== '' && !preg_match('#^(https?://|/)#', $heroImage)) {
    $heroImage = '';
}
$safeHeroUrl = static function (string $url, string $fallback): string {
    return preg_match('#^(https?://|/)#', $url) ? $url : $fallback;
};
$heroPrimaryUrl = $safeHeroUrl((string)($settings['hero_primary_url'] ?? '/produtos'), '/produtos');
$heroSecondaryUrl = $safeHeroUrl((string)($settings['hero_secondary_url'] ?? '/combos'), '/combos');
?>
<section class="hero"<?= $heroImage !== '' ? ' style="--hero-image: url(\'' . htmlspecialchars($heroImage, ENT_QUOTES, 'UTF-8') . '\');"' : '' ?>>
    <div class="hero__bg-grid"></div>
    <div class="container">
        <div class="hero__content">
            <p class="hero__label animate-in"><?= htmlspecialchars($settings['hero_label'] ?? 'Performance & Resultados', ENT_QUOTES, 'UTF-8') ?></p>
            <h1 class="hero__title animate-in">
                <?= htmlspecialchars($settings['hero_title_before'] ?? 'Suplementos para', ENT_QUOTES, 'UTF-8') ?><br>
                <em><?= htmlspecialchars($settings['hero_title_emphasis'] ?? 'performance', ENT_QUOTES, 'UTF-8') ?></em>
                <?= htmlspecialchars($settings['hero_title_after'] ?? 'real.', ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <p class="hero__subtitle animate-in"><?= htmlspecialchars($settings['hero_subtitle'] ?? 'Produtos selecionados para treino, rotina e evolução.', ENT_QUOTES, 'UTF-8') ?></p>
            <div class="hero__actions animate-in">
                <a href="<?= htmlspecialchars($heroPrimaryUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn--primary">
                    <?= htmlspecialchars($settings['hero_primary_label'] ?? 'Ver Produtos', ENT_QUOTES, 'UTF-8') ?>
                </a>
                <a href="<?= htmlspecialchars($heroSecondaryUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn--ghost">
                    <?= htmlspecialchars($settings['hero_secondary_label'] ?? 'Ver Combos', ENT_QUOTES, 'UTF-8') ?>
                </a>
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
        <div class="categories-carousel" id="categories-carousel">
            <button class="carousel-btn carousel-btn--prev" id="cat-prev" aria-label="Anterior">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <div class="categories-track-wrap">
                <div class="categories-track" id="cat-track">
                    <?php foreach ($categories as $cat): ?>
                    <a href="/categoria/<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>" class="category-card animate-in">
                        <div class="category-card__body">
                            <span class="category-card__name"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="category-card__count"><?= (int)($cat['product_count'] ?? 0) ?></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="carousel-btn carousel-btn--next" id="cat-next" aria-label="Próxima">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- PRODUTOS EM DESTAQUE -->
<?php if (!empty($featured)): ?>
<section class="section">
    <div class="container">
        <div class="section__header">
            <p class="section__label">O que você precisa?</p>
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
                    <span class="badge badge--red"><?= (int)($combo['item_count'] ?? 0) ?> itens</span>
                    <h2 class="combo-card__name"><?= htmlspecialchars($combo['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <?php if (!empty($combo['description'])): ?>
                    <p class="combo-card__desc"><?= htmlspecialchars(mb_substr($combo['description'], 0, 120), ENT_QUOTES, 'UTF-8') ?>...</p>
                    <?php endif; ?>
                    <div class="combo-card__price">R$ <?= Sanitizer::money((float)($combo['total_price'] ?? $combo['price'] ?? 0)) ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- AVALIACOES -->
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

<!-- FAQ -->
<?php if (($settings['home_faq_enabled'] ?? '1') === '1' && !empty($faq['content'])): ?>
<section class="section faq-section">
    <div class="container">
        <div class="section__header">
            <p class="section__label">Ajuda</p>
            <h2 class="section__title"><?= htmlspecialchars($faq['title'] ?? 'Perguntas Frequentes', ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
        <div class="faq-content">
            <?= $faq['content'] ?>
        </div>
    </div>
</section>
<?php endif; ?>
