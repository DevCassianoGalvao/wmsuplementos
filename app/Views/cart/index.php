<?php use Maia\Helpers\Sanitizer; use Maia\Helpers\CSRF; ?>

<div class="container cart-page">
    <h1>Meu Carrinho</h1>

    <?php if (!empty($flash['error'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (!empty($flash['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
    <div class="empty-cart">
        <p>Seu carrinho está vazio.</p>
        <a href="/produtos" class="btn btn-primary">Continuar Comprando</a>
    </div>
    <?php else: ?>

    <div class="cart-layout">
        <div class="cart-items">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Preço</th>
                        <th>Quantidade</th>
                        <th>Subtotal</th>
                        <th><span class="sr-only">Remover</span></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                <tr class="cart-item" data-product-id="<?= (int)$item['product_id'] ?>">
                    <td class="item-product">
                        <?php if (!empty($item['image'])): ?>
                        <img src="/uploads/products/<?= htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8') ?>"
                             alt="" width="60" height="60">
                        <?php endif; ?>
                        <a href="/produto/<?= htmlspecialchars($item['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </td>
                    <td class="item-price">R$ <?= Sanitizer::money((float)$item['price']) ?></td>
                    <td class="item-qty">
                        <div class="qty-control">
                            <button type="button" class="qty-btn qty-minus" data-id="<?= (int)$item['product_id'] ?>">−</button>
                            <input type="number" class="qty-input" value="<?= (int)$item['quantity'] ?>"
                                   min="1" data-id="<?= (int)$item['product_id'] ?>">
                            <button type="button" class="qty-btn qty-plus" data-id="<?= (int)$item['product_id'] ?>">+</button>
                        </div>
                    </td>
                    <td class="item-subtotal" id="subtotal-<?= (int)$item['product_id'] ?>">
                        R$ <?= Sanitizer::money((float)$item['price'] * (int)$item['quantity']) ?>
                    </td>
                    <td class="item-remove">
                        <button type="button" class="remove-btn" data-id="<?= (int)$item['product_id'] ?>" aria-label="Remover">✕</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <aside class="cart-summary">
            <h2>Resumo</h2>

            <form action="/carrinho/cupom" method="post" class="coupon-form">
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                <label for="coupon_code">Cupom de desconto</label>
                <div class="coupon-input-group">
                    <input type="text" id="coupon_code" name="coupon_code"
                           value="<?= htmlspecialchars($coupon['code'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Digite o cupom"
                           <?= $coupon ? 'readonly' : '' ?>>
                    <?php if (!$coupon): ?>
                    <button type="submit" class="btn btn-outline">Aplicar</button>
                    <?php endif; ?>
                </div>
            </form>

            <div class="summary-lines">
                <div class="summary-line">
                    <span>Subtotal:</span>
                    <span id="cart-subtotal">R$ <?= Sanitizer::money((float)$subtotal) ?></span>
                </div>
                <?php if ($discount > 0): ?>
                <div class="summary-line discount">
                    <span>Desconto:</span>
                    <span id="cart-discount">- R$ <?= Sanitizer::money((float)$discount) ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-line total">
                    <span>Total:</span>
                    <span id="cart-total">R$ <?= Sanitizer::money((float)$total) ?></span>
                </div>
            </div>

            <a href="/finalizar-compra" class="btn btn-primary btn-lg btn-block">Finalizar Compra</a>
            <a href="/produtos" class="btn btn-link btn-block">Continuar Comprando</a>
        </aside>
    </div>

    <input type="hidden" id="csrf-token" value="<?= CSRF::token() ?>">

    <?php endif; ?>
</div>
