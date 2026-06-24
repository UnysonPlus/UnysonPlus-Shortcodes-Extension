<?php
if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

$options = [
	'tab_content' => [
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_content' => [
				'type'    => 'group',
				'options' => [
					'text_align' => sc_alignment_field( [
						'label'   => __( 'Alignment', 'fw' ),
						'inherit' => true,
					] ),
				],
			],
		],
	],
	'tab_styling' => [
		'title'   => __( 'Styling', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_styling' => [
				'type'    => 'group',
				'options' => [
					'text_color'       => sc_color_field_compact( [ 'label' => __( 'Text Color', 'fw' ), 'kind' => 'text' ] ),
					'font_size_preset' => sc_font_size_field(),
					'spacing'          => [
						'type'  => 'spacing',
						'label' => __( 'Margin & Padding', 'fw' ),
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
