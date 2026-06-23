<?php use Maia\Helpers\Sanitizer; ?>

<h1>Dashboard</h1>

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card">
        <h3>Pedidos Hoje</h3>
        <span class="kpi-value"><?= (int)$counts['orders_today'] ?></span>
    </div>
    <div class="kpi-card kpi-warning">
        <h3>Aguardando Pagamento</h3>
        <span class="kpi-value"><?= (int)$counts['pending_orders'] ?></span>
        <a href="/admin/pedidos?status=aguardando_pagamento">Ver</a>
    </div>
    <div class="kpi-card">
        <h3>Total de Clientes</h3>
        <span class="kpi-value"><?= number_format((int)$counts['total_users']) ?></span>
        <a href="/admin/clientes">Ver</a>
    </div>
    <div class="kpi-card <?= (int)$counts['pending_reviews'] > 0 ? 'kpi-alert' : '' ?>">
        <h3>Avaliações Pendentes</h3>
        <span class="kpi-value"><?= (int)$counts['pending_reviews'] ?></span>
        <?php if ($counts['pending_reviews'] > 0): ?>
        <a href="/admin/avaliacoes">Moderar</a>
        <?php endif; ?>
    </div>
    <div class="kpi-card <?= (int)$counts['low_stock'] > 0 ? 'kpi-alert' : '' ?>">
        <h3>Estoque Baixo</h3>
        <span class="kpi-value"><?= (int)$counts['low_stock'] ?></span>
        <?php if ($counts['low_stock'] > 0): ?>
        <a href="/admin/estoque?filtro=baixo">Ver</a>
        <?php endif; ?>
    </div>
</div>

<!-- Resumo do Período -->
<div class="dashboard-row">
    <section class="dashboard-card">
        <h2>Faturamento (30 dias)</h2>
        <?php $cur = $summary['current'] ?? []; $prev = $summary['previous'] ?? []; ?>
        <div class="summary-stat">
            <span class="stat-label">Receita</span>
            <span class="stat-value">R$ <?= Sanitizer::money((float)($cur['revenue'] ?? 0)) ?></span>
            <?php
            $prevRev = (float)($prev['revenue'] ?? 0);
            $curRev  = (float)($cur['revenue']  ?? 0);
            if ($prevRev > 0):
                $diff = (($curRev - $prevRev) / $prevRev) * 100;
            ?>
            <span class="stat-change <?= $diff >= 0 ? 'positive' : 'negative' ?>">
                <?= $diff >= 0 ? '+' : '' ?><?= number_format($diff, 1) ?>% vs período anterior
            </span>
            <?php endif; ?>
        </div>
        <div class="summary-stat">
            <span class="stat-label">Pedidos</span>
            <span class="stat-value"><?= (int)($cur['orders'] ?? 0) ?></span>
        </div>
        <div class="summary-stat">
            <span class="stat-label">Ticket Médio</span>
            <span class="stat-value">R$ <?= Sanitizer::money((float)($cur['avg_ticket'] ?? 0)) ?></span>
        </div>
    </section>

    <!-- Top Produtos -->
    <section class="dashboard-card">
        <h2>Top Produtos do Mês</h2>
        <?php if (empty($top)): ?>
        <p class="empty-state">Sem dados ainda.</p>
        <?php else: ?>
        <table class="admin-table">
            <thead><tr><th>Produto</th><th>Un.</th><th>Receita</th></tr></thead>
            <tbody>
            <?php foreach ($top as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int)$row['units_sold'] ?></td>
                <td>R$ <?= Sanitizer::money((float)$row['revenue']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>
</div>

<!-- Gráfico de Vendas (Chart.js) -->
<section class="dashboard-card">
    <h2>Vendas Diárias (últimos 30 dias)</h2>
    <canvas id="salesChart" height="80" aria-label="Gráfico de vendas diárias"></canvas>
</section>

<script>
(function() {
    const daily = <?= json_encode($daily, JSON_UNESCAPED_UNICODE) ?>;
    const labels  = daily.map(d => d.date);
    const revenue = daily.map(d => parseFloat(d.revenue));

    const ctx = document.getElementById('salesChart');
    if (!ctx || !window.Chart) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Receita (R$)',
                data: revenue,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.1)',
                tension: 0.3,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
})();
</script>
