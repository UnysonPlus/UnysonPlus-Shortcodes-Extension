<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$ext = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-logo-grid',
	$ext->get_declared_URI( '/shortcodes/logo-grid/static/css/styles.css' ),
	array(),
	$ext->manifest->get_version()
);

/* Per-design: the Carousel layout needs Splide + the small mount script. */
if ( ! function_exists( '_fw_lg_enqueue_design_static' ) ) :
	function _fw_lg_enqueue_design_static( $data ) {
		$atts = shortcode_parse_atts( $data['atts_string'] );
		if ( ! is_array( $atts ) ) { return; }
		$post_id = ( isset( $data['post'] ) && isset( $data['post']->ID ) ) ? $data['post']->ID : 0;
		$atts    = fw_ext_shortcodes_decode_attr( $atts, 'logo_grid', $post_id );
		if ( is_wp_error( $atts ) || ! is_array( $atts ) ) { return; }
		$design = isset( $atts['design'] ) && is_string( $atts['design'] ) ? $atts['design'] : 'grid';
		$registry = require dirname( __FILE__ ) . '/views/parts/registry.php';
		if ( ! isset( $registry[ $design ] ) ) { $design = 'grid'; }
		$ext = fw_ext( 'shortcodes' );

		if ( ! empty( $registry[ $design ]['splide'] ) ) {
			wp_enqueue_style( 'splide', $ext->get_declared_URI( '/shortcodes/carousel/static/vendor/splide-core.min.css' ) );
			wp_enqueue_script( 'splide', $ext->get_declared_URI( '/shortcodes/carousel/static/vendor/splide.min.js' ), array(), '4.1.4', true );
			wp_enqueue_script(
				'fw-shortcode-logo-grid-carousel',
				$ext->get_declared_URI( '/shortcodes/logo-grid/static/js/carousel.js' ),
				array( 'splide' ),
				$ext->manifest->get_version(),
				true
			);
		}

		$css = dirname( __FILE__ ) . '/static/css/design/' . sanitize_file_name( $design ) . '.css';
		if ( file_exists( $css ) ) {
			wp_enqueue_style(
				'fw-shortcode-logo-grid-design-' . sanitize_file_name( $design ),
				$ext->get_declared_URI( '/shortcodes/logo-grid/static/css/design/' . sanitize_file_name( $design ) . '.css' ),
				array( 'fw-shortcode-logo-grid' ),
				$ext->manifest->get_version()
			);
		}
	}
	add_action( 'fw_ext_shortcodes_enqueue_static:logo_grid', '_fw_lg_enqueue_design_static' );
endif;
