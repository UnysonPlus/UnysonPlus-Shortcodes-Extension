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

    function play(el) {
        var classes = (el.getAttribute('data-sc-anim') || '').split(/\s+/).filter(Boolean);
        if (!classes.length) return;
        classes.forEach(function (c) { el.classList.add(c); });
        el.classList.remove('sc-anim-pending');
    }

    // Strip the animate classes. keepHidden re-adds sc-anim-pending so the "view" replay hides the
    // element until it re-enters the viewport; for click/hover the element stays visible so it can
    // simply be re-triggered.
    function reset(el, keepHidden) {
        var classes = (el.getAttribute('data-sc-anim') || '').split(/\s+/).filter(Boolean);
        classes.forEach(function (c) { el.classList.remove(c); });
        if (keepHidden) { el.classList.add('sc-anim-pending'); }
    }

    // Play once, then on end strip the classes so the SAME element can replay on the next event.
    // __scAnimBusy ignores repeat triggers until the current run finishes.
    function playReplayable(el) {
        if (el.__scAnimBusy) { return; }
        el.__scAnimBusy = true;
        play(el);
        var onEnd = function () {
            el.removeEventListener('animationend', onEnd);
            reset(el, false);
            el.__scAnimBusy = false;
        };
        el.addEventListener('animationend', onEnd);
    }

    var observer = ('IntersectionObserver' in window) ? new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            var el = entry.target;

            // An element that ALSO has a click/hover trigger must end with its animate classes
            // removed (so the interaction can cleanly re-trigger). Play it replayably, then stop
            // observing — the interaction handlers own subsequent replays.
            if (el.__scAnimInteractive) {
                playReplayable(el);
                observer.unobserve(el);
                return;
            }

            play(el);
            if (el.getAttribute('data-sc-anim-replay') === '1') {
                var onEnd = function () {
                    el.removeEventListener('animationend', onEnd);
                    reset(el, true);
                };
                el.addEventListener('animationend', onEnd);
            } else {
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -10% 0px' }) : null;

    // Per-child stagger: a COLLECTION wrapper (Gallery, …) carries data-sc-anim-children
    // (a descendant selector for its items) + data-sc-anim-stagger (ms between items). The
    // entrance is moved OFF the wrapper and ONTO each item, with an incremental --animate-delay
    // so the cards cascade in instead of the grid animating as one block. Items are pre-hidden
    // BEFORE the (already hidden) wrapper is revealed, so there is no flash. Returns true when it
    // took over; false (no items found) lets bind() animate the wrapper normally.
    var STAGGER_MAX_MS = 1500; // cap the cumulative extra delay so a big grid isn't glacial
    function setupContainer(el) {
        var selector = el.getAttribute('data-sc-anim-children');
        var kids = selector ? el.querySelectorAll(selector) : [];
        if (!kids.length) { return false; }
        var list = Array.prototype.slice.call(kids);

        var animData = el.getAttribute('data-sc-anim') || '';
        var triggers = (el.getAttribute('data-sc-anim-trigger') || 'view').split(/\s+/).filter(Boolean);
        if (!triggers.length) { triggers = ['view']; }
        var has = function (t) { return triggers.indexOf(t) >= 0; };
        var replay = el.getAttribute('data-sc-anim-replay') === '1';
        var interactive = has('click') || has('hover');
        var stagger = parseFloat(el.getAttribute('data-sc-anim-stagger')) || 0;
        var hideMode = el.classList.contains('sc-anim-pending');

        // Base delay (seconds) from the wrapper's --animate-delay, added to every item. The
        // animation-timing-function was applied inline (doesn't inherit) — carry it to each item.
        var cs = window.getComputedStyle(el);
        var bd = (cs.getPropertyValue('--animate-delay') || '').trim();
        var baseDelay = 0;
        if (bd) { var mm = parseFloat(bd); if (!isNaN(mm)) { baseDelay = /ms$/.test(bd) ? mm / 1000 : mm; } }
        var timing = el.style.animationTimingFunction || '';

        list.forEach(function (kid, i) {
            kid.__scAnimBound = true; // keep scan() from binding items independently
            kid.setAttribute('data-sc-anim', animData);
            var extra = Math.min(i * stagger, STAGGER_MAX_MS) / 1000;
            kid.style.setProperty('--animate-delay', (baseDelay + extra).toFixed(3) + 's');
            if (timing) { kid.style.animationTimingFunction = timing; }
            if (hideMode) { kid.classList.add('sc-anim-pending'); }
        });

        // Reveal the wrapper (the items are the hidden ones now) and stop it animating as a block.
        el.classList.remove('sc-anim-pending');
        el.removeAttribute('data-sc-anim');

        var playAll  = function () { list.forEach(function (k) { interactive ? playReplayable(k) : play(k); }); };
        var resetAll = function () { list.forEach(function (k) { reset(k, true); }); };

        if (has('load')) { playAll(); }
        if (has('view') && !has('load')) {
            if ('IntersectionObserver' in window) {
                var io = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting) { return; }
                        playAll();
                        if (!replay) { io.unobserve(el); }
                    });
                }, { threshold: 0.15, rootMargin: '0px 0px -10% 0px' });
                io.observe(el);
                if (replay) { // re-arm the items when the container leaves the viewport
                    var io2 = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) { if (!entry.isIntersecting) { resetAll(); } });
                    }, { threshold: 0 });
                    io2.observe(el);
                }
            } else {
                playAll();
            }
        }
        if (has('click')) { el.addEventListener('click', playAll); }
        if (has('hover')) { el.addEventListener('mouseenter', playAll); }
        return true;
    }

    // Route each element by its trigger(s). data-sc-anim-trigger is a SPACE-SEPARATED list
    // (view / load / click / hover); a missing attr = the classic scroll-into-view. An element can
    // carry several — e.g. "view click" reveals on scroll and replays on click.
    function bind(el) {
        if (el.__scAnimBound) { return; }
        el.__scAnimBound = true;

        // Collection wrapper → distribute the entrance to its items (staggered).
        if (el.hasAttribute('data-sc-anim-children') && setupContainer(el)) { return; }

        var triggers = (el.getAttribute('data-sc-anim-trigger') || 'view').split(/\s+/).filter(Boolean);
        if (!triggers.length) { triggers = ['view']; }
        var has = function (t) { return triggers.indexOf(t) >= 0; };
        var replay = el.getAttribute('data-sc-anim-replay') === '1';
        // "Interactive" = has a click/hover replay trigger. Such elements play their entrance
        // replayably (classes cleared on end) so a later click/hover restarts cleanly.
        var interactive = has('click') || has('hover');
        el.__scAnimInteractive = interactive;
        var entrancePlay = interactive ? playReplayable : play;

        // Page load fires immediately and pre-empts scroll-into-view (load wins).
        if (has('load')) {
            entrancePlay(el);
        }
        if (has('view') && !has('load')) {
            if (observer) { observer.observe(el); } else { entrancePlay(el); }
        } else if (has('view') && has('load') && replay && observer) {
            observer.observe(el); // load already played; keep observing only to honor replay-on-scroll
        }
        if (has('click')) {
            el.addEventListener('click', function () { playReplayable(el); });
        }
        if (has('hover')) {
            el.addEventListener('mouseenter', function () { playReplayable(el); });
        }
    }

    function scan(root) {
        (root || document).querySelectorAll('[data-sc-anim]').forEach(bind);
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
                    if (n.hasAttribute && n.hasAttribute('data-sc-anim')) { bind(n); }
                    if (n.querySelectorAll) scan(n);
                });
            });
        });
        mo.observe(document.body || document.documentElement, { childList: true, subtree: true });
    }
})();
