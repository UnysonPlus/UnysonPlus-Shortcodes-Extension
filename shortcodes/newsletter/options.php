<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(

	/* ========================== CONTENT ========================== */
	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_text' => array(
				'type'    => 'group',
				'options' => array(
					'title' => array(
						'type'  => 'text',
						'label' => __( 'Heading', 'fw' ),
						'value' => __( 'Subscribe to our newsletter', 'fw' ),
						'desc'  => __( 'Blank to hide.', 'fw' ),
					),
					'description' => array(
						'type'  => 'textarea',
						'label' => __( 'Description', 'fw' ),
						'value' => __( 'Get the latest updates straight to your inbox.', 'fw' ),
					),
				),
			),
			'group_fields' => array(
				'type'    => 'group',
				'options' => array(
					'show_name' => array(
						'type'  => 'switch',
						'label' => __( 'Ask for Name', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'no',
					),
					'name_placeholder' => array(
						'type'  => 'text',
						'label' => __( 'Name Placeholder', 'fw' ),
						'value' => __( 'Your name', 'fw' ),
					),
					'email_placeholder' => array(
						'type'  => 'text',
						'label' => __( 'Email Placeholder', 'fw' ),
						'value' => __( 'Your email address', 'fw' ),
					),
					// A leading glyph INSIDE the email field (an envelope beside the placeholder) — the icon picker's
					// packs / SVG / upload; rendered by sc_icon_render at the field's start, the input padded past it.
					'field_icon' => array(
						'type'  => 'icon',
						'label' => __( 'Field Icon', 'fw' ),
						'desc'  => __( 'Optional glyph shown inside the email field, before the placeholder.', 'fw' ),
					),
					'field_icon_color' => sc_color_field_compact( array( 'label' => __( 'Field Icon Color', 'fw' ), 'kind' => 'text' ) ),
					'button_label' => array(
						'type'  => 'text',
						'label' => __( 'Button Label', 'fw' ),
						'value' => __( 'Subscribe', 'fw' ),
					),
					'consent_text' => array(
						'type'  => 'textarea',
						'label' => __( 'Consent / Fine Print', 'fw' ),
						'desc'  => __( 'Optional small text under the form (basic HTML / links allowed).', 'fw' ),
					),
				),
			),
			'group_messages' => array(
				'type'    => 'group',
				'options' => array(
					'success_message' => array(
						'type'  => 'text',
						'label' => __( 'Success Message', 'fw' ),
						'value' => __( 'Thanks for subscribing!', 'fw' ),
					),
					'error_message' => array(
						'type'  => 'text',
						'label' => __( 'Error Message', 'fw' ),
						'value' => __( 'Something went wrong. Please try again.', 'fw' ),
					),
					'list_id' => array(
						'type'  => 'text',
						'label' => __( 'List ID', 'fw' ),
						'desc'  => __( 'Optional. Passed to the fw_newsletter_subscribe hook for list integrations (Mailchimp, etc.).', 'fw' ),
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
								'value'   => 'inline',
								'desc'    => __( 'Choose a design for this element. Hover a tile to see its name.', 'fw' ),
								'choices' => fw_sc_design_picker_choices( 'newsletter' ),
							);
						}
						$registry = require dirname( __FILE__ ) . '/views/parts/registry.php';
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/newsletter/static/img/design' );
						$choices  = array();
						foreach ( (array) $registry as $key => $meta ) {
							$choices[ $key ] = array( 'small' => array(
								'src'    => $base . '/' . ( isset( $meta['thumb'] ) ? $meta['thumb'] : $key . '.svg' ),
								'height' => 52,
								'title'  => isset( $meta['label'] ) ? $meta['label'] : $key,
							) );
						}
						return array(
							'type'    => 'image-picker',
							'label'   => __( 'Design', 'fw' ),
							'value'   => 'inline',
							'choices' => $choices,
						);
					} ),
				),
			),
			'group_layout' => array(
				'type'    => 'group',
				'options' => array(
					'align' => sc_alignment_field( array( 'label' => __( 'Alignment', 'fw' ), 'value' => 'left' ) ),
					'rounded' => array(
						'type'    => 'select',
						'label'   => __( 'Field Roundness', 'fw' ),
						'value'   => 'rounded',
						'choices' => array( 'rounded-0' => __( 'Square', 'fw' ), 'rounded' => __( 'Rounded', 'fw' ), 'pill' => __( 'Pill', 'fw' ) ),
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
					// Button Preset replaces the old free "Button Color" picker, so the form button
					// wears the same Theme Settings → Buttons presets as every other button on the
					// site instead of a one-off colour. None keeps the accent button, which follows
					// the theme's primary colour.
					'button_preset' => array(
						'type'         => 'button-style-picker',
						'label'        => __( 'Button Preset', 'fw' ),
						'choices'      => function_exists( 'sc_get_button_style_choices' ) ? sc_get_button_style_choices() : array(),
						'value'        => '',
						'allow_none'   => true,
						'preview_text' => __( 'Subscribe', 'fw' ),
						'desc'         => __( 'Apply a themed Button Preset (Theme Settings → General → Buttons) to the submit button, so it matches the rest of the site. Leave as None for the accent button, which follows the primary colour of the theme.', 'fw' ),
						'help'         => function_exists( 'sc_styling_help_text' ) ? sc_styling_help_text( 'button_style' ) : '',
					),
					'field_bg'     => sc_color_field_compact( array( 'label' => __( 'Field Background', 'fw' ), 'kind' => 'bg' ) ),
					'bg_color'     => sc_color_field_compact( array( 'label' => __( 'Box Background (Boxed)', 'fw' ), 'kind' => 'bg' ) ),
					'text_color'   => sc_color_field_compact( array( 'label' => __( 'Text Color', 'fw' ) ) ),
					'font_size_preset' => sc_font_size_field(),
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
