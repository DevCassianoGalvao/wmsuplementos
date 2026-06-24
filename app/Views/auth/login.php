<?php use Maia\Helpers\CSRF; ?>

<section class="auth-page">
    <div class="container auth-shell">
        <div class="auth-panel">
            <p class="section__label">&Aacute;rea do cliente</p>
            <h1>Entrar</h1>
            <p class="auth-lead">Acompanhe pedidos, dados de entrega e hist&oacute;rico de compras.</p>

            <?php if (!empty($flash['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if (!empty($flash['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form action="/entrar" method="post" novalidate class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required autocomplete="email"
                           placeholder="voce@email.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password"
                           placeholder="Sua senha">
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">Entrar</button>
            </form>

            <div class="auth-links">
                <p>N&atilde;o tem conta? <a href="/cadastro">Criar conta gr&aacute;tis</a></p>
            </div>
        </div>
    </div>
</section>
