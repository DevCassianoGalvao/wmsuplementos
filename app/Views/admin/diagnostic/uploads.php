<?php use Maia\Helpers\Sanitizer; ?>

<div class="page-header">
    <h1>Diagnóstico de Uploads</h1>
    <a href="/admin/produtos" class="btn btn--ghost">← Produtos</a>
</div>

<div class="dashboard-card" style="margin-bottom:1.5rem;">
    <h2>Ambiente PHP</h2>
    <dl class="detail-list">
        <div class="detail-row">
            <dt>ROOT_PATH</dt>
            <dd><code><?= htmlspecialchars($info['ROOT_PATH'], ENT_QUOTES, 'UTF-8') ?></code></dd>
        </div>
        <div class="detail-row">
            <dt>upload_max_filesize</dt>
            <dd><?= htmlspecialchars($info['upload_max_filesize'], ENT_QUOTES, 'UTF-8') ?></dd>
        </div>
        <div class="detail-row">
            <dt>post_max_size</dt>
            <dd><?= htmlspecialchars($info['post_max_size'], ENT_QUOTES, 'UTF-8') ?></dd>
        </div>
        <div class="detail-row">
            <dt>GD habilitado</dt>
            <dd><?= $info['gd_enabled'] === 'Sim' ? '<span class="badge badge--success">Sim</span>' : '<span class="badge badge--danger">Não</span>' ?></dd>
        </div>
        <div class="detail-row">
            <dt>Suporte WebP (GD)</dt>
            <dd><?= $info['gd_webp_encode'] === 'Sim' ? '<span class="badge badge--success">Sim</span>' : '<span class="badge badge--danger">Não — uploads de imagem vão falhar!</span>' ?></dd>
        </div>
        <div class="detail-row">
            <dt>imagewebp() disponível</dt>
            <dd><?= $info['imagewebp_exists'] === 'Sim' ? '<span class="badge badge--success">Sim</span>' : '<span class="badge badge--danger">Não</span>' ?></dd>
        </div>
    </dl>
</div>

<div class="dashboard-card" style="margin-bottom:1.5rem;">
    <h2>Diretórios de Upload</h2>
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tamanho</th>
                    <th>Existe</th>
                    <th>Gravável</th>
                    <th>Arquivos</th>
                    <th>Caminho</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($dirs as $size => $d): ?>
            <tr>
                <td style="font-weight:600;"><?= htmlspecialchars($size, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= $d['exists'] ? '<span class="badge badge--success">Sim</span>' : '<span class="badge badge--danger">Não</span>' ?></td>
                <td><?= $d['writable'] ? '<span class="badge badge--success">Sim</span>' : ($d['exists'] ? '<span class="badge badge--danger">Não</span>' : '<span class="badge badge--muted">—</span>') ?></td>
                <td><?= (int)$d['files'] ?></td>
                <td style="font-size:0.75rem;color:var(--color-text-secondary);word-break:break-all;"><?= htmlspecialchars($d['path'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php $anyNotWritable = array_filter($dirs, fn($d) => $d['exists'] && !$d['writable']); ?>
    <?php if (!empty($anyNotWritable)): ?>
    <p style="color:var(--color-red);margin-top:1rem;font-weight:600;">
        ⚠ Diretórios existem mas não têm permissão de escrita. Execute via SSH: <code>chmod -R 755 public/uploads/</code>
    </p>
    <?php endif; ?>
    <?php $anyMissing = array_filter($dirs, fn($d) => !$d['exists']); ?>
    <?php if (!empty($anyMissing)): ?>
    <p style="color:var(--color-red);margin-top:0.5rem;font-weight:600;">
        ⚠ Alguns diretórios não existem. Eles serão criados automaticamente no primeiro upload se o PHP tiver permissão.
    </p>
    <?php endif; ?>
</div>

<div class="dashboard-card">
    <h2>Últimas 10 Imagens no Banco</h2>
    <?php if (empty($recent)): ?>
    <p class="text-muted" style="padding:1rem 0;">Nenhuma imagem cadastrada ainda.</p>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Produto</th>
                    <th>Principal</th>
                    <th>Arquivo em Disco</th>
                    <th>Path no DB</th>
                    <th>Prévia</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recent as $row): ?>
            <tr>
                <td><?= (int)$row['id'] ?></td>
                <td><a href="/admin/produtos/<?= (int)$row['product_id'] ?>"><?= htmlspecialchars($row['product_name'] ?? '?', ENT_QUOTES, 'UTF-8') ?></a></td>
                <td><?= !empty($row['is_main']) ? '<span class="badge badge--success">Sim</span>' : '' ?></td>
                <td><?= $row['_disk_exists'] ? '<span class="badge badge--success">Existe</span>' : '<span class="badge badge--danger">Ausente</span>' ?></td>
                <td style="font-size:0.7rem;color:var(--color-text-secondary);word-break:break-all;"><?= htmlspecialchars((string)($row['filename_webp'] ?? $row['filename'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?php $imgPath = (string)($row['filename_webp'] ?? $row['filename'] ?? ''); ?>
                    <?php if ($imgPath !== ''): ?>
                    <img src="<?= htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8') ?>"
                         width="48" height="48"
                         style="object-fit:cover;border-radius:4px;background:#333;"
                         onerror="this.style.outline='2px solid red';this.title='404 — arquivo não encontrado';"
                         title="<?= htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
