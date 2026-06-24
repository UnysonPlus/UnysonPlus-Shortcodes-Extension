<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-countdown',
	fw_min_uri( $shortcodes_extension->get_declared_URI( '/shortcodes/countdown/static/css/styles.css' ) )
);

wp_enqueue_script(
	'fw-shortcode-countdown',
	fw_min_uri( $shortcodes_extension->get_declared_URI( '/shortcodes/countdown/static/js/scripts.js' ) ),
	array(),
	false,
	true
);
