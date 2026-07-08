<?php use Maia\Helpers\Sanitizer; ?>

<div class="container confirmation-page">
    <div class="confirmation-card">
        <div class="confirmation-icon" aria-hidden="true">✓</div>
        <span class="section__label">Pedido recebido</span>
        <h1>Agora falta finalizar o pagamento</h1>
        <p class="confirmation-msg">Pedido <strong>#<?= (int)$order['id'] ?></strong> em nome de <strong><?= htmlspecialchars($order['customer_name'], ENT_QUOTES, 'UTF-8') ?></strong>.</p>

        <?php $method = $order['payment_method'] ?? ''; ?>

        <?php if ($method === 'pix'): ?>
        <div class="payment-instructions pix">
            <h2>Pagamento via PIX</h2>
            <?php if (!empty($pixKey)): ?>
            <p>Copie a chave abaixo, pague no app do seu banco e envie o comprovante pelo WhatsApp.</p>
            <div class="pix-copy-box">
                <code><?= htmlspecialchars($pixKey, ENT_QUOTES, 'UTF-8') ?></code>
                <button type="button" class="btn btn-primary" data-copy-text="<?= htmlspecialchars($pixKey, ENT_QUOTES, 'UTF-8') ?>">Copiar chave PIX</button>
            </div>
            <?php else: ?>
            <p>A chave PIX ainda nao foi configurada. Fale conosco pelo WhatsApp para concluir o pagamento.</p>
            <?php endif; ?>
            <div class="payment-success-note">
                <strong>Importante:</strong> o pedido sera separado apos o envio do comprovante.
            </div>
            <a href="<?= htmlspecialchars($whatsappLink, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener">
                Enviar comprovante no WhatsApp
            </a>
        </div>
        <?php elseif ($method === 'cartao'): ?>
        <div class="payment-instructions cartao">
            <h2>Pagamento com cartao</h2>
            <div class="payment-success-note">
                <strong>Proximo passo:</strong> nossa equipe entra em contato pelo WhatsApp para confirmar parcelas, taxas e link de pagamento.
            </div>
            <a href="<?= htmlspecialchars($whatsappLink, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener">
                Chamar no WhatsApp
            </a>
        </div>
        <?php endif; ?>

        <div class="order-summary">
            <h2>Resumo do pedido</h2>
            <p>Total dos produtos: <strong>R$ <?= Sanitizer::money((float)$order['total']) ?></strong></p>
            <p>Entrega: <strong>combinada pelo WhatsApp</strong></p>
            <p>E-mail informado: <strong><?= htmlspecialchars($order['customer_email'], ENT_QUOTES, 'UTF-8') ?></strong></p>
        </div>

        <div class="confirmation-actions">
            <?php if (\Maia\Helpers\Auth::isUserLogged()): ?>
            <a href="/minha-conta/pedidos" class="btn btn-outline">Meus pedidos</a>
            <?php endif; ?>
            <a href="/" class="btn btn-link">Voltar a loja</a>
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
