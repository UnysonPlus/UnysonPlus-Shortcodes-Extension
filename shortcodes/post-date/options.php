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
					'date_type' => [
						'type'    => 'select',
						'label'   => __( 'Date', 'fw' ),
						'value'   => 'published',
						'choices' => [
							'published' => __( 'Published date', 'fw' ),
							'modified'  => __( 'Last modified date', 'fw' ),
						],
					],
					'date_format' => [
						'type'  => 'text',
						'label' => __( 'Format', 'fw' ),
						'desc'  => __( 'PHP date format (e.g. F j, Y). Leave blank to use the site date format.', 'fw' ),
						'value' => '',
					],
					'link_to_post' => [
						'type'         => 'switch',
						'label'        => __( 'Link to Post', 'fw' ),
						'value'        => 'no',
						'right-choice' => [ 'value' => 'yes', 'label' => __( 'On', 'fw' ) ],
						'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
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
