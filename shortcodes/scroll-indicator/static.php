<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

// Scroll Indicator — bounce/pulse animation CSS + the smooth-scroll click handler.
if ( ! is_admin() ) {
	$uri = fw_get_framework_directory_uri( '/extensions/shortcodes/shortcodes/scroll-indicator/static' );
	$ext = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' ) : null;
	$ver = ( $ext && $ext->manifest ) ? $ext->manifest->get_version() : false;

	wp_enqueue_style( 'fw-shortcode-scroll-indicator', $uri . '/css/styles.css', array(), $ver );
	wp_enqueue_script( 'fw-shortcode-scroll-indicator', $uri . '/js/scripts.js', array(), $ver, true );
}
