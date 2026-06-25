<?php use Maia\Helpers\Sanitizer; use Maia\Helpers\CSRF; ?>

<div class="page-header">
    <h1>Pedido #<?= (int)$order['id'] ?></h1>
    <a href="/admin/pedidos" class="btn btn-outline">← Voltar</a>
</div>

<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($flash['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="order-detail-grid">

    <section class="detail-card">
        <h2>Cliente</h2>
        <dl>
            <dt>Nome:</dt><dd><?= htmlspecialchars($order['customer_name'], ENT_QUOTES, 'UTF-8') ?></dd>
            <dt>E-mail:</dt><dd><?= htmlspecialchars($order['customer_email'], ENT_QUOTES, 'UTF-8') ?></dd>
            <dt>Telefone:</dt><dd><?= htmlspecialchars($order['customer_phone'] ?? '—', ENT_QUOTES, 'UTF-8') ?></dd>
        </dl>
    </section>

    <section class="detail-card">
        <h2>Pagamento</h2>
        <dl>
            <dt>Método:</dt><dd><?= htmlspecialchars(ucfirst($order['payment_method'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
            <dt>Status MP:</dt><dd><?= htmlspecialchars($order['payment_status'] ?? '—', ENT_QUOTES, 'UTF-8') ?></dd>
            <dt>ID MP:</dt><dd><small><?= htmlspecialchars($order['payment_id'] ?? '—', ENT_QUOTES, 'UTF-8') ?></small></dd>
        </dl>
        <?php if (!empty($order['notes'])): ?>
        <div class="order-notes">
            <?= nl2br(htmlspecialchars($order['notes'], ENT_QUOTES, 'UTF-8')) ?>
        </div>
        <?php endif; ?>
        <div class="summary-lines">
            <div class="summary-line"><span>Subtotal:</span><span>R$ <?= Sanitizer::money((float)$order['subtotal']) ?></span></div>
            <?php if ((float)($order['discount'] ?? 0) > 0): ?>
            <div class="summary-line"><span>Desconto (<?= htmlspecialchars($order['coupon_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>):</span><span>- R$ <?= Sanitizer::money((float)$order['discount']) ?></span></div>
            <?php endif; ?>
            <div class="summary-line total"><span>Total:</span><span>R$ <?= Sanitizer::money((float)$order['total']) ?></span></div>
        </div>
    </section>

    <section class="detail-card items-card">
        <h2>Itens</h2>
        <table class="admin-table">
            <thead><tr><th>Produto</th><th>Qtd</th><th>Preço</th><th>Subtotal</th></tr></thead>
            <tbody>
            <?php foreach ($order['items'] ?? [] as $item): ?>
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

    <section class="detail-card">
        <h2>Atualizar Status</h2>
        <form action="/admin/pedidos/<?= (int)$order['id'] ?>/status" method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

            <div class="form-group">
                <label for="status">Novo Status</label>
                <select id="status" name="status">
                    <?php foreach ($statuses as $val => $label): ?>
                    <option value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>"
                            <?= $order['status'] === $val ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="tracking_code">Código de Rastreio</label>
                <input type="text" id="tracking_code" name="tracking_code"
                       value="<?= htmlspecialchars($order['tracking_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="note">Observação interna</label>
                <textarea id="note" name="note" rows="2"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Atualizar Status</button>
        </form>
    </section>

    <section class="detail-card">
        <h2>Histórico</h2>
        <ol class="order-history">
            <?php foreach ($order['history'] ?? [] as $h): ?>
            <li>
                <strong><?= htmlspecialchars($statuses[$h['status'] ?? ''] ?? $h['status'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                <span><?= htmlspecialchars(substr($h['created_at'] ?? '', 0, 16), ENT_QUOTES, 'UTF-8') ?></span>
                <?php if (!empty($h['admin_name'])): ?>
                <em>por <?= htmlspecialchars($h['admin_name'], ENT_QUOTES, 'UTF-8') ?></em>
                <?php endif; ?>
                <?php if (!empty($h['note'])): ?>
                <p><?= htmlspecialchars($h['note'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ol>
    </section>

</div>
