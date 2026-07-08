<?php use Maia\Helpers\CSRF; ?>

<div class="page-header">
    <div>
        <h1>Configurações</h1>
        <p class="page-subtitle">Dados públicos da loja, regras operacionais e textos legais.</p>
    </div>
</div>

<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<section class="form-card settings-shortcut">
    <div>
        <h2>Popups e campanhas</h2>
        <p class="page-subtitle">Crie avisos, promocoes com cupom ou banners clicaveis para a loja.</p>
    </div>
    <a href="/admin/configuracoes/popups" class="btn btn-primary">Gerenciar popups</a>
</section>

<form action="/admin/configuracoes" method="post" class="admin-form" novalidate>
    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

    <div class="form-grid">
        <section class="form-card">
            <h2>Loja</h2>
            <div class="form-group">
                <label for="store_name">Nome da loja</label>
                <input type="text" id="store_name" name="store_name"
                       value="<?= htmlspecialchars($settings['store_name'] ?? 'WM Suplementos', ENT_QUOTES, 'UTF-8') ?>">
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
                <label for="store_address">Endereço</label>
                <textarea id="store_address" name="store_address" rows="3"><?= htmlspecialchars($settings['store_address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="form-group">
                <label for="store_pix_key">Chave PIX</label>
                <input type="text" id="store_pix_key" name="store_pix_key"
                       value="<?= htmlspecialchars($settings['store_pix_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </section>

        <section class="form-card">
            <h2>Operação</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="shipping_flat_rate">Frete fixo</label>
                    <input type="number" step="0.01" min="0" id="shipping_flat_rate" name="shipping_flat_rate"
                           value="<?= htmlspecialchars($settings['shipping_flat_rate'] ?? '0.00', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label for="free_shipping_above">Frete grátis acima de</label>
                    <input type="number" step="0.01" min="0" id="free_shipping_above" name="free_shipping_above"
                           value="<?= htmlspecialchars($settings['free_shipping_above'] ?? '0.00', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label for="stock_alert_min">Alerta mínimo de estoque</label>
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
        <h2>Hero da Home</h2>
        <div class="form-row">
            <div class="form-group">
                <label for="hero_label">Selo superior</label>
                <input type="text" id="hero_label" name="hero_label"
                       value="<?= htmlspecialchars($settings['hero_label'] ?? 'Performance & Resultados', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="hero_image">Imagem de fundo</label>
                <input type="text" id="hero_image" name="hero_image"
                       placeholder="/assets/img/hero-supplement.webp"
                       value="<?= htmlspecialchars((string)($settings['hero_image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="hero_title_before">Título antes do destaque</label>
                <input type="text" id="hero_title_before" name="hero_title_before"
                       value="<?= htmlspecialchars($settings['hero_title_before'] ?? 'Suplementos para', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="hero_title_emphasis">Palavra em destaque</label>
                <input type="text" id="hero_title_emphasis" name="hero_title_emphasis"
                       value="<?= htmlspecialchars($settings['hero_title_emphasis'] ?? 'performance', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="hero_title_after">Título depois do destaque</label>
                <input type="text" id="hero_title_after" name="hero_title_after"
                       value="<?= htmlspecialchars($settings['hero_title_after'] ?? 'real.', ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="hero_subtitle">Subtítulo</label>
            <textarea id="hero_subtitle" name="hero_subtitle" rows="2"><?= htmlspecialchars($settings['hero_subtitle'] ?? 'Produtos selecionados para treino, rotina e evolucao.', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="hero_primary_label">Botão principal</label>
                <input type="text" id="hero_primary_label" name="hero_primary_label"
                       value="<?= htmlspecialchars($settings['hero_primary_label'] ?? 'Ver Produtos', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="hero_primary_url">URL principal</label>
                <input type="text" id="hero_primary_url" name="hero_primary_url"
                       value="<?= htmlspecialchars($settings['hero_primary_url'] ?? '/produtos', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="hero_secondary_label">Botão secundário</label>
                <input type="text" id="hero_secondary_label" name="hero_secondary_label"
                       value="<?= htmlspecialchars($settings['hero_secondary_label'] ?? 'Ver Combos', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="hero_secondary_url">URL secundária</label>
                <input type="text" id="hero_secondary_url" name="hero_secondary_url"
                       value="<?= htmlspecialchars($settings['hero_secondary_url'] ?? '/combos', ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </div>
    </section>

    <section class="form-card">
        <h2>FAQ da Home</h2>
        <label class="checkbox-row">
            <input type="checkbox" name="home_faq_enabled" value="1"
                   <?= ($settings['home_faq_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
            Exibir perguntas frequentes na Home
        </label>
        <div class="form-group">
            <label for="faq_content">Conteúdo do FAQ</label>
            <textarea id="faq_content" name="faq_content" rows="10"><?= htmlspecialchars($pages['faq']['content'] ?? $settings['faq_content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
    </section>

    <section class="form-card">
        <h2>LGPD e Política de Privacidade</h2>
        <div class="form-group">
            <label for="privacy_policy">Conteúdo da política</label>
            <textarea id="privacy_policy" name="privacy_policy" rows="12"><?= htmlspecialchars($pages['politica-de-privacidade']['content'] ?? $settings['privacy_policy'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
    </section>

    <section class="form-card">
        <h2>Termos de Uso</h2>
        <div class="form-group">
            <label for="terms_of_use">Conteúdo dos termos</label>
            <textarea id="terms_of_use" name="terms_of_use" rows="12"><?= htmlspecialchars($pages['termos-de-uso']['content'] ?? $settings['terms_of_use'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
    </section>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">Salvar Configurações</button>
    </div>
</form>
