<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

$options = [
	'tab_content' => [
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_content' => [
				'type'    => 'group',
				'options' => [
					'image' => [
						'type'  => 'upload',
						'label' => __( 'Image', 'fw' ),
						'desc'  => __( 'Upload or choose an image from the media library', 'fw' ),
						'help'  => __( 'Use a high-resolution image at least as wide as its column; the theme scales it down crisply but cannot add detail when scaling up.', 'fw' ),
					],
					'content' => [
						'type'          => 'wp-editor',
						'reinit'        => true,
						'label'         => __( 'Content', 'fw' ),
						'desc'          => __( 'Enter the text content to display alongside the image', 'fw' ),
						'help'          => __( 'Accepts shortcodes, so you can nest headings or buttons here to build a full hero-style block beside the image.', 'fw' ),
						'tinymce'       => true,
						'size'          => 'large',
						'editor_height' => 300,
						'shortcodes'    => true,
						'wpautop'       => true,
						'value'         => '',
					],
					'image_link_group' => [
						'type'    => 'group',
						'options' => [
							'image_link' => [
								'type'  => 'text',
								'label' => __( 'Image Link', 'fw' ),
								'desc'  => __( 'Optional URL to link the image to', 'fw' ),
								'help'  => __( 'Enter a full URL including https://. Leave empty to keep the image non-clickable.', 'fw' ),
								'value' => '',
							],
							'image_link_target' => [
								'type'         => 'switch',
								'label'        => __( 'Open Link in New Window', 'fw' ),
								'help'         => __( 'Only applies when an Image Link is set. Turn on for links to external sites so visitors do not leave your page.', 'fw' ),
								'right-choice' => [
									'value' => '_blank',
									'label' => __( 'Yes', 'fw' ),
								],
								'left-choice'  => [
									'value' => '_self',
									'label' => __( 'No', 'fw' ),
								],
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
			'group_layout_arrange' => [
				'type'    => 'group',
				'options' => [
					'layout' => call_user_func( function() {
						$img_uri = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/image-content/static/img' );
						return [
							'type'    => 'image-picker',
							'label'   => __( 'Layout', 'fw' ),
							'desc'    => __( 'Position of the image relative to the content', 'fw' ),
							'help'    => __( 'Image Left / Right place the two side by side (use the Split slider below). Image Top stacks the image above the content, full width. Alternate Left/Right down a page for a zig-zag rhythm.', 'fw' ),
							'choices' => [
								'image-left'  => [ 'small' => [ 'src' => $img_uri . '/layout-image-left.svg',  'height' => 40, 'title' => __( 'Image Left / Content Right', 'fw' ) ] ],
								'image-right' => [ 'small' => [ 'src' => $img_uri . '/layout-image-right.svg', 'height' => 40, 'title' => __( 'Image Right / Content Left', 'fw' ) ] ],
								'image-top'   => [ 'small' => [ 'src' => $img_uri . '/layout-image-top.svg',   'height' => 40, 'title' => __( 'Image Top / Content Below', 'fw' ) ] ],
							],
							'value'   => 'image-left',
						];
					} ),
					'column_ratio' => [
						// Visual two-pane split bar (drag the divider). Stored value = the IMAGE's
						// column span (1–11 of 12) — same integer the old slider stored, so this is
						// a drop-in swap with no value migration (view.php is unchanged).
						'type'          => 'column-split',
						'label'         => __( 'Image / Content Split', 'fw' ),
						'desc'          => __( 'Drag the divider to set how the row is shared between the image and the content.', 'fw' ),
						'help'          => __( 'The image takes the left share, the content the right (shown as a fraction in lowest form, e.g. 1/3). The divider stops at 11/12 so the content always keeps at least one column. Applies to the Image Left / Image Right layouts — the Layout swatch chooses which side the image actually sits on.', 'fw' ),
						'value'         => 4,
						'denominator'   => 12,
						'min'           => 1,
						'max'           => 11,
						'show_fraction' => true,
						'panes'         => [
							[ 'label' => __( 'Image', 'fw' ),   'icon' => 'dashicons-format-image' ],
							[ 'label' => __( 'Content', 'fw' ), 'icon' => 'dashicons-text' ],
						],
					],
				],
			],
			'group_layout_align' => [
				'type'    => 'group',
				'options' => [
					'vertical_align' => call_user_func( function() {
						$img_uri = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/image-content/static/img' );
						$pick    = function ( $file, $title ) use ( $img_uri ) {
							return [ 'small' => [ 'src' => $img_uri . '/' . $file, 'height' => 40, 'title' => $title ] ];
						};
						return [
							'type'    => 'image-picker',
							'label'   => __( 'Vertical Alignment', 'fw' ),
							'desc'    => __( 'Align the image and content vertically within the row', 'fw' ),
							'help'    => __( 'Matters most when the image and the text are different heights. Center usually looks balanced; Top aligns both columns to the same baseline.', 'fw' ),
							'choices' => [
								'align-items-start'  => $pick( 'valign-top.svg',    __( 'Top', 'fw' ) ),
								'align-items-center' => $pick( 'valign-center.svg', __( 'Center', 'fw' ) ),
								'align-items-end'    => $pick( 'valign-bottom.svg', __( 'Bottom', 'fw' ) ),
							],
							'value'   => 'align-items-center',
						];
					} ),
					'content_align' => sc_alignment_field( array(
						'label'   => __( 'Content Alignment', 'fw' ),
						'inherit' => true,
						'desc'    => __( 'Horizontal alignment of the content text', 'fw' ),
						'help'    => __( 'Leave on Inherit to follow the theme / parent alignment (nothing is forced). Left reads best for body copy; Center suits short, punchy blocks. Pairs with Content Max Width (a centered block auto-centers in its column).', 'fw' ),
					) ),
					'gap' => [
						'type'    => 'short-select',
						'label'   => __( 'Gap', 'fw' ),
						'desc'    => __( 'Space between the image and the content (the column gutter), sourced from the Gap Scale presets.', 'fw' ),
						'help'    => function_exists( 'sc_styling_help_text' ) ? sc_styling_help_text( 'spacing' ) : '',
						'value'   => '4',
						'choices' => function_exists( 'sc_get_gap_select_choices' )
							? sc_get_gap_select_choices( __( 'Use Default Gap', 'fw' ) )
							: array( '' => __( 'Use Default Gap', 'fw' ), '4' => __( 'Medium', 'fw' ) ),
					],
				],
			],
			'group_layout_responsive' => [
				'type'    => 'group',
				'options' => [
					'mobile_order' => [
						'type'    => 'select',
						'label'   => __( 'Mobile Stacking Order', 'fw' ),
						'desc'    => __( 'Which column appears first when stacked on mobile', 'fw' ),
						'help'    => __( 'On narrow screens the columns stack vertically. Choose Content First when the heading should be read before the image on phones.', 'fw' ),
						'choices' => [
							'image-first'   => __( 'Image First', 'fw' ),
							'content-first' => __( 'Content First', 'fw' ),
						],
						'value' => 'image-first',
					],
					'breakpoint' => [
						'type'    => 'select',
						'label'   => __( 'Stack Below', 'fw' ),
						'desc'    => __( 'Screen width at which the image and content stack vertically', 'fw' ),
						'help'    => __( 'Above this width they sit side by side; below it they stack. Pick a larger breakpoint when the content needs more room before going side by side.', 'fw' ),
						'choices' => [
							'sm' => __( 'Small (≥ 576px)', 'fw' ),
							'md' => __( 'Medium (≥ 768px)', 'fw' ),
							'lg' => __( 'Large (≥ 992px)', 'fw' ),
						],
						'value' => 'md',
					],
					'stack_image_width' => [
						'type'  => 'unit-input',
						'label' => __( 'Stacked Image Max Width', 'fw' ),
						'desc'  => __( 'Image Top layout only — cap the image width so it is not forced full-bleed. Blank = full width.', 'fw' ),
						'help'  => __( 'A constrained, centered image reads better than an edge-to-edge one in the stacked layout. Pairs with Stacked Image Alignment.', 'fw' ),
						'units' => [ 'px', '%', 'rem' ],
						'value' => [ 'value' => '', 'unit' => 'px' ],
					],
					'stack_image_align' => sc_alignment_field( array(
						'label' => __( 'Stacked Image Alignment', 'fw' ),
						'desc'  => __( 'Image Top layout only — position the image within the block (takes effect once a max width is set)', 'fw' ),
						'value' => 'center',
					) ),
				],
			],
		],
	],

	'tab_styling' => [
		'title'   => __( 'Styling', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_options' => [
				'type'    => 'group',
				'options' => [
					'image_fit' => [
						'type'    => 'select',
						'label'   => __( 'Image Fit', 'fw' ),
						'desc'    => __( 'How the image fills its column', 'fw' ),
						'help'    => __( 'Cover crops the image to completely fill the column (good for matching heights); Contain shows the whole image, which may leave empty space.', 'fw' ),
						'choices' => [
							'contain' => __( 'Contain (show full image)', 'fw' ),
							'cover'   => __( 'Cover (fill column)', 'fw' ),
						],
						'value' => 'contain',
					],
					'image_ratio' => [
						'type'    => 'select',
						'label'   => __( 'Image Aspect Ratio', 'fw' ),
						'desc'    => __( 'Force the image into a fixed ratio box', 'fw' ),
						'help'    => __( 'Original keeps the image\'s own proportions. A fixed ratio (paired with Image Fit = Cover) crops predictably, without depending on the height of the other column.', 'fw' ),
						'choices' => [
							''     => __( 'Original', 'fw' ),
							'1x1'  => __( 'Square (1:1)', 'fw' ),
							'4x3'  => __( 'Landscape (4:3)', 'fw' ),
							'3x2'  => __( 'Photo (3:2)', 'fw' ),
							'16x9' => __( 'Wide (16:9)', 'fw' ),
							'3x4'  => __( 'Portrait (3:4)', 'fw' ),
						],
						'value' => '',
					],
					'image_radius' => [
						'type'    => 'select',
						'label'   => __( 'Image Border Radius', 'fw' ),
						'help'    => __( 'Rounds the image corners. Circle works best with a square image and Cover fit, otherwise the result will be an oval.', 'fw' ),
						'choices' => [
							'rounded-0'      => __( 'None', 'fw' ),
							'rounded-2'      => __( 'Small', 'fw' ),
							'rounded-3'      => __( 'Medium', 'fw' ),
							'rounded-4'      => __( 'Large', 'fw' ),
							'rounded-circle' => __( 'Circle', 'fw' ),
						],
						'value' => 'rounded-0',
					],
					'image_shadow' => [
						'type'    => 'select',
						'label'   => __( 'Image Shadow', 'fw' ),
						'help'    => __( 'Adds a drop shadow to lift the image off the page. Keep it subtle (Small) on light backgrounds so it does not look heavy.', 'fw' ),
						'choices' => [
							''          => __( 'None', 'fw' ),
							'shadow-sm' => __( 'Small', 'fw' ),
							'shadow'    => __( 'Medium', 'fw' ),
							'shadow-lg' => __( 'Large', 'fw' ),
						],
						'value' => '',
					],
				],
			],
			'group_colors' => [
				'type'    => 'group',
				'options' => [
					'content_max_width' => [
						'type'  => 'unit-input',
						'label' => __( 'Content Max Width', 'fw' ),
						'desc'  => __( 'Cap the content width for readability — leave blank to fill the column', 'fw' ),
						'help'  => __( 'A measure of about 50–75 characters (e.g. 60ch) keeps long copy comfortable to read. Centered content is auto-centered within its column.', 'fw' ),
						'units' => [ 'ch', 'px', 'rem', 'em', '%' ],
						'value' => [ 'value' => '', 'unit' => 'ch' ],
					],
					'content_color' => sc_color_field_compact( array(
						'label' => __( 'Content Color', 'fw' ),
						'desc'  => __( 'Color preset applied to the body content text.', 'fw' ),
					) ),
					'content_bg' => sc_color_field_compact( array(
						'label' => __( 'Content Background', 'fw' ),
						'desc'  => __( 'Background behind the content — turns the text side into a tinted "card" panel (pair with Content Padding).', 'fw' ),
						'kind'  => 'bg',
					) ),
					'content_padding' => [
						'type'  => 'spacing',
						'mode'  => 'padding',
						'label' => __( 'Content Padding', 'fw' ),
						'desc'  => __( 'Inner padding of the content panel — set per side, from the Spacing Scale presets (with responsive overrides).', 'fw' ),
						'help'  => function_exists( 'sc_styling_help_text' ) ? sc_styling_help_text( 'spacing' ) : '',
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
				'options' => array_merge(
					sc_get_advanced_tab(),
				),
			],
		],
	],
];
