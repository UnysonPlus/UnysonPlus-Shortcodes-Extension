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
		// Canonical settings-tab layout (mirrors blog-settings.php etc.):
		//   container tab (Icons, from the loader) -> box -> sub-tabs -> box -> fields.
		// The installer is split across three sub-tabs — Library (webfont/SVG on-off
		// toggles + Remove), Browse (catalog packs to Install), Upload (custom JSON) —
		// each an html-full container that installer.js renders into. The old "Enabled
		// libraries" checklist is folded into the Library tab's On/Off toggles.
		$panel = function ( $id ) {
			return array(
				'group_' . $id => array(
					'type'    => 'group',
					'options' => array(
						'icon_pack_' . $id => array(
							'type'  => 'html-full',
							'label' => false,
							'desc'  => false,
							'html'  => '<div id="upw-ipk-' . $id . '" class="upw-ipk">'
								. '<div class="upw-ipk__empty">' . esc_html__( 'Loading…', 'fw' ) . '</div>'
								. '</div>',
						),
					),
				),
			);
		};

		return array(
			'icons' => array(
				'title'   => __( 'Icon Settings', 'fw' ),
				'type'    => 'box',
				'options' => array(
					'tab_library' => array(
						'title'   => __( 'Library', 'fw' ),
						'type'    => 'tab',
						'options' => array(
							'library_box' => array( 'title' => '', 'type' => 'box', 'options' => $panel( 'library' ) ),
						),
					),
					'tab_browse' => array(
						'title'   => __( 'Browse', 'fw' ),
						'type'    => 'tab',
						'options' => array(
							'browse_box' => array( 'title' => '', 'type' => 'box', 'options' => $panel( 'browse' ) ),
						),
					),
					'tab_upload' => array(
						'title'   => __( 'Upload', 'fw' ),
						'type'    => 'tab',
						'options' => array(
							'upload_box' => array( 'title' => '', 'type' => 'box', 'options' => $panel( 'upload' ) ),
						),
					),
				),
			),
		);
	}
endif;
