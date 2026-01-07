jQuery(document).ready(function ($) {
    $(".tabs-container").tabs({
        // prevent focus scroll
        beforeActivate: function (event, ui) {
            // Remove focus to avoid jumping
            ui.newTab.find("a").blur();
        }
    });
});
