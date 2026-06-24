<?php use Maia\Helpers\Sanitizer; ?>

<section class="listing-hero">
    <div class="container">
        <p class="section__label">Kits</p>
        <h1 class="section__title">Combos</h1>
        <p class="listing-subtitle">Combinações prontas para comprar mais rápido.</p>
    </div>
</section>

<section class="section combos-section">
    <div class="container">
        <?php if (empty($combos)): ?>
        <p class="empty-state">Nenhum combo disponível agora.</p>
        <?php else: ?>
        <div class="combos-grid">
            <?php foreach ($combos as $combo): ?>
            <a href="/combo/<?= htmlspecialchars($combo['slug'], ENT_QUOTES, 'UTF-8') ?>" class="combo-card animate-in">
                <div class="combo-card__body">
                    <span class="badge badge--red"><?= (int)($combo['item_count'] ?? 0) ?> itens</span>
                    <h2 class="combo-card__name"><?= htmlspecialchars($combo['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <?php if (!empty($combo['description'])): ?>
                    <p class="combo-card__desc"><?= htmlspecialchars(mb_substr($combo['description'], 0, 120), ENT_QUOTES, 'UTF-8') ?>...</p>
                    <?php endif; ?>
                    <div class="combo-card__price">R$ <?= Sanitizer::money((float)($combo['price'] ?? 0)) ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
