<?php use Maia\Helpers\Sanitizer; ?>

<div class="container">
    <nav class="breadcrumb" aria-label="Navegação">
        <ol>
            <li><a href="/">Home</a></li>
            <li><a href="/produtos">Produtos</a></li>
            <li aria-current="page"><?= htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8') ?></li>
        </ol>
    </nav>

    <div class="brand-header">
        <?php if (!empty($brand['logo'])): ?>
        <img src="<?= htmlspecialchars($brand['logo'], ENT_QUOTES, 'UTF-8') ?>"
             alt="<?= htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8') ?>"
             class="brand-logo" height="60">
        <?php endif; ?>
        <div>
            <h1 class="brand-title"><?= htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="text-secondary"><?= $total ?> produto<?= $total !== 1 ? 's' : '' ?></p>
        </div>
    </div>

    <?php if (!empty($products)): ?>
    <div class="grid-products">
        <?php foreach ($products as $product): ?>
        <?php include __DIR__ . '/../partials/product_card.php'; ?>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?pagina=<?= $i ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <p class="text-secondary" style="padding:3rem 0;text-align:center">Nenhum produto disponível desta marca no momento.</p>
    <?php endif; ?>
</div>
