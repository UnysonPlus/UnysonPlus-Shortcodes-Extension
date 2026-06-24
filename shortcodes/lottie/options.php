<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = [

	/* ========================== CONTENT ========================== */
	'tab_content' => [
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group' => [
				'type'    => 'group',
				'options' => [
					'source' => [
						'type'    => 'select',
						'label'   => __( 'Source', 'fw' ),
						'value'   => 'url',
						'choices' => [
							'url'    => __( 'URL (.json)', 'fw' ),
							'upload' => __( 'Upload / Media Library', 'fw' ),
						],
					],
					'lottie_url' => [
						'type'  => 'text',
						'label' => __( 'Lottie JSON URL', 'fw' ),
						'desc'  => __( 'A direct link to a .json animation (e.g. the "Lottie JSON" export from LottieFiles).', 'fw' ),
						'help'  => __( 'Must be a .json Lottie/Bodymovin file, not a .lottie or GIF.', 'fw' ),
					],
					'lottie_file' => [
						'type'      => 'upload',
						'label'     => __( 'Lottie JSON File', 'fw' ),
						'desc'      => __( 'Upload or pick a .json animation from the media library.', 'fw' ),
						'files_ext' => [ 'json' ],
					],
				],
			],
		],
	],

	/* ========================== DESIGN / BEHAVIOR ========================== */
	'tab_design' => [
		'title'   => __( 'Design', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_behavior' => [
				'type'    => 'group',
				'options' => [
					'trigger' => [
						'type'    => 'select',
						'label'   => __( 'Trigger', 'fw' ),
						'value'   => 'autoplay',
						'choices' => [
							'autoplay' => __( 'Autoplay on load', 'fw' ),
							'viewport' => __( 'Play when scrolled into view', 'fw' ),
							'hover'    => __( 'Play on hover', 'fw' ),
							'click'    => __( 'Play / pause on click', 'fw' ),
						],
					],
					'loop' => [
						'type'  => 'switch',
						'label' => __( 'Loop', 'fw' ),
						'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
						'left-choice'  => [ 'value' => 'no',  'label' => __( 'No', 'fw' ) ],
						'value' => 'yes',
					],
					'reverse_hover' => [
						'type'  => 'switch',
						'label' => __( 'Rewind on hover-out', 'fw' ),
						'desc'  => __( 'For the Hover trigger: play forward on enter, rewind on leave.', 'fw' ),
						'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
						'left-choice'  => [ 'value' => 'no',  'label' => __( 'No', 'fw' ) ],
						'value' => 'no',
					],
					'speed' => [
						'type'       => 'slider',
						'label'      => __( 'Speed', 'fw' ),
						'value'      => 1,
						'properties' => [ 'min' => 0.25, 'max' => 2.5, 'step' => 0.25 ],
					],
					'direction' => [
						'type'    => 'select',
						'label'   => __( 'Direction', 'fw' ),
						'value'   => 'forward',
						'choices' => [
							'forward' => __( 'Forward', 'fw' ),
							'reverse' => __( 'Reverse', 'fw' ),
						],
					],
				],
			],
			'group_layout' => [
				'type'    => 'group',
				'options' => [
					'max_width' => [
						'type'  => 'text',
						'label' => __( 'Max Width (px)', 'fw' ),
						'desc'  => __( 'Caps the animation width. Leave empty for full container width.', 'fw' ),
						'value' => '240',
					],
					'alignment' => [
						'type'    => 'select',
						'label'   => __( 'Alignment', 'fw' ),
						'value'   => 'center',
						'choices' => [
							'left'   => __( 'Left', 'fw' ),
							'center' => __( 'Center', 'fw' ),
							'right'  => __( 'Right', 'fw' ),
						],
					],
				],
			],
		],
	],

	/* ========================== STYLING ========================== */
	'tab_styling' => [
		'title'   => __( 'Styling', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_spacings' => [
				'type'    => 'group',
				'options' => [
					'spacing' => [
						'type'  => 'spacing',
						'label' => __( 'Margin & Padding', 'fw' ),
						'help'  => sc_styling_help_text( 'spacing' ),
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
