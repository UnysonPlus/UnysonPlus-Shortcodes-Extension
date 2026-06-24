<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

// A Typography V2 field for one counter part (number / prefix / suffix). The
// "Script" (subset) selector and the built-in Color are disabled — colour is
// handled separately by a preset-backed picker (see counter_color_field()).
if ( ! function_exists( 'counter_font_field' ) ) {
	function counter_font_field( $label, $size, $line_height, $desc = '' ) {
		return [
			'type'       => 'typography-v2',
			'label'      => $label,
			'desc'       => $desc,
			'components' => [ 'subset' => false, 'color' => false ],
			'value'      => [
				'family'         => '',
				'style'          => 'normal',
				'weight'         => '700',
				'size'           => $size,
				'line-height'    => $line_height,
				'letter-spacing' => 0,
			],
		];
	}
}

// A preset-backed colour picker (theme Color Presets + custom hex). Falls back to
// a plain color-picker if the styling helper isn't loaded.
if ( ! function_exists( 'counter_color_field' ) ) {
	function counter_color_field( $label ) {
		if ( function_exists( 'sc_color_field_compact' ) ) {
			return sc_color_field_compact( [ 'label' => $label, 'kind' => 'text' ] );
		}
		return [ 'type' => 'color-picker', 'label' => $label, 'value' => '' ];
	}
}

$options = [
	'tab_content' => [
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_value' => [
				'type'    => 'group',
				'options' => [
					'number' => [
						'type'            => 'text',
						'label'           => __( 'Number', 'fw' ),
						'desc'            => __( 'The value to count up to — e.g. 45280, 96, 4.2. Commas are ignored.', 'fw' ),
						'value'           => '100',
						'dynamic_content' => false,
					],
					'start'  => [
						'type'            => 'text',
						'label'           => __( 'Start From', 'fw' ),
						'desc'            => __( 'Value the count begins at (default 0).', 'fw' ),
						'value'           => '0',
						'dynamic_content' => false,
					],
					'prefix' => [
						'type'            => 'text',
						'label'           => __( 'Prefix', 'fw' ),
						'desc'            => __( 'Text shown before the number (e.g. $). Doubles as the left-hand caption.', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					],
					'suffix' => [
						'type'            => 'text',
						'label'           => __( 'Suffix', 'fw' ),
						'desc'            => __( 'Text shown after the number (e.g. +, %, k). Doubles as the right-hand caption.', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					],
				],
			],
			'group_format' => [
				'type'    => 'group',
				'options' => [
					'decimals'  => [
						'type'    => 'select',
						'label'   => __( 'Decimal Places', 'fw' ),
						'desc'    => __( 'How many digits to show after the decimal point.', 'fw' ),
						'choices' => [
							'0' => __( '0 — e.g. 1,200', 'fw' ),
							'1' => __( '1 — e.g. 1,200.5', 'fw' ),
							'2' => __( '2 — e.g. 1,200.50', 'fw' ),
							'3' => __( '3 — e.g. 1,200.500', 'fw' ),
						],
						'value'   => '0',
					],
					'separator' => [
						'type'    => 'select',
						'label'   => __( 'Thousands Separator', 'fw' ),
						'desc'    => __( 'Insert commas in large numbers (45,280).', 'fw' ),
						'choices' => [ 'yes' => __( 'Yes', 'fw' ), 'no' => __( 'No', 'fw' ) ],
						'value'   => 'yes',
					],
					'duration'  => [
						'type'            => 'text',
						'label'           => __( 'Duration (ms)', 'fw' ),
						'desc'            => __( 'Count-up animation length in milliseconds.', 'fw' ),
						'value'           => '2000',
						'dynamic_content' => false,
					],
					'easing'    => [
						'type'    => 'select',
						'label'   => __( 'Easing', 'fw' ),
						'choices' => [
							'ease-out'    => __( 'Ease Out (fast → slow)', 'fw' ),
							'linear'      => __( 'Linear', 'fw' ),
							'ease-in-out' => __( 'Ease In-Out', 'fw' ),
						],
						'value'   => 'ease-out',
					],
				],
			],
		],
	],

	'tab_style' => [
		'title'   => __( 'Style', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_layout' => [
				'type'    => 'group',
				'options' => [
					'alignment' => function_exists( 'sc_alignment_field' )
						? sc_alignment_field( [
							'label'   => __( 'Alignment', 'fw' ),
							'inherit' => true,
							'desc'    => __( 'Horizontal alignment of the counter. Leave on Inherit to follow the theme / parent alignment (nothing is forced).', 'fw' ),
						] )
						: [
							'type'    => 'select',
							'label'   => __( 'Alignment', 'fw' ),
							'choices' => [ 'left' => __( 'Left', 'fw' ), 'center' => __( 'Center', 'fw' ), 'right' => __( 'Right', 'fw' ) ],
							'value'   => 'left',
						],
				],
			],
			'group_number' => [
				'type'    => 'group',
				'options' => [
					'number_font'  => counter_font_field( __( 'Number Font', 'fw' ), 42, 46, __( 'Font, size, weight and spacing of the number.', 'fw' ) ),
					'number_color' => counter_color_field( __( 'Number Color', 'fw' ) ),
				],
			],
			'group_prefix' => [
				'type'    => 'group',
				'options' => [
					'prefix_font'  => counter_font_field( __( 'Prefix Font', 'fw' ), 24, 28, __( 'Font, size, weight and spacing of the prefix text.', 'fw' ) ),
					'prefix_color' => counter_color_field( __( 'Prefix Color', 'fw' ) ),
				],
			],
			'group_suffix' => [
				'type'    => 'group',
				'options' => [
					'suffix_font'  => counter_font_field( __( 'Suffix Font', 'fw' ), 24, 28, __( 'Font, size, weight and spacing of the suffix text.', 'fw' ) ),
					'suffix_color' => counter_color_field( __( 'Suffix Color', 'fw' ) ),
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
