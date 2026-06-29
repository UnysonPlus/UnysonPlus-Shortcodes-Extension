<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Component Presets in Theme Settings.
 *
 * Surfaces the plugin-owned Component Presets (Color, Typography, Spacing,
 * Buttons, Box/Border, Table) inside Appearance -> Theme Settings, and makes that
 * page available under ANY active theme — even non-Unyson themes that ship no
 * framework-customizations/theme/options/settings.php — by forcing the menu on.
 *
 * Storage is THEME-SCOPED: the framework's standard Theme Settings save writes the
 * injected fields to fw_theme_settings_options:{theme-id}; the read side lives in
 * includes/presets/store.php (unysonplus_preset_store_get). Each theme therefore
 * keeps its own presets; switching themes resets/restores per theme.
 *
 * This replaces the dedicated "Component Presets" admin submenu (retired in
 * class-fw-shortcodes-settings-page.php) and the General -> Colors pointer tab the
 * parent theme used to show.
 */

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
 * no settings.php of its own, so the injected presets always have a host. The
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
 * Inject the Component Presets as a top-level "Components" section in Theme
 * Settings. Reuses the canonical schema (unysonplus_components_settings_options)
 * verbatim — the six library tabs, each wrapped in a box -> group card exactly as
 * the old dedicated page rendered them — so the leaf option ids (and therefore the
 * saved values) are unchanged and round-trip through the standard settings save.
 */
add_filter( 'fw_settings_options', function ( $options ) {
	if ( ! unysonplus_presets_in_theme_settings_enabled() ) {
		return $options;
	}
	if ( ! function_exists( 'unysonplus_components_settings_options' ) ) {
		return $options;
	}

	$libraries = unysonplus_components_settings_options();
	if ( ! is_array( $libraries ) || ! $libraries ) {
		return $options;
	}

	// Structure mirrors the theme's own settings sections (tab -> box -> tab ->
	// box -> fields, e.g. general-settings.php / misc.php) — the only nesting Unyson
	// reliably renders AND persists on the Theme Settings page. Each library is a
	// sub-tab wrapped in a box -> group card. (The preset grid's width is capped by
	// CSS in the enqueue handler above, because in this nested-tab context the grid
	// container is not width-constrained and would otherwise overflow.)
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

	// Place the Components section right after the first section (General) in the
	// left nav, instead of appending it after Miscellaneous.
	if ( is_array( $options ) && count( $options ) > 0 ) {
		array_splice( $options, 1, 0, array( $components_section ) );
	} else {
		$options[] = $components_section;
	}

	return $options;
} );


if ( ! function_exists( 'unysonplus_collect_preset_leaf_keys' ) ) :
	/**
	 * Walk the components schema and collect the LEAF option ids (the keys that
	 * actually store a value). Containers (tab / box / group) hold no value of their
	 * own, so we recurse through them. Used by the migration to copy exactly the
	 * preset keys — and nothing else (e.g. the Smooth Scroll toggle) — out of the
	 * legacy extension store.
	 *
	 * @param array $options Options array.
	 * @param array $keys    Accumulator (id => true), passed by reference.
	 */
	function unysonplus_collect_preset_leaf_keys( array $options, array &$keys ) {
		foreach ( $options as $id => $opt ) {
			if ( ! is_array( $opt ) ) {
				continue;
			}
			$type = isset( $opt['type'] ) ? $opt['type'] : '';
			if ( in_array( $type, array( 'tab', 'box', 'group' ), true ) ) {
				if ( isset( $opt['options'] ) && is_array( $opt['options'] ) ) {
					unysonplus_collect_preset_leaf_keys( $opt['options'], $keys );
				}
				continue;
			}
			// A real option (addable-box, button-presets, short-select, …) stores
			// its whole value under $id.
			if ( is_string( $id ) && $id !== '' ) {
				$keys[ $id ] = true;
			}
		}
	}
endif;

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
