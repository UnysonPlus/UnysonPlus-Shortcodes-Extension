<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

// Media Image ships one tiny self-contained rule (`.media-image__img` responsive
// image) — no dependency on the builder's global Bootstrap-style `.img-fluid`.
if ( ! is_admin() ) {
	$uri = fw_get_framework_directory_uri( '/extensions/shortcodes/shortcodes/media-image/static' );
	$ext = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' ) : null;
	$ver = ( $ext && $ext->manifest ) ? $ext->manifest->get_version() : false;

	wp_enqueue_style( 'fw-shortcode-media-image', $uri . '/css/media-image.css', array(), $ver );
}

// Per-instance: pull in the shared lightbox ONLY for images that turn it on.
if ( ! function_exists( '_fw_media_image_enqueue_static' ) ) :
	function _fw_media_image_enqueue_static( $data ) {
		if ( is_admin() || ! function_exists( 'sc_enqueue_lightbox' ) ) { return; }
		$atts = shortcode_parse_atts( isset( $data['atts_string'] ) ? $data['atts_string'] : '' );
		if ( ! is_array( $atts ) ) { return; }
		$post_id = ( isset( $data['post'] ) && isset( $data['post']->ID ) ) ? $data['post']->ID : 0;
		if ( function_exists( 'fw_ext_shortcodes_decode_attr' ) ) {
			$decoded = fw_ext_shortcodes_decode_attr( $atts, 'media_image', $post_id );
			if ( ! is_wp_error( $decoded ) && is_array( $decoded ) ) { $atts = $decoded; }
		}
		if ( isset( $atts['lightbox'] ) && 'yes' === $atts['lightbox'] ) {
			sc_enqueue_lightbox();
		}
	}
	// The shortcode tag is the folder with '-' → '_' (see class-fw-extension-shortcodes.php).
	add_action( 'fw_ext_shortcodes_enqueue_static:media_image', '_fw_media_image_enqueue_static' );
endif;
