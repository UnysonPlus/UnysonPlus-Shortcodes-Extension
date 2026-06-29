<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Built-in Theme Settings — loader.
 *
 * Surfaces the plugin-owned built-in sections (today: the Component Presets — Color,
 * Typography, Spacing, Buttons, Box/Border, Table) inside Appearance -> Theme
 * Settings, and makes that page available under ANY active theme — even non-Unyson
 * themes that ship no framework-customizations/theme/options/settings.php — by
 * forcing the menu on.
 *
 * Each section's schema lives in its own file in this folder (components-color.php,
 * components-typography.php, …) named like the theme's own option files, and is
 * loaded via upw_ts_get_options() (see helpers.php). New built-in sections (e.g.
 * Miscellaneous features ported from the theme) plug in the same way.
 *
 * Storage is THEME-SCOPED: the framework's standard Theme Settings save writes the
 * injected fields to fw_theme_settings_options:{theme-id}; the read side for presets
 * lives in includes/presets/store.php (unysonplus_preset_store_get). Each theme keeps
 * its own presets; switching themes resets/restores per theme.
 *
 * This replaces the dedicated "Component Presets" admin submenu (retired in
 * class-fw-shortcodes-settings-page.php) and the General -> Colors pointer tab the
 * parent theme used to show.
 */

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/miscellaneous-handlers.php';
require_once __DIR__ . '/settings-export-import.php';

if ( ! function_exists( 'upw_ts_misc_subtabs' ) ) :
	/**
	 * Built-in Miscellaneous sub-tabs (ported from the theme so they work under any
	 * theme). Each is a tab -> box wrapping the feature's schema file. Merged into the
	 * theme's Miscellaneous section by the fw_settings_options filter below.
	 *
	 * @return array tab_id => tab-definition.
	 */
	function upw_ts_misc_subtabs() {
		$defs = array(
			'tab_custom_css'     => array( 'title' => __( 'Custom CSS', 'fw' ),            'file' => 'miscellaneous-custom-css' ),
			'tab_custom_scripts' => array( 'title' => __( 'Custom Scripts', 'fw' ),        'file' => 'miscellaneous-scripts' ),
			'tab_analytics'      => array( 'title' => __( 'Analytics & Tracking', 'fw' ),  'file' => 'miscellaneous-analytics' ),
			'tab_performance'    => array( 'title' => __( 'Performance', 'fw' ),           'file' => 'miscellaneous-performance' ),
			'tab_404'            => array( 'title' => __( '404 Page', 'fw' ),              'file' => 'miscellaneous-404' ),
			'tab_maintenance'    => array( 'title' => __( 'Maintenance Mode', 'fw' ),      'file' => 'miscellaneous-maintenance' ),
			'tab_export_import'  => array( 'title' => __( 'Export / Import', 'fw' ),       'file' => 'miscellaneous-export-import' ),
			'tab_reset_settings' => array( 'title' => __( 'Reset Settings', 'fw' ),        'file' => 'miscellaneous-reset' ),
		);

		$subtabs = array();
		foreach ( $defs as $tab_id => $d ) {
			$opts = upw_ts_get_options( $d['file'] );
			if ( ! $opts ) {
				continue;
			}
			$subtabs[ $tab_id ] = array(
				'title'   => $d['title'],
				'type'    => 'tab',
				'options' => array(
					'box' => array(
						'title'   => $d['title'],
						'type'    => 'box',
						'options' => $opts,
					),
				),
			);
		}
		return $subtabs;
	}
endif;

if ( ! function_exists( 'unysonplus_presets_in_theme_settings_enabled' ) ) :
	/**
	 * Presets are surfaced unless the page builder's "bare" mode (Styling Presets
	 * off) is active — matching the old dedicated page's gate.
	 */
	function unysonplus_presets_in_theme_settings_enabled() {
		return ! function_exists( 'unysonplus_styling_presets_enabled' )
			|| unysonplus_styling_presets_enabled();
	}
endif;

/**
 * Force Appearance -> Theme Settings to register even when the active theme ships
 * no settings.php of its own, so the injected sections always have a host. The
 * framework still honours a theme's `disable_theme_settings_page` config (it
 * returns before this filter runs).
 */
add_filter( 'fw_theme_settings_menu_register', function ( $should_register ) {
	return unysonplus_presets_in_theme_settings_enabled() ? true : $should_register;
} );

/**
 * Enqueue the option-type statics (addable-box / button-presets / border-presets /
 * table-presets CSS + JS) on the Theme Settings page. The framework's own settings
 * enqueue does not reliably cover options injected via the fw_settings_options
 * filter, so without this the addable-box layout CSS (e.g. the .fw-preset-2col
 * 2-column grid) is missing and the preset rows stretch full-width. Mirrors what
 * the retired dedicated Component Presets page did.
 */
add_action( 'admin_enqueue_scripts', function () {
	if ( ! unysonplus_presets_in_theme_settings_enabled() || ! function_exists( 'fw' ) ) {
		return;
	}
	$slug = method_exists( fw()->backend, '_get_settings_page_slug' )
		? fw()->backend->_get_settings_page_slug()
		: 'fw-settings';
	$current = isset( $GLOBALS['plugin_page'] ) ? $GLOBALS['plugin_page'] : '';
	if ( $current !== $slug ) {
		return;
	}
	if ( function_exists( 'unysonplus_components_settings_options' ) ) {
		fw()->backend->enqueue_options_static( unysonplus_components_settings_options() );
	}
	// The built-in Miscellaneous sub-tabs use code-editor (CodeMirror) etc.; the
	// framework's settings enqueue does not reliably cover filter-injected options.
	fw()->backend->enqueue_options_static( upw_ts_misc_subtabs() );

	$ext = fw_ext( 'shortcodes' );
	if ( ! $ext ) {
		return;
	}
	$ver = $ext->manifest->get_version();

	// Layout containment: keep the wide addable-box preset grid (and the tab/option
	// wrappers around it) inside the content column instead of overflowing the page.
	wp_enqueue_style(
		'upw-presets-theme-settings',
		$ext->get_declared_URI( '/static/css/theme-settings-presets.css' ),
		array(),
		$ver
	);

	// Color Presets open/close fix (the native postbox toggle doesn't attach to the
	// color preset boxes in this nested-tab context; Typography/Spacing are unaffected).
	wp_enqueue_script(
		'upw-presets-theme-settings',
		$ext->get_declared_URI( '/static/js/theme-settings-presets.js' ),
		array(),
		$ver,
		true
	);

	// Optional overflow diagnostic — loads only with ?upw_preset_debug=1. Logs the
	// exact elements wider than the viewport (with their width chain) to the console.
	if ( isset( $_GET['upw_preset_debug'] ) && $_GET['upw_preset_debug'] === '1' ) {
		wp_enqueue_script(
			'upw-presets-theme-settings-debug',
			$ext->get_declared_URI( '/static/js/theme-settings-presets-debug.js' ),
			array(),
			$ver,
			true
		);
	}
} );

/**
 * Inject the built-in sections into Theme Settings:
 *   - Component Presets — a "Components" nav section placed right after General.
 *   - Miscellaneous features (Custom CSS / Scripts / Analytics) — merged INTO the
 *     theme's existing Miscellaneous section (or a created one), so the tab mixes
 *     plugin-provided and theme-provided sub-tabs.
 *
 * Reuses the canonical preset schema and the same misc_* storage keys, so saved
 * values round-trip through the standard settings save with no migration.
 */
add_filter( 'fw_settings_options', function ( $options ) {

	// --- Components (gated by the styling-presets "bare" mode) ---
	if ( unysonplus_presets_in_theme_settings_enabled() && function_exists( 'unysonplus_components_settings_options' ) ) {
		$libraries = unysonplus_components_settings_options();
		if ( is_array( $libraries ) && $libraries ) {
			// Structure mirrors the theme's own settings sections (tab -> box -> tab ->
			// box -> fields) — the only nesting Unyson reliably renders AND persists.
			$sub_tabs = array();
			foreach ( $libraries as $tab_id => $tab ) {
				$title = isset( $tab['title'] ) ? $tab['title'] : $tab_id;
				$inner = ( isset( $tab['options'] ) && is_array( $tab['options'] ) ) ? $tab['options'] : array();

				$sub_tabs[ $tab_id ] = array(
					'title'   => $title,
					'type'    => 'tab',
					'options' => array(
						$tab_id . '_box' => array(
							'type'    => 'box',
							'title'   => $title,
							'options' => array(
								'group_' . $tab_id => array(
									'type'    => 'group',
									'options' => $inner,
								),
							),
						),
					),
				);
			}

			$components_section = array(
				'components_container' => array(
					'title'   => __( 'Components', 'fw' ),
					'type'    => 'tab',
					'options' => array(
						'components' => array(
							'title'   => __( 'Component Presets', 'fw' ),
							'type'    => 'box',
							'options' => $sub_tabs,
						),
					),
				),
			);

			// Place it right after the first section (General).
			if ( is_array( $options ) && count( $options ) > 0 ) {
				array_splice( $options, 1, 0, array( $components_section ) );
			} else {
				$options[] = $components_section;
			}
		}
	}

	// --- Built-in Miscellaneous features (always; not gated by styling presets) ---
	$options = upw_ts_merge_into_misc( $options, upw_ts_misc_subtabs() );

	return $options;
} );


if ( ! function_exists( 'unysonplus_migrate_presets_to_theme_store' ) ) :
	/**
	 * One-time move of saved presets from the legacy theme-INDEPENDENT store
	 * (fw_ext_settings_options:shortcodes) into the CURRENT theme's theme-scoped
	 * settings (fw_theme_settings_options:{theme-id}). Runs once; the legacy store
	 * is left intact as a backup. Only seeds keys not already present in the theme
	 * store, and only the preset leaf keys (the Smooth Scroll toggle stays put).
	 */
	function unysonplus_migrate_presets_to_theme_store() {
		if ( get_option( 'upw_presets_theme_migrated' ) || ! class_exists( 'FW_WP_Option' ) || ! function_exists( 'fw' ) ) {
			return;
		}
		if ( ! function_exists( 'unysonplus_components_settings_options' ) ) {
			return; // schema not loaded yet; try again on a later request
		}

		$legacy = (array) FW_WP_Option::get( 'fw_ext_settings_options:shortcodes', null, array() );
		if ( $legacy ) {
			$keys = array();
			unysonplus_collect_preset_leaf_keys( unysonplus_components_settings_options(), $keys );

			$theme_key = 'fw_theme_settings_options:' . fw()->theme->manifest->get_id();
			$current   = (array) FW_WP_Option::get( $theme_key, null, array() );

			$changed = false;
			foreach ( array_keys( $keys ) as $k ) {
				if ( ! array_key_exists( $k, $current ) && array_key_exists( $k, $legacy ) ) {
					$current[ $k ] = $legacy[ $k ];
					$changed = true;
				}
			}
			if ( $changed ) {
				FW_WP_Option::set( $theme_key, null, $current );
			}
		}

		update_option( 'upw_presets_theme_migrated', 1 );
	}
endif;

// Seed the active theme's presets on the first request after upgrade (admin or
// front end), so reads switch cleanly to the theme store with no gap.
add_action( 'init', 'unysonplus_migrate_presets_to_theme_store', 20 );
