/**
 * UnysonPlus — GSAP "Scroll Motion" initializer.
 *
 * Reads the clean `data-upw-g*` attributes stamped by shortcode-gsap-helper.php
 * and builds the matching GSAP + ScrollTrigger animation. Loaded only on pages
 * that actually use a GSAP effect (gated server-side by sc_gsap_flag()).
 *
 * Effects: reveal | stagger | parallax | pin | scrub.
 *
 * Failsafe contract: elements that start hidden carry `.upw-g-pending`. If we
 * bail (builder / reduced-motion / GSAP missing) we strip that class so nothing
 * is left invisible. On the normal path, GSAP's fromTo (immediateRender) sets
 * the start-state inline, so we can drop the class immediately after building.
 */
(function () {
    'use strict';

    if (typeof window === 'undefined' || typeof document === 'undefined') return;

    function clearPending(root) {
        (root || document).querySelectorAll('.upw-g-pending').forEach(function (el) {
            el.classList.remove('upw-g-pending');
        });
    }

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
        var gsap = window.gsap;

        // Bail safely: keep content visible, run no motion.
        if (reducedMotion || inBuilder() || !gsap) {
            clearPending();
            return;
        }

        if (gsap.registerPlugin && window.ScrollTrigger) {
            gsap.registerPlugin(window.ScrollTrigger);
        }

        var isMobile = window.innerWidth < 768;

        var EASES = {
            'power1.out': 1, 'power2.out': 1, 'power3.out': 1, 'power4.out': 1,
            'back.out': 1, 'expo.out': 1, 'circ.out': 1, 'sine.out': 1, 'none': 1
        };

        function attr(el, name) { return el.getAttribute(name); }
        function num(v, d) { v = parseFloat(v); return isNaN(v) ? d : v; }
        function ease(v) { return (v && EASES[v]) ? v : 'power2.out'; }
        function startPos(v) {
            return (typeof v === 'string' && /^[a-z]+ [a-z0-9%]+$/i.test(v)) ? v : 'top 85%';
        }

        // Translate a direction + distance into from-vars.
        function offsetFor(dir, dist) {
            var o = {};
            if (dir === 'up') o.y = dist;
            else if (dir === 'down') o.y = -dist;
            else if (dir === 'left') o.x = dist;
            else if (dir === 'right') o.x = -dist;
            return o;
        }

        function reveal(el) {
            var dir = attr(el, 'data-upw-g-dir') || 'up';
            var from = offsetFor(dir, num(attr(el, 'data-upw-g-distance'), 40));
            from.opacity = 0;

            gsap.fromTo(el, from, {
                opacity: 1, x: 0, y: 0,
                duration: num(attr(el, 'data-upw-g-duration'), 0.8),
                delay: num(attr(el, 'data-upw-g-delay'), 0),
                ease: ease(attr(el, 'data-upw-g-ease')),
                scrollTrigger: {
                    trigger: el,
                    start: startPos(attr(el, 'data-upw-g-start')),
                    toggleActions: attr(el, 'data-upw-g-once') === '0'
                        ? 'play none none reverse'
                        : 'play none none none'
                }
            });
            el.classList.remove('upw-g-pending');
        }

        function stagger(el) {
            var kids = Array.prototype.slice.call(el.children);
            if (!kids.length) { el.classList.remove('upw-g-pending'); return; }

            var dir = attr(el, 'data-upw-g-dir') || 'up';
            var from = offsetFor(dir, num(attr(el, 'data-upw-g-distance'), 40));
            from.opacity = 0;

            var fromWhich = attr(el, 'data-upw-g-from') || 'start';
            if (['start', 'end', 'center', 'edges'].indexOf(fromWhich) === -1) fromWhich = 'start';

            gsap.fromTo(kids, from, {
                opacity: 1, x: 0, y: 0,
                duration: num(attr(el, 'data-upw-g-duration'), 0.8),
                ease: ease(attr(el, 'data-upw-g-ease')),
                stagger: { each: num(attr(el, 'data-upw-g-each'), 0.12), from: fromWhich },
                scrollTrigger: {
                    trigger: el,
                    start: startPos(attr(el, 'data-upw-g-start')),
                    toggleActions: 'play none none none'
                }
            });
            el.classList.remove('upw-g-pending');
        }

        function parallax(el) {
            var prop = attr(el, 'data-upw-g-axis') === 'x' ? 'xPercent' : 'yPercent';
            var speed = num(attr(el, 'data-upw-g-speed'), 20);
            var from = {}; from[prop] = -speed;
            var to = { ease: 'none', scrollTrigger: { trigger: el, start: 'top bottom', end: 'bottom top', scrub: true } };
            to[prop] = speed;
            gsap.fromTo(el, from, to);
        }

        function pin(el) {
            var len = num(attr(el, 'data-upw-g-pin-length'), 100);
            window.ScrollTrigger.create({
                trigger: el,
                start: 'top top',
                end: '+=' + len + '%',
                pin: true,
                pinSpacing: true
            });
        }

        function scrub(el) {
            var kind = attr(el, 'data-upw-g-scrub-kind') || 'fade';
            var intensity = num(attr(el, 'data-upw-g-intensity'), 20);
            var from = {}, to = {
                ease: 'none',
                scrollTrigger: {
                    trigger: el,
                    start: startPos(attr(el, 'data-upw-g-start')),
                    end: 'center center',
                    scrub: true
                }
            };

            if (kind === 'scale') {
                from.scale = Math.max(0, 1 - intensity / 100); to.scale = 1;
            } else if (kind === 'rotate') {
                from.rotation = -intensity; to.rotation = 0;
            } else if (kind === 'slide') {
                from.yPercent = intensity; to.yPercent = 0;
            } else { // fade
                from.opacity = 0; to.opacity = 1;
            }

            gsap.fromTo(el, from, to);
            el.classList.remove('upw-g-pending');
        }

        var BUILDERS = {
            reveal: reveal, stagger: stagger, parallax: parallax, pin: pin, scrub: scrub
        };

        function build(el) {
            if (el.__upwG) return;
            el.__upwG = true;

            // Per-element mobile opt-out.
            if (attr(el, 'data-upw-g-mobile') === '0' && isMobile) {
                el.classList.remove('upw-g-pending');
                return;
            }

            var fn = BUILDERS[attr(el, 'data-upw-g')];
            if (fn) { fn(el); } else { el.classList.remove('upw-g-pending'); }
        }

        function scan(root) {
            (root || document).querySelectorAll('[data-upw-g]').forEach(build);
        }

        scan();

        // Pick up content injected late (AJAX, infinite scroll, etc.).
        if ('MutationObserver' in window) {
            var mo = new MutationObserver(function (muts) {
                muts.forEach(function (m) {
                    m.addedNodes && m.addedNodes.forEach(function (n) {
                        if (n.nodeType !== 1) return;
                        if (n.hasAttribute && n.hasAttribute('data-upw-g')) build(n);
                        if (n.querySelectorAll) scan(n);
                    });
                });
            });
            mo.observe(document.body || document.documentElement, { childList: true, subtree: true });
        }

        // Recalculate triggers once images/fonts settle.
        window.addEventListener('load', function () {
            if (window.ScrollTrigger) window.ScrollTrigger.refresh();
        });
    });
})();
