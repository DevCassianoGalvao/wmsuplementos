<?php use Maia\Helpers\CSRF; ?>

<div class="admin-login-wrap">
    <div class="admin-login-card">
        <img src="/assets/img/logo.png?v=20260707-wm" alt="WM Suplementos" height="40" style="height:40px;width:auto;margin-bottom:1.5rem;">
        <h1>Painel Administrativo</h1>
        <p class="subtitle">Acesse com suas credenciais de administrador</p>

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

            <button type="submit" class="btn btn--primary btn-primary btn-lg btn-block" style="background:#E63329;color:#fff;">Entrar</button>
        </form>
    </div>
</div>
