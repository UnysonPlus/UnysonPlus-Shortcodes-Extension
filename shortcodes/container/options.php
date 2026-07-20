<?php if (!defined('FW')) die('Forbidden');

/**
 * Container options — a curated subset of the Section's controls. A Container is the
 * lighter, NESTED band you drop inside a section (the items-corrector lifts it out to
 * render as a SIBLING of the section's own container), so it deliberately inherits the
 * genuinely-useful styling — Background, Spacing, Min Height and Columns alignment — but
 * NOT the section-identity controls (Variant, Shape Dividers, per-section Container
 * Width). That keeps it clearly distinct from — not a clone of — the Section.
 *
 * The Columns alignment fields (halign / valign / reverse) come from the shared
 * sc_section_align_fields() helper so the exact same image-picker glyphs + the
 * `.section--cols-*` / `.section--rev*` modifier classes are reused (no duplication).
 */

$__align = function_exists( 'sc_section_align_fields' ) ? sc_section_align_fields( 'container' ) : array();

$options = [
	'tab_layout' => [
		'title'   => __( 'Layout', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_layout' => [
				'type'    => 'group',
				'options' => array_merge(
					[
						'is_fullwidth' => [
							'label' => __( 'Full Width', 'fw' ),
							'desc'  => __( 'Off: Boxed — the container is constrained to the site width (.fw-container). On: Full-width — it spans edge-to-edge (.fw-container-fluid).', 'fw' ),
							'help'  => __( 'A Container renders as a second container injected after the section\'s own container, so a section can hold both a boxed band and a full-width band (e.g. a contained heading above a full-bleed gallery).', 'fw' ),
							'type'  => 'switch',
							'value' => false,
						],
						// Min Height — hybrid multi-picker (viewport preset OR Custom unit-input),
						// canonical shape (label/desc/help on the picker; top-level label/desc false;
						// default in the top-level `value`; non-empty choice keys; `choices` reveals
						// the Custom sub-option; show_borders false). Mirrors the Section's control.
						'min_height' => [
							'type'         => 'multi-picker',
							'label'        => false,
							'desc'         => false,
							'value'        => [ 'preset' => 'auto' ],
							'picker'       => [
								'preset' => [
									'label'   => __( 'Min Height', 'fw' ),
									'desc'    => __( 'Minimum container height. Use a viewport preset (vh) for a tall band, or pick Custom for an exact value.', 'fw' ),
									'help'    => __( 'Pairs with Columns Vertical Alignment below — give the container a tall min-height, then centre the columns. "Auto" lets the container shrink-wrap its content.', 'fw' ),
									'type'    => 'select',
									'choices' => [
										'auto'   => __( 'Auto (fit content)', 'fw' ),
										'40vh'   => __( '40% of viewport', 'fw' ),
										'60vh'   => __( '60% of viewport', 'fw' ),
										'80vh'   => __( '80% of viewport', 'fw' ),
										'100vh'  => __( 'Full viewport (100vh)', 'fw' ),
										'custom' => __( 'Custom…', 'fw' ),
									],
								],
							],
							'choices'      => [
								'custom' => [
									'custom_height' => [
										'type'  => 'unit-input',
										'label' => __( 'Custom Height', 'fw' ),
										'desc'  => false,
										'units' => [ 'px', '%', 'vh', 'vw', 'rem', 'em' ],
										'value' => [ 'value' => '600', 'unit' => 'px' ],
									],
								],
							],
							'show_borders' => false,
						],
					],
					// column_halign / column_valign / reverse_columns (shared with Section).
					$__align
				),
			],
		],
	],

	'tab_styling' => [
		'title'   => __( 'Styling', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_background' => [
				'type'    => 'group',
				'options' => [
					'background' => [
						'type'  => 'background-pro',
						'label' => __( 'Background', 'fw' ),
						'desc'  => __( 'Color, gradient, image and video background layers (they stack: image over gradient over color). A boxed container tints only the contained band; a Full-width container spans edge-to-edge.', 'fw' ),
						'help'  => __( 'Image attachment "Fixed" gives a parallax effect. Video renders a muted, looping background; set a poster/fallback image for while it loads or where autoplay is blocked. (Video playback relies on the parent Section\'s background scripts, which are always present.)', 'fw' ),
					],
					// Reusable CSS/HTML background pattern drawn as a decorative layer BEHIND the
					// content (over the Background above). Stores the preset id (stable across renames).
					'background_pattern' => [
						'type'    => 'multi-picker',
						'label'   => __( 'Background Pattern', 'fw' ),
						'desc'    => __( 'A reusable CSS/HTML pattern drawn as a decorative layer behind the container content (on top of the Background above). Add / edit patterns in Theme Settings → Components → Background Patterns.', 'fw' ),
						'popover' => true,
						'value'   => [ 'pattern' => 'none' ],
						'picker'  => [
							'pattern' => [
								'type'    => 'image-picker',
								'label'   => false,
								'choices' => function_exists( 'unysonplus_pattern_imagepicker_choices' ) ? unysonplus_pattern_imagepicker_choices() : [ 'none' => [ 'label' => __( 'None', 'fw' ) ] ],
							],
						],
						'choices'      => [],
						'show_borders' => false,
					],
				],
			],
			'group_spacings' => [
				'type'    => 'group',
				'options' => [
					'padding_top' => sc_spacing_field( [
						'label'      => __( 'Top Spacing', 'fw' ),
						'prefix'     => 'pt',
						'responsive' => true,
						'desc'       => __( 'Vertical breathing room above the container content. Use the Phone / Tablet / Desktop tabs to set a different value per device (a blank device inherits the smaller one).', 'fw' ),
					] ),
					'padding_bottom' => sc_spacing_field( [
						'label'      => __( 'Bottom Spacing', 'fw' ),
						'prefix'     => 'pb',
						'responsive' => true,
						'desc'       => __( 'Vertical breathing room below the container content. Use the Phone / Tablet / Desktop tabs to set a different value per device (a blank device inherits the smaller one).', 'fw' ),
					] ),
					// Column-gap overrides for this container's row(s). Stored as scale slugs;
					// the view emits the same section--gap-{slug} / -x- / -y- modifier classes
					// (css-tokens.php turns them into --bs-gutter overrides on every .row inside).
					'gap' => [
						'type'    => 'responsive',
						'label'   => __( 'Gap', 'fw' ),
						'desc'    => __( 'Override the site-wide Default Gap for every row inside this container (both horizontal and vertical column gap). Use the Phone / Tablet / Desktop tabs to set a different gap per device (a blank device inherits the smaller one).', 'fw' ),
						'help'    => function_exists( 'sc_styling_help_text' ) ? sc_styling_help_text( 'spacing' ) : '',
						'value'   => [ 'base' => '', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'    => 'short-select',
							'choices' => function_exists( 'sc_get_gap_select_choices' )
								? sc_get_gap_select_choices( __( 'Use Default Gap', 'fw' ) )
								: array( '' => __( 'Use Default Gap', 'fw' ) ),
						],
					],
					'gap_x' => [
						'type'    => 'responsive',
						'label'   => __( 'Gap X (override)', 'fw' ),
						'desc'    => __( 'Overrides Gap on the horizontal axis only, per device. Leave at "Use Container Gap" to inherit.', 'fw' ),
						'help'    => __( 'Use this when you want wider/narrower space between columns side-to-side without changing the vertical gap. Only takes effect once Gap above is set.', 'fw' ),
						'value'   => [ 'base' => '', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'    => 'short-select',
							'choices' => function_exists( 'sc_get_gap_select_choices' )
								? sc_get_gap_select_choices( __( 'Use Container Gap', 'fw' ) )
								: array( '' => __( 'Use Container Gap', 'fw' ) ),
						],
					],
					'gap_y' => [
						'type'    => 'responsive',
						'label'   => __( 'Gap Y (override)', 'fw' ),
						'desc'    => __( 'Overrides Gap on the vertical axis only, per device. Leave at "Use Container Gap" to inherit.', 'fw' ),
						'help'    => __( 'Controls the vertical space between columns once they wrap onto new lines (e.g. on tablet/mobile). Only takes effect once Gap above is set.', 'fw' ),
						'value'   => [ 'base' => '', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'    => 'short-select',
							'choices' => function_exists( 'sc_get_gap_select_choices' )
								? sc_get_gap_select_choices( __( 'Use Container Gap', 'fw' ) )
								: array( '' => __( 'Use Container Gap', 'fw' ) ),
						],
					],
				],
			],
		],
	],

	// Animations + Advanced are the shared, generic element capabilities (entrance
	// animation; CSS ID / class / custom CSS / position / overflow). Both apply through
	// sc_build_wrapper_attr filters (shortcode-animation-helper.php / shortcode-get-option-helpers.php),
	// which the container view already calls — so they work with no view changes, exactly
	// like the Section. Mirrors the Section's tab order (Layout · Styling · Animations · Advanced).
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
