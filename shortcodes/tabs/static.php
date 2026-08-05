<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-tabs',
	fw_min_uri( $shortcodes_extension->get_uri( '/shortcodes/tabs/static/css/styles.css' ) ),
	array( 'fw-ext-builder-frontend-grid' ) // .fw- grid (vertical tabs) + utilities
);

// Self-contained tab switching + keyboard/roving-tabindex/accordion/popover — vanilla JS.
wp_enqueue_script(
	'fw-shortcode-tabs',
	fw_min_uri( $shortcodes_extension->get_uri( '/shortcodes/tabs/static/js/scripts.js' ) ),
	array(),
	$shortcodes_extension->manifest->get_version(),
	true
);

/* Per-instance design gating — loads an installed skin PACK's CSS (via the pluggable
   layer) and any built-in per-design file static/css/design/<key>.css if present.
   Built-in designs ship their CSS in the base styles.css, so those are no-ops. */
if ( ! function_exists( '_fw_tabs_enqueue_design_css' ) ) :
	function _fw_tabs_enqueue_design_css( $data ) {
		$atts = shortcode_parse_atts( $data['atts_string'] );
		if ( ! is_array( $atts ) ) { return; }
		$post_id = ( isset( $data['post'] ) && isset( $data['post']->ID ) ) ? $data['post']->ID : 0;
		$atts    = fw_ext_shortcodes_decode_attr( $atts, 'tabs', $post_id );
		if ( is_wp_error( $atts ) || ! is_array( $atts ) ) { return; }

		if ( function_exists( 'fw_sc_design_resolve' ) && function_exists( 'fw_sc_design_enqueue' ) ) {
			fw_sc_design_enqueue( 'tabs', fw_sc_design_resolve( 'tabs', $atts, 'underline' ) );
		}

		$design = '';
		if ( function_exists( 'fw_sc_design_resolve' ) ) {
			$design = fw_sc_design_resolve( 'tabs', $atts, '' );
		}
		if ( $design === '' ) { $design = isset( $atts['tab_style'] ) ? sanitize_file_name( (string) $atts['tab_style'] ) : ''; }
		if ( $design === '' ) { return; }
		$path = dirname( __FILE__ ) . '/static/css/design/' . $design . '.css';
		if ( file_exists( $path ) ) {
			$ext = fw_ext( 'shortcodes' );
			wp_enqueue_style(
				'fw-shortcode-tabs-design-' . $design,
				$ext->get_declared_URI( '/shortcodes/tabs/static/css/design/' . $design . '.css' ),
				array( 'fw-shortcode-tabs' ),
				$ext->manifest->get_version()
			);
		}
	}
	add_action( 'fw_ext_shortcodes_enqueue_static:tabs', '_fw_tabs_enqueue_design_css' );
endif;
