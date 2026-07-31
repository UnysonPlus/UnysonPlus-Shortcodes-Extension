/* =======================================================================
 *  Text Expander shortcode — front-end behaviour (flat-DOM model).
 *
 *  Paragraphs are direct children of the wrapper, with the hidden ones
 *  tagged via `data-expander-hidden="true"`. CSS handles all visibility
 *  via the wrapper's `.fw-text-expander--open` class. This script only
 *  needs to flip that one class and keep aria-expanded in sync across
 *  the (two) toggle buttons. Vanilla JS — no jQuery.
 *
 *  Responsibilities:
 *   - flip `.fw-text-expander--open` on the wrapper
 *   - sync aria-expanded across BOTH toggle buttons in the wrapper
 *   - count injection (rewrites each button's visible label on init)
 *   - click-anywhere on visible paragraphs (delegated handler)
 *   - hash deep-link auto-expand
 *   - native <details> hash handling
 * ===================================================================== */

( function () {
    'use strict';

    function prefersReducedMotion() {
        return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    /* ---- Open / close --------------------------------------------------
     *  setState flips the wrapper's --open class AND animates the
     *  wrapper's height between the collapsed and expanded values so the
     *  user sees the panel grow / shrink instead of snapping. Algorithm:
     *
     *    1. Capture the CURRENT rendered height of the wrapper.
     *    2. Toggle the --open class to the target state so the children's
     *       CSS reaches its new layout.
     *    3. Read the new natural height via scrollHeight.
     *    4. For close, re-add `--animating` so CSS keeps the hidden
     *       children visible (and opaque, untranslated) until our height
     *       transition finishes — otherwise the wrapper shrinks around
     *       empty space.
     *    5. Set inline height to the START value, force a reflow, then
     *       transition to the END value.
     *    6. On transitionend, clean up inline styles and drop
     *       `--animating`.
     *
     *  prefers-reduced-motion: skip the height dance entirely and just
     *  toggle the class.
     * ------------------------------------------------------------------ */

    function setState(el, isOpen) {
        var buttons = el.querySelectorAll('.fw-text-expander__toggle');
        Array.prototype.forEach.call(buttons, function (b) {
            b.setAttribute('aria-expanded', String(isOpen));
        });

        if (prefersReducedMotion()) {
            el.classList.toggle('fw-text-expander--open', isOpen);
            el.classList.remove('fw-text-expander--animating');
            el.style.height     = '';
            el.style.overflow   = '';
            el.style.transition = '';
            return;
        }

        // Remove any prior transitionend listener from a still-running
        // animation so rapid clicks don't trigger stale cleanups.
        if (el._fwTeOnEnd) {
            el.removeEventListener('transitionend', el._fwTeOnEnd);
            el._fwTeOnEnd = null;
        }

        // Cancel any in-flight transition before measuring.
        el.style.transition = 'none';

        // Step 1: current rendered height (might be mid-animation).
        var startH = el.getBoundingClientRect().height;

        // Step 2: apply target class while measuring. Clear any inline
        // height first so the measurement reflects the natural size.
        el.style.height   = '';
        el.style.overflow = '';
        el.classList.remove('fw-text-expander--animating');
        el.classList.toggle('fw-text-expander--open', isOpen);

        // Step 3: natural target height.
        var endH = el.scrollHeight;

        // Step 4: for close, pin children visible during the shrink.
        // (For open, the children are visible via --open already.)
        if (!isOpen) {
            el.classList.add('fw-text-expander--animating');
        }

        // Step 5: snap to start, then transition to end.
        // Use double-rAF instead of `void el.offsetHeight` so the browser
        // genuinely paints the start state before we apply the target.
        // A single forced layout flush isn't always enough — browsers
        // can still batch two height writes in the same task into one
        // commit, which collapses the transition (the symptom: open
        // animates, close snaps, because the intermediate "snap-to-start"
        // gets optimised away).
        el.style.overflow   = 'hidden';
        el.style.height     = startH + 'px';

        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                el.style.transition = 'height 0.35s ease';
                el.style.height     = endH + 'px';
            });
        });

        // Step 6: cleanup.
        var onEnd = function (e) {
            if (e.target !== el || e.propertyName !== 'height') return;
            el.style.height     = '';
            el.style.overflow   = '';
            el.style.transition = '';
            el.classList.remove('fw-text-expander--animating');
            el.removeEventListener('transitionend', onEnd);
            el._fwTeOnEnd = null;
        };
        el._fwTeOnEnd = onEnd;
        el.addEventListener('transitionend', onEnd);
    }

    function openPanel(el)  { setState(el, true);  }
    function closePanel(el) { setState(el, false); }

    /* ---- Click handlers ------------------------------------------------ */

    document.addEventListener('click', function (e) {
        var toggle = e.target.closest('.fw-text-expander__toggle');
        if (toggle) {
            var wrapper = toggle.closest('.fw-text-expander');
            if (!wrapper) return;
            if (wrapper.classList.contains('fw-text-expander--open')) {
                closePanel(wrapper);
            } else {
                openPanel(wrapper);
            }
            return;
        }

        /* Click-anywhere: clicking a visible <p> (not marked hidden) expands
           the wrapper. Guard against double-firing on interactive descendants. */
        if (e.target.closest('a, button, input, textarea, select, label')) return;
        var p = e.target.closest('.fw-text-expander--click-anywhere:not(.fw-text-expander--open) > p:not([data-expander-hidden])');
        if (!p) return;
        var caWrapper = p.closest('.fw-text-expander');
        if (!caWrapper || caWrapper.classList.contains('fw-text-expander--open')) return;
        openPanel(caWrapper);
    });

    /* ---- Word / character count ---------------------------------------
     *  Harvest text from every `[data-expander-hidden="true"]` descendant
     *  of the wrapper (this is where the hidden content lives in flat-DOM
     *  output). Rewrite each button's visible label independently so the
     *  Show / Hide labels can carry the count via a `{count}` token or
     *  appended " (N words/chars)" suffix.
     * ------------------------------------------------------------------ */

    function injectCount(wrapper) {
        var mode = wrapper.getAttribute('data-count-mode');
        if (!mode || mode === 'none') return;

        var buttons = wrapper.querySelectorAll('.fw-text-expander__toggle');
        if (!buttons.length) return;

        var text = '';
        Array.prototype.forEach.call(wrapper.querySelectorAll('[data-expander-hidden="true"]'), function (n) {
            text += ' ' + (n.textContent || '');
        });
        text = text.trim();

        var count, unit;
        if (mode === 'words') {
            count = text ? text.split(/\s+/).filter(Boolean).length : 0;
            unit  = count === 1 ? 'word' : 'words';
        } else {
            count = text.replace(/\s+/g, '').length;
            unit  = count === 1 ? 'char' : 'chars';
        }

        function applyToken(label) {
            if (label == null) return label;
            if (label.indexOf('{count}') !== -1) {
                return label.replace(/\{count\}/g, String(count));
            }
            return label + ' (' + count + ' ' + unit + ')';
        }

        Array.prototype.forEach.call(buttons, function (btn) {
            var original = btn.getAttribute('data-label');
            if (original == null) return;
            var rewritten = applyToken(original);
            btn.setAttribute('data-label', rewritten);
            var label = btn.querySelector('.fw-text-expander__label');
            if (label) {
                label.textContent = rewritten;
            } else {
                btn.textContent = rewritten;
            }
        });
    }

    /* ---- Hash deep-link ----------------------------------------------- */

    function expandForHash(hash) {
        if (!hash || hash === '#') return false;
        var target;
        try {
            target = document.querySelector(hash);
        } catch (e) {
            return false;
        }
        if (!target) return false;

        var wrapper = target.closest('.fw-text-expander');
        if (!wrapper) return false;

        /* Native <details>: just set [open]; the browser does the rest. */
        if (wrapper.matches('details.fw-text-expander--native')) {
            wrapper.setAttribute('open', 'open');
            return true;
        }

        if (wrapper.classList.contains('fw-text-expander--open')) return true;
        openPanel(wrapper);
        return true;
    }

    function handleHash() {
        if (!window.location.hash) return;
        if (expandForHash(window.location.hash)) {
            var el;
            try {
                el = document.querySelector(window.location.hash);
            } catch (e) {
                el = null;
            }
            if (el && typeof el.scrollIntoView === 'function') {
                el.scrollIntoView();
            }
        }
    }

    /* ---- Init ---------------------------------------------------------- */

    function init() {
        Array.prototype.forEach.call(document.querySelectorAll('.fw-text-expander[data-count-mode]'), injectCount);
        handleHash();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.addEventListener('hashchange', handleHash);
}() );
