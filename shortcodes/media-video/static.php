<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

// Self-contained: the aspect-ratio box + centering are defined in media-video.css,
// so this element no longer pulls in the builder's global Bootstrap-style grid sheet.
if ( ! is_admin() ) {
	$uri = fw_get_framework_directory_uri( '/extensions/shortcodes/shortcodes/media-video/static' );
	$ext = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' ) : null;
	$ver = ( $ext && $ext->manifest ) ? $ext->manifest->get_version() : false;

	// Self-hosted <video> + oEmbed lazy-load facade styling.
	wp_enqueue_style( 'fw-shortcode-media-video', $uri . '/css/media-video.css', array(), $ver );

	// Facade click-to-load + reduce-motion pause (dependency-free, footer).
	wp_enqueue_script( 'fw-shortcode-media-video', $uri . '/js/media-video.js', array(), $ver, true );
}
