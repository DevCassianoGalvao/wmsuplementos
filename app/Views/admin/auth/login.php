<?php use Maia\Helpers\CSRF; ?>

<div class="admin-login-card">
    <div class="login-logo">
        <img src="/assets/img/logo.svg" alt="Maia Suplementos" width="160" height="40">
        <p>Painel Administrativo</p>
    </div>

    <?php if (!empty($flash['error'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form action="/admin/login" method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" required autocomplete="username"
                   value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="password">Senha</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block">Entrar</button>
    </form>
</div>
