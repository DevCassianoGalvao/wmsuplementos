<?php use Maia\Helpers\CSRF; ?>

<section class="auth-page">
    <div class="container auth-shell">
        <div class="auth-panel">
            <p class="section__label">Área do cliente</p>
            <h1>Entrar na sua conta</h1>
            <p class="auth-lead">Acompanhe pedidos, dados de entrega e histórico de compras.</p>

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
                <p>Não tem conta? <a href="/cadastro">Criar conta grátis</a></p>
            </div>
        </div>
        <aside class="auth-aside" aria-label="Benefícios">
            <img src="/assets/img/logo.png" alt="Maia Suplementos" width="150" height="50" loading="lazy">
            <ul>
                <li>Checkout mais rápido</li>
                <li>Histórico de pedidos</li>
                <li>Acompanhamento de compra</li>
            </ul>
        </aside>
    </div>
</section>
