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
							'help'  => __('The panel shown when this tab is selected. Accepts HTML and shortcodes for richer content. In the "Media panel" layout it becomes the caption shown beneath the image.', 'fw'),
					),
					'tab_image' => array(
						'type'         => 'upload',
						'label'        => __('Image', 'fw'),
						'desc'         => __('Shown on the media side in the "Media panel (list + image)" layout — each tab has its own image. Ignored in the default Content layout.', 'fw'),
						'images_only'  => true,
					),
					'badge' => array(
						'type'  => 'text',
						'label' => __('Badge', 'fw'),
						'help'  => __('Optional small pill shown next to this tab title (e.g. "Save 20%"). Handy for a Monthly / Yearly pricing toggle.', 'fw'),
					),
					'icon' => array(
						'type'         => 'icon',
						'label'        => __('Icon', 'fw'),
						'preview_size' => 'small',
						'desc'         => __('Optional icon shown before the tab title.', 'fw'),
					),
					'disabled' => array(
						'type'  => 'switch',
						'label' => __('Disabled', 'fw'),
						'help'  => __('Greys the tab out and blocks selection. A visual/UX state only.', 'fw'),
						'value' => 'no',
						'left-choice'  => [ 'value' => 'no',  'label' => __('No', 'fw') ],
						'right-choice' => [ 'value' => 'yes', 'label' => __('Yes', 'fw') ],
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
			// Tab STYLE — the visual design, via the shared design registry (image-picker),
			// so it also becomes skin-pack-extensible. Legacy `tab_style` values (tabs/pills/
			// underline/segmented) match these keys, so saved instances keep their look.
			'design' => call_user_func( function () {
				if ( function_exists( 'fw_sc_design_picker_choices' ) ) {
					return array(
						'type'    => 'image-picker',
						'label'   => __( 'Tab Style', 'fw' ),
						'desc'    => __( 'The visual style of the tab navigation. Underline is the quiet editorial default; Pills / Buttons read as standalone buttons; Segmented is a compact switcher (great for Monthly / Yearly); Boxed is the classic folder look; Minimal is text-only; Popover floats the panel above the page.', 'fw' ),
						'value'   => 'underline',
						'choices' => fw_sc_design_picker_choices( 'tabs' ),
					);
				}
				$registry = require dirname( __FILE__ ) . '/views/parts/registry.php';
				$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/tabs/static/img/design' );
				$choices  = array();
				foreach ( (array) $registry as $key => $meta ) {
					$choices[ $key ] = array( 'small' => array(
						'src'    => $base . '/' . ( isset( $meta['thumb'] ) ? $meta['thumb'] : $key . '.svg' ),
						'height' => 44,
						'title'  => isset( $meta['label'] ) ? $meta['label'] : $key,
					) );
				}
				return array( 'type' => 'image-picker', 'label' => __( 'Tab Style', 'fw' ), 'value' => 'underline', 'choices' => $choices );
			} ),
			'tab_width' => [
				'type'    => 'select',
				'label'   => __('Tab Width', 'fw'),
				'desc'    => __('How the tab buttons share the row width (horizontal, content layout).', 'fw'),
				'help'    => __('Auto: each tab is as wide as its label. Fill: tabs grow to fill the row, proportional to their labels. Equal: every tab gets the same width (best with a few tabs).', 'fw'),
				'value'   => 'auto',
				'choices' => [
					'auto'  => __('Auto (fit content)', 'fw'),
					'fill'  => __('Fill (proportional)', 'fw'),
					'equal' => __('Equal width', 'fw'),
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
				'help'    => __('Vertical stacks the tab buttons in a side column with content to the right, which suits longer tab titles or many tabs. Ignored when Layout is "Media panel".', 'fw'),
				'value'   => 'horizontal',
				'choices' => [
					'horizontal' => __('Horizontal', 'fw'),
					'vertical'   => __('Vertical', 'fw'),
				],
			],
			'layout' => [
				'type'    => 'select',
				'label'   => __('Layout', 'fw'),
				'desc'    => __('Content panels: the classic tabs (each tab shows its Content). Media panel: a list of tabs on one side and a switching image on the other — each tab shows its own Image, with the Content as a caption. Great for a feature / product showcase.', 'fw'),
				'help'    => __('For the scroll-driven cinematic version (image pins while steps scroll), use the Animation Engine → Scrollytelling on a Section instead. This element is the click / hover version.', 'fw'),
				'value'   => 'content',
				'choices' => [
					'content' => __('Content panels (default)', 'fw'),
					'media'   => __('Media panel (list + image)', 'fw'),
				],
			],
			'media_side' => [
				'type'    => 'select',
				'label'   => __('Image Side', 'fw'),
				'desc'    => __('Which side the image sits on in the Media panel layout.', 'fw'),
				'value'   => 'right',
				'choices' => [
					'right' => __('Image Right', 'fw'),
					'left'  => __('Image Left', 'fw'),
				],
			],
			'activate_on' => [
				'type'    => 'select',
				'label'   => __('Activate On', 'fw'),
				'desc'    => __('Switch tabs on click, or when the pointer hovers a tab.', 'fw'),
				'value'   => 'click',
				'choices' => [
					'click' => __('Click', 'fw'),
					'hover' => __('Hover', 'fw'),
				],
			],
			'activation' => [
				'type'    => 'select',
				'label'   => __('Keyboard Activation', 'fw'),
				'desc'    => __('WAI-ARIA behaviour when moving between tabs with the arrow keys.', 'fw'),
				'help'    => __('Automatic: the panel changes the instant a tab receives focus. Manual: arrow keys only move focus and the visitor presses Enter/Space to switch — use this if panels are heavy.', 'fw'),
				'value'   => 'automatic',
				'choices' => [
					'automatic' => __('Automatic (on focus)', 'fw'),
					'manual'    => __('Manual (Enter / Space)', 'fw'),
				],
			],
			'mobile' => [
				'type'    => 'select',
				'label'   => __('On Mobile', 'fw'),
				'desc'    => __('How the tab bar behaves on narrow screens.', 'fw'),
				'help'    => __('Accordion collapses the tabs into stacked expandable panels (best for content-heavy tabs). Scroll keeps one horizontal row that swipes sideways. Wrap (default) lets the tab buttons wrap onto multiple rows.', 'fw'),
				'value'   => 'none',
				'choices' => [
					'none'      => __('Wrap (default)', 'fw'),
					'accordion' => __('Collapse to accordion', 'fw'),
					'scroll'    => __('Horizontal scroll', 'fw'),
				],
			],
			'autoplay' => [
				'type'  => 'switch',
				'label' => __('Auto-rotate', 'fw'),
				'desc'  => __('Cycle through the tabs automatically. Pauses while the visitor hovers or focuses the element.', 'fw'),
				'value' => 'no',
				'left-choice'  => [ 'value' => 'no',  'label' => __('No', 'fw') ],
				'right-choice' => [ 'value' => 'yes', 'label' => __('Yes', 'fw') ],
			],
			'autoplay_interval' => [
				'type'       => 'slider',
				'label'      => __('Auto-rotate Interval (s)', 'fw'),
				'desc'       => __('Seconds each tab stays active when Auto-rotate is on.', 'fw'),
				'value'      => 5,
				'properties' => [ 'min' => 2, 'max' => 12, 'step' => 1 ],
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
			'deep_link' => [
				'type'  => 'switch',
				'label' => __('Deep-link (URL hash)', 'fw'),
				'desc'  => __('Open a specific tab from the page URL (#tab) and update the URL as tabs switch — so a tab is shareable / bookmarkable.', 'fw'),
				'help'  => __('Set a CSS ID on this element (Advanced tab) for links that survive a page reload; otherwise deep-linking works within the current page load only.', 'fw'),
				'value' => 'no',
				'left-choice'  => [ 'value' => 'no',  'label' => __('No', 'fw') ],
				'right-choice' => [ 'value' => 'yes', 'label' => __('Yes', 'fw') ],
			],
			'remember' => [
				'type'  => 'switch',
				'label' => __('Remember Last Tab', 'fw'),
				'desc'  => __('Re-open the tab the visitor last viewed (stored in their browser). Works best with a CSS ID on this element (Advanced tab).', 'fw'),
				'value' => 'no',
				'left-choice'  => [ 'value' => 'no',  'label' => __('No', 'fw') ],
				'right-choice' => [ 'value' => 'yes', 'label' => __('Yes', 'fw') ],
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
