<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * Shortcodes extension settings form.
 *
 * Unyson core (FW_Extension::get_settings_options) loads this file and renders
 * `$options` as the extension's Settings page (Unyson+ → Extensions → Shortcodes
 * → Settings), saving to the THEME-INDEPENDENT store `fw_ext_settings_options:
 * shortcodes`. This is the single, plugin-owned home for the shortcode preset
 * libraries (Color Presets, Typography, Spacing, Buttons, Borders, Tables) — it
 * works identically under any active theme.
 *
 * The schema itself lives in includes/components-options.php (one source of
 * truth); the getters in framework/includes/presets.php read these same keys
 * back via unysonplus_preset_store_get().
 */

$options = function_exists( 'unysonplus_components_settings_options' )
	? unysonplus_components_settings_options()
	: array();
