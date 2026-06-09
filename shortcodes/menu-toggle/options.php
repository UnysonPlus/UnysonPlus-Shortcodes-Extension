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
					'target' => [
						'label' => __( 'Target Drawer ID', 'fw' ),
						'type'  => 'text',
						'value' => 'primary-navigation-drawer',
						'desc'  => __( 'The id of the off-canvas drawer to open. Leave as the default to use the theme\'s built-in drawer.', 'fw' ),
					],
					'label' => [
						'label' => __( 'Accessible Label', 'fw' ),
						'type'  => 'text',
						'value' => __( 'Menu', 'fw' ),
						'desc'  => __( 'Screen-reader label for the button.', 'fw' ),
					],
					'icon_style' => [
						'label'   => __( 'Icon Style', 'fw' ),
						'type'    => 'select',
						'value'   => 'bars',
						'choices' => [
							'bars' => __( 'Bars (≡)', 'fw' ),
							'dots' => __( 'Dots (⋮)', 'fw' ),
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
