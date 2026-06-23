<?php use Maia\Helpers\Sanitizer; ?>

<div class="container account-container">
    <nav class="breadcrumb" aria-label="Navegação">
        <ol>
            <li><a href="/minha-conta">Minha Conta</a></li>
            <li><a href="/minha-conta/pedidos">Pedidos</a></li>
            <li aria-current="page">Pedido #<?= (int)$order['id'] ?></li>
        </ol>
    </nav>

    <h1>Pedido #<?= (int)$order['id'] ?></h1>

    <div class="order-detail-layout">
        <div class="order-info">
            <section class="detail-section">
                <h2>Informações do Pedido</h2>
                <dl class="detail-list">
                    <dt>Data:</dt>
                    <dd><?= htmlspecialchars(substr($order['created_at'] ?? '', 0, 10), ENT_QUOTES, 'UTF-8') ?></dd>
                    <dt>Status:</dt>
                    <dd>
                        <span class="order-status status-<?= htmlspecialchars($order['status'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $order['status'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </dd>
                    <dt>Pagamento:</dt>
                    <dd><?= htmlspecialchars(ucfirst($order['payment_method'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
                    <?php if (!empty($order['tracking_code'])): ?>
                    <dt>Código de rastreio:</dt>
                    <dd><strong><?= htmlspecialchars($order['tracking_code'], ENT_QUOTES, 'UTF-8') ?></strong></dd>
                    <?php endif; ?>
                </dl>
            </section>

            <?php if (!empty($order['items'])): ?>
            <section class="detail-section">
                <h2>Itens do Pedido</h2>
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Qtd</th>
                            <th>Preço Unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int)$item['quantity'] ?></td>
                        <td>R$ <?= Sanitizer::money((float)$item['price']) ?></td>
                        <td>R$ <?= Sanitizer::money((float)$item['subtotal']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
            <?php endif; ?>
        </div>

        <aside class="order-summary-box">
            <h2>Resumo Financeiro</h2>
            <div class="summary-lines">
                <div class="summary-line">
                    <span>Subtotal:</span>
                    <span>R$ <?= Sanitizer::money((float)$order['subtotal']) ?></span>
                </div>
                <?php if ((float)($order['discount'] ?? 0) > 0): ?>
                <div class="summary-line">
                    <span>Desconto (<?= htmlspecialchars($order['coupon_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>):</span>
                    <span>- R$ <?= Sanitizer::money((float)$order['discount']) ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-line total">
                    <span>Total:</span>
                    <span>R$ <?= Sanitizer::money((float)$order['total']) ?></span>
                </div>
            </div>

            <?php if (!empty($order['history'])): ?>
            <h3>Histórico do Pedido</h3>
            <ol class="order-history">
                <?php foreach ($order['history'] as $h): ?>
                <li>
                    <span class="history-status"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $h['status'] ?? '')), ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="history-date"><?= htmlspecialchars(substr($h['created_at'] ?? '', 0, 16), ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if (!empty($h['note'])): ?>
                    <p class="history-note"><?= htmlspecialchars($h['note'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ol>
            <?php endif; ?>
        </aside>
    </div>

    <a href="/minha-conta/pedidos" class="btn btn-link">← Voltar para pedidos</a>
</div>
