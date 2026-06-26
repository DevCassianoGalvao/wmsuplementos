<?php use Maia\Helpers\Sanitizer; use Maia\Helpers\CSRF; ?>

<div class="page-header">
    <h1><?= htmlspecialchars($customer['name'], ENT_QUOTES, 'UTF-8') ?></h1>
    <div class="page-actions" style="display:flex;gap:0.75rem;">
        <a href="/admin/clientes/<?= (int)$customer['id'] ?>/editar" class="btn btn--ghost">Editar</a>
        <form action="/admin/clientes/<?= (int)$customer['id'] ?>/anonimizar" method="post"
              onsubmit="return confirm('Anonimizar este cliente? Esta ação remove dados pessoais permanentemente.');">
            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
            <button type="submit" class="btn btn--ghost" style="color:var(--color-red);border-color:var(--color-red)">Anonimizar LGPD</button>
        </form>
        <a href="/admin/clientes" class="btn btn--ghost">← Voltar</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">

    <div class="dashboard-card">
        <h2>Dados Pessoais</h2>
        <dl class="detail-list">
            <div class="detail-row"><dt>E-mail</dt><dd><?= htmlspecialchars($customer['email'], ENT_QUOTES, 'UTF-8') ?></dd></div>
            <div class="detail-row"><dt>Telefone</dt><dd><?= htmlspecialchars($customer['phone'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd></div>
            <div class="detail-row"><dt>Cadastro</dt><dd><?= htmlspecialchars(substr($customer['created_at'] ?? '', 0, 10), ENT_QUOTES, 'UTF-8') ?></dd></div>
            <div class="detail-row"><dt>Opt-in e-mail</dt><dd><?= !empty($customer['email_opt_in']) ? '<span class="badge badge--success">Sim</span>' : '<span class="badge badge--muted">Não</span>' ?></dd></div>
        </dl>

        <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--color-border);">
            <p style="font-size:var(--text-small);color:var(--color-text-secondary);margin-bottom:0.5rem;font-weight:500;">Tag CRM</p>
            <form action="/admin/clientes/<?= (int)$customer['id'] ?>/tag" method="post" style="display:flex;gap:0.5rem;align-items:center;">
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                <select name="tag" style="flex:1;">
                    <option value=""      <?= ($customer['tag'] ?? '') === ''         ? 'selected' : '' ?>>Sem tag</option>
                    <option value="vip"   <?= ($customer['tag'] ?? '') === 'vip'      ? 'selected' : '' ?>>VIP</option>
                    <option value="atacado" <?= ($customer['tag'] ?? '') === 'atacado' ? 'selected' : '' ?>>Atacado</option>
                    <option value="bloqueado" <?= ($customer['tag'] ?? '') === 'bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
                </select>
                <button type="submit" class="btn btn--primary" style="white-space:nowrap;">Salvar</button>
            </form>
        </div>
    </div>

    <div class="dashboard-card">
        <h2>Resumo de Compras</h2>
        <div class="summary-stat">
            <span class="stat-label">Total de Pedidos</span>
            <span class="stat-value"><?= (int)($customer['total_orders'] ?? 0) ?></span>
        </div>
        <div class="summary-stat">
            <span class="stat-label">Total Gasto</span>
            <span class="stat-value">R$ <?= Sanitizer::money((float)($customer['total_spent'] ?? 0)) ?></span>
        </div>
        <div class="summary-stat">
            <span class="stat-label">Última Compra</span>
            <span class="stat-value" style="font-size:var(--text-body);">
                <?= htmlspecialchars(!empty($customer['last_purchase_at']) ? substr($customer['last_purchase_at'], 0, 10) : '—', ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>
    </div>

</div>

<div class="dashboard-card">
    <h2>Pedidos</h2>
    <?php if (empty($orders)): ?>
    <p class="text-muted" style="padding:1.5rem 0;text-align:center;">Sem pedidos ainda.</p>
    <?php else: ?>
    <div style="overflow-x:auto;margin-top:0.75rem;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td style="font-weight:600;">#<?= (int)$order['id'] ?></td>
                <td><?= htmlspecialchars(substr($order['created_at'] ?? '', 0, 10), ENT_QUOTES, 'UTF-8') ?></td>
                <td style="font-weight:700;">R$ <?= Sanitizer::money((float)$order['total']) ?></td>
                <td><span class="badge badge--muted"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $order['status'] ?? '')), ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><a href="/admin/pedidos/<?= (int)$order['id'] ?>" class="btn btn--ghost" style="padding:0.375rem 0.75rem;font-size:var(--text-small);">Ver</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
