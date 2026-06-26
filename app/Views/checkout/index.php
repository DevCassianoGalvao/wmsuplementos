<?php use Maia\Helpers\Sanitizer; use Maia\Helpers\CSRF; ?>

<div class="container checkout-page">
    <h1>Finalizar Compra</h1>

    <?php if (!empty($flash['error'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="checkout-layout">
        <form action="/finalizar-compra" method="post" id="checkout-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

            <section class="checkout-section">
                <h2>Dados Pessoais</h2>

                <div class="form-group">
                    <label for="name">Nome completo <span aria-hidden="true">*</span></label>
                    <input type="text" id="name" name="name" required autocomplete="name"
                           value="<?= htmlspecialchars($user['name'] ?? $_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label for="email">E-mail <span aria-hidden="true">*</span></label>
                    <input type="email" id="email" name="email" required autocomplete="email"
                           value="<?= htmlspecialchars($user['email'] ?? $_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Telefone (WhatsApp) <span aria-hidden="true">*</span></label>
                    <input type="tel" id="phone" name="phone" required autocomplete="tel"
                           value="<?= htmlspecialchars($user['phone'] ?? $_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="boleto-address" data-boleto-address hidden>
                    <p class="form-hint">Endereco exigido apenas para emissao do boleto.</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="zip_code">CEP</label>
                            <input type="text" id="zip_code" name="zip_code" inputmode="numeric" autocomplete="postal-code"
                                   value="<?= htmlspecialchars($_POST['zip_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="form-group">
                            <label for="address_number">Numero</label>
                            <input type="text" id="address_number" name="address_number" autocomplete="address-line2"
                                   value="<?= htmlspecialchars($_POST['address_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address">Endereco</label>
                        <input type="text" id="address" name="address" autocomplete="address-line1"
                               value="<?= htmlspecialchars($_POST['address'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="neighborhood">Bairro</label>
                            <input type="text" id="neighborhood" name="neighborhood"
                                   value="<?= htmlspecialchars($_POST['neighborhood'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="form-group">
                            <label for="city">Cidade</label>
                            <input type="text" id="city" name="city" autocomplete="address-level2"
                                   value="<?= htmlspecialchars($_POST['city'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="form-group">
                            <label for="state">UF</label>
                            <input type="text" id="state" name="state" maxlength="2" autocomplete="address-level1"
                                   value="<?= htmlspecialchars($_POST['state'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                </div>
            </section>

            <section class="checkout-section">
                <h2>Forma de Pagamento</h2>

                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="pix"
                               <?= ($_POST['payment_method'] ?? 'pix') === 'pix' ? 'checked' : '' ?> required>
                        <span class="payment-label">PIX <small>(aprovação imediata)</small></span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="cartao"
                               <?= ($_POST['payment_method'] ?? '') === 'cartao' ? 'checked' : '' ?>>
                        <span class="payment-label">Cartão de Crédito</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="boleto"
                               <?= ($_POST['payment_method'] ?? '') === 'boleto' ? 'checked' : '' ?>>
                        <span class="payment-label">Boleto Bancário <small>(prazo de 3 dias úteis)</small></span>
                    </label>
                </div>
            </section>

            <button type="submit" class="btn btn-primary btn-lg btn-block">Confirmar Pedido</button>
        </form>

        <aside class="checkout-summary">
            <h2>Seu Pedido</h2>
            <ul class="checkout-items">
                <?php foreach ($items as $item): ?>
                <li class="checkout-item">
                    <span class="item-name"><?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="item-qty">× <?= (int)$item['quantity'] ?></span>
                    <span class="item-price">R$ <?= Sanitizer::money((float)$item['price'] * (int)$item['quantity']) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="summary-lines">
                <div class="summary-line">
                    <span>Subtotal:</span>
                    <span>R$ <?= Sanitizer::money((float)$subtotal) ?></span>
                </div>
                <?php if ($discount > 0): ?>
                <div class="summary-line discount">
                    <span>Desconto (<?= htmlspecialchars($coupon['code'] ?? '', ENT_QUOTES, 'UTF-8') ?>):</span>
                    <span>- R$ <?= Sanitizer::money((float)$discount) ?></span>
                </div>
                <?php endif; ?>
                <?php if (($shipping ?? 0) > 0 || ($freeShippingRemaining ?? 0) <= 0): ?>
                <div class="summary-line">
                    <span>Frete:</span>
                    <span><?= ((float)($shipping ?? 0) > 0) ? 'R$ ' . Sanitizer::money((float)$shipping) : 'Grátis' ?></span>
                </div>
                <?php endif; ?>
                <?php if (($freeShippingRemaining ?? 0) > 0): ?>
                <div class="summary-line">
                    <span>Frete grátis:</span>
                    <span>Faltam R$ <?= Sanitizer::money((float)$freeShippingRemaining) ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-line total">
                    <span>Total:</span>
                    <span>R$ <?= Sanitizer::money((float)$total) ?></span>
                </div>
            </div>
        </aside>
    </div>
</div>
