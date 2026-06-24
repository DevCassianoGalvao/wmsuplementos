<?php use Maia\Helpers\Sanitizer; ?>

<h1>Dashboard</h1>

<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6 5 3H2"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
        </div>
        <div class="kpi-body">
            <span class="kpi-label">Pedidos Hoje</span>
            <span class="kpi-value"><?= (int)$counts['orders_today'] ?></span>
        </div>
    </div>
    <div class="kpi-card kpi-warning">
        <div class="kpi-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        </div>
        <div class="kpi-body">
            <span class="kpi-label">Aguardando Pagamento</span>
            <span class="kpi-value"><?= (int)$counts['pending_orders'] ?></span>
            <a href="/admin/pedidos?status=aguardando_pagamento">Ver</a>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="kpi-body">
            <span class="kpi-label">Total de Clientes</span>
            <span class="kpi-value"><?= number_format((int)$counts['total_users']) ?></span>
            <a href="/admin/clientes">Ver</a>
        </div>
    </div>
    <div class="kpi-card <?= (int)$counts['pending_reviews'] > 0 ? 'kpi-alert' : '' ?>">
        <div class="kpi-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6-5.4-2.9-5.4 2.9 1-6-4.4-4.3 6.1-.9z"/></svg>
        </div>
        <div class="kpi-body">
            <span class="kpi-label">Avaliações Pendentes</span>
            <span class="kpi-value"><?= (int)$counts['pending_reviews'] ?></span>
            <?php if ($counts['pending_reviews'] > 0): ?>
            <a href="/admin/avaliacoes">Moderar</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="kpi-card <?= (int)$counts['low_stock'] > 0 ? 'kpi-alert' : '' ?>">
        <div class="kpi-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 16-9 5-9-5V8l9-5 9 5z"/><path d="M3.3 7.7 12 13l8.7-5.3"/><path d="M12 13v8"/><path d="M12 7v4"/></svg>
        </div>
        <div class="kpi-body">
            <span class="kpi-label">Estoque Baixo</span>
            <span class="kpi-value"><?= (int)$counts['low_stock'] ?></span>
            <?php if ($counts['low_stock'] > 0): ?>
            <a href="/admin/estoque?filtro=baixo">Ver</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($funnel)): ?>
<section class="dashboard-card">
    <h2>Funil de Conversao (30 dias)</h2>
    <div class="funnel-grid">
        <?php foreach ($funnel as $step): ?>
        <div class="funnel-step">
            <span class="funnel-label"><?= htmlspecialchars($step['label'], ENT_QUOTES, 'UTF-8') ?></span>
            <strong><?= number_format((int)$step['total']) ?></strong>
            <small><?= number_format((float)$step['conversion'], 1) ?>% das visitas</small>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

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

<section class="dashboard-card">
    <h2>Vendas Diárias (últimos 30 dias)</h2>
    <canvas id="salesChart" height="80" aria-label="Gráfico de vendas diárias"></canvas>
</section>

<script>
(function() {
    const daily = <?= json_encode($daily, JSON_UNESCAPED_UNICODE) ?>;
    const ctx = document.getElementById('salesChart');
    if (!ctx) return;

    const canvas = ctx;
    const parentWidth = canvas.parentElement ? canvas.parentElement.clientWidth : 800;
    const dpr = window.devicePixelRatio || 1;
    const width = Math.max(parentWidth, 320);
    const height = 260;
    canvas.width = width * dpr;
    canvas.height = height * dpr;
    canvas.style.width = width + 'px';
    canvas.style.height = height + 'px';

    const chart = canvas.getContext('2d');
    chart.scale(dpr, dpr);
    chart.clearRect(0, 0, width, height);

    const padding = { top: 24, right: 18, bottom: 34, left: 48 };
    const values = daily.map(d => Number.parseFloat(d.revenue) || 0);
    const max = Math.max(...values, 1);
    const plotW = width - padding.left - padding.right;
    const plotH = height - padding.top - padding.bottom;

    chart.strokeStyle = 'rgba(255,255,255,0.08)';
    chart.lineWidth = 1;
    chart.font = '12px Space Grotesk, sans-serif';
    chart.fillStyle = 'rgba(255,255,255,0.45)';

    for (let i = 0; i <= 4; i++) {
        const y = padding.top + (plotH / 4) * i;
        chart.beginPath();
        chart.moveTo(padding.left, y);
        chart.lineTo(width - padding.right, y);
        chart.stroke();
        const label = Math.round(max - (max / 4) * i);
        chart.fillText('R$ ' + label, 8, y + 4);
    }

    const points = values.map((value, index) => {
        const x = padding.left + (values.length <= 1 ? 0 : (plotW / (values.length - 1)) * index);
        const y = padding.top + plotH - (value / max) * plotH;
        return { x, y };
    });

    if (points.length > 0) {
        const gradient = chart.createLinearGradient(0, padding.top, 0, height - padding.bottom);
        gradient.addColorStop(0, 'rgba(230,51,41,0.28)');
        gradient.addColorStop(1, 'rgba(230,51,41,0)');

        chart.beginPath();
        chart.moveTo(points[0].x, height - padding.bottom);
        points.forEach(point => chart.lineTo(point.x, point.y));
        chart.lineTo(points[points.length - 1].x, height - padding.bottom);
        chart.closePath();
        chart.fillStyle = gradient;
        chart.fill();

        chart.beginPath();
        points.forEach((point, index) => {
            if (index === 0) chart.moveTo(point.x, point.y);
            else chart.lineTo(point.x, point.y);
        });
        chart.strokeStyle = '#E63329';
        chart.lineWidth = 3;
        chart.lineJoin = 'round';
        chart.lineCap = 'round';
        chart.stroke();
    }
})();
</script>
