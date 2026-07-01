<?php use Maia\Helpers\CSRF; ?>

<?php $isEdit = $campaign !== null; ?>
<?php $action = $isEdit ? '/admin/configuracoes/popups/' . (int)$campaign['id'] : '/admin/configuracoes/popups'; ?>
<?php
$startAt = !empty($campaign['start_at']) ? date('Y-m-d\TH:i', strtotime((string)$campaign['start_at'])) : '';
$endAt = !empty($campaign['end_at']) ? date('Y-m-d\TH:i', strtotime((string)$campaign['end_at'])) : '';
?>

<div class="page-header">
    <div>
        <h1><?= $isEdit ? 'Editar Popup' : 'Novo Popup' ?></h1>
        <p class="page-subtitle">Use imagem clicavel ou aviso com cupom copiavel.</p>
    </div>
    <a href="/admin/configuracoes/popups" class="btn btn-outline">Voltar</a>
</div>

<?php if (!empty($flash['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" method="post" enctype="multipart/form-data" class="admin-form" novalidate>
    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

    <div class="form-grid form-grid--single">
        <section class="form-card">
            <h2>Campanha</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="title">Titulo *</label>
                    <input type="text" id="title" name="title" required maxlength="160"
                           value="<?= htmlspecialchars($campaign['title'] ?? $_POST['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label for="slug">Slug</label>
                    <input type="text" id="slug" name="slug" maxlength="180"
                           value="<?= htmlspecialchars($campaign['slug'] ?? $_POST['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="mode">Tipo</label>
                    <select id="mode" name="mode">
                        <option value="message" <?= (($campaign['mode'] ?? 'message') === 'message') ? 'selected' : '' ?>>Aviso visual do site</option>
                        <option value="image" <?= (($campaign['mode'] ?? '') === 'image') ? 'selected' : '' ?>>Imagem clicavel</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="target_url">Link de destino</label>
                    <input type="text" id="target_url" name="target_url" placeholder="/produtos ou https://..."
                           value="<?= htmlspecialchars($campaign['target_url'] ?? $_POST['target_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="message">Mensagem</label>
                <textarea id="message" name="message" rows="5" placeholder="Texto do aviso no popup."><?= htmlspecialchars($campaign['message'] ?? $_POST['message'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </section>

        <section class="form-card">
            <h2>Imagem e cupom</h2>
            <?php if (!empty($campaign['image'])): ?>
            <div class="form-group">
                <label>Imagem atual</label>
                <div class="popup-admin-preview">
                    <img src="<?= htmlspecialchars($campaign['image'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                </div>
                <label class="checkbox-row">
                    <input type="checkbox" name="remove_image" value="1">
                    Remover imagem atual
                </label>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="image">Imagem do popup</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                <p class="help-text">JPG, PNG, GIF ou WebP. No modo imagem, o banner inteiro fica clicavel se houver link.</p>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="coupon_code">Cupom</label>
                    <input type="text" id="coupon_code" name="coupon_code" maxlength="80"
                           value="<?= htmlspecialchars($campaign['coupon_code'] ?? $_POST['coupon_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label for="cta_label">Texto do botao</label>
                    <input type="text" id="cta_label" name="cta_label" maxlength="80" placeholder="Comprar agora"
                           value="<?= htmlspecialchars($campaign['cta_label'] ?? $_POST['cta_label'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>
        </section>

        <section class="form-card">
            <h2>Exibicao</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="start_at">Inicio</label>
                    <input type="datetime-local" id="start_at" name="start_at" value="<?= htmlspecialchars($startAt, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label for="end_at">Fim</label>
                    <input type="datetime-local" id="end_at" name="end_at" value="<?= htmlspecialchars($endAt, ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>

            <div class="form-check-row">
                <label><input type="checkbox" name="active" value="1" <?= (int)($campaign['active'] ?? 1) === 1 ? 'checked' : '' ?>> Ativo na loja</label>
                <label><input type="checkbox" name="show_once" value="1" <?= (int)($campaign['show_once'] ?? 1) === 1 ? 'checked' : '' ?>> Nao reabrir para quem fechou</label>
            </div>
        </section>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvar Popup</button>
        <a href="/admin/configuracoes/popups" class="btn btn-link">Cancelar</a>
    </div>
</form>
