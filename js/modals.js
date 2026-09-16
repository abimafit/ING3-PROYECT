/* ================================================================
   RENTWHEELS — Sistema compartido de notificaciones y modales
   showAlert   → toast en esquina inferior izquierda
   showConfirm → modal centrado de confirmación (reemplaza confirm())
   showModal   → modal genérico
   handleAjaxError → muestra error del backend como toast
   ================================================================ */
(function (window, document) {
    'use strict';

    function createElement(tag, cls, text) {
        const el = document.createElement(tag);
        if (cls) el.className = cls;
        if (text) el.textContent = text;
        return el;
    }

    /* ---------------- TOASTS ---------------- */
    function getToastContainer() {
        let c = document.getElementById('rw-toast-container');
        if (!c) {
            c = createElement('div', 'rw-toast-container');
            c.id = 'rw-toast-container';
            document.body.appendChild(c);
        }
        return c;
    }

    function showToast(message, type, title, duration) {
        const container = getToastContainer();
        const total = duration || 5000;
        const key = (type || 'info') + '|' + message;

        const store = container.__rwToasts || (container.__rwToasts = new Map());

        if (store.has(key)) {
            const entry = store.get(key);
            if (entry && entry.toast.isConnected) {
                clearTimeout(entry.timer);
                entry.toast.classList.remove('rw-toast--hide');
                const bar = entry.toast.querySelector('.rw-toast__bar');
                if (bar) {
                    bar.style.animation = 'none';
                    void bar.offsetWidth;
                    bar.style.animation = '';
                    bar.style.animationDuration = total + 'ms';
                }
                entry.timer = setTimeout(function () { entry.dismiss(); }, total);
                return;
            }
            store.delete(key);
        }

        const bar = createElement('div', 'rw-toast__bar');
        bar.style.animationDuration = total + 'ms';

        const content = createElement('div', 'rw-toast__content');
        if (title) content.appendChild(createElement('div', 'rw-toast__title', title));
        const msgEl = createElement('div', 'rw-toast__message');
        msgEl.textContent = message;
        content.appendChild(msgEl);

        const close = createElement('button', 'rw-toast__close');
        close.type = 'button';
        close.setAttribute('aria-label', 'Cerrar notificación');
        close.innerHTML = '&times;';

        const toast = createElement('div', 'rw-toast rw-toast--' + (type || 'info'));
        toast.appendChild(bar);
        toast.appendChild(content);
        toast.appendChild(close);
        container.appendChild(toast);

        let dismissed = false;
        function dismiss() {
            if (dismissed) return;
            dismissed = true;
            clearTimeout(entry.timer);
            if (store.get(key) === entry) store.delete(key);
            toast.classList.add('rw-toast--hide');
            setTimeout(function () {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 250);
        }
        const entry = { toast: toast, dismiss: dismiss, timer: null };
        entry.timer = setTimeout(dismiss, total);
        close.addEventListener('click', dismiss);
        store.set(key, entry);
    }

    /* ---------------- MODAL ---------------- */
    function showModal(options) {
        const config = {
            icon: options.icon || '',
            title: options.title || 'Aviso',
            message: options.message || '',
            type: options.type || 'info',
            onConfirm: options.onConfirm || null,
            onCancel: options.onCancel || null,
            onClose: options.onClose || null,
            confirmText: options.confirmText || 'Aceptar',
            cancelText: options.cancelText || 'Cancelar'
        };

        const typeMap = {
            success: { icon: '<svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="#16A34A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>', title: 'Éxito' },
            error: { icon: '<svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="#DC2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>', title: 'Error' },
            warning: { icon: '<svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="#D97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>', title: 'Advertencia' },
            info: { icon: '<svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="#C61A30" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>', title: 'Información' },
            confirm: { icon: '<svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="#C61A30" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>', title: 'Confirmar' }
        };
        const tm = typeMap[config.type];
        if (tm) {
            if (!config.icon) config.icon = tm.icon;
            if (!config.title) config.title = tm.title;
        }

        const old = document.getElementById('rw-modal');
        if (old && old.parentNode) old.parentNode.removeChild(old);

        const modal = createElement('div', 'rw-modal');
        modal.id = 'rw-modal';

        const overlay = createElement('div', 'rw-modal__overlay');
        overlay.addEventListener('click', function () { close(); });

        const dialog = createElement('div', 'rw-modal__dialog rw-modal__dialog--' + config.type);
        const iconEl = createElement('div', 'rw-modal__icon');
        iconEl.innerHTML = config.icon;
        dialog.appendChild(iconEl);
        dialog.appendChild(createElement('h3', 'rw-modal__title', config.title));
        dialog.appendChild(createElement('p', 'rw-modal__message', config.message));

        const actions = createElement('div', 'rw-modal__actions');
        if (config.type === 'confirm') {
            const btnCancel = createElement('button', 'btn btn-outline', config.cancelText);
            btnCancel.onclick = function () { close(); if (config.onCancel) config.onCancel(); };
            const btnConfirm = createElement('button', 'btn btn-primary', config.confirmText);
            btnConfirm.onclick = function () { close(); if (config.onConfirm) config.onConfirm(); };
            actions.appendChild(btnCancel);
            actions.appendChild(btnConfirm);
        } else {
            const btnClose = createElement('button', 'btn btn-primary', 'Cerrar');
            btnClose.onclick = function () { close(); if (config.onClose) config.onClose(); };
            actions.appendChild(btnClose);
        }
        dialog.appendChild(actions);
        modal.appendChild(overlay);
        modal.appendChild(dialog);
        document.body.appendChild(modal);

        function close() {
            modal.classList.add('rw-modal--hide');
            document.removeEventListener('keydown', onKey);
            setTimeout(function () {
                if (modal.parentNode) modal.parentNode.removeChild(modal);
            }, 180);
        }
        function onKey(e) {
            if (e.key === 'Escape') close();
        }
        setTimeout(function () { document.addEventListener('keydown', onKey); }, 50);
    }

    /* ---------------- API pública ---------------- */
    function showAlert(message, type, title) {
        showToast(message, type || 'info', title);
    }

    function showConfirm(message, onConfirm, onCancel) {
        showModal({
            message: message,
            type: 'confirm',
            onConfirm: onConfirm || function () {},
            onCancel: onCancel || function () {},
            confirmText: 'Sí',
            cancelText: 'No'
        });
    }

    function handleAjaxError(xhr) {
        let msg = 'Error de conexión con el servidor.';
        try {
            const resp = JSON.parse(xhr.responseText);
            if (resp.error) msg = resp.error;
            else if (resp.message) msg = resp.message;
        } catch (e) {}
        showAlert(msg, 'error');
    }

    window.showAlert = showAlert;
    window.showConfirm = showConfirm;
    window.showModal = showModal;
    window.handleAjaxError = handleAjaxError;
    window.showToast = showToast;
})(window, document);

/* ================================================================
   RONDA 10 — rwAnim: animaciones de contenido
   - Reveal en cascada ([data-rw-reveal]) al cargar / hacer scroll
   - Entrada del panel (.dashboard-content .content-card) vía CSS
   - Contador animado de .stat-number
   Requiere: html.rw-js (agregado en el <head> de cada página).
   ================================================================ */
(function (window, document) {
    'use strict';

    var REDUCED = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)').matches : false;

    var GRUPOS_REVEAL = [
        '.about-card',
        '.features-grid > .feature-item',
        '.stat-card',
        '.admin-stats-grid > .admin-stat-card',
        '.chart-panel',
        '.chart-row > *',
        '.admin-section-title',
        '.admin-table',
        '.auth-card',
        '.faq-hero',
        '.faq-section.active > .faq-item'
    ];

    function setIndex(nodes) {
        Array.prototype.forEach.call(nodes, function (el, i) {
            el.style.setProperty('--i', Math.min(i, 7));
        });
    }

    function marcarReveal() {
        GRUPOS_REVEAL.forEach(function (sel) {
            var nodes = document.querySelectorAll(sel);
            setIndex(nodes);
            Array.prototype.forEach.call(nodes, function (el) {
                if (!el.hasAttribute('data-rw-reveal')) {
                    el.setAttribute('data-rw-reveal', '');
                }
            });
        });
        setIndex(document.querySelectorAll('.admin-table tbody tr'));
    }

    function contarNumero(el) {
        var texto = el.textContent.trim();
        var esDinero = texto.charAt(0) === '$';
        var bruto = texto.replace(/[^0-9.-]/g, '');
        var fin = parseFloat(bruto);
        if (isNaN(fin)) return;
        var decimales = texto.indexOf('.') !== -1 ? (texto.split('.')[1].match(/\d/g) || []).length : 0;
        var dur = 800;

        if (REDUCED) return;

        var t0 = null;
        function paso(ts) {
            if (t0 === null) t0 = ts;
            var p = Math.min((ts - t0) / dur, 1);
            var eased = 1 - Math.pow(1 - p, 3);
            var actual = eased * fin;
            var txt = Number(actual.toFixed(decimales)).toLocaleString('en-US', {
                minimumFractionDigits: decimales,
                maximumFractionDigits: decimales
            });
            el.textContent = (esDinero ? '$' : '') + txt;
            if (p < 1) requestAnimationFrame(paso);
        }
        requestAnimationFrame(paso);
    }

    function revelar(el) {
        if (el.classList.contains('rw-in')) return;
        el.classList.add('rw-in');
        var num = el.querySelector('.stat-number');
        if (num) contarNumero(num);
    }

    function iniciar() {
        marcarReveal();
        var items = document.querySelectorAll('[data-rw-reveal]');

        if (REDUCED || !('IntersectionObserver' in window)) {
            Array.prototype.forEach.call(items, revelar);
            return;
        }

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    revelar(entry.target);
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -25px 0px' });

        Array.prototype.forEach.call(items, function (el) {
            io.observe(el);
        });

        window.setTimeout(function () {
            Array.prototype.forEach.call(items, revelar);
        }, 2000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
})(window, document);