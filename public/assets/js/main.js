/* WM Suplementos — main.js */
'use strict';

function appUrl(path) {
    var baseMeta = document.querySelector('meta[name="app-base"]');
    var base = baseMeta && baseMeta.content ? baseMeta.content.replace(/\/$/, '') : '';
    return base + '/' + String(path || '').replace(/^\//, '');
}

/* ── Category carousel (home) ───────────────────────────────── */
(function() {
    var track = document.getElementById('cat-track');
    var prev  = document.getElementById('cat-prev');
    var next  = document.getElementById('cat-next');
    if (!track || !prev || !next) return;
    var cards = track.querySelectorAll('.category-card');
    var idx = 0;
    function visibleCount() {
        return window.innerWidth <= 768 ? 2 : 5;
    }
    function update() {
        var visible = visibleCount();
        var w = track.parentElement.offsetWidth;
        var gap = 12;
        var cardW = (w - gap * (visible - 1)) / visible;
        var maxIdx = Math.max(0, cards.length - visible);
        if (idx > maxIdx) idx = maxIdx;
        var offset = idx * (cardW + gap);
        track.style.transform = 'translateX(-' + offset + 'px)';
        prev.disabled = idx === 0;
        next.disabled = idx >= maxIdx;
    }
    prev.addEventListener('click', function() {
        if (idx > 0) { idx--; update(); }
    });
    next.addEventListener('click', function() {
        if (idx < cards.length - visibleCount()) { idx++; update(); }
    });
    window.addEventListener('resize', update);
    update();
})();

/* ── Mobile menu toggle ─────────────────────────────────────── */
var menuToggle = document.querySelector('.mobile-menu-toggle');
var mobileNav  = document.getElementById('mobile-nav');
if (menuToggle && mobileNav) {
    menuToggle.addEventListener('click', function () {
        var open = mobileNav.classList.toggle('open');
        menuToggle.setAttribute('aria-expanded', open);
    });
}

/* ── Sticky header shadow on scroll ─────────────────────────── */
var siteHeader = document.getElementById('site-header');
if (siteHeader) {
    window.addEventListener('scroll', function () {
        siteHeader.classList.toggle('scrolled', window.scrollY > 10);
    }, { passive: true });
}

/* ── IntersectionObserver for .animate-in elements ──────────── */
if ('IntersectionObserver' in window) {
    var animObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
                animObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.animate-in').forEach(function (el) {
        el.style.animationPlayState = 'paused';
        animObserver.observe(el);
    });
}

/* ── Cart count update ──────────────────────────────────────── */
document.querySelectorAll('.add-to-cart-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        if (typeof fbq === 'function' && form.dataset.productId) {
            fbq('track', 'AddToCart', {
                content_ids: [form.dataset.productId],
                content_name: form.dataset.productName || '',
                content_type: 'product',
                value: parseFloat(form.dataset.productValue || '0') || 0,
                currency: 'BRL'
            });
        }

        var btn = form.querySelector('.add-to-cart-btn');
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Adicionando...';
        }
    });
});

/* ── Flash message auto-dismiss ─────────────────────────────── */
document.querySelectorAll('.alert').forEach(function(el) {
    setTimeout(function() {
        el.style.transition = 'opacity .4s';
        el.style.opacity = '0';
        setTimeout(function() { el.remove(); }, 400);
    }, 5000);
});

/* ── Star rating interactive ─────────────────────────────────── */
var stars = document.querySelectorAll('.star-rating label');
stars.forEach(function(star, i) {
    star.addEventListener('mouseover', function() {
        stars.forEach(function(s, j) {
            s.style.color = j >= stars.length - i - 1 ? '#f59e0b' : '#ccc';
        });
    });
    star.addEventListener('mouseout', function() {
        stars.forEach(function(s) { s.style.color = ''; });
    });
});

/* ── Qty input min=1 ─────────────────────────────────────────── */
document.querySelectorAll('.qty-input').forEach(function(input) {
    input.addEventListener('change', function() {
        if (parseInt(this.value) < 1 || isNaN(parseInt(this.value))) {
            this.value = 1;
        }
    });
});

/* ── Cart quantity controls + remove (AJAX) ─────────────────── */
(function() {
    var cartLayout = document.querySelector('.cart-page, .cart-layout');
    if (!cartLayout) return;

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.content) return meta.content;
        var hidden = document.getElementById('csrf-token');
        return hidden ? hidden.value : '';
    }

    function money(value) {
        return (Math.round(value * 100) / 100)
            .toFixed(2)
            .replace('.', ',')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function submitCartUpdate(cartKey, qty, row) {
        var body = new URLSearchParams();
        body.set('csrf_token', csrfToken());
        body.set('cart_key', cartKey);
        body.set('quantity', qty);
        var url = appUrl(qty <= 0 ? '/carrinho/remover' : '/carrinho/atualizar');

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body.toString()
        })
        .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
        .then(function(res) {
            if (!res.ok || res.data.error) {
                if (res.data && res.data.error) { alert(res.data.error); }
                return;
            }
            var d = res.data;
            if (qty <= 0 && row) {
                row.parentNode.removeChild(row);
                if (!document.querySelector('.cart-item')) { window.location.reload(); return; }
            } else if (row) {
                var input = row.querySelector('.qty-input');
                var price = parseFloat((input.getAttribute('data-price') || '').replace(/\./g, '').replace(',', '.'));
                if (!isNaN(price)) {
                    var sub = row.querySelector('.item-subtotal');
                    if (sub) sub.textContent = 'R$ ' + money(price * qty);
                }
            }
            var setText = function(id, txt) { var el = document.getElementById(id); if (el) el.textContent = txt; };
            if (typeof d.subtotal !== 'undefined') setText('cart-subtotal', 'R$ ' + money(d.subtotal));
            if (typeof d.discount !== 'undefined') setText('cart-discount', '- R$ ' + money(d.discount));
            if (typeof d.total    !== 'undefined') setText('cart-total', 'R$ ' + money(d.total));
            var countEl = document.getElementById('cart-count');
            if (countEl && typeof d.count !== 'undefined') countEl.textContent = d.count;
        })
        .catch(function() {});
    }

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.qty-plus, .qty-minus');
        if (!btn) return;
        var row = btn.closest('.cart-item');
        if (!row) return;
        var input = row.querySelector('.qty-input');
        if (!input) return;
        var val = parseInt(input.value) || 1;
        if (btn.classList.contains('qty-plus')) val++;
        else val = Math.max(1, val - 1);
        input.value = val;
        submitCartUpdate(row.dataset.cartKey, val, row);
    });

    document.addEventListener('change', function(e) {
        var input = e.target.closest('.qty-input');
        if (!input) return;
        var row = input.closest('.cart-item');
        if (!row) return;
        var val = Math.max(1, parseInt(input.value) || 1);
        input.value = val;
        submitCartUpdate(row.dataset.cartKey, val, row);
    });

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.remove-btn');
        if (!btn) return;
        var row = btn.closest('.cart-item');
        if (!row) return;
        submitCartUpdate(row.dataset.cartKey, 0, row);
    });
})();

/* ── Confirm delete ──────────────────────────────────────────── */
document.querySelectorAll('[data-confirm]').forEach(function(el) {
    el.addEventListener('click', function(e) {
        if (!confirm(this.dataset.confirm || 'Tem certeza?')) {
            e.preventDefault();
        }
    });
});

/* ── CEP autocomplete (via ViaCEP) ──────────────────────────── */
/* Category carousel controls */
document.querySelectorAll('.category-carousel').forEach(function(carousel) {
    var track = carousel.querySelector('[data-category-carousel]');
    var prev = carousel.querySelector('[data-category-prev]');
    var next = carousel.querySelector('[data-category-next]');
    if (!track || !prev || !next) return;

    var scrollCategories = function(direction) {
        var card = track.querySelector('.category-card');
        var distance = card ? card.getBoundingClientRect().width + 16 : 220;
        track.scrollBy({ left: distance * direction, behavior: 'smooth' });
    };

    prev.addEventListener('click', function() { scrollCategories(-1); });
    next.addEventListener('click', function() { scrollCategories(1); });
});

var cepInput = document.getElementById('zip_code');
if (cepInput) {
    cepInput.addEventListener('blur', function() {
        var cep = this.value.replace(/\D/g, '');
        if (cep.length !== 8) return;

        fetch('https://viacep.com.br/ws/' + cep + '/json/')
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.erro) return;
                var set = function(id, val) {
                    var el = document.getElementById(id);
                    if (el && !el.value) el.value = val || '';
                };
                set('address',      d.logradouro);
                set('neighborhood', d.bairro);
                set('city',         d.localidade);
                set('state',        d.uf);
                var num = document.getElementById('address_number');
                if (num) num.focus();
            })
            .catch(function() {});
    });

    /* Máscara CEP */
    cepInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').replace(/^(\d{5})(\d)/, '$1-$2').substring(0, 9);
    });
}

/* ── Phone mask ──────────────────────────────────────────────── */
var phoneInput = document.getElementById('customer_phone') || document.getElementById('phone');
if (phoneInput) {
    phoneInput.addEventListener('input', function() {
        var v = this.value.replace(/\D/g, '');
        if (v.length <= 10) {
            v = v.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3');
        } else {
            v = v.replace(/^(\d{2})(\d{5})(\d{0,4})$/, '($1) $2-$3');
        }
        this.value = v.substring(0, 15);
    });
}

/* Card installments visibility */
(function() {
    var installments = document.querySelector('[data-card-installments]');
    if (!installments) return;
    var paymentInputs = document.querySelectorAll('input[name="payment_method"]');
    var toggleInstallments = function() {
        var selected = document.querySelector('input[name="payment_method"]:checked');
        installments.hidden = !(selected && selected.value === 'cartao');
    };
    paymentInputs.forEach(function(input) {
        input.addEventListener('change', toggleInstallments);
    });
    toggleInstallments();
})();

/* Copy text buttons */
document.querySelectorAll('[data-copy-text]').forEach(function(button) {
    button.addEventListener('click', function() {
        var text = button.getAttribute('data-copy-text') || '';
        var done = function() {
            var old = button.textContent;
            button.textContent = 'Copiado';
            setTimeout(function() { button.textContent = old; }, 1600);
        };

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(done).catch(function() {});
            return;
        }

        var tmp = document.createElement('textarea');
        tmp.value = text;
        document.body.appendChild(tmp);
        tmp.select();
        try { document.execCommand('copy'); done(); } catch (e) {}
        tmp.remove();
    });
});

/* LGPD cookie consent */
var cookieConsent = document.querySelector('[data-cookie-consent]');
if (cookieConsent) {
    var consentKey = 'maia_cookie_consent';
    var savedConsent = localStorage.getItem(consentKey);
    if (!savedConsent) {
        cookieConsent.hidden = false;
        requestAnimationFrame(function() {
            cookieConsent.classList.add('is-visible');
        });
    }

    cookieConsent.querySelectorAll('[data-cookie-choice]').forEach(function(button) {
        button.addEventListener('click', function() {
            localStorage.setItem(consentKey, this.dataset.cookieChoice || 'necessary');
            cookieConsent.classList.remove('is-visible');
            setTimeout(function() {
                cookieConsent.hidden = true;
            }, 220);
        });
    });
}

/* ── Product page qty controls ── */
(function() {
    var form = document.querySelector('.add-to-cart-form');
    if (!form) return;
    var input = form.querySelector('.qty-input-prod');
    if (!input) return;
    form.querySelector('.qty-minus-prod').addEventListener('click', function() {
        var v = parseInt(input.value) || 1;
        if (v > 1) input.value = v - 1;
    });
    form.querySelector('.qty-plus-prod').addEventListener('click', function() {
        var v = parseInt(input.value) || 1;
        var max = parseInt(input.getAttribute('max')) || 99;
        if (v < max) input.value = v + 1;
    });
})();

/* Product detail tabs */
document.querySelectorAll('[data-tabs]').forEach(function(tabs) {
    var buttons = tabs.querySelectorAll('[data-tab-target]');
    var panels = tabs.querySelectorAll('[data-tab-panel]');

    buttons.forEach(function(button) {
        button.addEventListener('click', function() {
            var target = button.dataset.tabTarget;
            buttons.forEach(function(btn) {
                btn.classList.toggle('is-active', btn === button);
            });
            panels.forEach(function(panel) {
                panel.classList.toggle('is-active', panel.dataset.tabPanel === target);
            });
        });
    });
});

/* Campaign popup */
(function() {
    var popup = document.querySelector('[data-campaign-popup]');
    if (!popup) return;

    var id = popup.getAttribute('data-campaign-id') || 'default';
    var showOnce = popup.getAttribute('data-show-once') === '1';
    var key = 'maia_popup_closed_' + id;

    if (showOnce && localStorage.getItem(key)) {
        popup.remove();
        return;
    }

    function closePopup() {
        if (showOnce) {
            localStorage.setItem(key, '1');
        }
        popup.classList.remove('is-visible');
        setTimeout(function() {
            popup.hidden = true;
        }, 180);
    }

    setTimeout(function() {
        popup.hidden = false;
        requestAnimationFrame(function() {
            popup.classList.add('is-visible');
        });
    }, 650);

    popup.querySelectorAll('[data-popup-close]').forEach(function(button) {
        button.addEventListener('click', closePopup);
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && !popup.hidden) {
            closePopup();
        }
    });

    popup.querySelectorAll('[data-copy-coupon]').forEach(function(button) {
        button.addEventListener('click', function() {
            var code = button.getAttribute('data-copy-coupon') || '';
            var done = function() {
                var old = button.textContent;
                button.textContent = 'Copiado';
                setTimeout(function() { button.textContent = old; }, 1600);
            };

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(code).then(done).catch(function() {});
                return;
            }

            var tmp = document.createElement('textarea');
            tmp.value = code;
            document.body.appendChild(tmp);
            tmp.select();
            try { document.execCommand('copy'); done(); } catch (e) {}
            tmp.remove();
        });
    });
})();
