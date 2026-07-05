<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$ext = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-avatar',
	$ext->get_declared_URI( '/shortcodes/avatar/static/css/styles.css' ),
	array(),
	$ext->manifest->get_version()
);

/* ---------------------------------------------------------------------------
 * Per-design CSS — only the DESIGN treatment actually used by each instance
 * loads. The base styles.css above carries the shared avatar/group/status/chip
 * CSS; each design's own CSS (ring, bordered, shadow, soft tint) lives in
 * static/css/design/<design>.css and is enqueued only for instances that pick
 * it. The default 'plain' design has no file — it is covered by the base.
 * ------------------------------------------------------------------------- */
if ( ! function_exists( '_fw_avatar_enqueue_design_css' ) ) :
	function _fw_avatar_enqueue_design_css( $data ) {
		$atts = shortcode_parse_atts( $data['atts_string'] );
		if ( ! is_array( $atts ) ) {
			return;
		}
		$post_id = ( isset( $data['post'] ) && isset( $data['post']->ID ) ) ? $data['post']->ID : 0;
		$atts    = fw_ext_shortcodes_decode_attr( $atts, 'avatar', $post_id );
		if ( is_wp_error( $atts ) || ! is_array( $atts ) ) {
			return;
		}

		$design = fw_akg( 'design', $atts, 'plain' );
		$design = is_string( $design ) ? sanitize_file_name( $design ) : 'plain';
		if ( $design === '' ) {
			return;
		}

		$path = dirname( __FILE__ ) . '/static/css/design/' . $design . '.css';
		if ( file_exists( $path ) ) {
			$ext = fw_ext( 'shortcodes' );
			wp_enqueue_style(
				'fw-shortcode-avatar-design-' . $design,
				$ext->get_declared_URI( '/shortcodes/avatar/static/css/design/' . $design . '.css' ),
				array( 'fw-shortcode-avatar' ),
				$ext->manifest->get_version()
			);
		}
	}
	add_action( 'fw_ext_shortcodes_enqueue_static:avatar', '_fw_avatar_enqueue_design_css' );
endif;
