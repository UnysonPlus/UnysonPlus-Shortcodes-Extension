<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-counter',
	fw_min_uri( $shortcodes_extension->get_declared_URI( '/shortcodes/counter/static/css/styles.css' ) )
);

wp_enqueue_script(
	'fw-shortcode-counter',
	fw_min_uri( $shortcodes_extension->get_declared_URI( '/shortcodes/counter/static/js/scripts.js' ) ),
	array(),
	false,
	true
);
