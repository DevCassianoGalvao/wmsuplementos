<?php use Maia\Helpers\CSRF; ?>

<?php if (!empty($flash['error'])): ?>
<div class="alert alert--error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($flash['success'])): ?>
<div class="alert alert--success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="page-header">
    <h1><?= $customer ? 'Editar Cliente' : 'Novo Cliente' ?></h1>
    <a href="/admin/clientes" class="btn btn--ghost">← Voltar</a>
</div>

<div class="dashboard-card" style="max-width:640px">
    <form method="post" action="<?= $customer ? '/admin/clientes/' . (int)$customer['id'] . '/editar' : '/admin/clientes/novo' ?>">
        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

        <div class="form-group">
            <label for="name">Nome completo *</label>
            <input type="text" id="name" name="name" required
                   value="<?= htmlspecialchars($customer['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="email">E-mail *</label>
            <input type="email" id="email" name="email" required
                   value="<?= htmlspecialchars($customer['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="phone">Telefone / WhatsApp</label>
            <input type="text" id="phone" name="phone"
                   value="<?= htmlspecialchars($customer['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="password"><?= $customer ? 'Nova Senha (deixe vazio para não alterar)' : 'Senha *' ?></label>
            <input type="password" id="password" name="password" <?= $customer ? '' : 'required' ?> minlength="8">
            <small style="color:var(--color-text-muted);font-size:var(--text-label)">Mínimo de 8 caracteres</small>
        </div>

        <button type="submit" class="btn btn--primary"><?= $customer ? 'Salvar Alterações' : 'Criar Cliente' ?></button>
    </form>
</div>
