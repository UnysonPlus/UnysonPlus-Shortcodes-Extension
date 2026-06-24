<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

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
					'front_image' => array(
						'type'  => 'upload',
						'label' => __( 'Front Background Image', 'fw' ),
						'desc'  => __( 'Used by the "Image front" design as the front background.', 'fw' ),
					),
					'front_title' => array(
						'type'  => 'text',
						'label' => __( 'Front Title', 'fw' ),
						'value' => __( 'Hover Me', 'fw' ),
					),
					'front_text' => array(
						'type'  => 'textarea',
						'label' => __( 'Front Text', 'fw' ),
					),
				),
			),
			'group_back' => array(
				'type'    => 'group',
				'options' => array(
					'back_title' => array(
						'type'  => 'text',
						'label' => __( 'Back Title', 'fw' ),
						'value' => __( 'More Info', 'fw' ),
					),
					'back_text' => array(
						'type'  => 'textarea',
						'label' => __( 'Back Text', 'fw' ),
						'value' => __( 'Add the detail you want revealed when the card flips.', 'fw' ),
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
					'design' => call_user_func( function () {
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
							'type'    => 'image-picker',
							'label'   => __( 'Design', 'fw' ),
							'value'   => 'solid',
							'choices' => $choices,
							'desc'    => __( 'The look of the two faces.', 'fw' ),
						);
					} ),
				),
			),
			'group_behavior' => array(
				'type'    => 'group',
				'options' => array(
					'flip_direction' => array(
						'type'    => 'select',
						'label'   => __( 'Flip Direction', 'fw' ),
						'value'   => 'left',
						'choices' => array(
							'left'  => __( 'Flip left', 'fw' ),
							'right' => __( 'Flip right', 'fw' ),
							'up'    => __( 'Flip up', 'fw' ),
							'down'  => __( 'Flip down', 'fw' ),
						),
					),
					'trigger' => array(
						'type'    => 'select',
						'label'   => __( 'Trigger', 'fw' ),
						'value'   => 'hover',
						'choices' => array(
							'hover' => __( 'On hover', 'fw' ),
							'click' => __( 'On click / tap', 'fw' ),
						),
						'desc' => __( 'Click is best for touch devices.', 'fw' ),
					),
					'height' => array(
						'type'  => 'slider',
						'label' => __( 'Height (px)', 'fw' ),
						'value' => 300,
						'properties' => array( 'min' => 160, 'max' => 560, 'step' => 10 ),
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
					'front_bg'    => sc_color_field_compact( array( 'label' => __( 'Front Background', 'fw' ), 'kind' => 'bg' ) ),
					'front_color' => sc_color_field_compact( array( 'label' => __( 'Front Text Color', 'fw' ) ) ),
					'back_bg'     => sc_color_field_compact( array( 'label' => __( 'Back Background', 'fw' ), 'kind' => 'bg' ) ),
					'back_color'  => sc_color_field_compact( array( 'label' => __( 'Back Text Color', 'fw' ) ) ),
					'accent_color'=> sc_color_field_compact( array( 'label' => __( 'Button Color', 'fw' ), 'kind' => 'bg' ) ),
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
