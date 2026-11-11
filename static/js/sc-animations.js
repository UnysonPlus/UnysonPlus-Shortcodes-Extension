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
            (window.self !== window.top && !/[?&]fw-le-motion-preview=/.test(window.location.search)) /* the Live Editor Preview canvas plays like the real page */
        );
    }

    if (inBuilder()) {
        document.querySelectorAll('.sc-anim-pending').forEach(function (el) {
            el.classList.remove('sc-anim-pending');
        });
        return;
    }

    // Animate.css reads --animate-delay only through its animate__delay-Ns classes, so an element's own
    // delay (the Animation Delay setting, and each item's stagger in a cascade) never applied. Set it as
    // a real animation-delay from the inline variable.
    function applyDelay(el) {
        var d = el.style ? String(el.style.getPropertyValue('--animate-delay') || '').trim() : '';
        if (d !== '') { el.style.animationDelay = d; }
    }

    function play(el) {
        var classes = (el.getAttribute('data-sc-anim') || '').split(/\s+/).filter(Boolean);
        if (!classes.length) return;
        applyDelay(el);
        classes.forEach(function (c) { el.classList.add(c); });
        el.classList.remove('sc-anim-pending', 'sc-anim-gone');
    }

    // ── Exit Animation round trip ───────────────────────────────────────────────────────────────
    // An element with data-sc-exit plays its exit as it leaves the screen (through the edge(s) in
    // data-sc-exit-when) and comes back in when it returns: with its entrance, or with the exit played
    // forwards when it has no entrance. A collection does this item by item. The exit is the entrance
    // keyframe in data-sc-exit, run backwards (.sc-anim-exit) unless data-sc-exit-rev is 0 (Hinge);
    // once it has played the item stays hidden (.sc-anim-gone) until it is back on screen.
    function exitCfg(el) {
        var kf = el.getAttribute('data-sc-exit');
        if (!kf || !/^animate__[A-Za-z]+$/.test(kf)) { return null; }
        return {
            kf: kf,
            rev: el.getAttribute('data-sc-exit-rev') !== '0',
            when: el.getAttribute('data-sc-exit-when') || 'both',
            dur: parseFloat(el.getAttribute('data-sc-exit-dur')) || 0.8,
            delay: parseFloat(el.getAttribute('data-sc-exit-delay')) || 0,
            order: el.getAttribute('data-sc-exit-order') === 'same' ? 'same' : 'reverse',
            stagger: (parseFloat(el.getAttribute('data-sc-anim-stagger')) || 0) / 1000
        };
    }

    var trips = [], tripTick = false;
    function checkTrips() {
        tripTick = false;
        for (var i = 0; i < trips.length; i++) { trips[i](); }
    }
    function onTripScroll() {
        if (tripTick) { return; }
        tripTick = true;
        window.requestAnimationFrame(checkTrips);
    }

    function roundTrip(el, targets, hasEntrance, viewTrigger, loadTrigger, cfg) {
        var n = targets.length;
        function stripExit(t) {
            t.classList.remove('sc-anim-exit', cfg.kf);
            if (t.__scExitSaved) {
                t.style.setProperty('--animate-duration', t.__scExitSaved.dur);
                t.style.animationDelay = t.__scExitSaved.delay;
                t.__scExitSaved = null;
            }
        }
        function whenDone(t, token, fn) {
            var onEnd = function (e) {
                if (e.target !== t) { return; }
                t.removeEventListener('animationend', onEnd);
                if (t.__scTripTok === token) { fn(); }
            };
            t.addEventListener('animationend', onEnd);
        }
        function playIn(t, i) {
            var token = (t.__scTripTok = (t.__scTripTok || 0) + 1);
            stripExit(t);
            t.classList.remove('sc-anim-gone');
            if (hasEntrance) {
                var classes = (t.getAttribute('data-sc-anim') || '').split(/\s+/).filter(Boolean);
                classes.forEach(function (c) { t.classList.remove(c); });
                void t.offsetWidth;                                           // restart the keyframes
                play(t);
                if (!/(^|\s)animate__infinite(\s|$)/.test(t.getAttribute('data-sc-anim') || '')) {
                    whenDone(t, token, function () { reset(t, false); });
                }
            } else {                                                          // no entrance: the exit played forwards
                t.__scExitSaved = { dur: t.style.getPropertyValue('--animate-duration'), delay: t.style.animationDelay };
                t.style.setProperty('--animate-duration', cfg.dur + 's');
                t.style.animationDelay = (Math.min(i * cfg.stagger, 1.5)).toFixed(3) + 's';
                t.classList.remove('animate__animated', cfg.kf);
                void t.offsetWidth;
                t.classList.add('animate__animated', cfg.kf);
                whenDone(t, token, function () { t.classList.remove('animate__animated'); stripExit(t); });
            }
        }
        function playOut(t, i) {
            var token = (t.__scTripTok = (t.__scTripTok || 0) + 1);
            var entr = (t.getAttribute('data-sc-anim') || '').split(/\s+/).filter(Boolean);
            entr.forEach(function (c) { t.classList.remove(c); });
            t.classList.remove('sc-anim-pending', 'sc-anim-exit', cfg.kf);
            var idx = cfg.order === 'reverse' ? n - 1 - i : i;
            if (!t.__scExitSaved) { t.__scExitSaved = { dur: t.style.getPropertyValue('--animate-duration'), delay: t.style.animationDelay }; }
            t.style.setProperty('--animate-duration', cfg.dur + 's');
            t.style.animationDelay = (cfg.delay + Math.min(idx * cfg.stagger, 1.5)).toFixed(3) + 's';
            void t.offsetWidth;
            t.classList.add('animate__animated', cfg.kf);
            if (cfg.rev) { t.classList.add('sc-anim-exit'); }
            whenDone(t, token, function () {
                t.classList.add('sc-anim-gone');
                t.classList.remove('animate__animated');
                stripExit(t);
            });
        }

        // pending = waiting for its first entrance; in = on screen; out = has exited.
        var state = (hasEntrance && (viewTrigger || loadTrigger)) ? 'pending' : 'in';
        var seen = false;                                                     // never exit before it was ever on screen
        if (hasEntrance && loadTrigger) { targets.forEach(playIn); state = 'in'; }
        trips.push(function () {
            var r = el.getBoundingClientRect(), vh = window.innerHeight || 1;
            if (!r.width && !r.height) { return; }                            // hidden (another tab, display:none)
            // First entrance: as soon as a slice of it is on screen (like the plain entrance). The exit is
            // measured from the element's MIDDLE, so it plays while the content is still in view (a gallery's
            // box has empty stage above and below its photos, and a sticky header covers the top of the
            // screen): out when the middle rises past 30% of the screen or drops past 80%; back in once the
            // middle is between 38% and 72% again (a gap, so it never flickers). It only exits after its
            // middle has been in that central band once, so nothing exits straight after loading.
            var mid = (r.top + r.bottom) / 2;
            var inZone = r.top < vh * 0.82 && r.bottom > vh * 0.18;
            var central = mid > vh * 0.38 && mid < vh * 0.72;
            if (state === 'pending') {
                if (inZone && viewTrigger) { targets.forEach(playIn); state = 'in'; }
                if (central) { seen = true; }
                return;
            }
            if (state === 'out') {
                if (central) { targets.forEach(playIn); state = 'in'; }
                return;
            }
            if (central) { seen = true; }
            if (!seen) { return; }
            var offTop = mid < vh * 0.3, offBottom = mid > vh * 0.8;
            if ((offTop && cfg.when !== 'bottom') || (offBottom && cfg.when !== 'top')) {
                targets.forEach(playOut); state = 'out';
            }
        });
        if (trips.length === 1) {
            window.addEventListener('scroll', onTripScroll, { passive: true });
            window.addEventListener('resize', onTripScroll, { passive: true });
        }
        onTripScroll();
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

    // Play a one-shot entrance, then STRIP the animate classes once it ends (keeping the element
    // visible). A finished entrance otherwise leaves animation-name: <effect> set on the element
    // forever, which a Hover Interaction that uses CSS animation would re-trigger on mouse-out — the
    // card would "enter" again every hover. Looping entrances (animate__infinite) must keep their
    // classes, so they are left alone.
    function playAndClear(el) {
        play(el);
        if (/(^|\s)animate__infinite(\s|$)/.test(el.getAttribute('data-sc-anim') || '')) { return; }
        var onEnd = function () { el.removeEventListener('animationend', onEnd); reset(el, false); };
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

            if (el.getAttribute('data-sc-anim-replay') === '1') {
                play(el);
                var onEnd = function () {
                    el.removeEventListener('animationend', onEnd);
                    reset(el, true);
                };
                el.addEventListener('animationend', onEnd);
            } else {
                playAndClear(el); // strip classes on end so a later hover can't re-trigger the entrance
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

        // With an Exit Animation the items make the round trip (in → out as the wrapper leaves → back in).
        var ecfg = exitCfg(el);
        if (ecfg) {
            roundTrip(el, list, animData !== '', has('view'), has('load'), ecfg);
            if (animData !== '') {
                var replayAll = function () { list.forEach(function (k) { playReplayable(k); }); };
                if (has('click')) { el.addEventListener('click', replayAll); }
                if (has('hover')) { el.addEventListener('mouseenter', replayAll); }
            }
            return true;
        }

        var playAll  = function () { list.forEach(function (k) { interactive ? playReplayable(k) : ( replay ? play(k) : playAndClear(k) ); }); };
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
        // Deferred wrapper (e.g. a 3D-gallery design whose items are built by JS after load): skip it
        // now and let the design's runtime call upwScAnimBind(el) once the items exist. Not marked
        // bound, so that later call re-enters here.
        if (el.hasAttribute('data-sc-anim-defer')) { return; }
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

        // Exit Animation → the round trip owns scroll-into-view, page load and Replay On Scroll.
        var ecfg = exitCfg(el);
        if (ecfg) {
            roundTrip(el, [el], (el.getAttribute('data-sc-anim') || '') !== '', has('view'), has('load'), ecfg);
            if (has('click')) { el.addEventListener('click', function () { playReplayable(el); }); }
            if (has('hover')) { el.addEventListener('mouseenter', function () { playReplayable(el); }); }
            return;
        }

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
        (root || document).querySelectorAll('[data-sc-anim],[data-sc-exit]').forEach(bind);
    }

    // Bind a single wrapper on demand — used by runtime-built collections (3D gallery) that carry
    // data-sc-anim-defer until their items exist. The caller removes the defer attr first.
    window.upwScAnimBind = function (el) { if (el) { bind(el); } };

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
                    if (n.hasAttribute && (n.hasAttribute('data-sc-anim') || n.hasAttribute('data-sc-exit'))) { bind(n); }
                    if (n.querySelectorAll) scan(n);
                });
            });
        });
        mo.observe(document.body || document.documentElement, { childList: true, subtree: true });
    }
})();
