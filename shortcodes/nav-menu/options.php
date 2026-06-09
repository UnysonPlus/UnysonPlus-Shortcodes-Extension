<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

// Build choices for the two menu sources. Admin-only context (options form), so
// the queries are cheap and only run on the builder edit screen.
$nav_menu_choices = array( '' => __( '— Select a menu —', 'fw' ) );
if ( function_exists( 'wp_get_nav_menus' ) ) {
	foreach ( wp_get_nav_menus() as $m ) {
		$nav_menu_choices[ (string) $m->term_id ] = $m->name;
	}
}

$nav_location_choices = array( '' => __( '— Select a location —', 'fw' ) );
if ( function_exists( 'get_registered_nav_menus' ) ) {
	foreach ( get_registered_nav_menus() as $loc => $label ) {
		$nav_location_choices[ $loc ] = $label;
	}
}

$options = [
	'tab_content' => [
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_content' => [
				'type'    => 'group',
				'options' => [
					'menu_source' => [
						'type'         => 'multi-picker',
						'label'        => false,
						'desc'         => false,
						'picker'       => [
							'type' => [
								'label'   => __( 'Menu Source', 'fw' ),
								'type'    => 'select',
								'value'   => 'location',
								'choices' => [
									'location' => __( 'Theme Menu Location', 'fw' ),
									'menu'     => __( 'Specific Menu', 'fw' ),
								],
							],
						],
						'choices'      => [
							'location' => [
								'menu_location' => [
									'label'   => __( 'Menu Location', 'fw' ),
									'type'    => 'select',
									'value'   => '',
									'choices' => $nav_location_choices,
									'desc'    => __( 'A menu assigned to this theme location (Appearance → Menus).', 'fw' ),
								],
							],
							'menu'     => [
								'menu_id' => [
									'label'   => __( 'Menu', 'fw' ),
									'type'    => 'select',
									'value'   => '',
									'choices' => $nav_menu_choices,
									'desc'    => __( 'Pick a specific menu regardless of its assigned location.', 'fw' ),
								],
							],
						],
						'show_borders' => false,
					],
					'orientation' => [
						'label'   => __( 'Orientation', 'fw' ),
						'type'    => 'select',
						'value'   => 'horizontal',
						'choices' => [
							'horizontal' => __( 'Horizontal', 'fw' ),
							'vertical'   => __( 'Vertical', 'fw' ),
						],
					],
					'submenu_style' => [
						'label'   => __( 'Submenu Style', 'fw' ),
						'type'    => 'select',
						'value'   => 'dropdown',
						'choices' => [
							'dropdown'  => __( 'Dropdown', 'fw' ),
							'mega'      => __( 'Mega (full width)', 'fw' ),
							'accordion' => __( 'Accordion (expand in place)', 'fw' ),
						],
						'desc'    => __( 'How sub-menus open. Dropdown/Mega suit horizontal menus; Accordion suits vertical / off-canvas menus.', 'fw' ),
					],
					'depth' => [
						'label'   => __( 'Maximum Depth', 'fw' ),
						'type'    => 'select',
						'value'   => '0',
						'choices' => [
							'0' => __( 'All levels', 'fw' ),
							'1' => __( 'Top level only', 'fw' ),
							'2' => '2',
							'3' => '3',
						],
					],
					'alignment' => [
						'label'   => __( 'Alignment', 'fw' ),
						'type'    => 'select',
						'value'   => '',
						'choices' => [
							''          => __( 'Default', 'fw' ),
							'start'     => __( 'Start', 'fw' ),
							'center'    => __( 'Center', 'fw' ),
							'end'       => __( 'End', 'fw' ),
							'justified' => __( 'Justified (space between)', 'fw' ),
						],
					],
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
