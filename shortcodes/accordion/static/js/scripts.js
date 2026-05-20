jQuery(document).ready(function ($) {

    $('.accordion').each(function () {
        var $accordion     = $(this);
        var multipleOpen   = $accordion.data('multiple-open') === true || $accordion.data('multiple-open') === 'true';
        var collapsible    = $accordion.data('collapsible') === true || $accordion.data('collapsible') === 'true';
        var initiallyOpen  = $accordion.data('initially-open') || 'first';

        if (initiallyOpen === 'all' && !multipleOpen) {
            var $allTitles = $accordion.find('.accordion-title');
            $allTitles.not(':first').removeClass('ui-state-active')
                      .attr('aria-expanded', 'false')
                      .next('.accordion-content')
                      .hide()
                      .attr('aria-hidden', 'true');
        }

        if (multipleOpen) {
            $accordion.find('.accordion-title').on('click keydown', function (e) {
                if (e.type === 'keydown' && e.which !== 13 && e.which !== 32) {
                    return;
                }
                e.preventDefault();

                var $title   = $(this);
                var $content = $title.next('.accordion-content');
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
            });
        } else {
            $accordion.find('.accordion-title').on('click keydown', function (e) {
                if (e.type === 'keydown' && e.which !== 13 && e.which !== 32) {
                    return;
                }
                e.preventDefault();

                var $title   = $(this);
                var $content = $title.next('.accordion-content');
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
                    $accordion.find('.accordion-title.ui-state-active')
                              .removeClass('ui-state-active')
                              .attr('aria-expanded', 'false')
                              .next('.accordion-content')
                              .slideUp(200)
                              .attr('aria-hidden', 'true');

                    $title.addClass('ui-state-active')
                          .attr('aria-expanded', 'true');
                    $content.slideDown(200)
                            .attr('aria-hidden', 'false');
                }
            });
        }
    });

});
