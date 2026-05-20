(function () {
    'use strict';

    if (typeof window === 'undefined' || typeof document === 'undefined') return;

    var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reducedMotion) {
        document.querySelectorAll('.sc-anim-pending').forEach(function (el) {
            el.classList.remove('sc-anim-pending');
        });
        return;
    }

    function inBuilder() {
        return document.body && (
            document.body.classList.contains('fw-builder-active') ||
            document.body.classList.contains('fw-backend-builder') ||
            window.self !== window.top
        );
    }

    if (inBuilder()) {
        document.querySelectorAll('.sc-anim-pending').forEach(function (el) {
            el.classList.remove('sc-anim-pending');
        });
        return;
    }

    if (!('IntersectionObserver' in window)) {
        document.querySelectorAll('[data-sc-anim]').forEach(function (el) {
            applyAnimation(el);
        });
        return;
    }

    function applyAnimation(el) {
        var classes = (el.getAttribute('data-sc-anim') || '').split(/\s+/).filter(Boolean);
        if (!classes.length) return;
        classes.forEach(function (c) { el.classList.add(c); });
        el.classList.remove('sc-anim-pending');
    }

    function resetForReplay(el) {
        var classes = (el.getAttribute('data-sc-anim') || '').split(/\s+/).filter(Boolean);
        classes.forEach(function (c) { el.classList.remove(c); });
        el.classList.add('sc-anim-pending');
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            var el = entry.target;
            applyAnimation(el);

            if (el.getAttribute('data-sc-anim-replay') === '1') {
                var onEnd = function () {
                    el.removeEventListener('animationend', onEnd);
                    resetForReplay(el);
                };
                el.addEventListener('animationend', onEnd);
            } else {
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -10% 0px' });

    function scan(root) {
        (root || document).querySelectorAll('[data-sc-anim]').forEach(function (el) {
            if (!el.__scAnimObserved) {
                el.__scAnimObserved = true;
                observer.observe(el);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { scan(); });
    } else {
        scan();
    }

    if ('MutationObserver' in window) {
        var mo = new MutationObserver(function (muts) {
            muts.forEach(function (m) {
                m.addedNodes && m.addedNodes.forEach(function (n) {
                    if (n.nodeType !== 1) return;
                    if (n.hasAttribute && n.hasAttribute('data-sc-anim')) {
                        if (!n.__scAnimObserved) {
                            n.__scAnimObserved = true;
                            observer.observe(n);
                        }
                    }
                    if (n.querySelectorAll) scan(n);
                });
            });
        });
        mo.observe(document.body || document.documentElement, { childList: true, subtree: true });
    }
})();
