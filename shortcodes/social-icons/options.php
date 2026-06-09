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
						'label'   => __( 'Source', 'fw' ),
						'type'    => 'select',
						'value'   => 'theme_settings',
						'choices' => [
							'theme_settings' => __( 'Theme Settings (Social Profiles)', 'fw' ),
							'manual'         => __( 'Manual list', 'fw' ),
						],
						'desc'    => __( 'Theme Settings reuses the profiles configured in the theme. Manual lets you define links here.', 'fw' ),
					],
					'profiles' => [
						'label'       => __( 'Profiles', 'fw' ),
						'type'        => 'addable-box',
						'value'       => [],
						'desc'        => __( 'Used only when Source is "Manual list".', 'fw' ),
						'box-options' => [
							'icon' => [
								'label'        => __( 'Icon', 'fw' ),
								'type'         => 'icon-v2',
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
