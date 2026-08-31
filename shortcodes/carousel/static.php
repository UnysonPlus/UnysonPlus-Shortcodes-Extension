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

// The slide CTA uses the .btn base from the button shortcode. That handle is only
// ENQUEUED (never registered) by the button shortcode's own static.php, so on a page
// that has a carousel but no standalone [button], 'fw-shortcode-button' is unknown and
// WordPress would SILENTLY DROP the carousel stylesheet below (arrows/skin lost — they
// fall unstyled to the top of the carousel). Enqueue the button base here so the
// dependency always resolves and the slide buttons are styled regardless.
wp_enqueue_style(
	'fw-shortcode-button',
	fw_min_uri( $shortcodes_extension->get_declared_URI( '/shortcodes/button/static/css/styles.css' ) )
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
