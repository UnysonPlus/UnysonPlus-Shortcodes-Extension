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
					'items' => array(
						'type'          => 'addable-popup',
						'label'         => __( 'Items', 'fw' ),
						'popup-title'   => __( 'Add / Edit Item', 'fw' ),
						'template'      => '{{= text }}',
						'popup-options' => array(
							'text' => array(
								'type'  => 'text',
								'label' => __( 'Text', 'fw' ),
								'value' => __( 'Feature item', 'fw' ),
							),
							'subtext' => array(
								'type'  => 'text',
								'label' => __( 'Sub-text', 'fw' ),
								'desc'  => __( 'Optional smaller line beneath the text.', 'fw' ),
							),
							'icon' => array(
								'type'         => 'icon-v2',
								'label'        => __( 'Icon', 'fw' ),
								'preview_size' => 'small',
								'desc'         => __( 'Used by the "Per-item icons" / "Badge" designs.', 'fw' ),
							),
							'state' => array(
								'type'    => 'select',
								'label'   => __( 'Check State', 'fw' ),
								'value'   => 'on',
								'choices' => array(
									'on'  => __( 'Available (check)', 'fw' ),
									'off' => __( 'Unavailable (cross)', 'fw' ),
								),
								'desc' => __( 'Used by the Checklist design.', 'fw' ),
							),
							'link_url' => array(
								'type'  => 'text',
								'label' => __( 'Link URL', 'fw' ),
								'desc'  => __( 'Optional — makes the item a link.', 'fw' ),
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
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/feature-list/static/img/design' );
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
							'label'   => __( 'Marker Style', 'fw' ),
							'value'   => 'check',
							'choices' => $choices,
						);
					} ),
				),
			),
			'group_layout' => array(
				'type'    => 'group',
				'options' => array(
					'columns' => array(
						'type'    => 'select',
						'label'   => __( 'Columns', 'fw' ),
						'value'   => '1',
						'choices' => array( '1' => '1', '2' => '2', '3' => '3' ),
					),
					'dividers' => array(
						'type'  => 'switch',
						'label' => __( 'Row Dividers', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'no',
					),
					'spacing_size' => array(
						'type'    => 'select',
						'label'   => __( 'Row Spacing', 'fw' ),
						'value'   => 'md',
						'choices' => array( 'sm' => __( 'Tight', 'fw' ), 'md' => __( 'Normal', 'fw' ), 'lg' => __( 'Roomy', 'fw' ) ),
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
					'box_style'    => sc_card_box_style_field( array( 'desc' => __( 'Apply a Box Preset to each feature item. Manage presets in Theme Settings → Components → Box Presets.', 'fw' ) ) ),
					'marker_color' => sc_color_field_compact( array( 'label' => __( 'Marker Color', 'fw' ), 'kind' => 'bg' ) ),
					'marker_size'  => array(
						'type'  => 'unit-input',
						'label' => __( 'Icon Size', 'fw' ),
						'desc'  => __( 'Marker / icon size (Ex: 20px, 1.5rem). Leave empty for the default.', 'fw' ),
						'value' => array( 'value' => '', 'unit' => 'px' ),
						'units' => array( 'px', 'rem', 'em' ),
					),
					'text_color'   => sc_color_field_compact( array( 'label' => __( 'Text Color', 'fw' ) ) ),
					'sub_color'    => sc_color_field_compact( array( 'label' => __( 'Sub-text Color', 'fw' ) ) ),
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
