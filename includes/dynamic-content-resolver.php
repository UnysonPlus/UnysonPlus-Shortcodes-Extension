<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Dynamic Content — frontend resolution for shortcodes / page-builder.
 *
 * Every shortcode renders through FW_Shortcode::_render(), which applies the
 * `fw_shortcode_render_view:atts` filter on its attributes immediately before the
 * view is rendered (and before the view's wp_kses_post() / esc_attr() escaping).
 * Resolving {{tokens}} here means the live value inherits the view's existing,
 * correct escaping. The page builder renders elements through this same path, so
 * it is covered automatically.
 *
 * Registered once; no-ops cheaply on atts that contain no token.
 */
add_filter( 'fw_shortcode_render_view:atts', '_fw_ext_shortcodes_resolve_dynamic_content', 10, 2 );

if ( ! function_exists( '_fw_ext_shortcodes_resolve_dynamic_content' ) ) :
	/**
	 * @param array  $atts
	 * @param string $tag
	 * @return array
	 */
	function _fw_ext_shortcodes_resolve_dynamic_content( $atts, $tag = '' ) {
		if ( ! function_exists( 'fw_dynamic_content' ) || ! is_array( $atts ) ) {
			return $atts;
		}

		$context = array( 'post_id' => (int) get_the_ID() );

		return fw_dynamic_content()->resolve_recursive( $atts, $context );
	}
endif;
