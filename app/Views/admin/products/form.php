<?php use Maia\Helpers\CSRF; use Maia\Helpers\Sanitizer; ?>

<?php $isEdit = $product !== null; ?>
<?php $action = $isEdit ? '/admin/produtos/' . (int)$product['id'] : '/admin/produtos'; ?>

<div class="page-header">
    <h1><?= $isEdit ? 'Editar: ' . htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') : 'Novo Produto' ?></h1>
    <a href="/admin/produtos" class="btn btn-outline">← Voltar</a>
</div>

<?php if (!empty($flash['error'])): ?>
<div class="alert alert-error"><?= nl2br(htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8')) ?></div>
<?php endif; ?>

<form action="<?= $action ?>" method="post" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

    <div class="form-grid">
        <section class="form-card">
            <h2>Dados Principais</h2>

            <div class="form-group">
                <label for="name">Nome *</label>
                <input type="text" id="name" name="name" required maxlength="200"
                       value="<?= htmlspecialchars($product['name'] ?? $_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="slug">Slug (URL)</label>
                <input type="text" id="slug" name="slug" maxlength="220"
                       value="<?= htmlspecialchars($product['slug'] ?? $_POST['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <small>Gerado automaticamente se vazio</small>
            </div>

            <div class="form-group">
                <label for="description">Descrição</label>
                <textarea id="description" name="description" rows="6"><?= htmlspecialchars($product['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category_id">Categoria</label>
                    <select id="category_id" name="category_id">
                        <option value="">Selecione</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= (int)($product['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="brand_id">Marca</label>
                    <select id="brand_id" name="brand_id">
                        <option value="">Sem marca</option>
                        <?php foreach ($brands as $brand): ?>
                        <option value="<?= (int)$brand['id'] ?>" <?= (int)($product['brand_id'] ?? 0) === (int)$brand['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </section>

        <section class="form-card">
            <h2>Preço e Estoque</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Preço (R$) *</label>
                    <input type="text" id="price" name="price" required inputmode="decimal"
                           value="<?= htmlspecialchars($product['price'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label for="price_sale">Preço Promocional (R$)</label>
                    <input type="text" id="price_sale" name="price_sale" inputmode="decimal"
                           value="<?= htmlspecialchars($product['price_sale'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="stock">Estoque</label>
                    <input type="number" id="stock" name="stock" min="0"
                           value="<?= (int)($product['stock'] ?? 0) ?>">
                </div>
                <div class="form-group">
                    <label for="stock_alert">Alerta de Estoque</label>
                    <input type="number" id="stock_alert" name="stock_alert" min="0"
                           value="<?= (int)($product['stock_alert'] ?? 5) ?>">
                </div>
                <div class="form-group">
                    <label for="weight_g">Peso (g)</label>
                    <input type="number" id="weight_g" name="weight_g" min="0"
                           value="<?= (int)($product['weight_g'] ?? 0) ?>">
                </div>
            </div>

            <div class="form-check-row">
                <label><input type="checkbox" name="active" value="1" <?= ($product['active'] ?? 1) ? 'checked' : '' ?>> Ativo</label>
                <label><input type="checkbox" name="featured" value="1" <?= ($product['featured'] ?? 0) ? 'checked' : '' ?>> Destaque</label>
            </div>
        </section>

        <section class="form-card">
            <h2>Imagens</h2>
            <?php if (!empty($product['images'])): ?>
            <div class="current-images">
                <?php foreach ($product['images'] as $img): ?>
                <div class="thumb-item">
                    <img src="/uploads/products/<?= htmlspecialchars($img['filename_webp'] ?? $img['filename'], ENT_QUOTES, 'UTF-8') ?>"
                         alt="" width="80" height="80">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="images">Adicionar imagens (JPG/PNG/WebP, máx. 5MB cada)</label>
                <input type="file" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/webp">
            </div>
        </section>

        <section class="form-card">
            <h2>SEO</h2>
            <div class="form-group">
                <label for="meta_title">Meta Title</label>
                <input type="text" id="meta_title" name="meta_title" maxlength="70"
                       value="<?= htmlspecialchars($product['seo_title'] ?? $product['meta_title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="meta_description">Meta Description</label>
                <textarea id="meta_description" name="meta_description" maxlength="160" rows="3"><?= htmlspecialchars($product['seo_description'] ?? $product['meta_description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </section>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">Salvar Produto</button>
        <a href="/admin/produtos" class="btn btn-link">Cancelar</a>
    </div>
</form>
