/* Maia Suplementos — admin.js */
'use strict';

/* ── Sidebar toggle (mobile) ─────────────────────────────────── */
var toggleBtn = document.querySelector('.sidebar-toggle');
var sidebar   = document.querySelector('.admin-sidebar');
if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('open');
    });
    document.addEventListener('click', function(e) {
        if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    });
}

document.querySelectorAll('.nav-group__label').forEach(function(button) {
    button.addEventListener('click', function() {
        var group = button.closest('.nav-group');
        if (!group) return;

        group.classList.toggle('is-open');
        button.setAttribute('aria-expanded', group.classList.contains('is-open') ? 'true' : 'false');
    });
});

var addComboItem = document.querySelector('[data-add-combo-item]');
var comboEditor = document.getElementById('combo-items-editor');
var comboTemplate = document.getElementById('combo-item-template');
if (addComboItem && comboEditor && comboTemplate) {
    addComboItem.addEventListener('click', function() {
        comboEditor.appendChild(comboTemplate.content.cloneNode(true));
    });
}

/* ── Flash message auto-dismiss ─────────────────────────────── */
document.querySelectorAll('.alert').forEach(function(el) {
    setTimeout(function() {
        el.style.transition = 'opacity .4s';
        el.style.opacity = '0';
        setTimeout(function() { el.remove(); }, 400);
    }, 4000);
});

/* ── Confirm dialogs ─────────────────────────────────────────── */
document.querySelectorAll('[data-confirm]').forEach(function(el) {
    el.addEventListener('click', function(e) {
        if (!confirm(this.dataset.confirm || 'Tem certeza?')) {
            e.preventDefault();
        }
    });
});

/* ── Slug auto-generate from name ────────────────────────────── */
var nameInput = document.getElementById('name');
var slugInput = document.getElementById('slug');
if (nameInput && slugInput && slugInput.value === '') {
    nameInput.addEventListener('input', function() {
        slugInput.value = this.value
            .toLowerCase()
            .normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    });
}

/* ── Image preview before upload ─────────────────────────────── */
var imageInput = document.querySelector('input[type="file"][accept*="image"]');
var previewContainer = document.getElementById('image-preview');
if (imageInput && previewContainer) {
    imageInput.addEventListener('change', function() {
        previewContainer.innerHTML = '';
        Array.from(this.files).forEach(function(file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = document.createElement('img');
                img.src = e.target.result;
                img.style.cssText = 'width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid rgba(255,255,255,0.08);';
                previewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    });
}

/* ── Table inline search ─────────────────────────────────────── */
var tableSearch = document.getElementById('table-search');
if (tableSearch) {
    tableSearch.addEventListener('input', function() {
        var q = this.value.toLowerCase();
        document.querySelectorAll('.admin-table tbody tr').forEach(function(row) {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
}

/* ── Toggle switch label sync ────────────────────────────────── */
document.querySelectorAll('.toggle-switch input[type=checkbox]').forEach(function(cb) {
    var label = cb.closest('.script-card')
              && cb.closest('.script-card').querySelector('.toggle-status');
    if (label) {
        var update = function() { label.textContent = cb.checked ? 'Ativo' : 'Inativo'; };
        cb.addEventListener('change', update);
        update();
    }
});

/* ── UTM builder live preview ────────────────────────────────── */
var utmForm    = document.getElementById('utm-form');
var utmPreview = document.getElementById('utm-preview');
if (utmForm && utmPreview) {
    var utmFields = ['base_url','source','medium','campaign','content'];
    function updateUtmPreview() {
        var base   = document.getElementById('base_url').value.trim();
        var params = {};
        ['source','medium','campaign','content'].forEach(function(f) {
            var el = document.getElementById(f);
            if (el && el.value.trim()) params['utm_' + f] = el.value.trim();
        });
        if (!base) { utmPreview.textContent = 'Preencha a URL base...'; return; }
        var query = Object.keys(params).map(function(k) {
            return k + '=' + encodeURIComponent(params[k]);
        }).join('&');
        utmPreview.textContent = base + (query ? (base.includes('?') ? '&' : '?') + query : '');
    }
    utmFields.forEach(function(f) {
        var el = document.getElementById(f);
        if (el) el.addEventListener('input', updateUtmPreview);
    });

    var copyBtn = document.getElementById('copy-utm');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            var url = utmPreview.textContent;
            if (!url || url.startsWith('Preencha')) return;
            navigator.clipboard.writeText(url).then(function() {
                copyBtn.textContent = 'Copiado!';
                setTimeout(function() { copyBtn.textContent = 'Copiar'; }, 2000);
            });
        });
    }
}
