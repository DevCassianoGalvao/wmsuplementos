<?php use Maia\Helpers\CSRF; ?>

<div class="container account-container">
    <h1>Minha Conta</h1>

    <?php if (!empty($flash['error'])): ?>
    <div class="alert alert-error"><?= nl2br(htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8')) ?></div>
    <?php endif; ?>
    <?php if (!empty($flash['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="account-layout">
        <nav class="account-sidebar" aria-label="Menu da conta">
            <a href="/minha-conta" class="active">Meus Dados</a>
            <a href="/minha-conta/pedidos">Meus Pedidos</a>
            <a href="/sair">Sair</a>
        </nav>

        <div class="account-content">
            <section class="account-section">
                <h2>Meus Dados</h2>
                <form action="/minha-conta/dados" method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

                    <div class="form-group">
                        <label for="name">Nome completo</label>
                        <input type="text" id="name" name="name" required
                               value="<?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" required
                               value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone">Telefone</label>
                        <input type="tel" id="phone" name="phone"
                               value="<?= htmlspecialchars($user['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="account-meta">
                        <small>Cliente desde: <?= htmlspecialchars(substr($user['created_at'] ?? '', 0, 10), ENT_QUOTES, 'UTF-8') ?></small>
                        <small>Pedidos: <?= (int)($user['total_orders'] ?? 0) ?></small>
                    </div>

                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </form>
            </section>

            <section class="account-section account-section--danger">
                <h2>Excluir dados pessoais</h2>
                <p>
                    Remove nome, e-mail, telefone e acesso da conta. Pedidos antigos continuam no sistema
                    de forma anonimizada para auditoria financeira e fiscal.
                </p>
                <form action="/minha-conta/excluir" method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                    <div class="form-group">
                        <label for="delete-confirmation">Digite EXCLUIR para confirmar</label>
                        <input type="text" id="delete-confirmation" name="confirmation" autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-outline btn-danger"
                            data-confirm="Esta acao remove seus dados pessoais e encerra sua sessao. Continuar?">
                        Remover meus dados
                    </button>
                </form>
            </section>
        </div>
    </div>
</div>
