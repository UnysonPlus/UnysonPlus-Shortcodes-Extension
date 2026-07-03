<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$ext = fw_ext( 'shortcodes' );
$dir = dirname( __FILE__ );

// Cache-bust CSS/JS on the file mtime so every edit forces a fresh download even when the
// extension version is unchanged (avoids stale-CSS surprises).
$css_ver = file_exists( $dir . '/static/css/styles.css' ) ? filemtime( $dir . '/static/css/styles.css' ) : $ext->manifest->get_version();
$js_ver  = file_exists( $dir . '/static/js/scripts.js' ) ? filemtime( $dir . '/static/js/scripts.js' ) : $ext->manifest->get_version();

// Base button styles (.btn + icon sizing) so the back-face button — which reuses the
// [button] preset classes (btn-{preset}) — renders. The color presets come from the
// theme's Buttons CSS (loaded globally).
wp_enqueue_style(
	'fw-shortcode-button',
	fw_min_uri( $ext->get_declared_URI( '/shortcodes/button/static/css/styles.css' ) )
);

wp_enqueue_style(
	'fw-shortcode-flip-box',
	$ext->get_declared_URI( '/shortcodes/flip-box/static/css/styles.css' ),
	array( 'fw-shortcode-button' ),
	$css_ver
);
wp_enqueue_script(
	'fw-shortcode-flip-box',
	$ext->get_declared_URI( '/shortcodes/flip-box/static/js/scripts.js' ),
	array(),
	$js_ver,
	true
);

/* Per-design CSS gating (none ship; base covers all). */
if ( ! function_exists( '_fw_fb_enqueue_design_css' ) ) :
	function _fw_fb_enqueue_design_css( $data ) {
		$atts = shortcode_parse_atts( $data['atts_string'] );
		if ( ! is_array( $atts ) ) { return; }
		$post_id = ( isset( $data['post'] ) && isset( $data['post']->ID ) ) ? $data['post']->ID : 0;
		$atts    = fw_ext_shortcodes_decode_attr( $atts, 'flip_box', $post_id );
		if ( is_wp_error( $atts ) || ! is_array( $atts ) ) { return; }
		$design = '';
		if ( isset( $atts['design_settings'] ) && is_array( $atts['design_settings'] ) && ! empty( $atts['design_settings']['skin'] ) ) {
			$design = sanitize_file_name( (string) $atts['design_settings']['skin'] );
		} elseif ( isset( $atts['design'] ) && is_string( $atts['design'] ) ) {
			$design = sanitize_file_name( $atts['design'] ); // legacy scalar
		}
		if ( $design === '' ) { return; }
		$path = dirname( __FILE__ ) . '/static/css/design/' . $design . '.css';
		if ( file_exists( $path ) ) {
			$ext = fw_ext( 'shortcodes' );
			wp_enqueue_style(
				'fw-shortcode-flip-box-design-' . $design,
				$ext->get_declared_URI( '/shortcodes/flip-box/static/css/design/' . $design . '.css' ),
				array( 'fw-shortcode-flip-box' ),
				$ext->manifest->get_version()
			);
		}
	}
	add_action( 'fw_ext_shortcodes_enqueue_static:flip_box', '_fw_fb_enqueue_design_css' );
endif;
