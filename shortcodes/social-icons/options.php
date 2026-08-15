<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = [
	'tab_content' => [
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_content' => [
				'type'    => 'group',
				'options' => [
					// Source is a multi-picker: the "Profiles" list is only revealed
					// when the picker is set to "Manual list" (Theme Settings needs
					// no per-element config). Value nests as source/mode (the picker)
					// and source/manual/profiles (the revealed list).
					'source' => [
						'type'   => 'multi-picker',
						'label'  => false,
						'desc'   => false,
						'picker' => [
							'mode' => [
								'label'   => __( 'Source', 'fw' ),
								'type'    => 'select',
								'value'   => 'theme_settings',
								'choices' => [
									'theme_settings' => __( 'Theme Settings (Social Profiles)', 'fw' ),
									'manual'         => __( 'Manual list', 'fw' ),
								],
								'desc'    => __( 'Theme Settings reuses the profiles configured in the theme. Manual lets you define links here.', 'fw' ),
							],
						],
						'choices' => [
							'theme_settings' => [],
							'manual'         => [
								'profiles' => [
									'label'       => __( 'Profiles', 'fw' ),
									'type'        => 'addable-box',
									'value'       => [],
									'box-options' => [
										'icon' => [
											'label'        => __( 'Icon', 'fw' ),
											'type'         => 'icon',
											'preview_size' => 'small',
											'modal_size'   => 'medium',
										],
										'link' => [
											'label' => __( 'URL', 'fw' ),
											'type'  => 'text',
											'value' => '',
										],
										'label' => [
											'label' => __( 'Accessible Label', 'fw' ),
											'type'  => 'text',
											'value' => '',
											'desc'  => __( 'Screen-reader text, e.g. "Facebook".', 'fw' ),
										],
									],
								],
							],
						],
						'show_borders' => false,
					],
					'size' => [
						'label'   => __( 'Icon Size', 'fw' ),
						'type'    => 'select',
						'value'   => 'md',
						'choices' => [
							'sm' => __( 'Small', 'fw' ),
							'md' => __( 'Medium', 'fw' ),
							'lg' => __( 'Large', 'fw' ),
						],
					],
					'icon_badge_preset' => sc_icon_badge_preset_field( array(
						'desc' => __( 'Turn each social icon into a shaped tile (circle / rounded / square / hexagon) — fill, border, corners, shadow, plus its own icon colour, size and hover effects. Manage presets in Theme Settings → Components → Icon Badges. Applies to the Manual list; the Theme Settings source is styled by the theme.', 'fw' ),
					) ),
				],
			],
		],
	],
	'tab_advanced' => [
		'title'   => __( 'Advanced', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'advanced_settings' => [
				'type'    => 'group',
				'options' => sc_get_advanced_tab(),
			],
		],
	],
];
