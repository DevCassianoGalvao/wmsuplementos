<?php use Maia\Helpers\CSRF; ?>

<div class="page-header">
    <h1>Central de Scripts</h1>
    <p class="page-subtitle">Configure os pixels e scripts de rastreio injetados nas páginas públicas.</p>
</div>

<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($flash['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form action="/admin/scripts" method="post" novalidate>
    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

    <div class="scripts-grid">
        <?php foreach ($keys as $key => $meta): ?>
        <?php $row = $scripts[$key] ?? ['value' => '', 'active' => 0]; ?>
        <div class="script-card <?= $row['active'] ? 'script-active' : '' ?>">
            <div class="script-header">
                <label class="script-label" for="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8') ?>
                </label>
                <label class="toggle-switch" title="Ativar/Desativar">
                    <input type="checkbox" name="active_<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                           value="1" <?= $row['active'] ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <?php $isMultiline = in_array($key, ['custom_head', 'custom_body'], true); ?>
            <?php if ($isMultiline): ?>
            <textarea id="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                      name="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                      rows="6"
                      placeholder="<?= htmlspecialchars($meta['placeholder'], ENT_QUOTES, 'UTF-8') ?>"
                      class="script-textarea"><?= htmlspecialchars($row['value'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            <?php else: ?>
            <input type="text"
                   id="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                   name="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                   value="<?= htmlspecialchars($row['value'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   placeholder="<?= htmlspecialchars($meta['placeholder'], ENT_QUOTES, 'UTF-8') ?>"
                   class="script-input">
            <?php endif; ?>

            <?php if ($row['active'] && !empty($row['value'])): ?>
            <span class="script-status-badge">● Ativo</span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">Salvar Scripts</button>
    </div>
</form>
