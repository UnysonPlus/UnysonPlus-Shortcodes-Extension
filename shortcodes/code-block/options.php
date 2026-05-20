<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = [
	'tab_code' => [
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_content' => [
                'type'    => 'group',
                'options' => [
					'code' => [
						'type'   => 'code-editor',
						'label'  => __( 'Code', 'fw' ),
						'desc'   => __( 'Enter some HTML/CSS/JavaScript here. Syntax highlighting enabled.', 'fw' ),
						'mode'   => 'htmlmixed', // covers HTML + inline CSS/JS — the common code-block case
						'height' => 500,
					],
				],
			],
		],
	],
	'tab_animation' => [
		'title'   => __( 'Animations', 'fw' ),
		'type'    => 'tab',
		'options' => sc_get_animation_fields(),
	],
	'tab_advanced' => [
		'title'   => __('Advanced', 'fw'),
		'type'    => 'tab',
		'options' => [
			'advanced_settings' => [
				'type'    => 'group',
				'options' => array_merge(
					sc_get_advanced_tab(),
				),
			],
		],
	],
];
