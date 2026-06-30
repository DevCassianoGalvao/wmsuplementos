/* Maia Suplementos - placeholder para imagens ausentes */
'use strict';

(function() {
    var PLACEHOLDER_SVG = 'data:image/svg+xml,' + encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">' +
        '<rect width="400" height="400" fill="#1a1a1a"/>' +
        '<rect x="140" y="140" width="120" height="80" rx="8" fill="none" stroke="#3a3a3a" stroke-width="3"/>' +
        '<circle cx="200" cy="168" r="12" fill="#3a3a3a"/>' +
        '<path d="M145 220 l35-40 25 28 20-22 35 34" fill="none" stroke="#3a3a3a" stroke-width="3" stroke-linejoin="round"/>' +
        '</svg>'
    );

    function applyPlaceholder(img) {
        if (img.src === PLACEHOLDER_SVG) return;
        var cur = img.getAttribute('src') || '';
        if (cur.indexOf('data:') === 0) return;
        img.src = PLACEHOLDER_SVG;
    }

    function checkAndApply(img) {
        var src = img.getAttribute('src') || '';
        // Sem src ou src vazio → placeholder
        if (!src) { applyPlaceholder(img); return; }
        // Já é placeholder
        if (src.indexOf('data:') === 0) return;
        // Imagem já carregou e falhou (complete=true, naturalWidth=0)
        if (img.complete && img.naturalWidth === 0) { applyPlaceholder(img); return; }
        // Caso ainda carregando: registra listener
        img.addEventListener('error', function handler() {
            img.removeEventListener('error', handler);
            applyPlaceholder(img);
        });
    }

    // Roda imediatamente em qualquer ponto do carregamento
    function init() {
        var imgs = document.querySelectorAll(
            '.product-card__image-wrap img, .product-img, ' +
            '.product-gallery img, .gallery-main img, .gallery-thumbs img'
        );
        imgs.forEach(checkAndApply);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
