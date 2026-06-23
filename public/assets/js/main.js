/* Maia Suplementos — main.js */
'use strict';

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
