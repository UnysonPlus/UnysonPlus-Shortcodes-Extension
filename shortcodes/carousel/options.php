<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

if ( ! function_exists( 'carousel_switch' ) ) {
	function carousel_switch( $label, $desc, $value = 'yes' ) {
		return [
			'type'         => 'switch',
			'label'        => $label,
			'desc'         => $desc,
			'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
			'left-choice'  => [ 'value' => 'no', 'label' => __( 'No', 'fw' ) ],
			'value'        => $value,
		];
	}
}
if ( ! function_exists( 'carousel_color' ) ) {
	function carousel_color( $label, $kind, $desc = '' ) {
		if ( function_exists( 'sc_color_field_compact' ) ) {
			return sc_color_field_compact( [ 'label' => $label, 'kind' => $kind, 'desc' => $desc ] );
		}
		return [ 'type' => 'color-picker', 'label' => $label, 'desc' => $desc, 'value' => '' ];
	}
}
if ( ! function_exists( 'carousel_per_page' ) ) {
	function carousel_per_page( $label, $desc, $value ) {
		return [
			'type'    => 'select',
			'label'   => $label,
			'desc'    => $desc,
			'value'   => $value,
			'choices' => [ '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ],
		];
	}
}

$options = [
	'tab_content' => [
		'title'   => __( 'Slides', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_slides' => [
				'type'    => 'group',
				'options' => [
					'slides' => [
						'type'          => 'addable-popup',
						'label'         => __( 'Slides', 'fw' ),
						'desc'          => __( 'Each entry is one slide. Add an image and/or a heading, text and button.', 'fw' ),
						'popup-title'   => __( 'Add / Edit Slide', 'fw' ),
						'template'      => '{{= heading || (image && image.url ? "Image slide" : "Slide") }}',
						'popup-options' => [
							'image' => [
								'type'  => 'upload',
								'label' => __( 'Image', 'fw' ),
								'desc'  => __( 'Slide image. Use it as a full-bleed background (with text overlaid) or as an inline picture — see "Image Mode".', 'fw' ),
							],
							'image_mode' => [
								'type'    => 'select',
								'label'   => __( 'Image Mode', 'fw' ),
								'desc'    => __( 'Background = image fills the slide with the text overlaid (hero style). Inline = image sits above the text.', 'fw' ),
								'value'   => 'background',
								'choices' => [
									'background' => __( 'Background (text overlaid)', 'fw' ),
									'inline'     => __( 'Inline (image above text)', 'fw' ),
								],
							],
							'heading' => [
								'type'  => 'text',
								'label' => __( 'Heading', 'fw' ),
								'desc'  => __( 'Optional slide title.', 'fw' ),
							],
							'text' => [
								'type'  => 'textarea',
								'label' => __( 'Text', 'fw' ),
								'desc'  => __( 'Optional slide paragraph.', 'fw' ),
							],
							'button_label' => [
								'type'  => 'text',
								'label' => __( 'Button Label', 'fw' ),
								'desc'  => __( 'Leave empty for no button.', 'fw' ),
							],
							'button_link' => [
								'type'  => 'text',
								'label' => __( 'Button Link', 'fw' ),
								'desc'  => __( 'URL the button points to.', 'fw' ),
								'value' => '#',
							],
							'link' => [
								'type'  => 'text',
								'label' => __( 'Slide Link', 'fw' ),
								'desc'  => __( 'Optional — makes the whole slide clickable (used when there is no button).', 'fw' ),
							],
							'content_align' => function_exists( 'sc_alignment_field' )
								? sc_alignment_field( [ 'label' => __( 'Content Alignment', 'fw' ), 'value' => 'center' ] )
								: [
									'type'    => 'select',
									'label'   => __( 'Content Alignment', 'fw' ),
									'value'   => 'center',
									'choices' => [ 'left' => __( 'Left', 'fw' ), 'center' => __( 'Center', 'fw' ), 'right' => __( 'Right', 'fw' ) ],
								],
						],
					],
				],
			],
		],
	],

	'tab_layout' => [
		'title'   => __( 'Layout', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_perview' => [
				'type'    => 'group',
				'options' => [
					'per_page'        => carousel_per_page( __( 'Slides per View (Desktop)', 'fw' ), __( 'How many slides show at once on desktop.', 'fw' ), '1' ),
					'per_page_tablet' => carousel_per_page( __( 'Slides per View (Tablet)', 'fw' ), __( 'Applied at ≤ 992px.', 'fw' ), '2' ),
					'per_page_mobile' => carousel_per_page( __( 'Slides per View (Mobile)', 'fw' ), __( 'Applied at ≤ 576px.', 'fw' ), '1' ),
				],
			],
			'group_dims' => [
				'type'    => 'group',
				'options' => [
					'gap' => [
						'type'  => 'text',
						'label' => __( 'Gap Between Slides', 'fw' ),
						'desc'  => __( 'Any CSS length, e.g. 1rem, 24px. Leave empty for none.', 'fw' ),
						'value' => '1rem',
					],
					'height' => [
						'type'  => 'text',
						'label' => __( 'Slide Height', 'fw' ),
						'desc'  => __( 'Fixed height for the slides, e.g. 600px or 80vh (great for hero sliders). Leave empty to size to content.', 'fw' ),
						'value' => '',
					],
				],
			],
			'group_nav' => [
				'type'    => 'group',
				'options' => [
					'arrows'     => carousel_switch( __( 'Arrows', 'fw' ), __( 'Show previous / next arrows.', 'fw' ), 'yes' ),
					'pagination' => carousel_switch( __( 'Dots', 'fw' ), __( 'Show the pagination dots.', 'fw' ), 'yes' ),
				],
			],
		],
	],

	'tab_behavior' => [
		'title'   => __( 'Behavior', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_behavior' => [
				'type'    => 'group',
				'options' => [
					'autoplay'   => carousel_switch( __( 'Autoplay', 'fw' ), __( 'Automatically advance slides.', 'fw' ), 'yes' ),
					'interval'   => [ 'type' => 'text', 'label' => __( 'Autoplay Interval (ms)', 'fw' ), 'desc' => __( 'Delay between slides.', 'fw' ), 'value' => '5000' ],
					'speed'      => [ 'type' => 'text', 'label' => __( 'Transition Speed (ms)', 'fw' ), 'desc' => __( 'Animation duration.', 'fw' ), 'value' => '600' ],
					'pause_hover'=> carousel_switch( __( 'Pause on Hover', 'fw' ), __( 'Pause autoplay while hovered.', 'fw' ), 'yes' ),
					'loop'       => carousel_switch( __( 'Loop', 'fw' ), __( 'Wrap from the last slide back to the first.', 'fw' ), 'yes' ),
					'drag'       => carousel_switch( __( 'Drag / Swipe', 'fw' ), __( 'Allow mouse drag and touch swipe.', 'fw' ), 'yes' ),
					'effect'     => [
						'type'    => 'select',
						'label'   => __( 'Transition', 'fw' ),
						'desc'    => __( 'Slide moves horizontally; Fade cross-fades (forces 1 slide per view).', 'fw' ),
						'value'   => 'slide',
						'choices' => [ 'slide' => __( 'Slide', 'fw' ), 'fade' => __( 'Fade', 'fw' ) ],
					],
				],
			],
		],
	],

	'tab_style' => [
		'title'   => __( 'Style', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_overlay' => [
				'type'    => 'group',
				'options' => [
					'overlay' => carousel_switch( __( 'Dark Overlay', 'fw' ), __( 'Darken background images so overlaid text stays legible.', 'fw' ), 'yes' ),
					'overlay_opacity' => [
						'type'       => 'slider',
						'label'      => __( 'Overlay Strength', 'fw' ),
						'desc'       => __( 'How dark the overlay is (%).', 'fw' ),
						'value'      => 45,
						'properties' => [ 'min' => 0, 'max' => 90, 'step' => 5 ],
					],
				],
			],
			'group_colors' => [
				'type'    => 'group',
				'options' => [
					'heading_color' => carousel_color( __( 'Heading Color', 'fw' ), 'text', __( 'Slide heading colour.', 'fw' ) ),
					'text_color'    => carousel_color( __( 'Text Color', 'fw' ), 'text', __( 'Slide paragraph colour.', 'fw' ) ),
				],
			],
		],
	],

	'tab_animation' => [
		'title'   => __( 'Animations', 'fw' ),
		'type'    => 'tab',
		'options' => function_exists( 'sc_get_animation_fields' ) ? sc_get_animation_fields() : [],
	],

	'tab_advanced' => [
		'title'   => __( 'Advanced', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'advanced_settings' => [
				'type'    => 'group',
				'options' => function_exists( 'sc_get_advanced_tab' ) ? sc_get_advanced_tab() : [],
			],
		],
	],
];
