<?php use Maia\Helpers\CSRF; ?>

<?php $isEdit = $category !== null; ?>
<?php $action = $isEdit ? '/admin/categorias/' . (int)$category['id'] : '/admin/categorias'; ?>

<div class="page-header">
    <h1><?= $isEdit ? 'Editar Categoria' : 'Nova Categoria' ?></h1>
    <a href="/admin/categorias" class="btn btn-outline">Voltar</a>
</div>

<?php if (!empty($flash['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form action="<?= $action ?>" method="post" novalidate>
    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

    <section class="form-card">
        <div class="form-row">
            <div class="form-group">
                <label for="name">Nome *</label>
                <input type="text" id="name" name="name" required maxlength="120"
                       value="<?= htmlspecialchars($category['name'] ?? $_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" maxlength="140"
                       value="<?= htmlspecialchars($category['slug'] ?? $_POST['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="sort_order">Ordem</label>
                <input type="number" id="sort_order" name="sort_order"
                       value="<?= (int)($category['sort_order'] ?? $_POST['sort_order'] ?? 0) ?>">
            </div>
            <div class="form-group">
                <label for="seo_title">SEO Title</label>
                <input type="text" id="seo_title" name="seo_title" maxlength="70"
                       value="<?= htmlspecialchars($category['seo_title'] ?? $_POST['seo_title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="seo_description">SEO Description</label>
            <textarea id="seo_description" name="seo_description" rows="3" maxlength="160"><?= htmlspecialchars($category['seo_description'] ?? $_POST['seo_description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="form-check-row">
            <label><input type="checkbox" name="active" value="1" <?= ($category['active'] ?? 1) ? 'checked' : '' ?>> Ativa na loja</label>
        </div>
    </section>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvar Categoria</button>
        <a href="/admin/categorias" class="btn btn-link">Cancelar</a>
    </div>
</form>
