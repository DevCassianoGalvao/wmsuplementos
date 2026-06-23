<?php use Maia\Helpers\Sanitizer; ?>

<div class="page-header">
    <h1><?= htmlspecialchars($customer['name'], ENT_QUOTES, 'UTF-8') ?></h1>
    <a href="/admin/clientes" class="btn btn-outline">← Voltar</a>
</div>

<div class="customer-detail-grid">

    <section class="detail-card">
        <h2>Dados Pessoais</h2>
        <dl>
            <dt>E-mail:</dt><dd><?= htmlspecialchars($customer['email'], ENT_QUOTES, 'UTF-8') ?></dd>
            <dt>Telefone:</dt><dd><?= htmlspecialchars($customer['phone'] ?? '—', ENT_QUOTES, 'UTF-8') ?></dd>
            <dt>Cadastro:</dt><dd><?= htmlspecialchars(substr($customer['created_at'] ?? '', 0, 10), ENT_QUOTES, 'UTF-8') ?></dd>
            <dt>Opt-in e-mail:</dt><dd><?= $customer['email_opt_in'] ? 'Sim' : 'Não' ?></dd>
        </dl>
    </section>

    <section class="detail-card">
        <h2>Resumo de Compras</h2>
        <dl>
            <dt>Total de Pedidos:</dt><dd><?= (int)($customer['total_orders'] ?? 0) ?></dd>
            <dt>Total Gasto:</dt><dd>R$ <?= Sanitizer::money((float)($customer['total_spent'] ?? 0)) ?></dd>
            <dt>Última Compra:</dt><dd><?= htmlspecialchars($customer['last_order_at'] ? substr($customer['last_order_at'], 0, 10) : '—', ENT_QUOTES, 'UTF-8') ?></dd>
        </dl>
    </section>

    <section class="detail-card orders-card">
        <h2>Pedidos</h2>
        <?php if (empty($orders)): ?>
        <p class="empty-state">Sem pedidos ainda.</p>
        <?php else: ?>
        <table class="admin-table">
            <thead><tr><th>ID</th><th>Data</th><th>Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td>#<?= (int)$order['id'] ?></td>
                <td><?= htmlspecialchars(substr($order['created_at'] ?? '', 0, 10), ENT_QUOTES, 'UTF-8') ?></td>
                <td>R$ <?= Sanitizer::money((float)$order['total']) ?></td>
                <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $order['status'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                <td><a href="/admin/pedidos/<?= (int)$order['id'] ?>">Ver</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>

</div>
