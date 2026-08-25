<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(

	/* ========================== CONTENT ========================== */
	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group' => array(
				'type'    => 'group',
				'options' => array(
					'steps' => array(
						'type'          => 'addable-popup',
						'label'         => __( 'Steps', 'fw' ),
						'popup-title'   => __( 'Add / Edit Step', 'fw' ),
						'template'      => '{{= title || "Step" }}',
						'popup-options' => array(
							'title' => array(
								'type'  => 'text',
								'label' => __( 'Title', 'fw' ),
								'value' => __( 'Step title', 'fw' ),
							),
							'content' => array(
								'type'  => 'textarea',
								'label' => __( 'Description', 'fw' ),
								'desc'  => __( 'Accepts HTML and shortcodes.', 'fw' ),
							),
							'icon' => array(
								'type'         => 'icon',
								'label'        => __( 'Icon', 'fw' ),
								'preview_size' => 'small',
								'desc'         => __( 'Used when Marker is set to Icon.', 'fw' ),
							),
							'number' => array(
								'type'  => 'text',
								'label' => __( 'Number / Label override', 'fw' ),
								'desc'  => __( 'Optional — defaults to the step position (1, 2, 3…).', 'fw' ),
							),
						),
					),
				),
			),
		),
	),

	/* ========================== DESIGN ========================== */
	'tab_design' => array(
		'title'   => __( 'Design', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_design' => array(
				'type'    => 'group',
				'options' => array(
					'design' => call_user_func( function () {
						if ( function_exists( 'fw_sc_design_picker_choices' ) ) {
							return array(
								'type'    => 'image-picker',
								'label'   => __( 'Design', 'fw' ),
								'value'   => 'horizontal',
								'desc'    => __( 'Choose a design for this element. Hover a tile to see its name.', 'fw' ),
								'choices' => fw_sc_design_picker_choices( 'steps' ),
							);
						}
						$registry = require dirname( __FILE__ ) . '/views/parts/registry.php';
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/steps/static/img/design' );
						$choices  = array();
						foreach ( (array) $registry as $key => $meta ) {
							$choices[ $key ] = array( 'small' => array(
								'src'    => $base . '/' . ( isset( $meta['thumb'] ) ? $meta['thumb'] : $key . '.svg' ),
								'height' => 56,
								'title'  => isset( $meta['label'] ) ? $meta['label'] : $key,
							) );
						}
						return array(
							'type'    => 'image-picker',
							'label'   => __( 'Design', 'fw' ),
							'value'   => 'horizontal',
							'choices' => $choices,
						);
					} ),
				),
			),
			'group_opts' => array(
				'type'    => 'group',
				'options' => array(
					'marker' => array(
						'type'    => 'select',
						'label'   => __( 'Marker', 'fw' ),
						'value'   => 'number',
						'choices' => array(
							'number' => __( 'Number', 'fw' ),
							'icon'   => __( 'Icon', 'fw' ),
							'none'   => __( 'None', 'fw' ),
						),
					),
					'marker_shape' => array(
						'type'    => 'select',
						'label'   => __( 'Marker Shape', 'fw' ),
						'value'   => 'circle',
						'choices' => array(
							'circle'  => __( 'Circle', 'fw' ),
							'rounded' => __( 'Rounded square', 'fw' ),
							'square'  => __( 'Square', 'fw' ),
						),
					),
					'connector' => array(
						'type'    => 'select',
						'label'   => __( 'Connector', 'fw' ),
						'value'   => 'solid',
						'choices' => array(
							'solid'  => __( 'Solid line', 'fw' ),
							'dashed' => __( 'Dashed line', 'fw' ),
							'none'   => __( 'None', 'fw' ),
						),
						'desc' => __( 'Line between markers (Horizontal / Vertical / Alternating).', 'fw' ),
					),
					'title_tag' => array(
						'type'    => 'select',
						'label'   => __( 'Title Tag', 'fw' ),
						'value'   => 'h3',
						'choices' => array( 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'div' => 'div' ),
					),
				),
			),
		),
	),

	/* ============================ CARD ============================ *
	 * The step CARD layout (Card Rows slot designer, shared with posts /
	 * testimonials / wc_products) + the card SKIN (Box Preset). Card Rows
	 * governs the step BODY interior (icon / number / title / description
	 * order + inline/stacked + alignment); the marker + connector SPINE is
	 * owned by the Design + Marker options, so the flow layouts stay intact. */
	'tab_card' => array(
		'title'   => __( 'Card', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_card' => array(
				'type'    => 'group',
				'options' => array(
					'card_preview' => array(
						'type'  => 'html-full',
						'label' => false,
						'html'  => function_exists( 'sc_card_preview_mount_html' ) ? sc_card_preview_mount_html() : '',
					),
					'card_rows' => sc_card_rows_field( array(
						'label' => __( 'Card Rows', 'fw' ),
						'desc'  => __( 'The step body layout — add / drag rows and pick each row\'s slots (Icon, Number, Title, Description) with inline/stacked + alignment. A slot shows only when it\'s in a row and has content. The Marker chip + connector line are controlled by the Design & Marker options above, so they stay on the flow spine.', 'fw' ),
						'slots' => array(
							'icon'    => __( 'Icon', 'fw' ),
							'number'  => __( 'Number', 'fw' ),
							'title'   => __( 'Title', 'fw' ),
							'content' => __( 'Description', 'fw' ),
						),
						'value' => array(
							array( 'slots' => array( 'title' ),   'direction' => 'stack', 'justify' => 'start', 'align' => 'start' ),
							array( 'slots' => array( 'content' ), 'direction' => 'stack', 'justify' => 'start', 'align' => 'start' ),
						),
					) ),
					'box_style' => sc_card_box_style_field( array(
						'desc' => __( 'Apply a reusable Box Preset (border, corners, shadow, fill + hover) to each step card. Most visible on the Cards design, where every step is its own box. Manage presets in Theme Settings → Components → Box Presets.', 'fw' ),
					) ),
				),
			),
		),
	),

	/* ========================== STYLING ========================== */
	'tab_styling' => array(
		'title'   => __( 'Styling', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_colors' => array(
				'type'    => 'group',
				'options' => array(
					'accent_color'      => sc_color_field_compact( array( 'label' => __( 'Marker / Connector', 'fw' ), 'kind' => 'bg' ) ),
						'icon_badge_preset' => sc_icon_badge_preset_field( array( 'desc' => __( 'Apply a reusable Icon Badge — a shaped tile (fill, border, corners, shadow) with its own icon colour + size — to EVERY step marker\'s icon (Marker = Icon). Manage presets in Theme Settings → Components → Icon Badges.', 'fw' ) ) ),
					'marker_text_color' => sc_color_field_compact( array( 'label' => __( 'Marker Text', 'fw' ) ) ),
					'title_color'       => sc_color_field_compact( array( 'label' => __( 'Title Color', 'fw' ) ) ),
					'text_color'        => sc_color_field_compact( array( 'label' => __( 'Description Color', 'fw' ) ) ),
					'font_size_preset'  => sc_font_size_field(),
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
