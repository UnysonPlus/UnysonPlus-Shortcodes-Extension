<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * Canonical schema for the shortcode "preset" settings (Color Presets,
 * Typography, Spacing/Gap, Buttons, Borders, Tables).
 *
 * SINGLE SOURCE OF TRUTH — owned by the plugin, theme-independent. Rendered by
 * Unyson's extension settings form (see settings-options.php) and saved to the
 * theme-independent store `fw_ext_settings_options:shortcodes`. The getters in
 * framework/includes/presets.php read the same keys back via
 * unysonplus_preset_store_get().
 *
 * Replaces both the old theme option fragments (general-colors / -typography /
 * -spacing / -buttons / -borders / -tables) and the stale Theme-Settings
 * injection schema, so there is exactly one definition.
 *
 * The six library schemas are split into per-tab files in the theme-settings/ folder
 * (components-color.php, components-typography.php, …) — named like the theme's own
 * option files — and assembled below via upw_ts_get_options(). This stays the single
 * public entry point (unysonplus_components_settings_options()).
 */

require_once __DIR__ . '/theme-settings/helpers.php';

if ( ! function_exists( 'unysonplus_components_color_choices' ) ) :
	/**
	 * Compact-color-picker choices from the current Color Presets:
	 *   slug => array( 'label' => Name, 'color' => #hex )
	 * Wired into every preset's color fields; css-tokens.php resolves the saved
	 * slug back to a hex when emitting CSS.
	 */
	function unysonplus_components_color_choices() {
		$choices = array();
		if ( function_exists( 'unysonplus_get_color_presets' ) ) {
			foreach ( unysonplus_get_color_presets() as $cp ) {
				if ( empty( $cp['name'] ) || empty( $cp['color'] ) ) { continue; }
				$slug = trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( $cp['name'] ) ), '-' );
				if ( $slug === '' ) { continue; }
				$choices[ $slug ] = array( 'label' => $cp['name'], 'color' => $cp['color'] );
			}
		}
		return $choices;
	}
endif;

if ( ! function_exists( 'unysonplus_components_settings_options' ) ) :
	function unysonplus_components_settings_options() {
		$color_choices = unysonplus_components_color_choices();

		// Default-Gap select choices, passed into the spacing schema.
		$gap_choices = function ( $empty_label ) {
			return function_exists( 'sc_get_gap_select_choices' )
				? sc_get_gap_select_choices( $empty_label )
				: array( '' => $empty_label );
		};

		$tab = function ( $title, $options ) {
			return array( 'title' => $title, 'type' => 'tab', 'options' => $options );
		};

		// Each library lives in its own file in theme-settings/ (named like the
		// theme's own option files). The color presets feed the button / box / table
		// color pickers; the gap-choices closure feeds the spacing selects.
		return apply_filters( 'unysonplus_components_settings_options', array(
			'tab_colors'     => $tab( __( 'Color Presets', 'fw' ), upw_ts_get_options( 'components-color' ) ),
			'tab_typography' => $tab( __( 'Text Styles', 'fw' ), upw_ts_get_options( 'components-typography' ) ),
			'tab_spacing'    => $tab( __( 'Spacing', 'fw' ), upw_ts_get_options( 'components-spacing', array( 'gap_choices' => $gap_choices ) ) ),
			'tab_buttons'    => $tab( __( 'Buttons', 'fw' ), upw_ts_get_options( 'components-buttons', array( 'color_choices' => $color_choices ) ) ),
			'tab_borders'    => $tab( __( 'Box Presets', 'fw' ), upw_ts_get_options( 'components-box', array( 'color_choices' => $color_choices ) ) ),
			'tab_tables'     => $tab( __( 'Tables', 'fw' ), upw_ts_get_options( 'components-table', array( 'color_choices' => $color_choices ) ) ),
			'tab_sections'   => $tab( __( 'Section Styles', 'fw' ), upw_ts_get_options( 'components-section-styles', array( 'color_choices' => $color_choices ) ) ),
		) );
	}
endif;
