<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
    'fw-shortcode-toc',
    fw_min_uri( $shortcodes_extension->get_declared_URI( '/shortcodes/toc/static/css/styles.css' ) )
);

wp_enqueue_script(
    'fw-shortcode-toc',
    fw_min_uri( $shortcodes_extension->get_declared_URI( '/shortcodes/toc/static/js/scripts.js' ) ),
    array(),   // vanilla JS, no jQuery dependency
    false,
    true       // in footer
);

/**
 * Protect this element's CSS from the Asset Optimizer's unused-CSS purge.
 *
 * The list, the items, the links, the numbers and the sub-lists are all built by
 * scripts.js AFTER the page is delivered, so `sc-toc__item`, `sc-toc__link`,
 * `sc-toc__num`, `sc-toc__sublist` and `sc-toc__text` appear nowhere in the
 * rendered HTML the purge scans. Every rule naming them therefore looks unused
 * and is removed - which showed up as the links rendering as plain inline text
 * (failing the 24px tap-target audit) and the numbers running into the titles.
 *
 * One prefix covers the family: the filter anchors each entry on a word boundary,
 * so `sc-toc` keeps `.sc-toc__link` and the rest. Registering it HERE rather than
 * globally means the protection applies only to pages that actually contain a
 * table of contents, so the purge stays fully effective everywhere else.
 */
add_filter(
    'fw:ext:asset-optimizer:protected_classes',
    function ( $classes ) {
        $classes[] = 'sc-toc';
        return $classes;
    }
);
