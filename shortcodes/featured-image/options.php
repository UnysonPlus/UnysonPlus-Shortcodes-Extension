<?php
if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

$fi_size_choices = array(
	'thumbnail' => __( 'Thumbnail', 'fw' ),
	'medium'    => __( 'Medium', 'fw' ),
	'large'     => __( 'Large', 'fw' ),
	'full'      => __( 'Full', 'fw' ),
);

// Registered intermediate sizes (so custom theme sizes are selectable too).
if ( function_exists( 'get_intermediate_image_sizes' ) ) {
	foreach ( get_intermediate_image_sizes() as $fi_size ) {
		if ( ! isset( $fi_size_choices[ $fi_size ] ) ) {
			$fi_size_choices[ $fi_size ] = ucwords( str_replace( array( '-', '_' ), ' ', $fi_size ) );
		}
	}
}

$options = [
	'tab_content' => [
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_content' => [
				'type'    => 'group',
				'options' => [
					'image_size' => [
						'type'    => 'select',
						'label'   => __( 'Image Size', 'fw' ),
						'desc'    => __( 'Which registered image size to output.', 'fw' ),
						'value'   => 'large',
						'choices' => $fi_size_choices,
					],
					'link_to' => [
						'type'    => 'select',
						'label'   => __( 'Link To', 'fw' ),
						'value'   => 'none',
						'choices' => [
							'none' => __( 'No link', 'fw' ),
							'post' => __( 'The post / page', 'fw' ),
							'file' => __( 'The image file', 'fw' ),
						],
					],
					'text_align' => sc_alignment_field( [
						'label'   => __( 'Alignment', 'fw' ),
						'inherit' => true,
						'desc'    => __( 'Horizontal alignment of the image within its column.', 'fw' ),
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
					'image_style' => function_exists( 'sc_image_style_field' )
						? sc_image_style_field()
						: [ 'type' => 'select', 'label' => __( 'Image Style', 'fw' ), 'value' => '', 'choices' => [ '' => __( 'None', 'fw' ) ] ],
					'spacing' => [
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
