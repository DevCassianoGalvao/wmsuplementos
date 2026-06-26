<section class="listing-hero">
    <div class="container">
        <p class="section__label">Loja</p>
        <h1 class="section__title">Produtos</h1>
        <form class="listing-toolbar" action="/produtos" method="get">
            <input type="search" name="busca" placeholder="Buscar produto..." value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <select name="ordem">
                <option value="relevancia" <?= ($currentSort ?? '') === 'relevancia' ? 'selected' : '' ?>>Relevância</option>
                <option value="preco_asc" <?= ($currentSort ?? '') === 'preco_asc' ? 'selected' : '' ?>>Menor preço</option>
                <option value="preco_desc" <?= ($currentSort ?? '') === 'preco_desc' ? 'selected' : '' ?>>Maior preço</option>
                <option value="mais_vendidos" <?= ($currentSort ?? '') === 'mais_vendidos' ? 'selected' : '' ?>>Mais vendidos</option>
                <option value="novidades" <?= ($currentSort ?? '') === 'novidades' ? 'selected' : '' ?>>Novidades</option>
            </select>
            <button type="submit" class="btn btn--primary">Filtrar</button>
        </form>
    </div>
</section>

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
                    <?php foreach ($categories as $category): ?>
                    <a href="/categoria/<?= htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8') ?>" class="category-card animate-in">
                        <div class="category-card__body">
                            <span class="category-card__name"><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="category-card__count"><?= (int)($category['product_count'] ?? 0) ?></span>
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

<section class="section">
    <div class="container">
        <div class="section__header">
            <p class="section__label"><?= (int)$total ?> encontrados</p>
            <h2 class="section__title">Catálogo</h2>
        </div>

        <?php if (empty($products)): ?>
        <p class="empty-state">Nenhum produto encontrado.</p>
        <?php else: ?>
        <div class="grid-products">
            <?php foreach ($products as $product): ?>
            <?php include __DIR__ . '/../partials/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (($totalPages ?? 1) > 1): ?>
        <nav class="pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $p])) ?>" class="page-link <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </nav>
        <?php endif; ?>
    </div>
</section>
