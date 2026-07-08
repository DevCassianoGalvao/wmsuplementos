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
            <?php if (!empty($pixKey)): ?>
            <p>Copie a chave PIX abaixo, cole no app do seu banco e efetue o pagamento.</p>
            <div class="pix-copy-box">
                <code><?= htmlspecialchars($pixKey, ENT_QUOTES, 'UTF-8') ?></code>
                <button type="button" class="btn btn-primary" data-copy-text="<?= htmlspecialchars($pixKey, ENT_QUOTES, 'UTF-8') ?>">Copiar chave</button>
            </div>
            <?php else: ?>
            <p>A chave PIX ainda não foi configurada. Fale conosco pelo WhatsApp para concluir o pagamento.</p>
            <?php endif; ?>
            <p class="payment-success-note">Envie o comprovante pelo nosso WhatsApp.</p>
            <a href="<?= htmlspecialchars($whatsappLink, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener">
                Enviar comprovante no WhatsApp
            </a>
        </div>
        <?php elseif ($method === 'cartao'): ?>
        <div class="payment-instructions cartao">
            <h2>Cartão de Crédito</h2>
            <p class="payment-success-note">Entraremos em contato pelo WhatsApp para finalizar sua compra.</p>
            <a href="<?= htmlspecialchars($whatsappLink, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener">
                Chamar no WhatsApp
            </a>
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
<script>
window.addEventListener('load', function() {
    if (typeof fbq !== 'function') return;
    var key = 'maia_purchase_<?= (int)$order['id'] ?>';
    if (sessionStorage.getItem(key)) return;
    sessionStorage.setItem(key, '1');
    fbq('track', 'Purchase', <?= json_encode([
        'content_ids' => array_map(
            static fn($item) => !empty($item['product_id']) ? (string)$item['product_id'] : 'combo-' . (string)($item['combo_id'] ?? ''),
            $order['items'] ?? []
        ),
        'content_type' => 'product',
        'value' => (float)$order['total'],
        'currency' => 'BRL',
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>);
});
</script>
