<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

// Modern calendar — no Bootstrap 3 / jQuery / Underscore / jstz. Just one CSS
// file and one tiny vanilla-JS file for month navigation.
$ext = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-calendar',
	$ext->get_declared_URI( '/shortcodes/calendar/static/css/styles.css' ),
	array(),
	$ext->manifest->get_version()
);
wp_enqueue_script(
	'fw-shortcode-calendar',
	$ext->get_declared_URI( '/shortcodes/calendar/static/js/scripts.js' ),
	array(),
	$ext->manifest->get_version(),
	true
);

/* ---------------------------------------------------------------------------
 * Per-design (skin) CSS — only the DESIGN actually used by each instance loads.
 * The base styles.css above carries the shared grid/list/nav structure; each
 * skin's own CSS lives in static/css/design/<design>.css and is enqueued here
 * only for instances that pick it (the gallery/image-box anti-bloat pattern).
 * The default 'classic' skin has no file — it is covered by the base.
 * ------------------------------------------------------------------------- */
if ( ! function_exists( '_fw_calendar_enqueue_design_css' ) ) :
	function _fw_calendar_enqueue_design_css( $data ) {
		$atts = shortcode_parse_atts( $data['atts_string'] );
		if ( ! is_array( $atts ) ) {
			return;
		}
		$post_id = ( isset( $data['post'] ) && isset( $data['post']->ID ) ) ? $data['post']->ID : 0;
		$atts    = fw_ext_shortcodes_decode_attr( $atts, 'calendar', $post_id );
		if ( is_wp_error( $atts ) || ! is_array( $atts ) ) {
			return;
		}

		$design = fw_akg( 'design', $atts, 'classic' );
		$design = is_string( $design ) ? sanitize_file_name( $design ) : 'classic';
		if ( $design === '' ) {
			return;
		}

		$path = dirname( __FILE__ ) . '/static/css/design/' . $design . '.css';
		if ( file_exists( $path ) ) {
			$ext = fw_ext( 'shortcodes' );
			wp_enqueue_style(
				'fw-shortcode-calendar-design-' . $design,
				$ext->get_declared_URI( '/shortcodes/calendar/static/css/design/' . $design . '.css' ),
				array( 'fw-shortcode-calendar' ),
				$ext->manifest->get_version()
			);
		}
	}
	add_action( 'fw_ext_shortcodes_enqueue_static:calendar', '_fw_calendar_enqueue_design_css' );
endif;
