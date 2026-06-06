<?php if (!defined('FW')) {
	die('Forbidden');
}

$bleed_illustration = '<div style="margin:0 0 15px;padding:15px;background:#f9f9f9;border:1px solid #e0e0e0;border-radius:6px;">
<svg viewBox="0 0 480 160" style="width:100%;max-width:480px;height:auto;display:block;margin:0 auto 12px;">
  <rect x="0" y="0" width="480" height="160" rx="4" fill="#1a1a2e" stroke="#444" stroke-width="1"/>
  <rect x="0" y="0" width="280" height="160" rx="0" fill="#22c55e"/>
  <rect x="280" y="0" width="200" height="160" rx="0" fill="#2a2a3e"/>
  <line x1="280" y1="0" x2="280" y2="160" stroke="#fff" stroke-width="1" stroke-dasharray="4,3" opacity="0.5"/>
  <line x1="60" y1="0" x2="60" y2="160" stroke="#fff" stroke-width="1" stroke-dasharray="4,3" opacity="0.3"/>
  <line x1="420" y1="0" x2="420" y2="160" stroke="#fff" stroke-width="1" stroke-dasharray="4,3" opacity="0.3"/>
  <text x="60" y="18" font-size="9" fill="#fff" opacity="0.4" font-family="sans-serif">container</text>
  <rect x="80" y="35" width="160" height="12" rx="2" fill="#fff" opacity="0.9"/>
  <rect x="80" y="55" width="180" height="8" rx="2" fill="#fff" opacity="0.5"/>
  <rect x="80" y="68" width="170" height="8" rx="2" fill="#fff" opacity="0.5"/>
  <rect x="80" y="81" width="150" height="8" rx="2" fill="#fff" opacity="0.5"/>
  <rect x="80" y="94" width="165" height="8" rx="2" fill="#fff" opacity="0.5"/>
  <rect x="80" y="107" width="140" height="8" rx="2" fill="#fff" opacity="0.5"/>
  <text x="140" y="138" font-size="10" fill="#fff" opacity="0.7" font-family="sans-serif" text-anchor="middle">Content</text>
  <rect x="300" y="25" width="80" height="80" rx="4" fill="#fff" opacity="0.15" stroke="#fff" stroke-width="1" opacity="0.3"/>
  <text x="340" y="70" font-size="20" fill="#fff" opacity="0.4" font-family="sans-serif" text-anchor="middle">&#x1f5bc;</text>
  <text x="350" y="138" font-size="10" fill="#fff" opacity="0.7" font-family="sans-serif" text-anchor="middle">Bleed Image</text>
  <text x="25" y="85" font-size="8" fill="#fff" opacity="0.35" font-family="sans-serif" text-anchor="middle" transform="rotate(-90,25,85)">bleeds to edge</text>
  <text x="455" y="85" font-size="8" fill="#fff" opacity="0.35" font-family="sans-serif" text-anchor="middle" transform="rotate(90,455,85)">bleeds to edge</text>
</svg>
<p style="margin:0;font-size:12px;color:#666;line-height:1.5;">
<strong>Bleed Layout</strong> splits the section into two sides. The <strong>content side</strong> (with your chosen background color) extends to the viewport edge, while a <strong>bleed image</strong> fills the opposite side and also extends to the viewport edge. This creates a striking split-screen effect commonly used for featured content sections.
</p>
</div>';

$options = [
	'tab_layout' => [
		'title'   => __( 'Layout', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_layout' => [
				'type'    => 'group',
				'options' => [
					'variant' => [
						'type'    => 'select',
						'label'   => __( 'Section Variant', 'fw' ),
						'desc'    => __( 'Named visual preset for this section. Pairs with the Background Color picker — pick a variant for the overall theme, override the bg color below if you want a one-off colour.', 'fw' ),
						'help'    => __( 'Use "Alt" on every other section to create a subtle banded rhythm down the page. "Light"/"Dark" force a fixed scheme regardless of the theme\'s default.', 'fw' ),
						'value'   => '',
						'choices' => [
							''      => __( 'Default', 'fw' ),
							'alt'   => __( 'Alt (subtle off-white for alternating rhythm)', 'fw' ),
							'light' => __( 'Light (force light bg + dark text)', 'fw' ),
							'dark'  => __( 'Dark (force dark bg + light text)', 'fw' ),
						],
					],
					'is_fullwidth' => [
						'label' => __( 'Full Width', 'fw' ),
						'help'  => __( 'On: the section background spans edge-to-edge while content stays in the container. Off: the whole section is constrained to the container width.', 'fw' ),
						'type'  => 'switch',
					],
					'background_color' => [
						'label' => __( 'Background Color', 'fw' ),
						'desc'  => __( 'Please select the background color', 'fw' ),
						'help'  => __( 'Legacy field from the original Unyson plugin, kept for backwards compatibility. For new sections, prefer the Section Variant above or the preset Background Color on the Styling tab — those adapt to the theme palette.', 'fw' ),
						'type'  => 'color-picker',
					],
					'background_image' => [
						'label'   => __( 'Background Image', 'fw' ),
						'desc'    => __( 'Please select the background image', 'fw' ),
						'help'    => __( 'Sits behind the section content; pair it with a semi-transparent Background Color or a dark text variant to keep overlaid text readable.', 'fw' ),
						'type'    => 'background-image',
						'choices' => [],
					],
					'video' => [
						'label' => __( 'Background Video', 'fw' ),
						'desc'  => __( 'Insert Video URL to embed this video', 'fw' ),
						'help'  => __( 'Accepts a YouTube/Vimeo or self-hosted .mp4 URL that loops behind the content. Set a Background Image too so it shows while the video loads or on devices that block autoplay.', 'fw' ),
						'type'  => 'text',
					],
				],
			],
		],
	],

	'tab_bleed_layout' => [
		'title'   => __( 'Bleed Layout', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_bleed_layout' => [
				'type'    => 'group',
				'options' => [
					'bleed_illustration' => [
						'type'  => 'html-full',
						'label' => false,
						'html'  => $bleed_illustration,
					],
					'bleed_layout' => [
						'type'    => 'multi-picker',
						'label'   => false,
						'desc'    => false,
						'picker'  => [
							'bleed_enabled' => [
								'type'         => 'switch',
								'label'        => __( 'Enable Bleed Layout', 'fw' ),
								'desc'         => __( 'Split this section into a content area and a full-bleed image', 'fw' ),
								'help'         => __( 'Turning this on reveals the rest of the bleed controls below. Ideal for featured / split-screen sections; leave off for a standard full-width section.', 'fw' ),
								'left-choice'  => [
									'value' => 'no',
									'label' => __( 'No', 'fw' ),
								],
								'right-choice' => [
									'value' => 'yes',
									'label' => __( 'Yes', 'fw' ),
								],
								'value' => 'no',
							],
						],
						'choices' => [
							'yes' => [
								'bleed_bg_color' => [
									'type'  => 'color-picker',
									'label' => __( 'Content Background Color', 'fw' ),
									'desc'  => __( 'Background color for the content side. Bleeds to the viewport edge.', 'fw' ),
									'help'  => __( 'Pick a solid brand color here so the text side reads as a distinct panel against the image half. Leave transparent to inherit the section background.', 'fw' ),
									'value' => '',
								],
								'bleed_image' => [
									'type'  => 'upload',
									'label' => __( 'Bleed Image', 'fw' ),
									'desc'  => __( 'Image that fills the opposite side and extends to the viewport edge', 'fw' ),
									'help'  => __( 'Use a high-resolution image — it is cropped to fill its half (cover), so detail near the edges may be trimmed depending on the Image / Content Ratio.', 'fw' ),
								],
								'bleed_image_position' => [
									'type'    => 'select',
									'label'   => __( 'Image Position', 'fw' ),
									'desc'    => __( 'How the image is positioned within its area', 'fw' ),
									'help'    => __( 'Controls which part stays visible when the image is cropped to fill. Example: choose "Top" for a portrait so faces near the top are not cut off.', 'fw' ),
									'choices' => [
										'center'       => __( 'Center', 'fw' ),
										'top'          => __( 'Top', 'fw' ),
										'bottom'       => __( 'Bottom', 'fw' ),
										'left'         => __( 'Left', 'fw' ),
										'right'        => __( 'Right', 'fw' ),
										'left top'     => __( 'Left Top', 'fw' ),
										'right top'    => __( 'Right Top', 'fw' ),
										'left bottom'  => __( 'Left Bottom', 'fw' ),
										'right bottom' => __( 'Right Bottom', 'fw' ),
									],
									'value' => 'center',
								],
								'bleed_image_side' => [
									'type'    => 'select',
									'label'   => __( 'Image Side', 'fw' ),
									'desc'    => __( 'Which side the image appears on', 'fw' ),
									'help'    => __( 'On desktop only — on mobile the two halves stack vertically per the Mobile Stacking Order option below.', 'fw' ),
									'choices' => [
										'right' => __( 'Right', 'fw' ),
										'left'  => __( 'Left', 'fw' ),
									],
									'value' => 'right',
								],
								'bleed_image_ratio' => [
									'type'    => 'select',
									'label'   => __( 'Image / Content Ratio', 'fw' ),
									'desc'    => __( 'How much space the image takes vs. the content area', 'fw' ),
									'help'    => __( 'Each pair sums to 12 Bootstrap columns. Give the content side more room (e.g. 5/12 image + 7/12 content) when you have several paragraphs or a form.', 'fw' ),
									'choices' => [
										'1-11' => __( '1/12 Image + 11/12 Content (col-md-1 + col-md-11)', 'fw' ),
										'2-10' => __( '1/6 Image + 5/6 Content (col-md-2 + col-md-10)', 'fw' ),
										'3-9'  => __( '1/4 Image + 3/4 Content (col-md-3 + col-md-9)', 'fw' ),
										'4-8'  => __( '1/3 Image + 2/3 Content (col-md-4 + col-md-8)', 'fw' ),
										'5-7'  => __( '5/12 Image + 7/12 Content (col-md-5 + col-md-7)', 'fw' ),
										'6-6'  => __( '1/2 Image + 1/2 Content (col-md-6 + col-md-6)', 'fw' ),
										'7-5'  => __( '7/12 Image + 5/12 Content (col-md-7 + col-md-5)', 'fw' ),
										'8-4'  => __( '2/3 Image + 1/3 Content (col-md-8 + col-md-4)', 'fw' ),
										'9-3'  => __( '3/4 Image + 1/4 Content (col-md-9 + col-md-3)', 'fw' ),
										'10-2' => __( '5/6 Image + 1/6 Content (col-md-10 + col-md-2)', 'fw' ),
										'11-1' => __( '11/12 Image + 1/12 Content (col-md-11 + col-md-1)', 'fw' ),
									],
									'value' => '5-7',
								],
								'bleed_vertical_align' => [
									'type'    => 'select',
									'label'   => __( 'Content Vertical Alignment', 'fw' ),
									'desc'    => __( 'Align the content vertically within the section', 'fw' ),
									'help'    => __( 'Most noticeable when the image side is taller than the text. "Center" keeps a short heading visually balanced against a tall image.', 'fw' ),
									'choices' => [
										'align-items-start'  => __( 'Top', 'fw' ),
										'align-items-center' => __( 'Center', 'fw' ),
										'align-items-end'    => __( 'Bottom', 'fw' ),
									],
									'value' => 'align-items-center',
								],
								'bleed_content_padding' => [
									'type'    => 'select',
									'label'   => __( 'Content Padding', 'fw' ),
									'desc'    => __( 'Vertical padding for the content area', 'fw' ),
									'help'    => __( 'Adds breathing room above and below the text on the content side. Choose "None" if your inner shortcodes already supply their own spacing.', 'fw' ),
									'choices' => [
										'0'    => __( 'None', 'fw' ),
										'2rem' => __( 'Small', 'fw' ),
										'3rem' => __( 'Medium', 'fw' ),
										'5rem' => __( 'Large', 'fw' ),
									],
									'value' => '3rem',
								],
								'bleed_mobile_stacking' => [
									'type'    => 'select',
									'label'   => __( 'Mobile Stacking Order', 'fw' ),
									'desc'    => __( 'Which appears first on mobile', 'fw' ),
									'help'    => __( 'On narrow screens the two halves stack top-to-bottom. Pick "Image First" for a hero-style lead visual, or "Content First" to surface the message immediately.', 'fw' ),
									'choices' => [
										'content-first' => __( 'Content First', 'fw' ),
										'image-first'   => __( 'Image First', 'fw' ),
									],
									'value' => 'content-first',
								],
							],
						],
					],
				],
			],
		],
	],

	'tab_styling' => [
		'title'   => __( 'Spacing & Style', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_colors' => [
				'type'    => 'group',
				'options' => [
					'bg_color' => sc_color_field_compact( [
						'label' => __( 'Background Color (preset)', 'fw' ),
						'kind'  => 'bg',
						'desc'  => __( 'A named color from the theme palette, or a custom hex via the inline picker. Layers on top of the legacy Background Color on the Layout tab if both are set.', 'fw' ),
					] ),
				],
			],
			'group_spacings' => [
				'type'    => 'group',
				'options' => [
					'padding_top' => sc_spacing_field( [
						'label'  => __( 'Top Spacing', 'fw' ),
						'prefix' => 'pt',
						'desc'   => __( 'Vertical breathing room above the section content.', 'fw' ),
					] ),
					'padding_bottom' => sc_spacing_field( [
						'label'  => __( 'Bottom Spacing', 'fw' ),
						'prefix' => 'pb',
						'desc'   => __( 'Vertical breathing room below the section content.', 'fw' ),
					] ),
					// Column-gap overrides for this section's rows. Stored
					// as scale slugs (e.g. "3"). The view emits matching
					// modifier classes (section--gap-{slug},
					// section--gap-x-{slug}, section--gap-y-{slug}) which
					// css-tokens.php turns into --bs-gutter-x / --bs-gutter-y
					// overrides on every .row inside this section.
					// Empty = inherit Theme Settings → Default Gap.
					'gap' => [
						'type'    => 'short-select',
						'label'   => __( 'Gap', 'fw' ),
						'desc'    => __( 'Override the site-wide Default Gap for every Bootstrap row inside this section. Sets both horizontal and vertical column gap.', 'fw' ),
						'help'    => function_exists( 'sc_styling_help_text' ) ? sc_styling_help_text( 'spacing' ) : '',
						'value'   => '',
						'choices' => function_exists( 'sc_get_gap_select_choices' )
							? sc_get_gap_select_choices( __( 'Use Default Gap', 'fw' ) )
							: array( '' => __( 'Use Default Gap', 'fw' ) ),
					],
					'gap_x' => [
						'type'    => 'short-select',
						'label'   => __( 'Gap X (override)', 'fw' ),
						'desc'    => __( 'Overrides Gap on the horizontal axis only. Leave at "Use Section Gap" to inherit.', 'fw' ),
						'help'    => __( 'Use this when you want wider/narrower space between columns side-to-side without changing the vertical gap. Only takes effect once Gap above is set.', 'fw' ),
						'value'   => '',
						'choices' => function_exists( 'sc_get_gap_select_choices' )
							? sc_get_gap_select_choices( __( 'Use Section Gap', 'fw' ) )
							: array( '' => __( 'Use Section Gap', 'fw' ) ),
					],
					'gap_y' => [
						'type'    => 'short-select',
						'label'   => __( 'Gap Y (override)', 'fw' ),
						'desc'    => __( 'Overrides Gap on the vertical axis only. Leave at "Use Section Gap" to inherit.', 'fw' ),
						'help'    => __( 'Controls the vertical space between columns once they wrap onto new lines (e.g. on tablet/mobile). Only takes effect once Gap above is set.', 'fw' ),
						'value'   => '',
						'choices' => function_exists( 'sc_get_gap_select_choices' )
							? sc_get_gap_select_choices( __( 'Use Section Gap', 'fw' ) )
							: array( '' => __( 'Use Section Gap', 'fw' ) ),
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
