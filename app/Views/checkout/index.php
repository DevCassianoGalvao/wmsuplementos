<?php use Maia\Helpers\Sanitizer; use Maia\Helpers\CSRF; ?>
<?php
$interestRate = max(0.0, (float)($cardInterestMonthly ?? 3.00));
$installmentAmount = static function (float $total, int $months, float $monthlyRate): float {
    if ($months <= 1 || $monthlyRate <= 0) {
        return $total / max(1, $months);
    }
    $rate = $monthlyRate / 100;
    return $total * ($rate / (1 - pow(1 + $rate, -$months)));
};
?>

<div class="container checkout-page">
    <div class="checkout-heading">
        <span class="section__label">Checkout</span>
        <h1>Finalizar compra</h1>
        <p>Pagamento, entrega e comprovantes serao finalizados com atendimento pelo WhatsApp.</p>
    </div>

    <?php if (!empty($flash['error'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="checkout-layout">
        <form action="/finalizar-compra" method="post" id="checkout-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

            <section class="checkout-section">
                <h2>Dados pessoais</h2>

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
            </section>

            <section class="checkout-section">
                <h2>Pagamento</h2>

                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="pix"
                               <?= ($_POST['payment_method'] ?? 'pix') === 'pix' ? 'checked' : '' ?> required>
                        <span class="payment-label">
                            <strong>PIX</strong>
                            <small>A chave PIX aparece na proxima tela. Depois envie o comprovante pelo WhatsApp.</small>
                        </span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="cartao"
                               <?= ($_POST['payment_method'] ?? '') === 'cartao' ? 'checked' : '' ?>>
                        <span class="payment-label">
                            <strong>Cartao de credito</strong>
                            <small>Escolha uma estimativa de parcelas. A confirmacao final acontece pelo WhatsApp.</small>
                        </span>
                    </label>
                </div>

                <div class="form-group card-installments" data-card-installments>
                    <label for="installments">Parcelamento desejado</label>
                    <select id="installments" name="installments">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                        <?php $parcel = $installmentAmount((float)$total, $i, $interestRate); ?>
                        <option value="<?= $i ?>" <?= (int)($_POST['installments'] ?? 1) === $i ? 'selected' : '' ?>>
                            <?= $i ?>x de R$ <?= Sanitizer::money($parcel) ?><?= $i > 1 ? ' (estimado)' : '' ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                    <p class="form-hint">Estimativa com taxa mensal configurada de <?= Sanitizer::money($interestRate) ?>%. O valor final sera confirmado no atendimento.</p>
                </div>

                <div class="checkout-notice">
                    <strong>Entrega combinada no WhatsApp</strong>
                    <span>Depois do pedido, nossa equipe confirma endereco, prazo e valor de entrega diretamente com voce.</span>
                </div>
            </section>

            <button type="submit" class="btn btn-primary btn-lg btn-block">Confirmar pedido</button>
        </form>

        <aside class="checkout-summary">
            <h2>Seu pedido</h2>
            <ul class="checkout-items">
                <?php foreach ($items as $item): ?>
                <li class="checkout-item">
                    <span class="item-name"><?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="item-qty">x <?= (int)$item['quantity'] ?></span>
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
                <div class="summary-line summary-line--muted">
                    <span>Entrega:</span>
                    <span>Combinar no WhatsApp</span>
                </div>
                <div class="summary-line total">
                    <span>Total:</span>
                    <span>R$ <?= Sanitizer::money((float)$total) ?></span>
                </div>
            </div>
        </aside>
    </div>
</div>
