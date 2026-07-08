<?php use Maia\Helpers\Sanitizer; use Maia\Helpers\CSRF; ?>

<div class="container">
    <?php if (!empty($flash)): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type'] ?? 'info', ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($flash['message'] ?? '', ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
    <div class="empty-cart">
        <h1 class="cart-title">Meu carrinho</h1>
        <p>Seu carrinho está vazio.</p>
        <a href="/produtos" class="btn btn-primary">Continuar comprando</a>
    </div>
    <?php else: ?>

    <div class="cart-page">
        <div class="cart-items">
            <div class="cart-header-row">
                <div>
                    <span class="section__label">Carrinho</span>
                    <h1 class="cart-title">Meu carrinho</h1>
                </div>
                <span class="cart-count-label"><?= count($items) ?> <?= count($items) === 1 ? 'item' : 'itens' ?></span>
            </div>

            <?php foreach ($items as $item): ?>
            <?php $cartKey = (string)($item['cart_key'] ?? $item['product_id'] ?? ''); ?>
            <?php $isCombo = ($item['type'] ?? '') === 'combo'; ?>
            <?php $unitPrice = (float)$item['price']; ?>
            <?php $qty = (int)$item['quantity']; ?>
            <div class="cart-item" data-cart-key="<?= htmlspecialchars($cartKey, ENT_QUOTES, 'UTF-8') ?>">
                <div class="cart-item__image">
                    <?php if (!empty($item['image'])): ?>
                    <img src="<?= htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>
                </div>
                <div class="cart-item__info">
                    <?php if (!empty($item['brand_name'])): ?>
                    <span class="cart-item__brand"><?= htmlspecialchars($item['brand_name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                    <h3 class="cart-item__name">
                        <a href="<?= $isCombo ? '/combo/' : '/produto/' ?><?= htmlspecialchars($item['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </h3>
                    <span class="cart-item__price">R$ <?= Sanitizer::money($unitPrice) ?></span>
                </div>
                <div class="cart-item__qty">
                    <button type="button" class="qty-btn qty-minus" data-id="<?= htmlspecialchars($cartKey, ENT_QUOTES, 'UTF-8') ?>" aria-label="Diminuir">&minus;</button>
                    <input type="number" class="qty-input" value="<?= $qty ?>"
                           min="1" max="99"
                           data-price="<?= htmlspecialchars(Sanitizer::money($unitPrice), ENT_QUOTES, 'UTF-8') ?>"
                           data-id="<?= htmlspecialchars($cartKey, ENT_QUOTES, 'UTF-8') ?>" readonly>
                    <button type="button" class="qty-btn qty-plus" data-id="<?= htmlspecialchars($cartKey, ENT_QUOTES, 'UTF-8') ?>" aria-label="Aumentar">+</button>
                </div>
                <div class="cart-item__subtotal item-subtotal" id="subtotal-<?= htmlspecialchars($cartKey, ENT_QUOTES, 'UTF-8') ?>">
                    R$ <?= Sanitizer::money($unitPrice * $qty) ?>
                </div>
                <button type="button" class="cart-item__remove remove-btn" data-id="<?= htmlspecialchars($cartKey, ENT_QUOTES, 'UTF-8') ?>" aria-label="Remover item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
            </div>
            <?php endforeach; ?>
        </div>

        <aside class="cart-summary">
            <h2>Resumo do pedido</h2>

            <form action="/carrinho/cupom" method="post" class="coupon-form cart-coupon">
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                <label for="coupon_code">Cupom de desconto</label>
                <div class="coupon-input-group cart-coupon-row">
                    <input type="text" id="coupon_code" name="coupon_code"
                           value="<?= htmlspecialchars($coupon['code'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Digite o cupom"
                           <?= $coupon ? 'readonly' : '' ?>>
                    <?php if (!$coupon): ?>
                    <button type="submit" class="btn btn--ghost">Aplicar</button>
                    <?php endif; ?>
                </div>
            </form>

            <div class="summary-lines">
                <div class="summary-line cart-summary-row">
                    <span>Subtotal</span>
                    <span id="cart-subtotal">R$ <?= Sanitizer::money((float)$subtotal) ?></span>
                </div>
                <?php if ($discount > 0): ?>
                <div class="summary-line discount cart-summary-row">
                    <span>Desconto</span>
                    <span id="cart-discount">- R$ <?= Sanitizer::money((float)$discount) ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-line cart-summary-row">
                    <span>Entrega</span>
                    <span>Combinar no WhatsApp</span>
                </div>
                <div class="summary-line total cart-summary-total">
                    <span class="label">Total</span>
                    <span class="value" id="cart-total">R$ <?= Sanitizer::money((float)$total) ?></span>
                </div>
            </div>

            <p class="cart-delivery-note">Prazo, endereço e valor de entrega serão confirmados pelo WhatsApp após o pedido.</p>

            <div class="cart-actions">
                <a href="/finalizar-compra" class="btn btn--primary btn--block">Finalizar compra</a>
                <a href="/produtos" class="btn btn--ghost btn--block">Continuar comprando</a>
            </div>
        </aside>
    </div>

    <input type="hidden" id="csrf-token" value="<?= CSRF::token() ?>">

    <?php if (!empty($suggestions)): ?>
    <section class="cart-upsell">
        <div class="section__header">
            <span class="section__label">Complete o pedido</span>
            <h2 class="section__title">Produtos que combinam com seu carrinho</h2>
        </div>
        <div class="products-grid">
            <?php foreach ($suggestions as $suggestedProduct): ?>
            <?php $cardProduct = $product ?? null; $product = $suggestedProduct; ?>
            <?php include __DIR__ . '/../partials/product_card.php'; ?>
            <?php if ($cardProduct !== null) { $product = $cardProduct; } else { unset($product); } unset($cardProduct); ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php endif; ?>
</div>
