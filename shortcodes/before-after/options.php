<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Before / After — edit-modal options (the saved `atts` schema).
 *
 * The element has two TYPES, chosen with an inline image-picker multi-picker:
 *
 *   • comparison — the classic before/after slider (drag / hover / toggle). The
 *     Design picker `choices` come from the design registry (views/parts/registry.php)
 *     so the catalog has one source of truth.
 *   • spotlight  — a cursor-following circular reveal (the Lithos effect): the
 *     Before image sits on top and a soft radial hole under the pointer reveals the
 *     After image beneath. Can optionally FILL its Section as a background, with the
 *     Section's own content lifted above it.
 *
 * Per the multi-picker convention (INLINE / non-popover): the top-level multi-picker
 * is label/desc `false`; the user-visible label lives on the `type` picker sub-option.
 */

$options = array(

	/* ==========================================================
	   TAB 1 — CONTENT
	   ========================================================== */
	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_images' => array(
				'type'    => 'group',
				'options' => array(
					'before_image' => array(
						'type'  => 'upload',
						'label' => __( 'Before Image', 'fw' ),
						'desc'  => __( 'The base image. In Comparison it is the "before" side; in Spotlight it is what shows normally (the After image is revealed under the cursor).', 'fw' ),
						'help'  => __( 'For the cleanest result, use two images with the SAME dimensions and framing. The Image Ratio on the Design tab crops both to a consistent shape.', 'fw' ),
					),
					'after_image' => array(
						'type'  => 'upload',
						'label' => __( 'After Image', 'fw' ),
						'desc'  => __( 'The revealed image. In Comparison it is the "after" side; in Spotlight it is revealed inside the circular spotlight under the pointer.', 'fw' ),
						'help'  => __( 'Use the same size as the Before image so the two line up pixel-for-pixel.', 'fw' ),
					),
				),
			),
		),
	),

	/* ==========================================================
	   TAB 2 — DESIGN (Type + shared image framing)
	   ========================================================== */
	'tab_design' => array(
		'title'   => __( 'Design', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			/* --- The TYPE selector (inline image-picker multi-picker) ----- */
			'type' => call_user_func( function () {
				$registry = require dirname( __FILE__ ) . '/views/parts/registry.php';
				$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/before-after/static/img/design' );

				/* Design skin choices (revealed under the Comparison type). */
				$design_choices = array();
				if ( is_array( $registry ) ) {
					foreach ( $registry as $key => $meta ) {
						$thumb = isset( $meta['thumb'] ) ? $meta['thumb'] : ( $key . '.svg' );
						$design_choices[ $key ] = array(
							'small' => array(
								'src'    => $base . '/' . $thumb,
								'height' => 72,
								'title'  => isset( $meta['label'] ) ? $meta['label'] : $key,
							),
						);
					}
				}

				return array(
					'type'         => 'multi-picker',
					'label'        => false,
					'desc'         => false,
					'show_borders' => false,
					'value'        => array( 'type' => 'comparison' ),
					'picker'       => array(
						'type' => array(
							'type'    => 'image-picker',
							'label'   => __( 'Type', 'fw' ),
							'desc'    => __( 'Comparison is the classic slider. Spotlight reveals the After image in a soft circle that follows the cursor — and can fill its Section as a background.', 'fw' ),
							'help'    => __( 'Comparison uses the Design / handle / label options; Spotlight uses the radius, softness and background options.', 'fw' ),
							'value'   => 'comparison',
							'choices' => array(
								'comparison' => array( 'small' => array( 'src' => $base . '/type-comparison.svg', 'height' => 72, 'title' => __( 'Comparison slider', 'fw' ) ) ),
								'spotlight'  => array( 'small' => array( 'src' => $base . '/type-spotlight.svg',  'height' => 72, 'title' => __( 'Spotlight reveal', 'fw' ) ) ),
							),
						),
					),
					'choices' => array(

						/* ---- COMPARISON: the slider options ---- */
						'comparison' => array(
							'group_cmp_design' => array(
								'type'    => 'group',
								'options' => array(
									'design' => array(
										'type'    => 'image-picker',
										'label'   => __( 'Design', 'fw' ),
										'desc'    => __( 'The handle / label look of the comparison. The behaviour (orientation, drag/hover, etc.) is set below and works with any design.', 'fw' ),
										'help'    => __( 'Hover a thumbnail to see its name. All designs share the same slider engine — pick the look you like, then tune the behaviour.', 'fw' ),
										'value'   => 'classic',
										'choices' => $design_choices,
									),
								),
							),
							'group_cmp_behavior' => array(
								'type'    => 'group',
								'options' => array(
									'orientation' => array(
										'type'    => 'select',
										'label'   => __( 'Orientation', 'fw' ),
										'value'   => 'horizontal',
										'choices' => array(
											'horizontal' => __( 'Horizontal (drag left ↔ right)', 'fw' ),
											'vertical'   => __( 'Vertical (drag up ↕ down)', 'fw' ),
										),
										'desc' => __( 'Direction of the divider and the reveal.', 'fw' ),
									),
									'interaction' => array(
										'type'    => 'select',
										'label'   => __( 'Interaction', 'fw' ),
										'value'   => 'drag',
										'choices' => array(
											'drag'   => __( 'Drag the handle', 'fw' ),
											'hover'  => __( 'Follow the cursor (hover)', 'fw' ),
											'toggle' => __( 'Click to toggle (crossfade)', 'fw' ),
										),
										'desc' => __( 'How visitors reveal the after image. Toggle crossfades the whole image on click/tap and hides the handle.', 'fw' ),
									),
									'start_position' => array(
										'type'  => 'slider',
										'label' => __( 'Start Position (%)', 'fw' ),
										'value' => 50,
										'properties' => array( 'min' => 0, 'max' => 100, 'step' => 1 ),
										'desc'  => __( 'Where the divider sits initially (0 = all after, 100 = all before).', 'fw' ),
									),
									'auto_intro' => array(
										'type'  => 'switch',
										'label' => __( 'Auto Intro Sweep', 'fw' ),
										'desc'  => __( 'Animate a quick sweep when the slider first scrolls into view, hinting that it is interactive.', 'fw' ),
										'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
										'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
										'value' => 'yes',
										'help'  => __( 'Ignored for the Click-to-toggle interaction.', 'fw' ),
									),
									'handle_size' => array(
										'type'    => 'select',
										'label'   => __( 'Handle Size', 'fw' ),
										'value'   => 'md',
										'choices' => array(
											'sm' => __( 'Small', 'fw' ),
											'md' => __( 'Medium', 'fw' ),
											'lg' => __( 'Large', 'fw' ),
										),
										'desc' => __( 'Size of the drag handle / knob.', 'fw' ),
									),
								),
							),
							'group_cmp_labels' => array(
								'type'    => 'group',
								'options' => array(
									'show_labels' => array(
										'type'  => 'switch',
										'label' => __( 'Show Labels', 'fw' ),
										'desc'  => __( 'Display a small label over each side.', 'fw' ),
										'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
										'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
										'value' => 'yes',
										'help'  => __( 'The Labeled and Framed designs always show labels regardless of this switch.', 'fw' ),
									),
									'before_label' => array(
										'type'  => 'text',
										'label' => __( 'Before Label', 'fw' ),
										'value' => __( 'Before', 'fw' ),
									),
									'after_label' => array(
										'type'  => 'text',
										'label' => __( 'After Label', 'fw' ),
										'value' => __( 'After', 'fw' ),
									),
								),
							),
						),

						/* ---- SPOTLIGHT: the cursor-reveal options ---- */
						'spotlight' => array(
							'group_spot' => array(
								'type'    => 'group',
								'options' => array(
									'spotlight_radius' => array(
										'type'  => 'slider',
										'label' => __( 'Spotlight Radius (px)', 'fw' ),
										'value' => 240,
										'properties' => array( 'min' => 60, 'max' => 700, 'step' => 10 ),
										'desc'  => __( 'Size of the circular reveal that follows the cursor.', 'fw' ),
									),
									'spotlight_softness' => array(
										'type'  => 'slider',
										'label' => __( 'Edge Softness (%)', 'fw' ),
										'value' => 55,
										'properties' => array( 'min' => 0, 'max' => 95, 'step' => 5 ),
										'desc'  => __( 'How feathered the spotlight edge is. 0 = a hard circle, higher = a soft, gradual fade.', 'fw' ),
									),
									'smooth_follow' => array(
										'type'  => 'switch',
										'label' => __( 'Smooth Follow', 'fw' ),
										'desc'  => __( 'The spotlight eases toward the cursor for a fluid, slightly trailing feel. Off = it tracks the pointer exactly.', 'fw' ),
										'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
										'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
										'value' => 'yes',
									),
									'reveal_on_load' => array(
										'type'  => 'switch',
										'label' => __( 'Idle Reveal', 'fw' ),
										'desc'  => __( 'Before the visitor moves the pointer (and on touch, where there is no hover), show the spotlight resting in the centre so the effect is discoverable.', 'fw' ),
										'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
										'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
										'value' => 'yes',
									),
								),
							),
						),
					),
				);
			} ),

			'group_appearance' => array(
				'type'    => 'group',
				'options' => array(
					// Reusable "Use as Section Background" toggle — works for BOTH types
					// (Spotlight reveal or a Comparison slider, e.g. an invisible-line
					// slider that wipes under the cursor as a full-bleed hero backdrop).
					'as_background' => sc_section_background_field( array(
						'desc' => __( 'Fill the parent Section and sit behind its content — the Section\'s own elements are automatically lifted on top. Works with either Type; pairs best with the Spotlight type or the Comparison "Follow the cursor" interaction (try the Invisible design).', 'fw' ),
						'help' => __( 'When on, the Image Ratio / Max Width / Corner Radius below are ignored — the element stretches to cover its Section. Give the Section a min-height so it has room to fill.', 'fw' ),
					) ),
					'ratio' => array(
						'type'    => 'select',
						'label'   => __( 'Image Ratio', 'fw' ),
						'value'   => 'ratio-16-9',
						'choices' => array(
							'original'   => __( 'Original (uncropped — uses the Before image)', 'fw' ),
							'ratio-1-1'  => __( 'Square 1:1', 'fw' ),
							'ratio-4-3'  => __( 'Landscape 4:3', 'fw' ),
							'ratio-3-2'  => __( 'Landscape 3:2', 'fw' ),
							'ratio-16-9' => __( 'Widescreen 16:9', 'fw' ),
							'ratio-3-4'  => __( 'Portrait 3:4', 'fw' ),
							'ratio-2-3'  => __( 'Portrait 2:3', 'fw' ),
						),
						'desc' => __( 'Crop both images to a consistent shape (object-fit cover). Ignored when Spotlight fills its Section as a background.', 'fw' ),
					),
					'max_width' => array(
						'type'  => 'text',
						'label' => __( 'Max Width', 'fw' ),
						'value' => '',
						'desc'  => __( 'Optional. Constrain the width, e.g. 800px or 80%. Blank = full width. Ignored in background mode.', 'fw' ),
					),
					'rounded' => array(
						'type'    => 'select',
						'label'   => __( 'Corner Radius', 'fw' ),
						'value'   => 'rounded',
						'choices' => array(
							'rounded-0'  => __( 'Square', 'fw' ),
							'rounded'    => __( 'Rounded', 'fw' ),
							'rounded-lg' => __( 'Large', 'fw' ),
						),
						'desc' => __( 'Rounding of the corners. Ignored in background mode.', 'fw' ),
					),
				),
			),
		),
	),

	/* ==========================================================
	   TAB 3 — STYLING
	   ========================================================== */
	'tab_styling' => array(
		'title'   => __( 'Styling', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_colors' => array(
				'type'    => 'group',
				'options' => array(
					'bg_color'         => sc_color_field_compact( array( 'label' => __( 'Background Color', 'fw' ), 'kind' => 'bg' ) ),
					'font_size_preset' => sc_font_size_field( array(
						'desc' => __( 'Base font size for the labels. A named size from the framework presets.', 'fw' ),
					) ),
					'divider_color' => sc_color_field_compact( array(
						'label' => __( 'Divider Color', 'fw' ),
						'desc'  => __( 'Color of the divider line (Comparison only; custom color is honored, presets fall back to white).', 'fw' ),
					) ),
					'handle_color' => sc_color_field_compact( array(
						'label' => __( 'Handle Color', 'fw' ),
						'kind'  => 'bg',
						'desc'  => __( 'Background color of the knob (Comparison only).', 'fw' ),
					) ),
					'handle_icon_color' => sc_color_field_compact( array(
						'label' => __( 'Handle Icon Color', 'fw' ),
						'desc'  => __( 'Color of the arrows inside the knob (Comparison only).', 'fw' ),
					) ),
					'label_bg' => sc_color_field_compact( array(
						'label' => __( 'Label Background', 'fw' ),
						'kind'  => 'bg',
						'desc'  => __( 'Background of the Before/After labels (Comparison only).', 'fw' ),
					) ),
					'label_text' => sc_color_field_compact( array(
						'label' => __( 'Label Text Color', 'fw' ),
						'desc'  => __( 'Text color of the Before/After labels (Comparison only).', 'fw' ),
					) ),
				),
			),
			'group_spacings' => array(
				'type'    => 'group',
				'options' => array(
					'spacing' => array(
						'type'  => 'spacing',
						'label' => __( 'Margin & Padding', 'fw' ),
						'desc'  => __( 'All Sides applies to every side at once; any per-side value (Top, Right, Bottom, Left) overrides it for that direction.', 'fw' ),
						'help'  => sc_styling_help_text( 'spacing' ),
					),
				),
			),
		),
	),
	'tab_animation' => array(
		'title'   => __( 'Animations', 'fw' ),
		'type'    => 'tab',
		'options' => sc_get_animation_fields(),
	),
	'tab_advanced' => array(
		'title'   => __( 'Advanced', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'advanced_settings' => array(
				'type'    => 'group',
				'options' => sc_get_advanced_tab(),
			),
		),
	),
);
