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
					'style' => [
						'label'   => __( 'Style', 'fw' ),
						'type'    => 'select',
						'value'   => 'inline-form',
						'choices' => [
							'inline-form' => __( 'Inline Form (always visible)', 'fw' ),
							'icon-toggle' => __( 'Icon (expands on click)', 'fw' ),
						],
					],
					'placeholder' => [
						'label' => __( 'Placeholder Text', 'fw' ),
						'type'  => 'text',
						'value' => __( 'Search …', 'fw' ),
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
