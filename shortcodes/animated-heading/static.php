<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$ext = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-animated-heading',
	$ext->get_declared_URI( '/shortcodes/animated-heading/static/css/styles.css' ),
	array(),
	$ext->manifest->get_version()
);
wp_enqueue_script(
	'fw-shortcode-animated-heading',
	$ext->get_declared_URI( '/shortcodes/animated-heading/static/js/scripts.js' ),
	array(),
	$ext->manifest->get_version(),
	true
);

if ( ! function_exists( '_fw_ah_enqueue_design_css' ) ) :
	function _fw_ah_enqueue_design_css( $data ) {
		$atts = shortcode_parse_atts( $data['atts_string'] );
		if ( ! is_array( $atts ) ) { return; }
		$post_id = ( isset( $data['post'] ) && isset( $data['post']->ID ) ) ? $data['post']->ID : 0;
		$atts    = fw_ext_shortcodes_decode_attr( $atts, 'animated_heading', $post_id );
		if ( is_wp_error( $atts ) || ! is_array( $atts ) ) { return; }
		$anim = isset( $atts['anim'] ) && is_string( $atts['anim'] ) ? sanitize_file_name( $atts['anim'] ) : '';
		if ( $anim === '' ) { return; }
		$path = dirname( __FILE__ ) . '/static/css/design/' . $anim . '.css';
		if ( file_exists( $path ) ) {
			$ext = fw_ext( 'shortcodes' );
			wp_enqueue_style(
				'fw-shortcode-animated-heading-design-' . $anim,
				$ext->get_declared_URI( '/shortcodes/animated-heading/static/css/design/' . $anim . '.css' ),
				array( 'fw-shortcode-animated-heading' ),
				$ext->manifest->get_version()
			);
		}
	}
	add_action( 'fw_ext_shortcodes_enqueue_static:animated_heading', '_fw_ah_enqueue_design_css' );
endif;
