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
					'image' => array(
						'type'  => 'upload',
						'label' => __( 'Image', 'fw' ),
						'desc'  => __( 'The base image the pins are placed on.', 'fw' ),
					),
					'hotspots' => array(
						'type'          => 'addable-popup',
						'label'         => __( 'Hotspots', 'fw' ),
						'popup-title'   => __( 'Add / Edit Hotspot', 'fw' ),
						'template'      => '{{= title || ("x" + (x||0) + " y" + (y||0)) }}',
						'popup-options' => array(
							'x' => array(
								'type'  => 'slider',
								'label' => __( 'Horizontal Position (%)', 'fw' ),
								'value' => 50,
								'properties' => array( 'min' => 0, 'max' => 100, 'step' => 1 ),
							),
							'y' => array(
								'type'  => 'slider',
								'label' => __( 'Vertical Position (%)', 'fw' ),
								'value' => 50,
								'properties' => array( 'min' => 0, 'max' => 100, 'step' => 1 ),
							),
							'icon' => array(
								'type'         => 'icon-v2',
								'label'        => __( 'Pin Icon', 'fw' ),
								'preview_size' => 'small',
								'desc'         => __( 'Used by the "Icon" pin design (defaults to +).', 'fw' ),
							),
							'title' => array(
								'type'  => 'text',
								'label' => __( 'Title', 'fw' ),
							),
							'text' => array(
								'type'  => 'textarea',
								'label' => __( 'Text', 'fw' ),
							),
							'link_label' => array(
								'type'  => 'text',
								'label' => __( 'Link Label', 'fw' ),
							),
							'link_url' => array(
								'type'  => 'text',
								'label' => __( 'Link URL', 'fw' ),
							),
							'link_target' => array(
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
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/image-hotspots/static/img/design' );
						$choices  = array();
						foreach ( (array) $registry as $key => $meta ) {
							$choices[ $key ] = array( 'small' => array(
								'src'    => $base . '/' . ( isset( $meta['thumb'] ) ? $meta['thumb'] : $key . '.svg' ),
								'height' => 60,
								'title'  => isset( $meta['label'] ) ? $meta['label'] : $key,
							) );
						}
						return array(
							'type'    => 'image-picker',
							'label'   => __( 'Pin Style', 'fw' ),
							'value'   => 'pulse',
							'choices' => $choices,
						);
					} ),
				),
			),
			'group_behavior' => array(
				'type'    => 'group',
				'options' => array(
					'trigger' => array(
						'type'    => 'select',
						'label'   => __( 'Open Tooltip On', 'fw' ),
						'value'   => 'hover',
						'choices' => array(
							'hover' => __( 'Hover', 'fw' ),
							'click' => __( 'Click / tap', 'fw' ),
						),
						'desc' => __( 'Click is best for touch devices.', 'fw' ),
					),
					'pin_size' => array(
						'type'    => 'select',
						'label'   => __( 'Pin Size', 'fw' ),
						'value'   => 'md',
						'choices' => array( 'sm' => __( 'Small', 'fw' ), 'md' => __( 'Medium', 'fw' ), 'lg' => __( 'Large', 'fw' ) ),
					),
					'rounded' => array(
						'type'    => 'select',
						'label'   => __( 'Image Corner Radius', 'fw' ),
						'value'   => 'rounded',
						'choices' => array( 'rounded-0' => __( 'Square', 'fw' ), 'rounded' => __( 'Rounded', 'fw' ), 'rounded-lg' => __( 'Large', 'fw' ) ),
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
					'pin_color' => sc_color_field_compact( array( 'label' => __( 'Pin Color', 'fw' ), 'kind' => 'bg' ) ),
					'pop_bg'    => sc_color_field_compact( array( 'label' => __( 'Tooltip Background', 'fw' ), 'kind' => 'bg' ) ),
					'pop_color' => sc_color_field_compact( array( 'label' => __( 'Tooltip Text', 'fw' ) ) ),
					'accent_color' => sc_color_field_compact( array( 'label' => __( 'Link / Accent Color', 'fw' ) ) ),
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
