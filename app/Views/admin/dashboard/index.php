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
    <div class="chart-header">
        <h2 id="chartTitle">Vendas Diárias (30 dias)</h2>
        <div class="chart-periods" role="group" aria-label="Período do gráfico">
            <button class="chart-period-btn" data-days="7">7d</button>
            <button class="chart-period-btn" data-days="15">15d</button>
            <button class="chart-period-btn chart-period-btn--active" data-days="30">30d</button>
            <button class="chart-period-btn" data-days="60">60d</button>
            <button class="chart-period-btn" data-days="90">90d</button>
        </div>
    </div>
    <div class="chart-wrap">
        <canvas id="salesChart" aria-label="Gráfico de vendas diárias"></canvas>
        <div id="chartTooltip" class="chart-tooltip" hidden></div>
    </div>
</section>

<script>
(function() {
    const allDaily = <?= json_encode($daily, JSON_UNESCAPED_UNICODE) ?>;
    let activeDays = 30;
    let chartPts = [];

    function buildDataset(days) {
        const out = [];
        const now = new Date();
        now.setHours(0, 0, 0, 0);
        for (let i = days - 1; i >= 0; i--) {
            const d = new Date(now);
            d.setDate(d.getDate() - i);
            const ds = d.toISOString().slice(0, 10);
            const found = allDaily.find(r => r.date === ds);
            out.push({ date: ds, revenue: found ? parseFloat(found.revenue) : 0, orders: found ? parseInt(found.orders) : 0 });
        }
        return out;
    }

    function fmtDate(ds) { return ds.slice(8, 10) + '/' + ds.slice(5, 7); }
    function fmtMoney(n) { return 'R$ ' + n.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

    function drawChart(data) {
        const canvas = document.getElementById('salesChart');
        if (!canvas) return [];
        const wrap = canvas.parentElement;
        const dpr = window.devicePixelRatio || 1;
        const width = Math.max(wrap.clientWidth || 300, 200);
        const height = 240;

        canvas.width = Math.round(width * dpr);
        canvas.height = Math.round(height * dpr);
        canvas.style.width = width + 'px';
        canvas.style.height = height + 'px';

        const ctx = canvas.getContext('2d');
        ctx.scale(dpr, dpr);
        ctx.clearRect(0, 0, width, height);

        const n = data.length;
        if (n === 0) return [];

        const values = data.map(d => d.revenue);
        const maxVal = Math.max(...values, 1);
        const maxLabels = Math.max(2, Math.floor(width / 48));
        const labelStep = Math.max(1, Math.ceil(n / maxLabels));

        const pad = { top: 20, right: 12, bottom: 38, left: 58 };
        const plotW = width - pad.left - pad.right;
        const plotH = height - pad.top - pad.bottom;

        ctx.font = '11px Space Grotesk, sans-serif';
        ctx.textAlign = 'right';
        ctx.fillStyle = 'rgba(255,255,255,0.35)';
        ctx.strokeStyle = 'rgba(255,255,255,0.06)';
        ctx.lineWidth = 1;
        for (let i = 0; i <= 4; i++) {
            const y = pad.top + (plotH / 4) * i;
            ctx.beginPath();
            ctx.moveTo(pad.left, y);
            ctx.lineTo(width - pad.right, y);
            ctx.stroke();
            const val = Math.round(maxVal * (1 - i / 4));
            ctx.fillText('R$' + val, pad.left - 4, y + 4);
        }

        const pts = data.map((d, i) => ({
            x: pad.left + (n <= 1 ? plotW / 2 : (plotW / (n - 1)) * i),
            y: pad.top + plotH - (maxVal > 0 ? (d.revenue / maxVal) * plotH : 0),
            revenue: d.revenue,
            orders: d.orders,
            date: d.date
        }));

        const grad = ctx.createLinearGradient(0, pad.top, 0, height - pad.bottom);
        grad.addColorStop(0, 'rgba(230,51,41,0.22)');
        grad.addColorStop(1, 'rgba(230,51,41,0)');
        ctx.beginPath();
        ctx.moveTo(pts[0].x, height - pad.bottom);
        pts.forEach(pt => ctx.lineTo(pt.x, pt.y));
        ctx.lineTo(pts[pts.length - 1].x, height - pad.bottom);
        ctx.closePath();
        ctx.fillStyle = grad;
        ctx.fill();

        ctx.beginPath();
        pts.forEach((pt, i) => i === 0 ? ctx.moveTo(pt.x, pt.y) : ctx.lineTo(pt.x, pt.y));
        ctx.strokeStyle = '#E63329';
        ctx.lineWidth = 2.5;
        ctx.lineJoin = 'round';
        ctx.lineCap = 'round';
        ctx.stroke();

        ctx.font = '10px Space Grotesk, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillStyle = 'rgba(255,255,255,0.3)';
        pts.forEach((pt, i) => {
            if (i % labelStep === 0 || i === n - 1) {
                ctx.fillText(fmtDate(pt.date), pt.x, height - pad.bottom + 14);
            }
        });

        return pts;
    }

    const canvas = document.getElementById('salesChart');
    const tooltip = document.getElementById('chartTooltip');

    if (canvas && tooltip) {
        canvas.addEventListener('mousemove', function(e) {
            if (!chartPts.length) return;
            const rect = this.getBoundingClientRect();
            const mx = e.clientX - rect.left;
            let closest = chartPts[0];
            let minD = Math.abs(chartPts[0].x - mx);
            chartPts.forEach(pt => { const d = Math.abs(pt.x - mx); if (d < minD) { minD = d; closest = pt; } });
            if (minD > 40) { tooltip.hidden = true; return; }
            tooltip.innerHTML = '<span class="tt-date">' + fmtDate(closest.date) + '</span>'
                + '<span class="tt-revenue">' + fmtMoney(closest.revenue) + '</span>'
                + '<span class="tt-orders">' + closest.orders + ' pedido' + (closest.orders !== 1 ? 's' : '') + '</span>';
            const tipLeft = closest.x + (closest.x > rect.width * 0.65 ? -148 : 12);
            tooltip.style.left = tipLeft + 'px';
            tooltip.style.top = Math.max(4, closest.y - 44) + 'px';
            tooltip.hidden = false;
        });
        canvas.addEventListener('mouseleave', () => { tooltip.hidden = true; });
    }

    document.querySelectorAll('.chart-period-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            activeDays = parseInt(this.dataset.days);
            document.querySelectorAll('.chart-period-btn').forEach(b => b.classList.remove('chart-period-btn--active'));
            this.classList.add('chart-period-btn--active');
            document.getElementById('chartTitle').textContent = 'Vendas Diárias (' + activeDays + ' dias)';
            chartPts = drawChart(buildDataset(activeDays));
        });
    });

    const wrap = canvas ? canvas.parentElement : null;
    if (wrap && window.ResizeObserver) {
        new ResizeObserver(() => { chartPts = drawChart(buildDataset(activeDays)); }).observe(wrap);
    }

    chartPts = drawChart(buildDataset(activeDays));
})();
</script>
