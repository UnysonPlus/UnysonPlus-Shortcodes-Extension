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
					'title' => array(
						'type'  => 'text',
						'label' => __( 'Heading', 'fw' ),
						'desc'  => __( 'Optional heading above the timeline.', 'fw' ),
					),
					'items' => array(
						'type'          => 'addable-popup',
						'label'         => __( 'Milestones', 'fw' ),
						'popup-title'   => __( 'Add / Edit Milestone', 'fw' ),
						'template'      => '{{= (date ? date + " — " : "") + (title || "") }}',
						'popup-options' => array(
							'date' => array(
								'type'  => 'text',
								'label' => __( 'Date / Label', 'fw' ),
								'value' => __( '2024', 'fw' ),
							),
							'title' => array(
								'type'  => 'text',
								'label' => __( 'Title', 'fw' ),
								'value' => __( 'Milestone', 'fw' ),
							),
							'text' => array(
								'type'  => 'textarea',
								'label' => __( 'Text', 'fw' ),
							),
							'icon' => array(
								'type'         => 'icon-v2',
								'label'        => __( 'Marker Icon', 'fw' ),
								'preview_size' => 'small',
								'desc'         => __( 'Shown in the marker when Marker Style is "Icon".', 'fw' ),
							),
							'image' => array(
								'type'  => 'upload',
								'label' => __( 'Image', 'fw' ),
								'desc'  => __( 'Optional image at the top of the milestone card.', 'fw' ),
							),
							'link_label' => array(
								'type'  => 'text',
								'label' => __( 'Link Label', 'fw' ),
								'desc'  => __( 'Leave blank to hide the link.', 'fw' ),
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
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/timeline/static/img/design' );
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
							'label'   => __( 'Layout', 'fw' ),
							'value'   => 'alternating',
							'choices' => $choices,
						);
					} ),
				),
			),
			'group_style' => array(
				'type'    => 'group',
				'options' => array(
					'marker' => array(
						'type'    => 'select',
						'label'   => __( 'Marker Style', 'fw' ),
						'value'   => 'dot',
						'choices' => array(
							'dot'    => __( 'Dot', 'fw' ),
							'icon'   => __( 'Icon (per milestone)', 'fw' ),
							'number' => __( 'Number', 'fw' ),
						),
					),
					'card_style' => array(
						'type'    => 'select',
						'label'   => __( 'Card Style', 'fw' ),
						'value'   => 'card',
						'choices' => array(
							'card'    => __( 'Card (shadow)', 'fw' ),
							'outline' => __( 'Outline', 'fw' ),
							'plain'   => __( 'Plain (no box)', 'fw' ),
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
					'accent_color' => sc_color_field_compact( array( 'label' => __( 'Accent (line + markers)', 'fw' ), 'kind' => 'bg' ) ),
					'line_color'   => sc_color_field_compact( array( 'label' => __( 'Line Color', 'fw' ) ) ),
					'card_bg'      => sc_color_field_compact( array( 'label' => __( 'Card Background', 'fw' ), 'kind' => 'bg' ) ),
					'date_color'   => sc_color_field_compact( array( 'label' => __( 'Date Color', 'fw' ) ) ),
					'title_color'  => sc_color_field_compact( array( 'label' => __( 'Title Color', 'fw' ) ) ),
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
