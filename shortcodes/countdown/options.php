<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

// A Typography V2 field for one countdown part (number / label). "Script" (subset) and the
// built-in Color are disabled — colour is handled by a preset-backed picker below.
if ( ! function_exists( 'countdown_font_field' ) ) {
	// The default value is INTENTIONALLY empty so a default countdown emits a clean
	// DOM (no inline styles) — the visual defaults live in styles.css instead. The
	// view only writes inline typography for keys the user actually sets in the Style
	// tab. The $weight/$size/$line_height args are kept only to DOCUMENT the CSS
	// defaults at the call sites; they are no longer baked into the saved value.
	function countdown_font_field( $label, $weight, $size, $line_height, $desc = '' ) {
		return [
			'type'       => 'typography',
			'label'      => $label,
			'desc'       => $desc,
			'components' => [ 'subset' => false, 'color' => false ],
			'value'      => [
				'family'         => '',
				'style'          => 'normal',
				// Default 400 (matches styles.css). The control shows 400 rather than
				// defaulting to its lowest option (100); the view suppresses writing
				// '400' inline, so a default countdown stays a clean DOM.
				'weight'         => '400',
				'size'           => '',
				'line-height'    => '',
				'letter-spacing' => 0,
			],
		];
	}
}

// Preset-backed colour picker (theme Color Presets + custom hex), falling back to a plain
// color-picker if the styling helper isn't loaded.
if ( ! function_exists( 'countdown_color_field' ) ) {
	function countdown_color_field( $label ) {
		if ( function_exists( 'sc_color_field_compact' ) ) {
			return sc_color_field_compact( [ 'label' => $label, 'kind' => 'text' ] );
		}
		return [ 'type' => 'color-picker', 'label' => $label, 'value' => '' ];
	}
}

if ( ! function_exists( 'countdown_unit_switch' ) ) {
	function countdown_unit_switch( $label, $value = 'yes' ) {
		return [
			'type'         => 'switch',
			'label'        => $label,
			'right-choice' => [ 'value' => 'yes', 'label' => __( 'Show', 'fw' ) ],
			'left-choice'  => [ 'value' => 'no', 'label' => __( 'Hide', 'fw' ) ],
			'value'        => $value,
		];
	}
}

$options = [
	'tab_content' => [
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_target' => [
				'type'    => 'group',
				'options' => [
					'target' => [
						'type'            => 'datetime-picker',
						'label'           => __( 'Target Date & Time', 'fw' ),
						'desc'            => __( 'The moment the timer counts down to (interpreted in the site timezone).', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					],
				],
			],
			'group_units' => [
				'type'    => 'group',
				'options' => [
					'show_days'    => countdown_unit_switch( __( 'Days', 'fw' ) ),
					'show_hours'   => countdown_unit_switch( __( 'Hours', 'fw' ) ),
					'show_minutes' => countdown_unit_switch( __( 'Minutes', 'fw' ) ),
					'show_seconds' => countdown_unit_switch( __( 'Seconds', 'fw' ) ),
				],
			],
			'group_labels' => [
				'type'    => 'group',
				'options' => [
					'label_days'    => [ 'type' => 'text', 'label' => __( 'Days Label', 'fw' ),    'value' => __( 'Days', 'fw' ),    'dynamic_content' => false ],
					'label_hours'   => [ 'type' => 'text', 'label' => __( 'Hours Label', 'fw' ),   'value' => __( 'Hours', 'fw' ),   'dynamic_content' => false ],
					'label_minutes' => [ 'type' => 'text', 'label' => __( 'Minutes Label', 'fw' ), 'value' => __( 'Minutes', 'fw' ), 'dynamic_content' => false ],
					'label_seconds' => [ 'type' => 'text', 'label' => __( 'Seconds Label', 'fw' ), 'value' => __( 'Seconds', 'fw' ), 'dynamic_content' => false ],
				],
			],
			'group_complete' => [
				'type'    => 'group',
				'options' => [
					'on_complete'   => [
						'type'    => 'select',
						'label'   => __( 'When It Reaches Zero', 'fw' ),
						'desc'    => __( 'What to do when the target time passes.', 'fw' ),
						'choices' => [
							'message' => __( 'Show a message', 'fw' ),
							'zeros'   => __( 'Keep showing zeros', 'fw' ),
							'hide'    => __( 'Hide the timer', 'fw' ),
						],
						'value'   => 'message',
					],
					'complete_text' => [
						'type'            => 'text',
						'label'           => __( 'Completed Message', 'fw' ),
						'desc'            => __( 'Shown when the countdown ends (if "Show a message" is selected).', 'fw' ),
						'value'           => __( 'This event has ended.', 'fw' ),
						'dynamic_content' => false,
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
							'desc'    => __( 'Horizontal alignment of the timer. Leave on Inherit to follow the theme / parent alignment (nothing is forced).', 'fw' ),
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
					'number_font'  => countdown_font_field( __( 'Number Font', 'fw' ), '700', 40, 44, __( 'Font, size, weight and spacing of the digits.', 'fw' ) ),
					'number_color' => countdown_color_field( __( 'Number Color', 'fw' ) ),
				],
			],
			'group_label' => [
				'type'    => 'group',
				'options' => [
					'label_font'  => countdown_font_field( __( 'Label Font', 'fw' ), '600', 13, 16, __( 'Font, size, weight and spacing of the unit labels.', 'fw' ) ),
					'label_color' => countdown_color_field( __( 'Label Color', 'fw' ) ),
				],
			],
			'group_box' => [
				'type'    => 'group',
				'options' => [
					'box_preset' => [
						'type'         => 'border-style-picker',
						'label'        => __( 'Box Preset', 'fw' ),
						'desc'         => __( 'Wrap each unit in a reusable box style — border, corners, shadow and fill. Leave as None for plain numbers. Manage presets in Theme Settings → Components → Box Presets.', 'fw' ),
						'value'        => '',
						'choices'      => function_exists( 'sc_get_border_preset_choices' ) ? sc_get_border_preset_choices() : array( '' => __( 'None', 'fw' ) ),
						'preview_text' => __( 'Box', 'fw' ),
						'placeholder'  => __( '— None (plain) —', 'fw' ),
					],
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
