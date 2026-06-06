<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-masonry-section',
	$shortcodes_extension->get_uri( '/shortcodes/masonry_section/static/css/masonry-section.css' ),
	array(),
	$shortcodes_extension->manifest->get_version()
);

wp_enqueue_script(
	'fw-shortcode-masonry-section',
	$shortcodes_extension->get_uri( '/shortcodes/masonry_section/static/js/masonry-section.js' ),
	array(),
	$shortcodes_extension->manifest->get_version(),
	true
);
