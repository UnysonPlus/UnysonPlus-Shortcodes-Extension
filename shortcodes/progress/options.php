<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

if ( ! function_exists( 'progress_switch' ) ) {
	function progress_switch( $label, $desc, $value = 'yes' ) {
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
if ( ! function_exists( 'progress_color' ) ) {
	function progress_color( $label, $kind, $desc = '' ) {
		if ( function_exists( 'sc_color_field_compact' ) ) {
			return sc_color_field_compact( [ 'label' => $label, 'kind' => $kind, 'desc' => $desc ] );
		}
		return [ 'type' => 'color-picker', 'label' => $label, 'desc' => $desc, 'value' => '' ];
	}
}
if ( ! function_exists( 'progress_columns_field' ) ) {
	function progress_columns_field() {
		return [
			'type'    => 'select',
			'label'   => __( 'Per Row', 'fw' ),
			'desc'    => __( 'How many circles / gauges sit side by side on one row.', 'fw' ),
			'value'   => '3',
			'choices' => [
				'1' => __( '1', 'fw' ),
				'2' => __( '2', 'fw' ),
				'3' => __( '3', 'fw' ),
				'4' => __( '4', 'fw' ),
				'5' => __( '5', 'fw' ),
				'6' => __( '6', 'fw' ),
			],
		];
	}
}

$options = [
	'tab_content' => [
		'title'   => __( 'Bars', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_layout' => [
				'type'    => 'group',
				'options' => [
					// multi-picker: the Progress Style. 'bar' reveals nothing extra
					// (it uses the Style tab's height); circle/gauge reveal sizing.
					'layout' => [
						'type'         => 'multi-picker',
						'label'        => false,
						'desc'         => false,
						'show_borders' => false,
						'value'        => [ 'type' => 'bar' ],
						'picker'       => [
							'type' => call_user_func( function () {
								$img    = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/progress/static/img/styles' );
								$swatch = function ( $file, $title ) use ( $img ) {
									return [ 'small' => [ 'src' => $img . '/' . $file, 'height' => 60, 'title' => $title ] ];
								};
								return [
									'type'    => 'image-picker',
									'label'   => __( 'Progress Style', 'fw' ),
									'desc'    => __( 'Horizontal bars, circular rings, or semi-circle gauges.', 'fw' ),
									'help'    => __( 'Bars list vertically. Circles and gauges flow in a responsive grid — set how many per row below.', 'fw' ),
									'choices' => [
										'bar'    => $swatch( 'bar.svg',    __( 'Horizontal Bar', 'fw' ) ),
										'circle' => $swatch( 'circle.svg', __( 'Circle', 'fw' ) ),
										'gauge'  => $swatch( 'gauge.svg',  __( 'Gauge (Semi-circle)', 'fw' ) ),
										'pie'       => $swatch( 'pie.svg',       __( 'Pie', 'fw' ) ),
										'vertical'  => $swatch( 'vertical.svg',  __( 'Vertical Bars', 'fw' ) ),
										'segmented' => $swatch( 'segmented.svg', __( 'Segmented', 'fw' ) ),
									],
								];
							} ),
						],
						'choices'      => [
							'circle' => [
								'circle_size'      => [
									'type'  => 'text',
									'label' => __( 'Diameter', 'fw' ),
									'desc'  => __( 'Ring diameter in pixels.', 'fw' ),
									'value' => '120',
								],
								'circle_thickness' => [
									'type'       => 'slider',
									'label'      => __( 'Thickness', 'fw' ),
									'desc'       => __( 'Stroke width of the ring (px).', 'fw' ),
									'value'      => 10,
									'properties' => [ 'min' => 2, 'max' => 40, 'step' => 1 ],
								],
								'circle_columns'   => progress_columns_field(),
							],
							'gauge'  => [
								'gauge_size'      => [
									'type'  => 'text',
									'label' => __( 'Width', 'fw' ),
									'desc'  => __( 'Gauge width in pixels.', 'fw' ),
									'value' => '160',
								],
								'gauge_thickness' => [
									'type'       => 'slider',
									'label'      => __( 'Thickness', 'fw' ),
									'desc'       => __( 'Stroke width of the arc (px).', 'fw' ),
									'value'      => 12,
									'properties' => [ 'min' => 2, 'max' => 40, 'step' => 1 ],
								],
								'gauge_columns'   => progress_columns_field(),
							],
							'pie' => [
								'pie_size'    => [
									'type'  => 'text',
									'label' => __( 'Diameter', 'fw' ),
									'desc'  => __( 'Pie diameter in pixels.', 'fw' ),
									'value' => '140',
								],
								'pie_columns' => progress_columns_field(),
							],
							'vertical' => [
								'vertical_height'  => [
									'type'  => 'text',
									'label' => __( 'Height', 'fw' ),
									'desc'  => __( 'Column height in pixels.', 'fw' ),
									'value' => '180',
								],
								'vertical_columns' => progress_columns_field(),
							],
							'segmented' => [
								'segment_count' => [
									'type'       => 'slider',
									'label'      => __( 'Segments', 'fw' ),
									'desc'       => __( 'Number of blocks in each bar.', 'fw' ),
									'value'      => 10,
									'properties' => [ 'min' => 3, 'max' => 30, 'step' => 1 ],
								],
							],
						],
					],
				],
			],
			'group_bars'   => [
				'type'    => 'group',
				'options' => [
					'bars' => [
						'type'          => 'addable-popup',
						'label'         => __( 'Bars', 'fw' ),
						'desc'          => __( 'Each entry is one labelled progress item (bar, circle, or gauge).', 'fw' ),
						'popup-title'   => __( 'Add / Edit Bar', 'fw' ),
						'template'      => '{{= label || "Bar" }}{{= (typeof percent !== "undefined") ? " — " + percent + "%" : "" }}',
						'popup-options' => [
							'label'   => [
								'type'  => 'text',
								'label' => __( 'Label', 'fw' ),
								'desc'  => __( 'Shown with the bar, e.g. "Interface Design".', 'fw' ),
							],
							'percent' => [
								'type'       => 'slider',
								'label'      => __( 'Percent', 'fw' ),
								'desc'       => __( 'Fill amount (0–100).', 'fw' ),
								'value'      => 80,
								'properties' => [ 'min' => 0, 'max' => 100, 'step' => 1 ],
							],
							'icon'    => [
								'type'  => 'icon-v2',
								'label' => __( 'Icon', 'fw' ),
								'desc'  => __( 'Optional icon shown with the label (or inside circles / gauges).', 'fw' ),
							],
							'color'   => progress_color( __( 'Bar Color', 'fw' ), 'bg', __( 'Override the default fill color for this bar.', 'fw' ) ),
						],
					],
				],
			],
		],
	],

	'tab_style' => [
		'title'   => __( 'Style', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_style'  => [
				'type'    => 'group',
				'options' => [
					'height'         => [
						'type'  => 'text',
						'label' => __( 'Bar Height', 'fw' ),
						'desc'  => __( 'Horizontal bars only. Any CSS length, e.g. 10px, .6rem.', 'fw' ),
						'value' => '10px',
					],
					'value_position' => [
						'type'    => 'select',
						'label'   => __( 'Value Position', 'fw' ),
						'desc'    => __( 'Horizontal bars: place the % beside the label or inside the bar.', 'fw' ),
						'value'   => 'head',
						'choices' => [
							'head'   => __( 'Beside label', 'fw' ),
							'inside' => __( 'Inside the bar', 'fw' ),
						],
					],
					'rounded'        => progress_switch( __( 'Rounded', 'fw' ), __( 'Round the bar / ring ends.', 'fw' ), 'yes' ),
					'striped'        => progress_switch( __( 'Striped', 'fw' ), __( 'Diagonal stripe texture on the fill (bars only).', 'fw' ), 'no' ),
					'show_value'     => progress_switch( __( 'Show Percentage', 'fw' ), __( 'Display the % value.', 'fw' ), 'yes' ),
					'animate'        => progress_switch( __( 'Animate on Scroll', 'fw' ), __( 'Fill the items when they scroll into view.', 'fw' ), 'yes' ),
					'count_up'       => progress_switch( __( 'Count Up Number', 'fw' ), __( 'Count the % up from 0 while it fills.', 'fw' ), 'yes' ),
					'gap'            => [
						'type'  => 'text',
						'label' => __( 'Spacing', 'fw' ),
						'desc'  => __( 'Gap between items (CSS length, e.g. 1.1rem, 24px).', 'fw' ),
						'value' => '1.1rem',
					],
				],
			],
			'group_colors' => [
				'type'    => 'group',
				'options' => [
					'fill_color'   => progress_color( __( 'Fill Color', 'fw' ), 'bg', __( 'Default fill color (per-bar color overrides this).', 'fw' ) ),
					'fill_color_2' => progress_color( __( 'Gradient Color', 'fw' ), 'bg', __( 'Optional. Set a second color to fill with a gradient instead of a solid color.', 'fw' ) ),
					'track_color'  => progress_color( __( 'Track Color', 'fw' ), 'bg', __( 'The unfilled track behind each item.', 'fw' ) ),
					'label_color'  => progress_color( __( 'Label Color', 'fw' ), 'text', __( 'Color of the label / percentage text.', 'fw' ) ),
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
