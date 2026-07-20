<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

// Effect swatches for the popover image-picker (value stays a scalar via the `popover`
// option type's single-inner passthrough — no migration from the old select).
$fb_fx_img = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/flip-box/static/img/effect' );
$fb_fx = array(
	'left'        => __( 'Flip — Left', 'fw' ),
	'right'       => __( 'Flip — Right', 'fw' ),
	'up'          => __( 'Flip — Up', 'fw' ),
	'down'        => __( 'Flip — Down', 'fw' ),
	'diagonal'    => __( 'Flip — Diagonal', 'fw' ),
	'fade'        => __( 'Reveal — Fade', 'fw' ),
	'zoom'        => __( 'Reveal — Zoom', 'fw' ),
	'slide-up'    => __( 'Reveal — Slide Up', 'fw' ),
	'slide-down'  => __( 'Reveal — Slide Down', 'fw' ),
	'slide-left'  => __( 'Reveal — Slide Left', 'fw' ),
	'slide-right' => __( 'Reveal — Slide Right', 'fw' ),
);
$fb_fx_choices = array();
foreach ( $fb_fx as $fb_k => $fb_lbl ) {
	$fb_fx_choices[ $fb_k ] = array( 'small' => array( 'src' => $fb_fx_img . '/' . $fb_k . '.svg', 'height' => 66, 'title' => $fb_lbl ) );
}

// Corner-radius swatches (also a popover). Keys kept for back-compat; view.php maps each
// to a --fb-radius value (applied straight to the faces, so Bootstrap's !important .rounded
// utilities can't override it and the parallax layer can't break it).
$fb_radius_img = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/flip-box/static/img/radius' );
$fb_radius = array(
	'rounded-0'  => __( 'Square', 'fw' ),
	'rounded-sm' => __( 'Small', 'fw' ),
	'rounded'    => __( 'Medium', 'fw' ),
	'rounded-lg' => __( 'Large', 'fw' ),
	'rounded-xl' => __( 'Extra Large', 'fw' ),
);
$fb_radius_choices = array();
foreach ( $fb_radius as $fb_rk => $fb_rlbl ) {
	$fb_radius_choices[ $fb_rk ] = array( 'small' => array( 'src' => $fb_radius_img . '/' . $fb_rk . '.svg', 'height' => 66, 'title' => $fb_rlbl ) );
}

// Shared Title Tag choices (semantic level, like the Special Heading shortcode).
$fb_title_tags = array(
	'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6',
	'span' => __( 'Span (decorative)', 'fw' ), 'p' => __( 'Paragraph', 'fw' ),
);

// Back-face button reuses the [button] presets (Theme Settings → General → Buttons).
$fb_btn_styles        = function_exists( 'sc_get_button_style_choices' ) ? sc_get_button_style_choices() : array();
$fb_btn_style_default = ( is_array( $fb_btn_styles ) && $fb_btn_styles ) ? (string) key( $fb_btn_styles ) : '';
$fb_btn_sizes         = function_exists( 'sc_get_button_size_choices' ) ? sc_get_button_size_choices() : array();
$fb_btn_size_default  = ( is_array( $fb_btn_sizes ) && $fb_btn_sizes ) ? (string) key( $fb_btn_sizes ) : '';

$options = array(

	/* ========================== CONTENT ========================== */
	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_front' => array(
				'type'    => 'group',
				'options' => array(
					'front_icon' => array(
						'type'         => 'icon-v2',
						'label'        => __( 'Front Icon', 'fw' ),
						'preview_size' => 'small',
						'desc'         => __( 'Optional icon shown on the front face.', 'fw' ),
					),
					'front_title' => array(
						'type'  => 'text',
						'label' => __( 'Front Title', 'fw' ),
						'value' => __( 'Hover Me', 'fw' ),
					),
					'front_title_tag' => array(
						'type'    => 'select',
						'label'   => __( 'Front Title Tag', 'fw' ),
						'desc'    => __( 'Semantic heading level (SEO), not the visual size.', 'fw' ),
						'value'   => 'h3',
						'choices' => $fb_title_tags,
					),
					'front_text' => array(
						'type'          => 'wp-editor',
						'label'         => __( 'Front Text', 'fw' ),
						'desc'          => __( 'Optional text shown on the front face. Supports rich text and links.', 'fw' ),
						'size'          => 'small',
						'editor_height' => 150,
						'tinymce'       => true,
						'wpautop'       => true,
						'value'         => '',
					),
					'front_button_label' => array(
						'type'  => 'text',
						'label' => __( 'Front Button (flips to back)', 'fw' ),
						'desc'  => __( 'Optional. Shows a button on the front (e.g. "Details") that flips the card — handy on touch. Uses the Button Style / Size below. Leave blank for none.', 'fw' ),
					),
				),
			),
			'group_back' => array(
				'type'    => 'group',
				'options' => array(
					'back_icon' => array(
						'type'         => 'icon-v2',
						'label'        => __( 'Back Icon', 'fw' ),
						'preview_size' => 'small',
						'desc'         => __( 'Optional icon shown on the back face.', 'fw' ),
					),
					'back_title' => array(
						'type'  => 'text',
						'label' => __( 'Back Title', 'fw' ),
						'value' => __( 'More Info', 'fw' ),
					),
					'back_title_tag' => array(
						'type'    => 'select',
						'label'   => __( 'Back Title Tag', 'fw' ),
						'desc'    => __( 'Semantic heading level (SEO), not the visual size.', 'fw' ),
						'value'   => 'h3',
						'choices' => $fb_title_tags,
					),
					'back_text' => array(
						'type'          => 'wp-editor',
						'label'         => __( 'Back Text', 'fw' ),
						'desc'          => __( 'The detail revealed when the card flips. Supports rich text and links.', 'fw' ),
						'size'          => 'small',
						'editor_height' => 160,
						'tinymce'       => true,
						'wpautop'       => true,
						'value'         => __( 'Add the detail you want revealed when the card flips.', 'fw' ),
					),
					'button_label' => array(
						'type'  => 'text',
						'label' => __( 'Button Label', 'fw' ),
						'desc'  => __( 'Leave blank to hide the button.', 'fw' ),
					),
					'button_url' => array(
						'type'  => 'text',
						'label' => __( 'Button URL', 'fw' ),
					),
					'button_target' => array(
						'type'  => 'switch',
						'label' => __( 'Open in New Tab', 'fw' ),
						'right-choice' => array( 'value' => '_blank', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => '_self', 'label' => __( 'No', 'fw' ) ),
						'value' => '_self',
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
					// Future-proof Design picker: a POPOVER multi-picker built from the design
					// registry (single source of truth). Add a registry entry + swatch + CSS and
					// it appears here automatically. A design that needs its OWN options later
					// just adds an entry to `choices` keyed by its slug — the common options
					// (Effect, Trigger, Parallax, …) stay in their own sections. New `design_settings`
					// key (the old scalar `design` is read as a fallback in view.php), so no
					// migration and no editor "illegal string offset" on pre-existing boxes.
					'design_settings' => call_user_func( function () {
						$registry = require dirname( __FILE__ ) . '/views/parts/registry.php';
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/flip-box/static/img/design' );
						$choices  = array();
						foreach ( (array) $registry as $key => $meta ) {
							$choices[ $key ] = array( 'small' => array(
								'src'    => $base . '/' . ( isset( $meta['thumb'] ) ? $meta['thumb'] : $key . '.svg' ),
								'height' => 72,
								'title'  => isset( $meta['label'] ) ? $meta['label'] : $key,
							) );
						}
						return array(
							'type'         => 'multi-picker',
							'label'        => __( 'Design', 'fw' ),
							'desc'         => __( 'The look of the two faces. Click to pick.', 'fw' ),
							'popover'      => true,
							'show_borders' => false,
							'value'        => array( 'skin' => 'solid' ),
							'picker'       => array(
								'skin' => array(
									'type'    => 'image-picker',
									'label'   => false,
									'value'   => 'solid',
									'choices' => $choices,
								),
							),
							// Per-design options go here, keyed by design slug, e.g.:
							//   'gradient' => array( 'angle' => array( 'type' => 'slider', ... ) ),
							// then read them in view.php via design_settings/{slug}/{id}.
							'choices'      => array(),
						);
					} ),
				),
			),
			'group_behavior' => array(
				'type'    => 'group',
				'options' => array(
					'flip_direction' => array(
						'type'          => 'popover',
						'label'         => __( 'Effect', 'fw' ),
						'desc'          => __( 'How the back is revealed — a 3D flip or a 2D reveal. Click to pick.', 'fw' ),
						'value'         => 'left',
						'summary'       => $fb_fx, // value => trigger label
						'inner-options' => array(
							'fx' => array(
								'type'    => 'image-picker',
								'label'   => false,
								'value'   => 'left',
								'choices' => $fb_fx_choices,
							),
						),
					),
					'trigger' => array(
						'type'    => 'select',
						'label'   => __( 'Trigger', 'fw' ),
						'value'   => 'hover',
						'choices' => array(
							'hover' => __( 'On hover', 'fw' ),
							'click' => __( 'On click / tap', 'fw' ),
							'both'  => __( 'Hover + click', 'fw' ),
						),
						'desc' => __( 'Hover is desktop-friendly; Click is best for touch. "Hover + click" flips on hover and also toggles on click/tap.', 'fw' ),
					),
					'parallax' => array(
						'type'         => 'switch',
						'label'        => __( 'Parallax Depth', 'fw' ),
						'desc'         => __( 'Floats the content forward in 3D so it lifts off the background — a layered depth effect, most visible during the flip.', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no', 'label' => __( 'No', 'fw' ) ),
						'value'        => 'no',
					),
					'flip_speed' => array(
						'type'       => 'slider',
						'label'      => __( 'Speed (ms)', 'fw' ),
						'desc'       => __( 'How long the flip / reveal takes.', 'fw' ),
						'value'      => 600,
						'properties' => array( 'min' => 150, 'max' => 1500, 'step' => 50 ),
					),
					'flip_easing' => array(
						'type'    => 'select',
						'label'   => __( 'Easing', 'fw' ),
						'value'   => 'smooth',
						'choices' => array(
							'smooth'      => __( 'Smooth', 'fw' ),
							'ease'        => __( 'Ease', 'fw' ),
							'ease-in-out' => __( 'Ease in-out', 'fw' ),
							'spring'      => __( 'Spring (overshoot)', 'fw' ),
							'linear'      => __( 'Linear', 'fw' ),
						),
					),
					'height' => array(
						'type'  => 'slider',
						'label' => __( 'Height (px)', 'fw' ),
						'value' => 300,
						'properties' => array( 'min' => 160, 'max' => 560, 'step' => 10 ),
					),
					'rounded' => array(
						'type'          => 'popover',
						'label'         => __( 'Corner Radius', 'fw' ),
						'desc'          => __( 'Click to pick the corner roundness.', 'fw' ),
						'value'         => 'rounded',
						'summary'       => $fb_radius,
						'inner-options' => array(
							'r' => array(
								'type'    => 'image-picker',
								'label'   => false,
								'value'   => 'rounded',
								'choices' => $fb_radius_choices,
							),
						),
					),
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
					'box_style'   => sc_card_box_style_field(),
					'front_bg'    => sc_color_field_compact( array( 'label' => __( 'Front Background', 'fw' ), 'kind' => 'bg' ) ),
					'front_image' => array(
						'type'  => 'upload',
						'label' => __( 'Front Background Image', 'fw' ),
						'desc'  => __( 'Optional. Shows as the front background on ANY design, with a dark overlay for legibility.', 'fw' ),
					),
					'front_color' => sc_color_field_compact( array( 'label' => __( 'Front Text Color', 'fw' ) ) ),
					'back_bg'     => sc_color_field_compact( array( 'label' => __( 'Back Background', 'fw' ), 'kind' => 'bg' ) ),
					'back_image' => array(
						'type'  => 'upload',
						'label' => __( 'Back Background Image', 'fw' ),
						'desc'  => __( 'Optional. Shows as the back background on ANY design, with a dark overlay for legibility.', 'fw' ),
					),
					'back_color'  => sc_color_field_compact( array( 'label' => __( 'Back Text Color', 'fw' ) ) ),
					'font_size_preset' => sc_font_size_field(),
				),
			),
			'group_button' => array(
				'type'    => 'group',
				'options' => array(
					'button_style' => array(
						'type'    => 'button-style-picker',
						'label'   => __( 'Button Style', 'fw' ),
						'desc'    => __( 'Sourced from Theme Settings → General → Buttons (colors + outline presets). Each option previews the real button.', 'fw' ),
						'choices' => $fb_btn_styles,
						'value'   => $fb_btn_style_default,
					),
					'button_size' => array(
						'type'    => 'button-style-picker',
						'label'   => __( 'Button Size', 'fw' ),
						'choices' => $fb_btn_sizes,
						'value'   => $fb_btn_size_default,
					),
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
