<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$shortcodes_extension = fw_ext( 'shortcodes' );

// Tiny toggle behaviour for the "icon-toggle" search style. Loaded globally with
// the other shortcode assets; it no-ops on pages without an icon-toggle search.
wp_enqueue_script(
	'fw-shortcode-site-search',
	fw_min_uri( $shortcodes_extension->get_declared_URI( '/shortcodes/site-search/static/js/site-search.js' ) ),
	array(),
	$shortcodes_extension->manifest->get_version(),
	true
);
