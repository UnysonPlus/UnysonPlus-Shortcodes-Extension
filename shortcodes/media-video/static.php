<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

// The `.ratio*` aspect-ratio container + `.mx-auto` helper live in the builder's
// frontend-grid sheet (shipped in place of Bootstrap).
if ( ! is_admin() ) {
	wp_enqueue_style( 'fw-ext-builder-frontend-grid' );

	$uri = fw_get_framework_directory_uri( '/extensions/shortcodes/shortcodes/media-video/static' );
	$ext = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' ) : null;
	$ver = ( $ext && $ext->manifest ) ? $ext->manifest->get_version() : false;

	// Self-hosted <video> + oEmbed lazy-load facade styling.
	wp_enqueue_style( 'fw-shortcode-media-video', $uri . '/css/media-video.css', array(), $ver );

	// Facade click-to-load + reduce-motion pause (dependency-free, footer).
	wp_enqueue_script( 'fw-shortcode-media-video', $uri . '/js/media-video.js', array(), $ver, true );
}
