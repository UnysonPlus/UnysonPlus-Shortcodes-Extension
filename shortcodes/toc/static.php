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
