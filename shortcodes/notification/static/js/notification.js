(function () {
    'use strict';

    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function persistKey(alert) {
        return alert && alert.getAttribute ? alert.getAttribute('data-persist-key') : null;
    }

    function isDismissed(key) {
        if (!key) return false;
        try { return window.localStorage.getItem(key) === '1'; } catch (e) { return false; }
    }

    function rememberDismissed(alert) {
        var key = persistKey(alert);
        if (!key) return;
        try { window.localStorage.setItem(key, '1'); } catch (e) {}
    }

    function initPersisted() {
        var alerts = document.querySelectorAll('.alert[data-persist-key]');
        for (var i = 0; i < alerts.length; i++) {
            if (isDismissed(persistKey(alerts[i]))) {
                removeAlert(alerts[i]);
            }
        }
    }

    function dismiss(alert) {
        if (!alert || alert.dataset.dismissing === '1') {
            return;
        }
        alert.dataset.dismissing = '1';
        rememberDismissed(alert);

        if (reduceMotion) {
            removeAlert(alert);
            return;
        }

        alert.classList.add('is-dismissing');

        var removed = false;
        var done = function () {
            if (removed) return;
            removed = true;
            removeAlert(alert);
        };

        alert.addEventListener('transitionend', done, { once: true });
        // Fallback in case transitionend never fires (no transition, hidden, etc.)
        setTimeout(done, 300);
    }

    function removeAlert(alert) {
        if (alert && alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }

    function onClick(event) {
        var target = event.target;
        if (!target) return;
        var btn = target.closest ? target.closest('.alert__close') : null;
        if (!btn) return;
        var alert = btn.closest('.alert');
        if (!alert) return;
        event.preventDefault();
        dismiss(alert);
    }

    function initAutoDismiss() {
        var alerts = document.querySelectorAll('.alert[data-auto-dismiss]');
        for (var i = 0; i < alerts.length; i++) {
            var alert = alerts[i];
            var secs = parseInt(alert.getAttribute('data-auto-dismiss'), 10);
            if (!isFinite(secs) || secs <= 0) continue;
            (function (el, ms) {
                setTimeout(function () { dismiss(el); }, ms);
            })(alert, secs * 1000);
        }
    }

    function init() {
        initPersisted();
        document.addEventListener('click', onClick, false);
        initAutoDismiss();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
