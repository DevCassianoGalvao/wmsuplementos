<?php use Maia\Helpers\CSRF; ?>

<div class="page-header">
    <div>
        <h1>Popups e Campanhas</h1>
        <p class="page-subtitle">Promoções, avisos e banners clicáveis exibidos na loja.</p>
    </div>
    <a href="/admin/configuracoes/popups/novo" class="btn btn-primary">Nova campanha</a>
</div>

<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($flash['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (!empty($tableMissing)): ?>
<div class="alert alert-warning">
    Tabela de popups ainda não existe. Rode <strong>database/popup_campaigns.sql</strong> no banco antes de criar campanhas.
</div>
<?php endif; ?>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Campanha</th>
                <th>Tipo</th>
                <th>Cupom</th>
                <th>Periodo</th>
                <th>Status</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($campaigns)): ?>
            <tr><td colspan="6" class="empty-state">Nenhum popup cadastrado.</td></tr>
        <?php else: ?>
            <?php foreach ($campaigns as $campaign): ?>
            <tr>
                <td>
                    <strong><?= htmlspecialchars($campaign['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                    <div class="muted"><?= htmlspecialchars($campaign['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                </td>
                <td><?= ($campaign['mode'] ?? 'message') === 'image' ? 'Imagem clicavel' : 'Aviso visual' ?></td>
                <td><?= htmlspecialchars($campaign['coupon_code'] ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <span class="muted">
                        <?= htmlspecialchars($campaign['start_at'] ?: 'Agora', ENT_QUOTES, 'UTF-8') ?>
                        ate
                        <?= htmlspecialchars($campaign['end_at'] ?: 'sem fim', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </td>
                <td>
                    <span class="<?= !empty($campaign['active']) ? 'status-pago' : 'status-cancelado' ?>">
                        <?= !empty($campaign['active']) ? 'Ativo' : 'Inativo' ?>
                    </span>
                </td>
                <td>
                    <div class="table-actions">
                        <a href="/admin/configuracoes/popups/<?= (int)$campaign['id'] ?>" class="btn btn-sm btn-outline">Editar</a>
                        <form action="/admin/configuracoes/popups/<?= (int)$campaign['id'] ?>/toggle" method="post">
                            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                            <button type="submit" class="btn btn-sm btn-outline"><?= !empty($campaign['active']) ? 'Desativar' : 'Ativar' ?></button>
                        </form>
                        <form action="/admin/configuracoes/popups/<?= (int)$campaign['id'] ?>/excluir" method="post" data-confirm="Remover este popup?">
                            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                            <button type="submit" class="btn btn-sm btn-outline">Remover</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
