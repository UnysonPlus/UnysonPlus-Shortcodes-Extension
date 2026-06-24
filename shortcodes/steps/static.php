<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$ext = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-steps',
	$ext->get_declared_URI( '/shortcodes/steps/static/css/styles.css' ),
	array(),
	$ext->manifest->get_version()
);
