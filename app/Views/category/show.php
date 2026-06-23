<?php use Maia\Helpers\Sanitizer; ?>

<div class="container page-layout">

    <nav class="breadcrumb" aria-label="Navegação">
        <ol>
            <li><a href="/">Home</a></li>
            <li aria-current="page"><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></li>
        </ol>
    </nav>

    <aside class="sidebar">
        <h2 class="sidebar-title">Filtrar</h2>

        <form method="get" action="/categoria/<?= htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8') ?>" id="filter-form">
            <?php if (!empty($brands)): ?>
            <fieldset class="filter-group">
                <legend>Marca</legend>
                <?php foreach ($brands as $brand): ?>
                <label class="filter-label">
                    <input type="checkbox" name="marca[]"
                           value="<?= htmlspecialchars($brand['slug'] ?? $brand['name'], ENT_QUOTES, 'UTF-8') ?>"
                           <?= in_array($brand['slug'] ?? $brand['name'], (array)($_GET['marca'] ?? []), true) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8') ?>
                </label>
                <?php endforeach; ?>
            </fieldset>
            <?php endif; ?>

            <fieldset class="filter-group">
                <legend>Preço</legend>
                <div class="price-range">
                    <input type="number" name="preco_min" placeholder="Mín" min="0" step="1"
                           value="<?= htmlspecialchars($_GET['preco_min'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <span>—</span>
                    <input type="number" name="preco_max" placeholder="Máx" min="0" step="1"
                           value="<?= htmlspecialchars($_GET['preco_max'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </fieldset>

            <button type="submit" class="btn btn-primary btn-block">Aplicar Filtros</button>
            <a href="/categoria/<?= htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-link btn-block">Limpar</a>
        </form>
    </aside>

    <section class="category-content">
        <header class="category-header">
            <h1><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></h1>
            <div class="category-controls">
                <span><?= (int)$total ?> produto<?= $total !== 1 ? 's' : '' ?></span>
                <select name="ordem" onchange="document.getElementById('filter-form').submit()">
                    <option value="relevancia" <?= ($_GET['ordem'] ?? '') === 'relevancia' ? 'selected' : '' ?>>Relevância</option>
                    <option value="menor_preco" <?= ($_GET['ordem'] ?? '') === 'menor_preco' ? 'selected' : '' ?>>Menor Preço</option>
                    <option value="maior_preco" <?= ($_GET['ordem'] ?? '') === 'maior_preco' ? 'selected' : '' ?>>Maior Preço</option>
                    <option value="mais_vendido" <?= ($_GET['ordem'] ?? '') === 'mais_vendido' ? 'selected' : '' ?>>Mais Vendidos</option>
                    <option value="novidade" <?= ($_GET['ordem'] ?? '') === 'novidade' ? 'selected' : '' ?>>Novidades</option>
                </select>
            </div>
        </header>

        <?php if (empty($products)): ?>
        <p class="empty-state">Nenhum produto encontrado para os filtros selecionados.</p>
        <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <?php include __DIR__ . '/../partials/product_card.php'; ?>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Páginas">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php $queryString = http_build_query(array_merge($_GET, ['pagina' => $p])); ?>
                <a href="?<?= $queryString ?>"
                   class="page-link <?= $p === $page ? 'active' : '' ?>"
                   <?= $p === $page ? 'aria-current="page"' : '' ?>>
                    <?= $p ?>
                </a>
            <?php endfor; ?>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </section>
</div>
