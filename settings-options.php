<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * Shortcodes extension settings form.
 *
 * Unyson core (FW_Extension::get_settings_options) loads this file and renders
 * `$options` as the extension's Settings page (Unyson+ → Extensions → Shortcodes
 * → Settings), saving to the store `fw_ext_settings_options:shortcodes`.
 *
 * The Component Presets (Color, Typography, Spacing, Buttons, Box, Table) USED to
 * live here; they now live in Appearance → Theme Settings → Components and are
 * stored THEME-SCOPED (see includes/theme-settings-presets.php). Only the
 * site-wide Smooth Scroll default remains on this theme-independent form.
 */

// Site-wide Smooth Scroll default. Individual pages override this from the
// page editor's "Smooth Scroll" box; read on the front end via
// fw_get_db_ext_settings_option( 'shortcodes', 'smooth_scroll_global' ).
$options = array(
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
);
