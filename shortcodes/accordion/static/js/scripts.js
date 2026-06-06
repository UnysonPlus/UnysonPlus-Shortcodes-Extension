jQuery(document).ready(function ($) {

    // Shared open / close helpers used by both the click handlers and the
    // new Expand/Collapse All buttons. They mirror the same animations
    // (slideDown / slideUp 200ms) and ARIA state updates the click paths use.
    function openPanel($title, animate) {
        var $content = $title.closest('.accordion-item').find('.accordion-content');
        $title.addClass('ui-state-active').attr('aria-expanded', 'true');
        if (animate === false) {
            $content.show();
        } else {
            $content.slideDown(200);
        }
        $content.attr('aria-hidden', 'false');
    }

    function closePanel($title, animate) {
        var $content = $title.closest('.accordion-item').find('.accordion-content');
        $title.removeClass('ui-state-active').attr('aria-expanded', 'false');
        if (animate === false) {
            $content.hide();
        } else {
            $content.slideUp(200);
        }
        $content.attr('aria-hidden', 'true');
    }

    $('.accordion').each(function () {
        var $accordion     = $(this);
        var multipleOpen   = $accordion.data('multiple-open') === true || $accordion.data('multiple-open') === 'true';
        var collapsible    = $accordion.data('collapsible') === true || $accordion.data('collapsible') === 'true';
        var initiallyOpen  = $accordion.data('initially-open') || 'first';
        var hashLinking    = $accordion.data('hash-linking') === true || $accordion.data('hash-linking') === 'true';

        if (initiallyOpen === 'all' && !multipleOpen) {
            var $allTitles = $accordion.find('.accordion-title');
            var $extraTitles = $allTitles.not(':first');
            $extraTitles.removeClass('ui-state-active').attr('aria-expanded', 'false');
            $extraTitles.each(function () {
                $(this).closest('.accordion-item').find('.accordion-content')
                       .hide()
                       .attr('aria-hidden', 'true');
            });
        }

        // B — URL hash deep-linking. On init, if the URL hash matches one of
        // this accordion's header or panel IDs, open the corresponding item
        // and scroll it into view. Skip when the option is disabled.
        if (hashLinking) {
            var rawHash = (window.location.hash || '').replace(/^#/, '');
            if (rawHash) {
                var $hashTitle = $accordion.find('#' + window.CSS.escape(rawHash));
                if ($hashTitle.length === 0) {
                    // Hash might point at the panel rather than the header — fall back to its sibling title.
                    var $panel = $accordion.find('.accordion-content#' + window.CSS.escape(rawHash));
                    if ($panel.length) {
                        $hashTitle = $panel.closest('.accordion-item').find('.accordion-title');
                    }
                }
                if ($hashTitle.length && $hashTitle.hasClass('accordion-title')) {
                    // Close any current opens first when single-open mode.
                    if (!multipleOpen) {
                        $accordion.find('.accordion-title.ui-state-active').each(function () {
                            closePanel($(this), false);
                        });
                    }
                    openPanel($hashTitle, false);
                    // Defer the scroll so any layout shift from the open settles first.
                    setTimeout(function () {
                        $hashTitle[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 50);
                }
            }
        }

        // D — Expand-All / Collapse-All convenience buttons.
        $accordion.find('[data-accordion-action="expand-all"]').on('click', function (e) {
            e.preventDefault();
            $accordion.find('.accordion-title').not('.ui-state-active').each(function () {
                openPanel($(this), true);
            });
        });
        $accordion.find('[data-accordion-action="collapse-all"]').on('click', function (e) {
            e.preventDefault();
            $accordion.find('.accordion-title.ui-state-active').each(function () {
                closePanel($(this), true);
            });
        });

        if (multipleOpen) {
            $accordion.find('.accordion-title').on('click keydown', function (e) {
                if (e.type === 'keydown' && e.which !== 13 && e.which !== 32) {
                    return;
                }
                e.preventDefault();

                var $title   = $(this);
                var $content = $title.closest('.accordion-item').find('.accordion-content');
                var isActive = $title.hasClass('ui-state-active');

                if (isActive && !collapsible) {
                    var openCount = $accordion.find('.accordion-title.ui-state-active').length;
                    if (openCount <= 1) {
                        return;
                    }
                }

                $title.toggleClass('ui-state-active');
                $content.slideToggle(200);

                var nowOpen = $title.hasClass('ui-state-active');
                $title.attr('aria-expanded', nowOpen ? 'true' : 'false');
                $content.attr('aria-hidden', nowOpen ? 'false' : 'true');

                if (hashLinking && nowOpen && $title.attr('id')) {
                    history.replaceState(null, '', '#' + $title.attr('id'));
                }
            });
        } else {
            $accordion.find('.accordion-title').on('click keydown', function (e) {
                if (e.type === 'keydown' && e.which !== 13 && e.which !== 32) {
                    return;
                }
                e.preventDefault();

                var $title   = $(this);
                var $content = $title.closest('.accordion-item').find('.accordion-content');
                var isActive = $title.hasClass('ui-state-active');

                if (isActive) {
                    if (!collapsible) {
                        return;
                    }
                    $title.removeClass('ui-state-active')
                          .attr('aria-expanded', 'false');
                    $content.slideUp(200)
                            .attr('aria-hidden', 'true');
                } else {
                    var $openTitles = $accordion.find('.accordion-title.ui-state-active');
                    $openTitles.removeClass('ui-state-active').attr('aria-expanded', 'false');
                    $openTitles.each(function () {
                        $(this).closest('.accordion-item').find('.accordion-content')
                               .slideUp(200)
                               .attr('aria-hidden', 'true');
                    });

                    $title.addClass('ui-state-active')
                          .attr('aria-expanded', 'true');
                    $content.slideDown(200)
                            .attr('aria-hidden', 'false');

                    if (hashLinking && $title.attr('id')) {
                        history.replaceState(null, '', '#' + $title.attr('id'));
                    }
                }
            });
        }
    });

});
