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
						'value'   => 'text',
						'choices' => array(
							'text'   => __( 'Text (dotted underline)', 'fw' ),
							'button' => __( 'Button', 'fw' ),
							'icon'   => __( 'Icon', 'fw' ),
						),
					),
					'trigger_text' => array(
						'type'  => 'text',
						'label' => __( 'Trigger Text', 'fw' ),
						'value' => __( 'hover me', 'fw' ),
						'desc'  => __( 'Used by the Text and Button triggers.', 'fw' ),
					),
					'trigger_icon' => array(
						'type'         => 'icon-v2',
						'label'        => __( 'Trigger Icon', 'fw' ),
						'preview_size' => 'small',
						'desc'         => __( 'Used by the Icon trigger (defaults to a "?" if empty).', 'fw' ),
					),
				),
			),
			'group_tip' => array(
				'type'    => 'group',
				'options' => array(
					'tip_title' => array(
						'type'  => 'text',
						'label' => __( 'Tooltip Title', 'fw' ),
						'desc'  => __( 'Optional bold heading inside the tooltip.', 'fw' ),
					),
					'tip_content' => array(
						'type'  => 'textarea',
						'label' => __( 'Tooltip Content', 'fw' ),
						'value' => __( 'Helpful text goes here.', 'fw' ),
						'help'  => __( 'Basic HTML (links, bold, etc.) is allowed.', 'fw' ),
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
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/tooltip/static/img/design' );
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
							'label'   => __( 'Theme', 'fw' ),
							'value'   => 'dark',
							'choices' => $choices,
						);
					} ),
				),
			),
			'group_behavior' => array(
				'type'    => 'group',
				'options' => array(
					'position' => array(
						'type'    => 'select',
						'label'   => __( 'Position', 'fw' ),
						'value'   => 'top',
						'choices' => array(
							'top'    => __( 'Top', 'fw' ),
							'right'  => __( 'Right', 'fw' ),
							'bottom' => __( 'Bottom', 'fw' ),
							'left'   => __( 'Left', 'fw' ),
						),
						'desc' => __( 'Flips automatically if it would overflow the screen.', 'fw' ),
					),
					'event' => array(
						'type'    => 'select',
						'label'   => __( 'Open On', 'fw' ),
						'value'   => 'hover',
						'choices' => array(
							'hover' => __( 'Hover / focus', 'fw' ),
							'click' => __( 'Click / tap', 'fw' ),
						),
					),
					'arrow' => array(
						'type'  => 'switch',
						'label' => __( 'Show Arrow', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'yes',
					),
					'max_width' => array(
						'type'  => 'text',
						'label' => __( 'Max Width', 'fw' ),
						'value' => '240px',
						'desc'  => __( 'e.g. 240px or 60vw.', 'fw' ),
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
					'tip_bg'      => sc_color_field_compact( array( 'label' => __( 'Tooltip Background', 'fw' ), 'kind' => 'bg' ) ),
					'tip_color'   => sc_color_field_compact( array( 'label' => __( 'Tooltip Text', 'fw' ) ) ),
					'accent_color'=> sc_color_field_compact( array( 'label' => __( 'Accent (button / icon trigger)', 'fw' ), 'kind' => 'bg' ) ),
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
