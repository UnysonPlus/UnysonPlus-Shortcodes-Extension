<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-hero-section',
	fw_min_uri($shortcodes_extension->get_uri( '/shortcodes/hero-section/static/css/hero-section.css' )),
	array(),
	$shortcodes_extension->manifest->get_version()
);

wp_enqueue_script(
	'fw-shortcode-hero-section',
	fw_min_uri($shortcodes_extension->get_uri( '/shortcodes/hero-section/static/js/hero-section.js' )),
	array(),
	$shortcodes_extension->manifest->get_version(),
	true
);
