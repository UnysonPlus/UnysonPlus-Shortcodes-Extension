<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$ext = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-tooltip',
	$ext->get_declared_URI( '/shortcodes/tooltip/static/css/styles.css' ),
	array(),
	$ext->manifest->get_version()
);
wp_enqueue_script(
	'fw-shortcode-tooltip',
	$ext->get_declared_URI( '/shortcodes/tooltip/static/js/scripts.js' ),
	array(),
	$ext->manifest->get_version(),
	true
);

if ( ! function_exists( '_fw_tt_enqueue_design_css' ) ) :
	function _fw_tt_enqueue_design_css( $data ) {
		$atts = shortcode_parse_atts( $data['atts_string'] );
		if ( ! is_array( $atts ) ) { return; }
		$post_id = ( isset( $data['post'] ) && isset( $data['post']->ID ) ) ? $data['post']->ID : 0;
		$atts    = fw_ext_shortcodes_decode_attr( $atts, 'tooltip', $post_id );
		if ( is_wp_error( $atts ) || ! is_array( $atts ) ) { return; }
		$design = isset( $atts['design'] ) && is_string( $atts['design'] ) ? sanitize_file_name( $atts['design'] ) : '';
		if ( $design === '' ) { return; }
		$path = dirname( __FILE__ ) . '/static/css/design/' . $design . '.css';
		if ( file_exists( $path ) ) {
			$ext = fw_ext( 'shortcodes' );
			wp_enqueue_style(
				'fw-shortcode-tooltip-design-' . $design,
				$ext->get_declared_URI( '/shortcodes/tooltip/static/css/design/' . $design . '.css' ),
				array( 'fw-shortcode-tooltip' ),
				$ext->manifest->get_version()
			);
		}
	}
	add_action( 'fw_ext_shortcodes_enqueue_static:tooltip', '_fw_tt_enqueue_design_css' );
endif;
