/* Maia Suplementos - placeholder para imagens ausentes */
'use strict';

(function() {
    var PLACEHOLDER_SVG = 'data:image/svg+xml,' + encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">' +
        '<rect width="400" height="400" fill="#1a1a1a"/>' +
        '<rect x="140" y="140" width="120" height="80" rx="8" fill="none" stroke="#444" stroke-width="3"/>' +
        '<circle cx="200" cy="168" r="12" fill="#444"/>' +
        '<path d="M145 220 l35-40 25 28 20-22 35 34" fill="none" stroke="#444" stroke-width="3" stroke-linejoin="round"/>' +
        '</svg>'
    );

    function applyPlaceholder(img) {
        var cur = img.getAttribute('src') || '';
        if (!cur || cur === PLACEHOLDER_SVG || cur.indexOf('data:') === 0) return;
        img.src = PLACEHOLDER_SVG;
        img.alt = img.alt || 'Imagem indisponível';
    }

    document.addEventListener('DOMContentLoaded', function() {
        var imgs = document.querySelectorAll(
            '.product-card__image-wrap img, .product-img, .product-gallery img, .gallery-main img, .gallery-thumbs img'
        );

        imgs.forEach(function(img) {
            var src = img.getAttribute('src') || '';
            if (!src || src.includes('placeholder')) {
                applyPlaceholder(img);
            }
            img.addEventListener('error', function() { applyPlaceholder(img); });
        });
    });
})();
