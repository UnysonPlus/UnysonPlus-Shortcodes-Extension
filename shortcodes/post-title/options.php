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
					'heading_tag' => [
						'type'    => 'select',
						'label'   => __( 'Heading Tag', 'fw' ),
						'desc'    => __( 'HTML tag for the title. Use a single H1 per page for SEO.', 'fw' ),
						'value'   => 'h1',
						'choices' => [
							'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3',
							'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6',
							'p'  => __( 'Paragraph', 'fw' ),
						],
					],
					'link_to_post' => [
						'type'         => 'switch',
						'label'        => __( 'Link to Post', 'fw' ),
						'desc'         => __( 'Wrap the title in a link to the post / page.', 'fw' ),
						'value'        => 'no',
						'right-choice' => [ 'value' => 'yes', 'label' => __( 'On', 'fw' ) ],
						'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
					],
					'text_align' => sc_alignment_field( [
						'label'   => __( 'Alignment', 'fw' ),
						'inherit' => true,
						'desc'    => __( 'Horizontal alignment. Output as a Bootstrap text-* class on the heading.', 'fw' ),
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
