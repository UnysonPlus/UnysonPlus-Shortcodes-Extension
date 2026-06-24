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
					'note' => [
						'type'  => 'html-fixed',
						'label' => __( 'Dynamic', 'fw' ),
						'html'  => __( 'This element outputs the content of the post or page being viewed. There is nothing to type here — design it where it appears with the Styling and Advanced tabs.', 'fw' ),
					],
					'text_align' => sc_alignment_field( [
						'label'   => __( 'Alignment', 'fw' ),
						'inherit' => true,
						'desc'    => __( 'Horizontal alignment for the content. Output as a Bootstrap text-* class on the wrapper.', 'fw' ),
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
