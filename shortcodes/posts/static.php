<?php
if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
    'fw-shortcode-posts',
    fw_min_uri($shortcodes_extension->get_declared_URI( '/shortcodes/posts/static/css/styles.css' ))
);

wp_enqueue_script(
    'fw-shortcode-posts',
    fw_min_uri($shortcodes_extension->get_declared_URI( '/shortcodes/posts/static/js/scripts.js' )),
    [ 'jquery' ],
    false,
    true
);

wp_localize_script( 'fw-shortcode-posts', 'fwScPosts', [
    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
    'nonce'   => wp_create_nonce( 'fw_sc_posts' ),
] );
