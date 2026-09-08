/* ================================================================
   RENTWHEELS — Sistema compartido de notificaciones y modales
   showAlert   → toast en esquina inferior derecha
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
            toast.classList.add('rw-toast--hide');
            clearTimeout(timer);
            setTimeout(function () {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 250);
        }
        const timer = setTimeout(dismiss, total);
        close.addEventListener('click', dismiss);
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