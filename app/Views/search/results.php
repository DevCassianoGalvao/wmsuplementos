<div class="container">
    <h1>Busca: "<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>"</h1>

    <?php if (mb_strlen($query) < 2): ?>
    <p class="empty-state">Digite ao menos 2 caracteres para buscar.</p>
    <?php elseif (empty($results)): ?>
    <p class="empty-state">Nenhum produto encontrado para "<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>".</p>
    <a href="/produtos" class="btn btn-outline">Ver todos os produtos</a>
    <?php else: ?>
    <p class="search-info"><?= (int)$total ?> resultado<?= $total !== 1 ? 's' : '' ?> encontrado<?= $total !== 1 ? 's' : '' ?></p>
    <div class="products-grid">
        <?php foreach ($results as $product): ?>
        <?php include __DIR__ . '/../partials/product_card.php'; ?>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="pagination" aria-label="Páginas">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <a href="?q=<?= urlencode($query) ?>&pagina=<?= $p ?>"
           class="page-link <?= $p === $page ? 'active' : '' ?>"
           <?= $p === $page ? 'aria-current="page"' : '' ?>>
            <?= $p ?>
        </a>
        <?php endfor; ?>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
</div>
