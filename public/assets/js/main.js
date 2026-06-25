/* Maia Suplementos — main.js */
'use strict';

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

/* Boleto address fields */
var boletoAddress = document.querySelector('[data-boleto-address]');
if (boletoAddress) {
    var paymentInputs = document.querySelectorAll('input[name="payment_method"]');
    var addressFields = boletoAddress.querySelectorAll('input');
    var toggleBoletoAddress = function() {
        var selected = document.querySelector('input[name="payment_method"]:checked');
        var isBoleto = selected && selected.value === 'boleto';
        boletoAddress.hidden = !isBoleto;
        addressFields.forEach(function(input) {
            input.required = !!isBoleto;
        });
    };

    paymentInputs.forEach(function(input) {
        input.addEventListener('change', toggleBoletoAddress);
    });
    toggleBoletoAddress();
}

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
