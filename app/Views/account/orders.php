<?php use Maia\Helpers\Sanitizer; ?>

<div class="container account-container">
    <h1>Meus Pedidos</h1>

    <div class="account-layout">
        <nav class="account-sidebar" aria-label="Menu da conta">
            <a href="/minha-conta">Meus Dados</a>
            <a href="/minha-conta/pedidos" class="active">Meus Pedidos</a>
            <a href="/sair">Sair</a>
        </nav>

        <div class="account-content">
            <?php if (empty($orders)): ?>
            <p class="empty-state">Você ainda não fez nenhum pedido.</p>
            <a href="/produtos" class="btn btn-primary">Comprar agora</a>
            <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Data</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th><span class="sr-only">Ação</span></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?= (int)$order['id'] ?></td>
                    <td><?= htmlspecialchars(substr($order['created_at'] ?? '', 0, 10), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>R$ <?= Sanitizer::money((float)$order['total']) ?></td>
                    <td>
                        <span class="order-status status-<?= htmlspecialchars($order['status'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $order['status'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td><a href="/minha-conta/pedidos/<?= (int)$order['id'] ?>">Ver detalhes</a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>
