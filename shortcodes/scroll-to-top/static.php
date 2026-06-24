<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$ext = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-scroll-to-top',
	$ext->get_declared_URI( '/shortcodes/scroll-to-top/static/css/styles.css' ),
	array(),
	$ext->manifest->get_version()
);
wp_enqueue_script(
	'fw-shortcode-scroll-to-top',
	$ext->get_declared_URI( '/shortcodes/scroll-to-top/static/js/scripts.js' ),
	array(),
	$ext->manifest->get_version(),
	true
);
