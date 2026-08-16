<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Card Rows — LIVE PREVIEW (shared).
 *
 * A visual companion to the shared Card Rows designer (sc_card_rows_field /
 * sc_card_rows_value / sc_card_rows_render live in shortcode-styling-helper.php).
 * This adds a schematic wireframe that redraws in the builder as the rows/slots
 * change — a shortcode drops in a `sc_card_preview_mount_html()` mount and the
 * preview JS wires it. Used by [wc_products], [team_member], etc.
 */

if ( ! function_exists( 'sc_card_preview_mount_html' ) ) :
	/**
	 * Markup for the live-preview mount (use as an `html-full` option's `html`).
	 *
	 * @param array $args heading, note, rows_name (the addable-popup option id, default card_rows).
	 */
	function sc_card_preview_mount_html( $args = array() ) {
		$args = array_merge( array(
			'heading'   => __( 'Card preview', 'fw' ),
			'note'      => __( 'Schematic only — a wireframe of the layout, not real content.', 'fw' ),
			'rows_name' => 'card_rows',
		), $args );

		return '<div class="upwc-cp-wrap">'
			. '<div class="upwc-cp-heading">' . esc_html( $args['heading'] ) . '</div>'
			. '<div class="upwc-cp-mount" data-upwc-card-preview data-cp-rows="' . esc_attr( $args['rows_name'] ) . '">'
			. '<div class="upwc-cp-card"><div class="upwc-cp-emptycard">' . esc_html__( 'Loading preview…', 'fw' ) . '</div></div>'
			. '</div>'
			. ( $args['note'] !== '' ? '<div class="upwc-cp-note">' . esc_html( $args['note'] ) . '</div>' : '' )
			. '</div>';
	}
endif;

if ( ! function_exists( '_sc_card_preview_enqueue' ) ) :
	/**
	 * Enqueue the shared preview JS/CSS in EVERY builder/options context — the backend
	 * post editor AND the front-end Live Editor shell (and any settings modal). All of
	 * them enqueue the framework's options-editor runtime (`fw-backend-options`), so we
	 * key off that at a late priority; post.php/post-new.php are also covered explicitly.
	 *
	 * @param string $hook Passed on admin_enqueue_scripts; empty on wp_enqueue_scripts.
	 */
	function _sc_card_preview_enqueue( $hook = '' ) {
		if ( ! function_exists( 'fw_ext' ) || ! fw_ext( 'page-builder' ) ) { return; }
		if ( wp_script_is( 'sc-card-preview', 'enqueued' ) ) { return; } // already loaded

		$is_builder_ctx = wp_script_is( 'fw-backend-options', 'enqueued' )
			|| in_array( $hook, array( 'post.php', 'post-new.php' ), true );
		if ( ! $is_builder_ctx ) { return; }

		$ext  = fw_ext( 'shortcodes' );
		$base = $ext->get_declared_URI( '/includes/card-preview' );
		$ver  = $ext->manifest->get_version();

		wp_enqueue_style( 'sc-card-preview', $base . '/card-preview.css', array(), $ver );
		wp_enqueue_script( 'sc-card-preview', $base . '/card-preview.js', array( 'jquery' ), $ver, true );
	}
	// Late priority so the options runtime (fw-backend-options) is already enqueued.
	add_action( 'admin_enqueue_scripts', '_sc_card_preview_enqueue', 100 );
	add_action( 'wp_enqueue_scripts', '_sc_card_preview_enqueue', 100 );
endif;
