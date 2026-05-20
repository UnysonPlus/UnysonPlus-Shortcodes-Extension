<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
    'fw-shortcode-text-expander',
    $shortcodes_extension->get_declared_URI( '/shortcodes/text-expander/static/css/styles.css' )
);

wp_enqueue_script(
    'fw-shortcode-text-expander',
    $shortcodes_extension->get_declared_URI( '/shortcodes/text-expander/static/js/scripts.js' ),
    array( 'jquery' ),
    false,
    true
);
