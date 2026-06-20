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

// Site-wide Smooth Scroll default. Individual pages override this from the
// page editor's "Smooth Scroll" box; read on the front end via
// fw_get_db_ext_settings_option( 'shortcodes', 'smooth_scroll_global' ).
$options = array_merge(
	array(
		'smooth_scroll_box' => array(
			'title'   => __( 'Smooth Scroll', 'fw' ),
			'type'    => 'box',
			'options' => array(
				'group_smooth_scroll' => array(
					'type'    => 'group',
					'options' => array(
						'smooth_scroll_global' => array(
							'label'        => __( 'Smooth Scroll site-wide', 'fw' ),
							'desc'         => __( 'Turn on buttery inertia scrolling (Lenis) on every front-end page by default. Individual pages can still override this (On / Off) from the page editor\'s Smooth Scroll box. Respects reduced-motion; loads only when on.', 'fw' ),
							'type'         => 'switch',
							'value'        => 'no',
							'left-choice'  => array( 'value' => 'no',  'label' => __( 'Off', 'fw' ) ),
							'right-choice' => array( 'value' => 'yes', 'label' => __( 'On', 'fw' ) ),
						),
					),
				),
			),
		),
	),
	$options
);
