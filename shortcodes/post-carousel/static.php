<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$ext = fw_ext( 'shortcodes' );

/* Splide (vendored under the Carousel shortcode) + the mount + base styles. */
wp_enqueue_style( 'splide', $ext->get_declared_URI( '/shortcodes/carousel/static/vendor/splide-core.min.css' ) );
wp_enqueue_script( 'splide', $ext->get_declared_URI( '/shortcodes/carousel/static/vendor/splide.min.js' ), array(), '4.1.4', true );

wp_enqueue_style(
	'fw-shortcode-post-carousel',
	$ext->get_declared_URI( '/shortcodes/post-carousel/static/css/styles.css' ),
	array( 'splide' ),
	$ext->manifest->get_version()
);
wp_enqueue_script(
	'fw-shortcode-post-carousel',
	$ext->get_declared_URI( '/shortcodes/post-carousel/static/js/scripts.js' ),
	array( 'splide' ),
	$ext->manifest->get_version(),
	true
);

if ( ! function_exists( '_fw_pc_enqueue_design_css' ) ) :
	function _fw_pc_enqueue_design_css( $data ) {
		$atts = shortcode_parse_atts( $data['atts_string'] );
		if ( ! is_array( $atts ) ) { return; }
		$post_id = ( isset( $data['post'] ) && isset( $data['post']->ID ) ) ? $data['post']->ID : 0;
		$atts    = fw_ext_shortcodes_decode_attr( $atts, 'post_carousel', $post_id );
		if ( is_wp_error( $atts ) || ! is_array( $atts ) ) { return; }
		$design = isset( $atts['design'] ) && is_string( $atts['design'] ) ? sanitize_file_name( $atts['design'] ) : '';
		if ( $design === '' ) { return; }
		$path = dirname( __FILE__ ) . '/static/css/design/' . $design . '.css';
		if ( file_exists( $path ) ) {
			$ext = fw_ext( 'shortcodes' );
			wp_enqueue_style(
				'fw-shortcode-post-carousel-design-' . $design,
				$ext->get_declared_URI( '/shortcodes/post-carousel/static/css/design/' . $design . '.css' ),
				array( 'fw-shortcode-post-carousel' ),
				$ext->manifest->get_version()
			);
		}
	}
	add_action( 'fw_ext_shortcodes_enqueue_static:post_carousel', '_fw_pc_enqueue_design_css' );
endif;
