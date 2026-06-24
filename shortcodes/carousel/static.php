<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$shortcodes_extension = fw_ext( 'shortcodes' );

// Splide (vendored, already minified — enqueue the file as-is, do NOT run it through
// fw_min_uri or it would look for splide.min.min.*).
wp_enqueue_style(
	'splide',
	$shortcodes_extension->get_declared_URI( '/shortcodes/carousel/static/vendor/splide-core.min.css' )
);
wp_enqueue_script(
	'splide',
	$shortcodes_extension->get_declared_URI( '/shortcodes/carousel/static/vendor/splide.min.js' ),
	array(),
	'4.1.4',
	true
);

// The shortcode's own styling + init (init depends on Splide).
wp_enqueue_style(
	'fw-shortcode-carousel',
	fw_min_uri( $shortcodes_extension->get_declared_URI( '/shortcodes/carousel/static/css/styles.css' ) ),
	array( 'splide', 'fw-shortcode-button' ) // slide button uses the .btn base
);
wp_enqueue_script(
	'fw-shortcode-carousel',
	fw_min_uri( $shortcodes_extension->get_declared_URI( '/shortcodes/carousel/static/js/scripts.js' ) ),
	array( 'splide' ),
	false,
	true
);
