<?php use Maia\Helpers\CSRF; ?>

<div class="page-header">
    <div>
        <h1>Configuracoes</h1>
        <p class="page-subtitle">Dados publicos da loja, regras operacionais e textos legais.</p>
    </div>
</div>

<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form action="/admin/configuracoes" method="post" class="admin-form" novalidate>
    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

    <div class="form-grid">
        <section class="form-card">
            <h2>Loja</h2>
            <div class="form-group">
                <label for="store_name">Nome da loja</label>
                <input type="text" id="store_name" name="store_name"
                       value="<?= htmlspecialchars($settings['store_name'] ?? 'Maia Suplementos', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="store_email">E-mail de atendimento</label>
                <input type="email" id="store_email" name="store_email"
                       value="<?= htmlspecialchars($settings['store_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="store_whatsapp">WhatsApp</label>
                <input type="text" id="store_whatsapp" name="store_whatsapp"
                       value="<?= htmlspecialchars($settings['store_whatsapp'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="store_address">Endereco</label>
                <textarea id="store_address" name="store_address" rows="3"><?= htmlspecialchars($settings['store_address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </section>

        <section class="form-card">
            <h2>Operacao</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="free_shipping_above">Frete gratis acima de</label>
                    <input type="number" step="0.01" min="0" id="free_shipping_above" name="free_shipping_above"
                           value="<?= htmlspecialchars($settings['free_shipping_above'] ?? '0.00', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label for="stock_alert_min">Alerta minimo de estoque</label>
                    <input type="number" min="0" id="stock_alert_min" name="stock_alert_min"
                           value="<?= htmlspecialchars($settings['stock_alert_min'] ?? '5', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="store_color_primary">Cor principal</label>
                <input type="text" id="store_color_primary" name="store_color_primary"
                       value="<?= htmlspecialchars($settings['store_color_primary'] ?? '#e60000', ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </section>
    </div>

    <section class="form-card">
        <h2>LGPD e Politica de Privacidade</h2>
        <div class="form-group">
            <label for="privacy_policy">Conteudo da politica</label>
            <textarea id="privacy_policy" name="privacy_policy" rows="12"><?= htmlspecialchars($pages['politica-de-privacidade']['content'] ?? $settings['privacy_policy'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
    </section>

    <section class="form-card">
        <h2>Termos de Uso</h2>
        <div class="form-group">
            <label for="terms_of_use">Conteudo dos termos</label>
            <textarea id="terms_of_use" name="terms_of_use" rows="12"><?= htmlspecialchars($pages['termos-de-uso']['content'] ?? $settings['terms_of_use'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
    </section>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">Salvar Configuracoes</button>
    </div>
</form>
