<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/* Build the `design` image-picker choices from the single-source-of-truth
   registry, so adding a design there automatically lists it here. SVG
   thumbnails live under static/img/design/. */
$av_uri            = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/avatar' );
$av_designs        = require dirname( __FILE__ ) . '/views/parts/registry.php';
$av_design_choices = array();
foreach ( $av_designs as $av_key => $av_def ) {
	$av_design_choices[ $av_key ] = array(
		'small' => array(
			'src'    => $av_uri . '/static/img/design/' . $av_def['thumb'],
			'height' => 56,
			'title'  => $av_def['label'],
		),
	);
}

/* Reusable status select — shared by the single avatar and each group member. */
$av_status_choices = array(
	''        => __( 'None', 'fw' ),
	'online'  => __( 'Online', 'fw' ),
	'away'    => __( 'Away', 'fw' ),
	'busy'    => __( 'Busy / Do not disturb', 'fw' ),
	'offline' => __( 'Offline', 'fw' ),
);

$options = array(

	/* ============================ CONTENT ============================ */
	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group' => array(
				'type'    => 'group',
				'options' => array(
					'mode_settings' => array(
						'type'         => 'multi-picker',
						'label'        => false,
						'desc'         => false,
						'show_borders' => false,
						'picker'       => array(
							'mode' => array(
								'label'   => __( 'Mode', 'fw' ),
								'type'    => 'image-picker',
								'choices' => array(
									'single' => array( 'small' => array( 'src' => $av_uri . '/static/img/mode/single.svg', 'height' => 56, 'title' => __( 'Single Avatar', 'fw' ) ) ),
									'group'  => array( 'small' => array( 'src' => $av_uri . '/static/img/mode/group.svg', 'height' => 56, 'title' => __( 'Avatar Group (stacked)', 'fw' ) ) ),
								),
								'desc'    => __( 'One avatar, or an overlapping row of avatars with an optional "+N" counter.', 'fw' ),
							),
						),
						'value'        => array( 'mode' => 'single' ),
						'choices'      => array(

							/* ---------------- SINGLE ---------------- */
							'single' => array(
								'image' => array(
									'type'  => 'upload',
									'label' => __( 'Image', 'fw' ),
									'desc'  => __( 'Upload or choose an avatar image. Leave empty to fall back to initials.', 'fw' ),
									'help'  => __( 'Library images are server-side cropped to a sharp square at the chosen size (retina-ready). If no image is set, the initials below are shown on a colored circle instead.', 'fw' ),
								),
								'name' => array(
									'type'  => 'text',
									'label' => __( 'Name', 'fw' ),
									'desc'  => __( 'Used for the alt text, the tooltip, the optional label, and to derive initials.', 'fw' ),
								),
								'initials' => array(
									'type'  => 'text',
									'label' => __( 'Initials (override)', 'fw' ),
									'desc'  => __( 'Leave empty to auto-derive from the Name (e.g. "Jane Lee" → "JL").', 'fw' ),
									'help'  => __( 'Only shown when there is no image. Keep it to 1–2 characters.', 'fw' ),
								),
								'subtitle' => array(
									'type'  => 'text',
									'label' => __( 'Subtitle / Role', 'fw' ),
									'desc'  => __( 'Optional second line, shown beside the avatar when "Show Label" is on.', 'fw' ),
								),
								'link' => array(
									'type'  => 'text',
									'label' => __( 'Link', 'fw' ),
									'desc'  => __( 'Optional URL to wrap the avatar in. Leave blank for a non-clickable avatar.', 'fw' ),
								),
								'target' => array(
									'type'         => 'switch',
									'label'        => __( 'Open Link in New Tab', 'fw' ),
									'right-choice' => array( 'value' => '_blank', 'label' => __( 'Yes', 'fw' ) ),
									'left-choice'  => array( 'value' => '_self', 'label' => __( 'No', 'fw' ) ),
									'value'        => '_self',
								),
								'status' => array(
									'type'    => 'select',
									'label'   => __( 'Status Dot', 'fw' ),
									'value'   => '',
									'choices' => $av_status_choices,
									'desc'    => __( 'Presence indicator shown at the corner of the avatar.', 'fw' ),
								),
							),

							/* ---------------- GROUP ---------------- */
							'group' => array(
								'people' => array(
									'type'          => 'addable-popup',
									'label'         => __( 'People', 'fw' ),
									'popup-title'   => __( 'Add / Edit Person', 'fw' ),
									'desc'          => __( 'Each entry is one avatar in the stack.', 'fw' ),
									'template'      => '{{= name || "Person" }}',
									'popup-options' => array(
										'image' => array(
											'type'  => 'upload',
											'label' => __( 'Image', 'fw' ),
											'desc'  => __( 'Avatar image. Leave empty to show initials.', 'fw' ),
										),
										'name' => array(
											'type'  => 'text',
											'label' => __( 'Name', 'fw' ),
											'desc'  => __( 'Alt text + tooltip, and the source for initials.', 'fw' ),
										),
										'initials' => array(
											'type'  => 'text',
											'label' => __( 'Initials (override)', 'fw' ),
											'desc'  => __( 'Leave empty to auto-derive from the Name.', 'fw' ),
										),
										'link' => array(
											'type'  => 'text',
											'label' => __( 'Link', 'fw' ),
											'desc'  => __( 'Optional URL for this person.', 'fw' ),
										),
										'status' => array(
											'type'    => 'select',
											'label'   => __( 'Status Dot', 'fw' ),
											'value'   => '',
											'choices' => $av_status_choices,
										),
									),
								),
								'max_visible' => array(
									'type'  => 'text',
									'label' => __( 'Max Visible', 'fw' ),
									'value' => '5',
									'desc'  => __( 'How many avatars to show before collapsing the rest into a "+N" counter. 0 or empty = show all.', 'fw' ),
								),
								'extra_count' => array(
									'type'  => 'text',
									'label' => __( 'Extra Count Label', 'fw' ),
									'desc'  => __( 'Optional manual counter, e.g. "2K+" for social proof. Overrides the auto "+N" of hidden avatars.', 'fw' ),
								),
								'overlap' => array(
									'type'       => 'slider',
									'label'      => __( 'Overlap', 'fw' ),
									'value'      => 35,
									'properties' => array( 'min' => 0, 'max' => 80, 'step' => 5 ),
									'desc'       => __( 'How much each avatar overlaps the previous one, as a percentage of its width.', 'fw' ),
								),
								'stack_order' => array(
									'type'    => 'select',
									'label'   => __( 'Stacking Order', 'fw' ),
									'value'   => 'first-on-top',
									'choices' => array(
										'first-on-top' => __( 'First on top', 'fw' ),
										'last-on-top'  => __( 'Last on top', 'fw' ),
									),
									'desc'    => __( 'Which avatar overlaps the others where they meet.', 'fw' ),
								),
							),
						),
					),
				),
			),
		),
	),

	/* ============================ DESIGN ============================ */
	'tab_design' => array(
		'title'   => __( 'Design', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_design' => array(
				'type'    => 'group',
				'options' => array(
					'design' => array(
						'type'    => 'image-picker',
						'label'   => __( 'Design', 'fw' ),
						'value'   => 'plain',
						'choices' => $av_design_choices,
						'desc'    => __( 'Visual treatment applied to the avatar(s) in either mode.', 'fw' ),
						'help'    => __( 'Bordered/Ring are especially useful in Group mode — the gap separates overlapping avatars cleanly. Colors for the ring/border/initials live on the Style tab.', 'fw' ),
					),
					'shape' => array(
						'type'    => 'select',
						'label'   => __( 'Shape', 'fw' ),
						'value'   => 'circle',
						'choices' => array(
							'circle'  => __( 'Circle', 'fw' ),
							'rounded' => __( 'Rounded', 'fw' ),
							'square'  => __( 'Square', 'fw' ),
						),
					),
					'size' => array(
						'type'       => 'slider',
						'label'      => __( 'Size (px)', 'fw' ),
						'value'      => 56,
						'properties' => array( 'min' => 24, 'max' => 240, 'step' => 2 ),
						'desc'       => __( 'Rendered width & height of each avatar, in pixels.', 'fw' ),
						'help'       => __( 'Library images are cropped server-side to a square at twice this size for retina sharpness, then displayed at this size.', 'fw' ),
					),
				),
			),
			'group_behavior' => array(
				'type'    => 'group',
				'options' => array(
					'show_status' => array(
						'type'         => 'switch',
						'label'        => __( 'Show Status Dot', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no', 'label' => __( 'No', 'fw' ) ),
						'value'        => 'yes',
						'desc'         => __( 'Master toggle for the presence dots (each avatar still needs a Status set).', 'fw' ),
					),
					'show_label' => array(
						'type'         => 'switch',
						'label'        => __( 'Show Label (Single)', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no', 'label' => __( 'No', 'fw' ) ),
						'value'        => 'no',
						'desc'         => __( 'Single mode: show the Name (and Subtitle) beside the avatar as a user chip.', 'fw' ),
					),
					'initials_color_mode' => array(
						'type'    => 'select',
						'label'   => __( 'Initials Background', 'fw' ),
						'value'   => 'auto',
						'choices' => array(
							'auto'  => __( 'Auto (per-name color)', 'fw' ),
							'theme' => __( 'Fixed (from Style tab)', 'fw' ),
						),
						'desc'    => __( 'Auto gives each name a stable, distinct color (nice in groups); Fixed uses the Initials Background color from the Style tab.', 'fw' ),
					),
				),
			),
		),
	),

	/* ============================ STYLE ============================ */
	'tab_style' => array(
		'title'   => __( 'Style', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_colors' => array(
				'type'    => 'group',
				'options' => array(
					'ring_color'     => sc_color_field_compact( array( 'label' => __( 'Ring / Border Color', 'fw' ), 'kind' => 'bg' ) ),
					'initials_bg'    => sc_color_field_compact( array( 'label' => __( 'Initials Background (Fixed mode)', 'fw' ), 'kind' => 'bg' ) ),
					'initials_color' => sc_color_field_compact( array( 'label' => __( 'Initials Text Color', 'fw' ) ) ),
					'label_color'    => sc_color_field_compact( array( 'label' => __( 'Label Text Color', 'fw' ) ) ),
					'counter_bg'     => sc_color_field_compact( array( 'label' => __( 'Counter "+N" Background', 'fw' ), 'kind' => 'bg' ) ),
					'counter_color'  => sc_color_field_compact( array( 'label' => __( 'Counter "+N" Text Color', 'fw' ) ) ),
					'font_size_preset' => sc_font_size_field( array(
						'desc' => __( 'Label / initials / counter text size. A named size from the framework presets.', 'fw' ),
					) ),
				),
			),
			'group_spacings' => array(
				'type'    => 'group',
				'options' => array(
					'spacing' => array(
						'type'  => 'spacing',
						'label' => __( 'Margin & Padding', 'fw' ),
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
