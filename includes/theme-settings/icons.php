<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * Theme Settings -> Icons.
 *
 * A single checklist of the available icon libraries (webfont packs + bundled
 * SVG libraries). The reading + picker-wiring live in the core pack-settings.php
 * (includes/option-types/icon-v2/includes/pack-settings.php); this only defines
 * the settings schema. Font packs feed the icon-v2 `filter_packs` hook; SVG
 * packs (Lucide) gate their own picker tab.
 *
 * Value shape: { pack_id => bool }. Default: every pack enabled, so existing
 * sites see no change until they curate.
 */
if ( ! function_exists( 'unysonplus_icons_settings_options' ) ) :
	function unysonplus_icons_settings_options() {
		$choices = function_exists( 'unysonplus_icon_pack_choices' ) ? unysonplus_icon_pack_choices() : array();

		// Default = all enabled (no change for existing content).
		$default = array();
		foreach ( array_keys( $choices ) as $id ) { $default[ $id ] = true; }

		return array(
			'icon_libraries_box' => array(
				'title'   => __( 'Icon Libraries', 'fw' ),
				'type'    => 'box',
				'options' => array(
					'group_icon_libraries' => array(
						'type'    => 'group',
						'options' => array(
							'icon_packs_enabled' => array(
								'type'    => 'checkboxes',
								'label'   => __( 'Enabled libraries', 'fw' ),
								'desc'    => __( 'Choose which icon libraries appear in the icon picker. Disabling a library only hides it when picking NEW icons — icons already used on your pages keep rendering. Icons play a big role on a site, so pick the sets that match your design and leave the rest off to keep the picker tidy.', 'fw' ),
								'value'   => $default,
								'choices' => $choices,
							),
						),
					),
				),
			),
		);
	}
endif;
