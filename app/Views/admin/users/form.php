<?php use Maia\Helpers\CSRF; ?>

<?php $isEdit = !empty($user); ?>

<div class="page-header">
    <div>
        <h1><?= $isEdit ? 'Editar Usuario' : 'Novo Usuario' ?></h1>
        <p class="page-subtitle">Use senhas fortes e mantenha perfil admin apenas para quem precisa.</p>
    </div>
    <a href="/admin/usuarios" class="btn btn-outline">Voltar</a>
</div>

<?php if (!empty($flash['error'])): ?>
<div class="alert alert-error"><?= $flash['error'] ?></div>
<?php endif; ?>

<form action="<?= $isEdit ? '/admin/usuarios/' . (int)$user['id'] : '/admin/usuarios' ?>" method="post" class="admin-form" novalidate>
    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

    <section class="form-card">
        <h2>Acesso</h2>
        <div class="form-row">
            <div class="form-group">
                <label for="name">Nome</label>
                <input type="text" id="name" name="name" required
                       value="<?= htmlspecialchars($user['name'] ?? $_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required
                       value="<?= htmlspecialchars($user['email'] ?? $_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="role">Perfil</label>
                <?php $role = $user['role'] ?? $_POST['role'] ?? 'operator'; ?>
                <select id="role" name="role">
                    <option value="operator" <?= $role === 'operator' ? 'selected' : '' ?>>Operador</option>
                    <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label for="password"><?= $isEdit ? 'Nova senha' : 'Senha' ?></label>
                <input type="password" id="password" name="password" <?= $isEdit ? '' : 'required' ?> minlength="8" autocomplete="new-password">
                <small><?= $isEdit ? 'Deixe em branco para manter a senha atual.' : 'Minimo de 8 caracteres.' ?></small>
            </div>
        </div>

        <div class="form-check-row">
            <label>
                <input type="checkbox" name="active" value="1" <?= (int)($user['active'] ?? 1) === 1 ? 'checked' : '' ?>>
                Ativo
            </label>
        </div>
    </section>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg"><?= $isEdit ? 'Salvar Usuario' : 'Criar Usuario' ?></button>
    </div>
</form>
