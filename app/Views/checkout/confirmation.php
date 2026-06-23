<?php use Maia\Helpers\Sanitizer; ?>

<div class="container confirmation-page">
    <div class="confirmation-card">
        <div class="confirmation-icon" aria-hidden="true">✓</div>
        <h1>Pedido Recebido!</h1>
        <p class="confirmation-msg">Obrigado pela sua compra, <strong><?= htmlspecialchars($order['customer_name'], ENT_QUOTES, 'UTF-8') ?></strong>!</p>
        <p>Pedido <strong>#<?= (int)$order['id'] ?></strong> · <?= htmlspecialchars($order['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>

        <?php $method = $order['payment_method'] ?? ''; ?>

        <?php if ($method === 'pix'): ?>
        <div class="payment-instructions pix">
            <h2>Pague via PIX</h2>
            <?php if (!empty($mpUrl)): ?>
            <p>Clique no botão para acessar o QR Code do PIX no Mercado Pago:</p>
            <a href="<?= htmlspecialchars($mpUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener">
                Pagar com PIX
            </a>
            <?php else: ?>
            <p>Você receberá o QR Code do PIX em instantes por e-mail.</p>
            <?php endif; ?>
            <p class="payment-note">⚠️ O pedido será processado após confirmação do pagamento.</p>
        </div>
        <?php elseif ($method === 'cartao'): ?>
        <div class="payment-instructions cartao">
            <h2>Pagamento com Cartão</h2>
            <?php if (!empty($mpUrl)): ?>
            <a href="<?= htmlspecialchars($mpUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener">
                Inserir dados do Cartão
            </a>
            <?php else: ?>
            <p>Você receberá as instruções de pagamento por e-mail.</p>
            <?php endif; ?>
        </div>
        <?php elseif ($method === 'boleto'): ?>
        <div class="payment-instructions boleto">
            <h2>Boleto Bancário</h2>
            <?php if (!empty($mpUrl)): ?>
            <a href="<?= htmlspecialchars($mpUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener">
                Ver Boleto
            </a>
            <?php else: ?>
            <p>O boleto foi enviado para <strong><?= htmlspecialchars($order['customer_email'], ENT_QUOTES, 'UTF-8') ?></strong>.</p>
            <?php endif; ?>
            <p class="payment-note">O prazo de compensação é de até 3 dias úteis.</p>
        </div>
        <?php endif; ?>

        <div class="order-summary">
            <h2>Resumo do Pedido</h2>
            <p>Total: <strong>R$ <?= Sanitizer::money((float)$order['total']) ?></strong></p>
            <p>E-mail de confirmação enviado para: <strong><?= htmlspecialchars($order['customer_email'], ENT_QUOTES, 'UTF-8') ?></strong></p>
        </div>

        <div class="confirmation-actions">
            <?php if (\Maia\Helpers\Auth::isUserLogged()): ?>
            <a href="/minha-conta/pedidos" class="btn btn-outline">Meus Pedidos</a>
            <?php endif; ?>
            <a href="/" class="btn btn-link">Voltar à loja</a>
        </div>
    </div>
</div>
