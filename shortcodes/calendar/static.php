<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

// Modern calendar — no Bootstrap 3 / jQuery / Underscore / jstz. Just one CSS
// file and one tiny vanilla-JS file for month navigation.
$ext = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-calendar',
	$ext->get_declared_URI( '/shortcodes/calendar/static/css/styles.css' ),
	array(),
	$ext->manifest->get_version()
);
wp_enqueue_script(
	'fw-shortcode-calendar',
	$ext->get_declared_URI( '/shortcodes/calendar/static/js/scripts.js' ),
	array(),
	$ext->manifest->get_version(),
	true
);
