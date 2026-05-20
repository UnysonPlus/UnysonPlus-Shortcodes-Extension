/* =======================================================================
 *  Text Expander shortcode — front-end behaviour (flat-DOM model).
 *
 *  Paragraphs are direct children of the wrapper, with the hidden ones
 *  tagged via `data-expander-hidden="true"`. CSS handles all visibility
 *  via the wrapper's `.fw-text-expander--open` class. This script only
 *  needs to flip that one class and keep aria-expanded in sync across
 *  the (two) toggle buttons.
 *
 *  Responsibilities:
 *   - flip `.fw-text-expander--open` on the wrapper
 *   - sync aria-expanded across BOTH toggle buttons in the wrapper
 *   - count injection (rewrites each button's visible label on init)
 *   - click-anywhere on visible paragraphs (delegated handler)
 *   - hash deep-link auto-expand
 *   - native <details> hash handling
 * ===================================================================== */

jQuery(function ($) {

    function wrapperOf($el) {
        return $el.closest('.fw-text-expander');
    }

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

    function setState($wrapper, isOpen) {
        var el       = $wrapper[0];
        var $buttons = $wrapper.find('.fw-text-expander__toggle');

        $buttons.attr('aria-expanded', String(isOpen));

        if (prefersReducedMotion()) {
            $wrapper.toggleClass('fw-text-expander--open', isOpen);
            $wrapper.removeClass('fw-text-expander--animating');
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
        $wrapper.removeClass('fw-text-expander--animating');
        $wrapper.toggleClass('fw-text-expander--open', isOpen);

        // Step 3: natural target height.
        var endH = el.scrollHeight;

        // Step 4: for close, pin children visible during the shrink.
        // (For open, the children are visible via --open already.)
        if (!isOpen) {
            $wrapper.addClass('fw-text-expander--animating');
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
            $wrapper.removeClass('fw-text-expander--animating');
            el.removeEventListener('transitionend', onEnd);
            el._fwTeOnEnd = null;
        };
        el._fwTeOnEnd = onEnd;
        el.addEventListener('transitionend', onEnd);
    }

    function openPanel($wrapper)  { setState($wrapper, true);  }
    function closePanel($wrapper) { setState($wrapper, false); }

    /* ---- Click handlers ------------------------------------------------ */

    $(document).on('click', '.fw-text-expander__toggle', function () {
        var $wrapper = wrapperOf($(this));
        if (!$wrapper.length) return;
        if ($wrapper.hasClass('fw-text-expander--open')) {
            closePanel($wrapper);
        } else {
            openPanel($wrapper);
        }
    });

    /* Click-anywhere: clicking a visible <p> (not marked hidden) expands
       the wrapper. Guard against double-firing on interactive descendants. */
    $(document).on(
        'click',
        '.fw-text-expander--click-anywhere:not(.fw-text-expander--open) > p:not([data-expander-hidden])',
        function (e) {
            if ($(e.target).closest('.fw-text-expander__toggle, a, button, input, textarea, select, label').length) {
                return;
            }
            var $wrapper = wrapperOf($(this));
            if (!$wrapper.length || $wrapper.hasClass('fw-text-expander--open')) return;
            openPanel($wrapper);
        }
    );

    /* ---- Word / character count ---------------------------------------
     *  Harvest text from every `[data-expander-hidden="true"]` descendant
     *  of the wrapper (this is where the hidden content lives in flat-DOM
     *  output). Rewrite each button's visible label independently so the
     *  Show / Hide labels can carry the count via a `{count}` token or
     *  appended " (N words/chars)" suffix.
     * ------------------------------------------------------------------ */

    function injectCount($wrapper) {
        var mode = $wrapper.attr('data-count-mode');
        if (!mode || mode === 'none') return;

        var $buttons = $wrapper.find('.fw-text-expander__toggle');
        if (!$buttons.length) return;

        var text = '';
        $wrapper.find('[data-expander-hidden="true"]').each(function () {
            text += ' ' + (this.textContent || '');
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

        $buttons.each(function () {
            var $btn = $(this);
            var original = $btn.attr('data-label');
            if (original == null) return;
            var rewritten = applyToken(original);
            $btn.attr('data-label', rewritten);
            var $label = $btn.find('.fw-text-expander__label');
            if ($label.length) {
                $label.text(rewritten);
            } else {
                $btn.text(rewritten);
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

        var $wrapper = $(target).closest('.fw-text-expander');
        if (!$wrapper.length) return false;

        /* Native <details>: just set [open]; the browser does the rest. */
        if ($wrapper.is('details.fw-text-expander--native')) {
            $wrapper.attr('open', 'open');
            return true;
        }

        if ($wrapper.hasClass('fw-text-expander--open')) return true;
        openPanel($wrapper);
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

    $(function () {
        $('.fw-text-expander[data-count-mode]').each(function () {
            injectCount($(this));
        });
        handleHash();
    });

    $(window).on('hashchange', handleHash);
});
