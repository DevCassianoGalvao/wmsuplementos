<?php use Maia\Helpers\CSRF; ?>

<div class="container auth-container">
    <div class="auth-card">
        <h1>Entrar</h1>

        <?php if (!empty($flash['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if (!empty($flash['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form action="/entrar" method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required autocomplete="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block">Entrar</button>
        </form>

        <div class="auth-links">
            <p>Não tem conta? <a href="/cadastro">Criar conta grátis</a></p>
        </div>
    </div>
</div>
