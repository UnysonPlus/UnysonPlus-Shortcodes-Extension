<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$ext = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-steps',
	$ext->get_declared_URI( '/shortcodes/steps/static/css/styles.css' ),
	array(),
	$ext->manifest->get_version()
);

/* ---------------------------------------------------------------------------
 * Per-design CSS — only the DESIGN actually used by each instance loads.
 * The base styles.css above carries the shared marker/connector/content CSS;
 * each design's own layout CSS lives in static/css/design/<design>.css and is
 * enqueued only for instances that pick it (the gallery anti-bloat pattern).
 * ------------------------------------------------------------------------- */
if ( ! function_exists( '_fw_steps_enqueue_design_css' ) ) :
	function _fw_steps_enqueue_design_css( $data ) {
		$atts = shortcode_parse_atts( $data['atts_string'] );
		if ( ! is_array( $atts ) ) {
			return;
		}
		$post_id = ( isset( $data['post'] ) && isset( $data['post']->ID ) ) ? $data['post']->ID : 0;
		$atts    = fw_ext_shortcodes_decode_attr( $atts, 'steps', $post_id );
		if ( is_wp_error( $atts ) || ! is_array( $atts ) ) {
			return;
		}

		$design = fw_akg( 'design', $atts, 'horizontal' );
		$design = is_string( $design ) ? sanitize_file_name( $design ) : 'horizontal';
		if ( $design === '' ) {
			return;
		}

		$path = dirname( __FILE__ ) . '/static/css/design/' . $design . '.css';
		if ( file_exists( $path ) ) {
			$ext = fw_ext( 'shortcodes' );
			wp_enqueue_style(
				'fw-shortcode-steps-design-' . $design,
				$ext->get_declared_URI( '/shortcodes/steps/static/css/design/' . $design . '.css' ),
				array( 'fw-shortcode-steps' ),
				$ext->manifest->get_version()
			);
		}
	}
	add_action( 'fw_ext_shortcodes_enqueue_static:steps', '_fw_steps_enqueue_design_css' );
endif;
