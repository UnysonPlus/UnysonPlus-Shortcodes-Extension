<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$ext = fw_ext( 'shortcodes' );

// Vendored lottie-web (SVG/light build). Filterable so a site can swap in a CDN
// or the full build if it needs the canvas renderer.
$lib_src = apply_filters(
	'fw_shortcode_lottie_library_src',
	$ext->get_declared_URI( '/shortcodes/lottie/static/js/vendor/lottie_light.min.js' )
);

wp_enqueue_style(
	'fw-shortcode-lottie',
	$ext->get_declared_URI( '/shortcodes/lottie/static/css/styles.css' ),
	array(),
	$ext->manifest->get_version()
);
wp_enqueue_script( 'lottie-web', $lib_src, array(), '5.12.2', true );
wp_enqueue_script(
	'fw-shortcode-lottie',
	$ext->get_declared_URI( '/shortcodes/lottie/static/js/scripts.js' ),
	array( 'lottie-web' ),
	$ext->manifest->get_version(),
	true
);
