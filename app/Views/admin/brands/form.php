<?php use Maia\Helpers\CSRF; ?>

<?php $isEdit = $brand !== null; ?>
<?php $action = $isEdit ? '/admin/marcas/' . (int)$brand['id'] : '/admin/marcas'; ?>

<div class="page-header">
    <h1><?= $isEdit ? 'Editar Marca' : 'Nova Marca' ?></h1>
    <a href="/admin/marcas" class="btn btn-outline">Voltar</a>
</div>

<?php if (!empty($flash['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form action="<?= $action ?>" method="post" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

    <section class="form-card">
        <div class="form-row">
            <div class="form-group">
                <label for="name">Nome *</label>
                <input type="text" id="name" name="name" required maxlength="150"
                       value="<?= htmlspecialchars($brand['name'] ?? $_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" maxlength="150"
                       value="<?= htmlspecialchars($brand['slug'] ?? $_POST['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="description">Descricao</label>
            <textarea id="description" name="description" rows="5"><?= htmlspecialchars($brand['description'] ?? $_POST['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <?php if (!empty($brand['logo'])): ?>
        <div class="form-group">
            <label>Logo atual</label>
            <img src="<?= htmlspecialchars($brand['logo'], ENT_QUOTES, 'UTF-8') ?>" alt="" width="120" height="80" style="object-fit:contain">
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="logo">Logo da marca</label>
            <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/webp">
        </div>

        <div class="form-check-row">
            <label><input type="checkbox" name="active" value="1" <?= ($brand['active'] ?? 1) ? 'checked' : '' ?>> Ativa na loja</label>
        </div>
    </section>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvar Marca</button>
        <a href="/admin/marcas" class="btn btn-link">Cancelar</a>
    </div>
</form>
