<?php use Maia\Helpers\CSRF; ?>

<?php $isEdit = $combo !== null; ?>
<?php $action = $isEdit ? '/admin/combos/' . (int)$combo['id'] : '/admin/combos'; ?>
<?php $items = $combo['items'] ?? [['product_id' => 0, 'quantity' => 1]]; ?>

<div class="page-header">
    <h1><?= $isEdit ? 'Editar Combo' : 'Novo Combo' ?></h1>
    <a href="/admin/combos" class="btn btn-outline">Voltar</a>
</div>

<?php if (!empty($flash['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form action="<?= $action ?>" method="post" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

    <div class="form-grid">
        <section class="form-card">
            <h2>Dados do Combo</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Nome *</label>
                    <input type="text" id="name" name="name" required maxlength="255"
                           value="<?= htmlspecialchars($combo['name'] ?? $_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label for="slug">Slug</label>
                    <input type="text" id="slug" name="slug" maxlength="255"
                           value="<?= htmlspecialchars($combo['slug'] ?? $_POST['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="description">Descricao</label>
                <textarea id="description" name="description" rows="5"><?= htmlspecialchars($combo['description'] ?? $_POST['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Preco do combo (R$) *</label>
                    <input type="text" id="price" name="price" required inputmode="decimal"
                           value="<?= htmlspecialchars($combo['price'] ?? $_POST['price'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label for="image">Imagem</label>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                </div>
            </div>

            <?php if (!empty($combo['image'])): ?>
            <div class="form-group">
                <label>Imagem atual</label>
                <img src="<?= htmlspecialchars($combo['image'], ENT_QUOTES, 'UTF-8') ?>" alt="" width="140" height="100" style="object-fit:cover">
            </div>
            <?php endif; ?>

            <div class="form-check-row">
                <label><input type="checkbox" name="active" value="1" <?= ($combo['active'] ?? 1) ? 'checked' : '' ?>> Ativo na loja</label>
            </div>
        </section>

        <section class="form-card">
            <h2>Produtos do Combo</h2>
            <div class="combo-items-editor" id="combo-items-editor">
                <?php foreach ($items as $item): ?>
                <div class="combo-item-row">
                    <div class="form-group">
                        <label>Produto</label>
                        <select name="product_id[]">
                            <option value="">Selecione</option>
                            <?php foreach ($products as $product): ?>
                            <option value="<?= (int)$product['id'] ?>" <?= (int)($item['product_id'] ?? 0) === (int)$product['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Qtd.</label>
                        <input type="number" name="quantity[]" min="1" value="<?= (int)($item['quantity'] ?? 1) ?>">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-outline" data-add-combo-item>Adicionar produto</button>
        </section>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">Salvar Combo</button>
        <a href="/admin/combos" class="btn btn-link">Cancelar</a>
    </div>
</form>

<template id="combo-item-template">
    <div class="combo-item-row">
        <div class="form-group">
            <label>Produto</label>
            <select name="product_id[]">
                <option value="">Selecione</option>
                <?php foreach ($products as $product): ?>
                <option value="<?= (int)$product['id'] ?>"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Qtd.</label>
            <input type="number" name="quantity[]" min="1" value="1">
        </div>
    </div>
</template>
