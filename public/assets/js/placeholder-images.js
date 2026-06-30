/* Maia Suplementos - temporary product placeholders by category */
'use strict';

const CATEGORY_PLACEHOLDER = {
    'proteinas': 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?w=400&q=80',
    'creatina': 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&q=80',
    'pre-treino': 'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?w=400&q=80',
    'aminoacidos': 'https://images.unsplash.com/photo-1579722820308-d74e571900a9?w=400&q=80',
    'vitaminas': 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&q=80',
    'hipercaloricos': 'https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?w=400&q=80',
    'termogenicos': 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=400&q=80',
    'barras': 'https://images.unsplash.com/photo-1622484212850-c0ce8d21a1fc?w=400&q=80',
    'default': 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=400&q=80'
};

document.addEventListener('DOMContentLoaded', function() {
    const productImages = document.querySelectorAll('.product-card__image-wrap img, .product-img, .product-gallery img');

    productImages.forEach(function(img) {
        function applyPlaceholder() {
            const card = img.closest('[data-category]');
            const category = card ? card.dataset.category.toLowerCase().replace(/\s/g, '-') : 'default';
            const key = Object.keys(CATEGORY_PLACEHOLDER).find(function(item) {
                return category.includes(item);
            }) || 'default';

            img.src = CATEGORY_PLACEHOLDER[key];
            img.alt = img.alt || 'Produto ' + category;
        }

        const src = img.getAttribute('src') || '';
        if (!src || src.includes('placeholder')) {
            applyPlaceholder();
        }

        img.addEventListener('error', applyPlaceholder);
    });
});
