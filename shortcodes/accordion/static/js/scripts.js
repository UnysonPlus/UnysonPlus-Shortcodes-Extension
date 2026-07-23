jQuery(document).ready(function ($) {

    // WAI-ARIA accordion (disclosure) pattern: the interactive handle is the
    // .accordion-trigger <button> (carries aria-expanded, id, keyboard + focus).
    // The .ui-state-active styling class stays on the .accordion-title bar; the
    // panel is .accordion-content (role="region", aria-hidden).
    function parts($trigger) {
        var $item = $trigger.closest('.accordion-item');
        return {
            $item:    $item,
            $bar:     $item.find('.accordion-title').first(),
            $content: $item.find('.accordion-content').first()
        };
    }

    function isOpen($trigger) {
        return parts($trigger).$bar.hasClass('ui-state-active');
    }

    function openPanel($trigger, animate) {
        var p = parts($trigger);
        p.$bar.addClass('ui-state-active');
        $trigger.attr('aria-expanded', 'true');
        if (animate === false) { p.$content.show(); } else { p.$content.slideDown(200); }
        p.$content.attr('aria-hidden', 'false');
    }

    function closePanel($trigger, animate) {
        var p = parts($trigger);
        p.$bar.removeClass('ui-state-active');
        $trigger.attr('aria-expanded', 'false');
        if (animate === false) { p.$content.hide(); } else { p.$content.slideUp(200); }
        p.$content.attr('aria-hidden', 'true');
    }

    $('.accordion').each(function () {
        var $accordion    = $(this);
        var multipleOpen  = $accordion.data('multiple-open') === true || $accordion.data('multiple-open') === 'true';
        var collapsible   = $accordion.data('collapsible') === true || $accordion.data('collapsible') === 'true';
        var initiallyOpen = $accordion.data('initially-open') || 'first';
        var hashLinking   = $accordion.data('hash-linking') === true || $accordion.data('hash-linking') === 'true';
        var $triggers     = $accordion.find('.accordion-trigger');

        function openTriggers() {
            return $triggers.filter(function () { return isOpen($(this)); });
        }

        // Single-open accordions can't honour "open all" — collapse all but the first.
        if (initiallyOpen === 'all' && !multipleOpen) {
            $triggers.slice(1).each(function () { closePanel($(this), false); });
        }

        // URL hash deep-linking. Match either a trigger id or a panel id.
        if (hashLinking) {
            var rawHash = (window.location.hash || '').replace(/^#/, '');
            if (rawHash) {
                var $target  = $accordion.find('#' + window.CSS.escape(rawHash));
                var $trigger = null;
                if ($target.hasClass('accordion-trigger')) {
                    $trigger = $target;
                } else if ($target.hasClass('accordion-content')) {
                    $trigger = $target.closest('.accordion-item').find('.accordion-trigger').first();
                }
                if ($trigger && $trigger.length) {
                    if (!multipleOpen) {
                        openTriggers().each(function () { closePanel($(this), false); });
                    }
                    openPanel($trigger, false);
                    setTimeout(function () {
                        $trigger.closest('.accordion-item')[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 50);
                }
            }
        }

        // Expand-All / Collapse-All convenience buttons.
        $accordion.find('[data-accordion-action="expand-all"]').on('click', function (e) {
            e.preventDefault();
            $triggers.each(function () { if (!isOpen($(this))) { openPanel($(this), true); } });
        });
        $accordion.find('[data-accordion-action="collapse-all"]').on('click', function (e) {
            e.preventDefault();
            $triggers.each(function () { if (isOpen($(this))) { closePanel($(this), true); } });
        });

        // Toggle. The trigger is a native <button>, so it fires 'click' on
        // Enter/Space itself — binding 'click' only avoids a double-toggle.
        $triggers.on('click', function () {
            var $trigger = $(this);
            var active   = isOpen($trigger);

            if (multipleOpen) {
                if (active && !collapsible && openTriggers().length <= 1) { return; }
                if (active) { closePanel($trigger, true); } else { openPanel($trigger, true); }
            } else {
                if (active) {
                    if (!collapsible) { return; }
                    closePanel($trigger, true);
                } else {
                    openTriggers().each(function () { closePanel($(this), true); });
                    openPanel($trigger, true);
                }
            }

            if (hashLinking && isOpen($trigger) && $trigger.attr('id')) {
                history.replaceState(null, '', '#' + $trigger.attr('id'));
            }
        });
    });

});
