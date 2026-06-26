<?php use Maia\Helpers\CSRF; ?>

<div class="container auth-container">
    <div class="auth-card">
        <h1>Criar Conta</h1>

        <?php if (!empty($flash['error'])): ?>
        <div class="alert alert-error"><?= nl2br(htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8')) ?></div>
        <?php endif; ?>

        <form action="/cadastro" method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

            <div class="form-group">
                <label for="name">Nome completo <span aria-hidden="true">*</span></label>
                <input type="text" id="name" name="name" required autocomplete="name"
                       value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="email">E-mail <span aria-hidden="true">*</span></label>
                <input type="email" id="email" name="email" required autocomplete="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="phone">Telefone (WhatsApp) <span aria-hidden="true">*</span></label>
                <input type="tel" id="phone" name="phone" required autocomplete="tel"
                       value="<?= htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="password">Senha <span aria-hidden="true">*</span></label>
                <input type="password" id="password" name="password" required autocomplete="new-password"
                       minlength="8">
                <small>Mínimo de 8 caracteres</small>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirmar Senha <span aria-hidden="true">*</span></label>
                <input type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password">
            </div>

            <div class="form-group form-check">
                <label>
                    <input type="checkbox" name="email_opt_in" value="1"
                           <?= !empty($_POST['email_opt_in']) ? 'checked' : '' ?>>
                    Quero receber novidades e promoções por e-mail
                </label>
            </div>

            <button type="submit" class="btn btn--primary btn-primary btn-lg btn-block" style="background:#E63329;color:#fff;">Criar Conta</button>
        </form>

        <div class="auth-links">
            <p>Já tem conta? <a href="/entrar">Entrar</a></p>
        </div>
    </div>
</div>
