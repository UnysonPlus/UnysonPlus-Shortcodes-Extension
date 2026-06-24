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
					'author_prefix' => [
						'type'  => 'text',
						'label' => __( 'Prefix', 'fw' ),
						'desc'  => __( 'Text before the name, e.g. "By".', 'fw' ),
						'value' => '',
					],
					'link_to_author' => [
						'type'         => 'switch',
						'label'        => __( 'Link to Author', 'fw' ),
						'value'        => 'no',
						'right-choice' => [ 'value' => 'yes', 'label' => __( 'On', 'fw' ) ],
						'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
					],
					'show_avatar' => [
						'type'         => 'switch',
						'label'        => __( 'Show Avatar', 'fw' ),
						'value'        => 'no',
						'right-choice' => [ 'value' => 'yes', 'label' => __( 'On', 'fw' ) ],
						'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
					],
					'avatar_size' => [
						'type'         => 'number',
						'label'        => __( 'Avatar Size (px)', 'fw' ),
						'value'        => 48,
						'min'          => 16,
						'max'          => 256,
						'step'         => 1,
						'numeric_type' => 'integer',
					],
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
