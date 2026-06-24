<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-before-after',
	$shortcodes_extension->get_declared_URI( '/shortcodes/before-after/static/css/styles.css' ),
	array(),
	$shortcodes_extension->manifest->get_version()
);

wp_enqueue_script(
	'fw-shortcode-before-after',
	$shortcodes_extension->get_declared_URI( '/shortcodes/before-after/static/js/scripts.js' ),
	array(),
	$shortcodes_extension->manifest->get_version(),
	true
);

/* ---------------------------------------------------------------------------
 * Per-design CSS — only the DESIGN actually used by each instance loads. The
 * base styles.css carries every skin, so there are no per-design files yet; add
 * static/css/design/<design-key>.css and it auto-loads when that design is used.
 * ------------------------------------------------------------------------- */
if ( ! function_exists( '_fw_bac_enqueue_design_css' ) ) :
	function _fw_bac_enqueue_design_css( $data ) {
		$atts = shortcode_parse_atts( $data['atts_string'] );
		if ( ! is_array( $atts ) ) {
			return;
		}
		$post_id = ( isset( $data['post'] ) && isset( $data['post']->ID ) ) ? $data['post']->ID : 0;
		$atts    = fw_ext_shortcodes_decode_attr( $atts, 'before_after', $post_id );
		if ( is_wp_error( $atts ) || ! is_array( $atts ) ) {
			return;
		}

		$design = isset( $atts['design'] ) && is_string( $atts['design'] ) ? sanitize_file_name( $atts['design'] ) : '';
		if ( $design === '' ) {
			return;
		}

		$path = dirname( __FILE__ ) . '/static/css/design/' . $design . '.css';
		if ( file_exists( $path ) ) {
			$ext = fw_ext( 'shortcodes' );
			wp_enqueue_style(
				'fw-shortcode-before-after-design-' . $design,
				$ext->get_declared_URI( '/shortcodes/before-after/static/css/design/' . $design . '.css' ),
				array( 'fw-shortcode-before-after' ),
				$ext->manifest->get_version()
			);
		}
	}
	add_action( 'fw_ext_shortcodes_enqueue_static:before_after', '_fw_bac_enqueue_design_css' );
endif;
