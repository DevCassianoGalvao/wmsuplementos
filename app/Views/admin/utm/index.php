<?php use Maia\Helpers\CSRF; ?>

<div class="page-header">
    <h1>UTM Builder</h1>
    <p class="page-subtitle">Gere links rastreados para campanhas de marketing.</p>
</div>

<?php if (!empty($flash['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($flash['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="utm-layout">

    <!-- Formulário de geração -->
    <section class="utm-form-card">
        <h2>Gerar Novo Link</h2>
        <form action="/admin/utm" method="post" novalidate id="utm-form">
            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

            <div class="form-group">
                <label for="base_url">URL Base *</label>
                <input type="url" id="base_url" name="base_url" required
                       placeholder="https://wmsuplementos.com.br/produto/whey-protein">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="source">Source * <small>(ex: instagram, google, email)</small></label>
                    <input type="text" id="source" name="source" required
                           placeholder="instagram" list="source-suggestions">
                    <datalist id="source-suggestions">
                        <option value="instagram">
                        <option value="facebook">
                        <option value="google">
                        <option value="tiktok">
                        <option value="email">
                        <option value="whatsapp">
                        <option value="youtube">
                    </datalist>
                </div>

                <div class="form-group">
                    <label for="medium">Medium * <small>(ex: cpc, social, email)</small></label>
                    <input type="text" id="medium" name="medium" required
                           placeholder="social" list="medium-suggestions">
                    <datalist id="medium-suggestions">
                        <option value="cpc">
                        <option value="social">
                        <option value="email">
                        <option value="stories">
                        <option value="reels">
                        <option value="organic">
                        <option value="influencer">
                    </datalist>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="campaign">Campaign * <small>(ex: black-friday-2025)</small></label>
                    <input type="text" id="campaign" name="campaign" required
                           placeholder="black-friday-2025">
                </div>

                <div class="form-group">
                    <label for="content">Content <small>(diferencia criativos)</small></label>
                    <input type="text" id="content" name="content"
                           placeholder="banner-240x400">
                </div>
            </div>

            <!-- Preview em tempo real -->
            <div class="utm-preview" id="utm-preview" style="display:none">
                <label>Prévia do link:</label>
                <div class="utm-preview-url" id="utm-preview-url"></div>
            </div>

            <button type="submit" class="btn btn-primary">Gerar Link</button>
        </form>
    </section>

    <!-- Links gerados -->
    <section class="utm-history">
        <h2>Links Gerados <span class="badge"><?= (int)$total ?></span></h2>

        <?php if (empty($links)): ?>
        <p class="empty-state">Nenhum link gerado ainda.</p>
        <?php else: ?>
        <div class="utm-links-list">
            <?php foreach ($links as $link): ?>
            <div class="utm-link-item">
                <div class="utm-link-meta">
                    <span class="utm-source"><?= htmlspecialchars($link['source'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="utm-medium"><?= htmlspecialchars($link['medium'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="utm-campaign"><?= htmlspecialchars($link['campaign'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if ($link['content']): ?>
                    <span class="utm-content"><?= htmlspecialchars($link['content'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                    <span class="utm-date"><?= htmlspecialchars(substr($link['created_at'] ?? '', 0, 10), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="utm-link-url">
                    <input type="text" value="<?= htmlspecialchars($link['full_url'], ENT_QUOTES, 'UTF-8') ?>"
                           readonly class="utm-url-input"
                           onclick="this.select()"
                           title="Clique para selecionar">
                    <button type="button" class="btn btn-sm btn-outline copy-btn"
                            data-url="<?= htmlspecialchars($link['full_url'], ENT_QUOTES, 'UTF-8') ?>">
                        Copiar
                    </button>
                    <form method="post" action="/admin/utm/<?= (int)$link['id'] ?>/excluir"
                          style="display:inline"
                          onsubmit="return confirm('Remover este link?')">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                        <button type="submit" class="btn btn-sm btn-danger">✕</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="?pagina=<?= $p ?>" class="page-link <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </section>
</div>

<script>
(function () {
    const fields = ['base_url', 'source', 'medium', 'campaign', 'content'];
    const preview = document.getElementById('utm-preview');
    const previewUrl = document.getElementById('utm-preview-url');

    function buildUrl() {
        const base = document.getElementById('base_url').value.trim();
        if (!base) { preview.style.display = 'none'; return; }

        const params = new URLSearchParams();
        const source   = document.getElementById('source').value.trim();
        const medium   = document.getElementById('medium').value.trim();
        const campaign = document.getElementById('campaign').value.trim();
        const content  = document.getElementById('content').value.trim();

        if (source)   params.set('utm_source',   source);
        if (medium)   params.set('utm_medium',   medium);
        if (campaign) params.set('utm_campaign', campaign);
        if (content)  params.set('utm_content',  content);

        const sep = base.includes('?') ? '&' : '?';
        const full = params.toString() ? base + sep + params.toString() : base;

        previewUrl.textContent = full;
        preview.style.display = 'block';
    }

    fields.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', buildUrl);
    });

    // Copiar link
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            navigator.clipboard.writeText(btn.dataset.url).then(() => {
                btn.textContent = '✓ Copiado!';
                setTimeout(() => { btn.textContent = 'Copiar'; }, 2000);
            });
        });
    });
})();
</script>
