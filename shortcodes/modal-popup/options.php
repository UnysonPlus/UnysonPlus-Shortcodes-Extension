<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(

	/* ========================== CONTENT ========================== */
	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_trigger' => array(
				'type'    => 'group',
				'options' => array(
					'trigger_type' => array(
						'type'    => 'select',
						'label'   => __( 'Trigger', 'fw' ),
						'value'   => 'button',
						'choices' => array(
							'button' => __( 'Button', 'fw' ),
							'text'   => __( 'Text link', 'fw' ),
							'icon'   => __( 'Icon', 'fw' ),
							'image'  => __( 'Image', 'fw' ),
						),
					),
					'trigger_label' => array(
						'type'  => 'text',
						'label' => __( 'Trigger Label', 'fw' ),
						'value' => __( 'Open', 'fw' ),
						'desc'  => __( 'Used by the Button and Text triggers.', 'fw' ),
					),
					'trigger_icon' => array(
						'type'         => 'icon-v2',
						'label'        => __( 'Trigger Icon', 'fw' ),
						'preview_size' => 'small',
					),
					'trigger_image' => array(
						'type'  => 'upload',
						'label' => __( 'Trigger Image', 'fw' ),
						'desc'  => __( 'Used by the Image trigger.', 'fw' ),
					),
				),
			),
			'group_modal' => array(
				'type'    => 'group',
				'options' => array(
					'modal_title' => array(
						'type'  => 'text',
						'label' => __( 'Modal Title', 'fw' ),
						'desc'  => __( 'Optional heading at the top of the modal.', 'fw' ),
					),
					'modal_content' => array(
						'type'  => 'textarea',
						'label' => __( 'Modal Content', 'fw' ),
						'value' => __( 'Your popup content goes here. Basic HTML is allowed.', 'fw' ),
						'help'  => __( 'HTML allowed (headings, links, lists, images, etc.).', 'fw' ),
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
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/modal-popup/static/img/design' );
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
							'label'   => __( 'Modal Style', 'fw' ),
							'value'   => 'center',
							'choices' => $choices,
						);
					} ),
				),
			),
			'group_behavior' => array(
				'type'    => 'group',
				'options' => array(
					'size' => array(
						'type'    => 'select',
						'label'   => __( 'Size', 'fw' ),
						'value'   => 'md',
						'choices' => array( 'sm' => __( 'Small', 'fw' ), 'md' => __( 'Medium', 'fw' ), 'lg' => __( 'Large', 'fw' ) ),
						'desc'    => __( 'Width of the centered card (drawers/fullscreen ignore this).', 'fw' ),
					),
					// NOTE: keyed 'open_animation', NOT 'animation' — 'animation' is a
					// RESERVED att written by the page builder's Animations tab (an
					// object), which would collide and break the render.
					'open_animation' => array(
						'type'    => 'select',
						'label'   => __( 'Open Animation', 'fw' ),
						'value'   => 'zoom',
						'choices' => array(
							'fade' => __( 'Fade', 'fw' ),
							'zoom' => __( 'Zoom', 'fw' ),
							'slide'=> __( 'Slide up', 'fw' ),
						),
					),
					'open_on_load' => array(
						'type'  => 'switch',
						'label' => __( 'Open Automatically', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'no',
						'desc'  => __( 'Open the modal on page load after the delay below.', 'fw' ),
					),
					'open_delay' => array(
						'type'  => 'text',
						'label' => __( 'Open Delay (ms)', 'fw' ),
						'value' => '0',
						'desc'  => __( 'Used only when Open Automatically is on.', 'fw' ),
					),
					'close_overlay' => array(
						'type'  => 'switch',
						'label' => __( 'Close on Overlay Click', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'yes',
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
					'accent_color' => sc_color_field_compact( array( 'label' => __( 'Trigger / Accent Color', 'fw' ), 'kind' => 'bg' ) ),
					'overlay_color'=> sc_color_field_compact( array( 'label' => __( 'Overlay Color', 'fw' ), 'kind' => 'bg' ) ),
					'modal_bg'     => sc_color_field_compact( array( 'label' => __( 'Modal Background', 'fw' ), 'kind' => 'bg' ) ),
					'modal_color'  => sc_color_field_compact( array( 'label' => __( 'Modal Text', 'fw' ) ) ),
					'font_size_preset' => sc_font_size_field(),
				),
			),
			'group_spacings' => array(
				'type'    => 'group',
				'options' => array(
					'spacing' => array(
						'type'  => 'spacing',
						'label' => __( 'Trigger Margin & Padding', 'fw' ),
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
