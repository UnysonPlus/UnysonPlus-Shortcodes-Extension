<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$ext = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-badge',
	$ext->get_declared_URI( '/shortcodes/badge/static/css/styles.css' ),
	array(),
	$ext->manifest->get_version()
);

// Tiny dismissible helper (only acts when a pill opts in via data-ap-dismiss); loaded in the footer.
wp_enqueue_script(
	'fw-shortcode-badge',
	$ext->get_declared_URI( '/shortcodes/badge/static/js/badge.js' ),
	array(),
	$ext->manifest->get_version(),
	true
);
