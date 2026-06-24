<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = [
	'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'tabs' => [
				'type'          => 'addable-popup',
				'label'         => __( 'Tabs', 'fw' ),
				'popup-title'   => __( 'Add/Edit Tab', 'fw' ),
				'desc'          => __( 'Create your tabs', 'fw' ),
				'help'          => __( 'Add one entry per tab; the navigation buttons are generated from each tab\'s Title in this order. Drag rows to reorder.', 'fw' ),
				'template'      => '{{=tab_title}}',
				'popup-options' => array(
					'tab_title' => array(
						'type'  => 'text',
						'label' => __('Title', 'fw'),
							'help'  => __('Shown on the clickable tab button, so keep it short. This is also used as the row label in the list above.', 'fw'),
					),
					'tab_content' => array(
						'type'  => 'wp-editor',
						'label' => __('Content', 'fw'),
							'help'  => __('The panel shown when this tab is selected. Accepts HTML and shortcodes for richer content.', 'fw'),
					),
					'badge' => array(
						'type'  => 'text',
						'label' => __('Badge', 'fw'),
						'help'  => __('Optional small pill shown next to this tab title (e.g. "Save 20%"). Handy for a Monthly / Yearly pricing toggle.', 'fw'),
					),
					'is_active' => array(
						'type'  => 'switch',
						'label' => __('Active Tab', 'fw'),
						'help'  => __('Marks this as the tab open on page load. Set Yes on only one tab; if none is set, the first tab opens by default.', 'fw'),
						'value' => 'no',
						'left-choice' => [
							'value' => 'no',
							'label' => __('No', 'fw'),
						],
						'right-choice' => [
							'value' => 'yes',
							'label' => __('Yes', 'fw'),
						],
					),
				),
			],
			'tab_style' => [
				'type'    => 'select',
				'label'   => __('Tab Style', 'fw'),
				'desc'    => __('Choose the style of the tabs', 'fw'),
				'help'    => __('Pills read as standalone buttons; Underline is a quieter editorial strip; Segmented Toggle is a compact pill switcher (ideal for a Monthly / Yearly pricing toggle). Pick the look that suits the section.', 'fw'),
				'value'   => 'tabs',
				'choices' => [
					'tabs'      => __('Tabs (Default)', 'fw'),
					'pills'     => __('Pills', 'fw'),
					'underline' => __('Underline (v5.2+)', 'fw'),
					'segmented' => __('Segmented Toggle (switcher)', 'fw'),
				],
			],
			'justified' => [
				'type'  => 'switch',
				'label' => __('Justified Tabs', 'fw'),
				'desc'  => __('Stretch tabs to the full width of the container', 'fw'),
				'help'  => __('When on, each tab button gets an equal share of the full width. Best with a small number of tabs; many tabs become very narrow.', 'fw'),
				'value' => 'no',
				'left-choice'  => [
					'value' => 'no',
					'label' => __('No', 'fw'),
				],
				'right-choice' => [
					'value' => 'yes',
					'label' => __('Yes', 'fw'),
				],
			],
			'alignment' => [
				'type'    => 'select',
				'label'   => __('Tab Alignment', 'fw'),
				'desc'    => __('Choose alignment of tab navigation', 'fw'),
				'help'    => __('Positions the tab buttons within their row. Has no visible effect when Justified Tabs is on, since the buttons already fill the width.', 'fw'),
				'value'   => 'start',
				'choices' => [
					'start'  => __('Start (Default)', 'fw'),
					'center' => __('Center', 'fw'),
					'end'    => __('End', 'fw'),
				],
			],
			'orientation' => [
				'type'    => 'select',
				'label'   => __('Tabs Orientation', 'fw'),
				'desc'    => __('Choose whether tabs are horizontal or vertical', 'fw'),
				'help'    => __('Vertical stacks the tab buttons in a side column with content to the right, which suits longer tab titles or many tabs.', 'fw'),
				'value'   => 'horizontal',
				'choices' => [
					'horizontal' => __('Horizontal', 'fw'),
					'vertical'   => __('Vertical', 'fw'),
				],
			],
			'fade' => [
				'type'  => 'switch',
				'label' => __('Fade Animation', 'fw'),
				'desc'  => __('Enable fade transition between tab content', 'fw'),
				'help'  => __('Adds a soft cross-fade when switching tabs instead of an instant swap. A subtle touch that makes panel changes feel smoother.', 'fw'),
				'value' => 'no',
				'left-choice' => [
					'value' => 'no',
					'label' => __('No', 'fw'),
				],
				'right-choice' => [
					'value' => 'yes',
					'label' => __('Yes', 'fw'),
				],
			],
        ],
    ],
	'tab_styling' => [
		'title'   => __( 'Styling', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_colors' => [
				'type'    => 'group',
				'options' => [
					'text_color'       => sc_color_field_compact( array( 'label' => __( 'Text Color', 'fw' ),       'kind' => 'text' ) ),
					'bg_color'         => sc_color_field_compact( array( 'label' => __( 'Background Color', 'fw' ), 'kind' => 'bg' ) ),
					'font_size_preset' => sc_font_size_field( array(
						'desc' => __( 'A named size from the framework presets. Customizable in Theme Settings on the official Unyson+ theme.', 'fw' ),
					) ),
					'tab_title_color' => sc_color_field_compact( array(
						'label' => __( 'Tab Title Color', 'fw' ),
						'desc'  => __( 'Overrides the general Text Color for the tab navigation buttons (applied across all tabs).', 'fw' ),
					) ),
					'tab_content_color' => sc_color_field_compact( array(
						'label' => __( 'Tab Content Color', 'fw' ),
						'desc'  => __( 'Overrides the general Text Color for the tab content panels (applied across all tabs).', 'fw' ),
					) ),
				],
			],
			'group_spacings' => [
				'type'    => 'group',
				'options' => [
					'spacing' => array(
						'type'  => 'spacing',
						'label' => __( 'Margin & Padding', 'fw' ),
						'desc'  => __( 'All Sides applies to every side at once; any per-side value (Top, Right, Bottom, Left) overrides it for that direction.', 'fw' ),
						'help'  => sc_styling_help_text( 'spacing' ),
					),
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
        'title'   => __('Advanced', 'fw'),
        'type'    => 'tab',
        'options' => [
            'advanced_settings' => [
                'type'    => 'group',
                'options' => array_merge(
                    sc_get_advanced_tab(),
                    [
                        /* 'title_extra' => [
                            'type'  => 'text',
                            'label' => __('Some Title', 'fw'),
                            'desc'  => __('Write some heading title content', 'fw'),
                        ],
                        'title_extra_2' => [
                            'type'  => 'text',
                            'label' => __('Some Title2', 'fw'),
                            'desc'  => __('Write some heading title content', 'fw'),
                        ],*/
                    ]
                ),
            ],
        ],
    ],
];
