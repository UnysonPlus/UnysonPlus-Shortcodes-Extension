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
					'source' => [
						'label'   => __( 'Logo Source', 'fw' ),
						'type'    => 'select',
						'value'   => 'site_identity',
						'choices' => [
							'site_identity' => __( 'Site Identity (Customizer logo / title)', 'fw' ),
							'custom'        => __( 'Custom Image', 'fw' ),
						],
						'desc'    => __( 'Site Identity uses the logo/title from Appearance → Customize. Custom Image uses the upload below.', 'fw' ),
					],
					'custom_image' => [
						'label' => __( 'Custom Logo Image', 'fw' ),
						'type'  => 'upload',
						'desc'  => __( 'Used only when Logo Source is "Custom Image".', 'fw' ),
					],
					'link_home' => [
						'label'        => __( 'Link to Home', 'fw' ),
						'type'         => 'switch',
						'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
						'left-choice'  => [ 'value' => 'no', 'label' => __( 'No', 'fw' ) ],
						'value'        => 'yes',
					],
					'max_height' => [
						'label' => __( 'Max Height', 'fw' ),
						'type'  => 'unit-input',
						'units' => [ 'px', 'rem', 'em' ],
						'value' => [ 'value' => '', 'unit' => 'px' ],
						'min'   => 0,
						'desc'  => __( 'Optional. Constrains the logo image height; width scales automatically.', 'fw' ),
					],
					'alignment' => [
						'label'   => __( 'Alignment', 'fw' ),
						'type'    => 'select',
						'value'   => '',
						'choices' => [
							''      => __( 'Default', 'fw' ),
							'start' => __( 'Left', 'fw' ),
							'center' => __( 'Center', 'fw' ),
							'end'   => __( 'Right', 'fw' ),
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
