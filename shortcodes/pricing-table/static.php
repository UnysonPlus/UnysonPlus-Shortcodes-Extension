<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$ext = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-pricing-table',
	$ext->get_declared_URI( '/shortcodes/pricing-table/static/css/styles.css' ),
	array(),
	$ext->manifest->get_version()
);

// The Button Preset option renders each plan button as a themed .btn preset — pull the [button]
// shortcode's base CSS so it renders correctly. Colours come from the theme's globally-output
// Button presets (Theme Settings → Buttons); a no-op when no preset is used.
wp_enqueue_style(
	'fw-shortcode-button',
	fw_min_uri( $ext->get_declared_URI( '/shortcodes/button/static/css/styles.css' ) ),
	array(),
	$ext->manifest->get_version()
);

// Monthly / Yearly billing toggle behavior (dependency-free; inert when no toggle is present).
wp_enqueue_script(
	'fw-shortcode-pricing-table',
	$ext->get_declared_URI( '/shortcodes/pricing-table/static/js/scripts.js' ),
	array(),
	$ext->manifest->get_version(),
	true
);

/* Per-design CSS gating (none ship; base covers all designs). */
if ( ! function_exists( '_fw_pt_enqueue_design_css' ) ) :
	function _fw_pt_enqueue_design_css( $data ) {
		$atts = shortcode_parse_atts( $data['atts_string'] );
		if ( ! is_array( $atts ) ) { return; }
		$post_id = ( isset( $data['post'] ) && isset( $data['post']->ID ) ) ? $data['post']->ID : 0;
		$atts    = fw_ext_shortcodes_decode_attr( $atts, 'pricing_table', $post_id );
		if ( is_wp_error( $atts ) || ! is_array( $atts ) ) { return; }
		// Enqueue the active design's assets via the pluggable layer — this loads an
		// installed SKIN PACK's styles.css (scoped to .design-<key>). Built-in skins
		// ship no per-design CSS, so this is a no-op for them.
		if ( function_exists( 'fw_sc_design_resolve' ) && function_exists( 'fw_sc_design_enqueue' ) ) {
			fw_sc_design_enqueue( 'pricing_table', fw_sc_design_resolve( 'pricing_table', $atts, 'classic' ) );
		}

		$design = isset( $atts['design'] ) && is_string( $atts['design'] ) ? sanitize_file_name( $atts['design'] ) : '';
		if ( $design === '' ) { return; }
		$path = dirname( __FILE__ ) . '/static/css/design/' . $design . '.css';
		if ( file_exists( $path ) ) {
			$ext = fw_ext( 'shortcodes' );
			wp_enqueue_style(
				'fw-shortcode-pricing-table-design-' . $design,
				$ext->get_declared_URI( '/shortcodes/pricing-table/static/css/design/' . $design . '.css' ),
				array( 'fw-shortcode-pricing-table' ),
				$ext->manifest->get_version()
			);
		}
	}
	add_action( 'fw_ext_shortcodes_enqueue_static:pricing_table', '_fw_pt_enqueue_design_css' );
endif;
