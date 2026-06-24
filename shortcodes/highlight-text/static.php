<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$ext = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-highlight-text',
	$ext->get_declared_URI( '/shortcodes/highlight-text/static/css/styles.css' ),
	array(),
	$ext->manifest->get_version()
);

if ( ! function_exists( '_fw_hl_enqueue_design_css' ) ) :
	function _fw_hl_enqueue_design_css( $data ) {
		$atts = shortcode_parse_atts( $data['atts_string'] );
		if ( ! is_array( $atts ) ) { return; }
		$post_id = ( isset( $data['post'] ) && isset( $data['post']->ID ) ) ? $data['post']->ID : 0;
		$atts    = fw_ext_shortcodes_decode_attr( $atts, 'highlight_text', $post_id );
		if ( is_wp_error( $atts ) || ! is_array( $atts ) ) { return; }
		$fx = isset( $atts['fx'] ) && is_string( $atts['fx'] ) ? sanitize_file_name( $atts['fx'] ) : '';
		if ( $fx === '' ) { return; }
		$path = dirname( __FILE__ ) . '/static/css/design/' . $fx . '.css';
		if ( file_exists( $path ) ) {
			$ext = fw_ext( 'shortcodes' );
			wp_enqueue_style(
				'fw-shortcode-highlight-text-design-' . $fx,
				$ext->get_declared_URI( '/shortcodes/highlight-text/static/css/design/' . $fx . '.css' ),
				array( 'fw-shortcode-highlight-text' ),
				$ext->manifest->get_version()
			);
		}
	}
	add_action( 'fw_ext_shortcodes_enqueue_static:highlight_text', '_fw_hl_enqueue_design_css' );
endif;
