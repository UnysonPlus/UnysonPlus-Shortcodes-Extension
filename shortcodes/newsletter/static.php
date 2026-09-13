<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$ext = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-newsletter',
	$ext->get_declared_URI( '/shortcodes/newsletter/static/css/styles.css' ),
	array(),
	$ext->manifest->get_version()
);

// The Button Preset option renders the submit as a themed .btn preset — pull the [button]
// shortcode's base CSS (display, padding, border, transitions) so it renders correctly. Colours
// come from the theme's globally-output Button presets (Theme Settings → Buttons). Without this,
// a page with a newsletter but no [button] element got the preset's colours over a browser-default
// button box. A no-op when no preset is chosen. Same wiring as the Pricing Table.
wp_enqueue_style(
	'fw-shortcode-button',
	fw_min_uri( $ext->get_declared_URI( '/shortcodes/button/static/css/styles.css' ) ),
	array(),
	$ext->manifest->get_version()
);

wp_enqueue_script(
	'fw-shortcode-newsletter',
	$ext->get_declared_URI( '/shortcodes/newsletter/static/js/scripts.js' ),
	array(),
	$ext->manifest->get_version(),
	true
);

if ( ! function_exists( '_fw_nl_enqueue_design_css' ) ) :
	function _fw_nl_enqueue_design_css( $data ) {
		$atts = shortcode_parse_atts( $data['atts_string'] );
		if ( ! is_array( $atts ) ) { return; }
		$post_id = ( isset( $data['post'] ) && isset( $data['post']->ID ) ) ? $data['post']->ID : 0;
		$atts    = fw_ext_shortcodes_decode_attr( $atts, 'newsletter', $post_id );
		if ( is_wp_error( $atts ) || ! is_array( $atts ) ) { return; }
		if ( function_exists( 'fw_sc_design_resolve' ) && function_exists( 'fw_sc_design_enqueue' ) ) {
			fw_sc_design_enqueue( 'newsletter', fw_sc_design_resolve( 'newsletter', $atts, 'inline' ) );
		}
		$design = isset( $atts['design'] ) && is_string( $atts['design'] ) ? sanitize_file_name( $atts['design'] ) : '';
		if ( $design === '' ) { return; }
		$path = dirname( __FILE__ ) . '/static/css/design/' . $design . '.css';
		if ( file_exists( $path ) ) {
			$ext = fw_ext( 'shortcodes' );
			wp_enqueue_style(
				'fw-shortcode-newsletter-design-' . $design,
				$ext->get_declared_URI( '/shortcodes/newsletter/static/css/design/' . $design . '.css' ),
				array( 'fw-shortcode-newsletter' ),
				$ext->manifest->get_version()
			);
		}
	}
	add_action( 'fw_ext_shortcodes_enqueue_static:newsletter', '_fw_nl_enqueue_design_css' );
endif;
