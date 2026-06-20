/**
 * UnysonPlus — Smooth Scroll (Lenis) initializer.
 *
 * Loaded only on pages where the per-page "Smooth Scroll" switch is on.
 * Bridges Lenis into GSAP's ticker + ScrollTrigger when they exist so pinned /
 * scrubbed scroll effects stay perfectly in sync; otherwise runs Lenis on its
 * own RAF loop. Disabled under prefers-reduced-motion and inside the builder.
 */
(function () {
    'use strict';

    if (typeof window === 'undefined' || typeof document === 'undefined') return;

    function inBuilder() {
        return document.body && (
            document.body.classList.contains('fw-builder-active') ||
            document.body.classList.contains('fw-backend-builder') ||
            window.self !== window.top
        );
    }

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    ready(function () {
        if (reducedMotion || inBuilder() || typeof window.Lenis === 'undefined') return;

        var lenis = new window.Lenis({
            duration: 1.1,
            smoothWheel: true
        });

        if (window.gsap && window.gsap.ticker) {
            // Drive Lenis from GSAP's ticker and feed scroll into ScrollTrigger.
            var gsap = window.gsap;
            if (window.ScrollTrigger) {
                lenis.on('scroll', window.ScrollTrigger.update);
            }
            gsap.ticker.add(function (time) { lenis.raf(time * 1000); });
            gsap.ticker.lagSmoothing(0);
        } else {
            // Stand-alone RAF loop when GSAP isn't on the page.
            var raf = function (t) { lenis.raf(t); window.requestAnimationFrame(raf); };
            window.requestAnimationFrame(raf);
        }

        // Smoothly handle same-page anchor links (e.g. CSS-ID jump links).
        document.addEventListener('click', function (e) {
            var a = e.target && e.target.closest ? e.target.closest('a[href^="#"]') : null;
            if (!a) return;
            var id = a.getAttribute('href');
            if (!id || id === '#' || id.length < 2) return;
            var target = document.querySelector(id);
            if (!target) return;
            e.preventDefault();
            lenis.scrollTo(target, { offset: 0 });
        });

        window.upwLenis = lenis; // exposed for debugging / programmatic scrollTo
    });
})();
