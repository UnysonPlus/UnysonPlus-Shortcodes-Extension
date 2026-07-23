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
					'rating' => array(
						'type'  => 'slider',
						'label' => __( 'Rating', 'fw' ),
						'value' => 4.5,
						'properties' => array( 'min' => 0, 'max' => 10, 'step' => 0.5 ),
						'desc'  => __( 'Supports half steps (0–10). Set it on the same scale as the "Out Of" value below.', 'fw' ),
					),
					'max' => array(
						'type'    => 'select',
						'label'   => __( 'Out Of', 'fw' ),
						'value'   => '5',
						'choices' => array( '5' => '5', '10' => '10' ),
					),
					'label' => array(
						'type'  => 'text',
						'label' => __( 'Label', 'fw' ),
						'desc'  => __( 'Optional text before the symbols (e.g. "Excellent").', 'fw' ),
					),
					'show_value' => array(
						'type'  => 'switch',
						'label' => __( 'Show Value', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'yes',
						'desc'  => __( 'Show "4.5/5" after the symbols.', 'fw' ),
					),
					'count_text' => array(
						'type'  => 'text',
						'label' => __( 'Count Text', 'fw' ),
						'desc'  => __( 'Optional, e.g. "based on 220 reviews".', 'fw' ),
					),
					'rating_schema' => array(
						'type'         => 'switch',
						'label'        => __( 'Rating Schema (JSON-LD)', 'fw' ),
						'desc'         => __( 'Output AggregateRating structured data so this rating is machine-readable to search engines and AI agents.', 'fw' ),
						'help'         => __( 'Only enable when this reflects a real aggregate rating. Any number found in Count Text is used as the rating count.', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value'        => 'no',
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
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/star-rating/static/img/design' );
						$choices  = array();
						foreach ( (array) $registry as $key => $meta ) {
							$choices[ $key ] = array( 'small' => array(
								'src'    => $base . '/' . ( isset( $meta['thumb'] ) ? $meta['thumb'] : $key . '.svg' ),
								'height' => 44,
								'title'  => isset( $meta['label'] ) ? $meta['label'] : $key,
							) );
						}
						return array(
							'type'    => 'image-picker',
							'label'   => __( 'Symbol', 'fw' ),
							'value'   => 'star',
							'choices' => $choices,
						);
					} ),
				),
			),
			'group_layout' => array(
				'type'    => 'group',
				'options' => array(
					'size' => array(
						'type'    => 'select',
						'label'   => __( 'Size', 'fw' ),
						'value'   => 'md',
						'choices' => array( 'xs' => __( 'Extra small', 'fw' ), 'sm' => __( 'Small', 'fw' ), 'md' => __( 'Medium', 'fw' ), 'lg' => __( 'Large', 'fw' ), 'xl' => __( 'Extra large', 'fw' ) ),
					),
					'align' => sc_alignment_field( array(
						'label' => __( 'Alignment', 'fw' ),
						'value' => 'left',
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
					'fill_color'  => sc_color_field_compact( array( 'label' => __( 'Filled Color', 'fw' ), 'kind' => 'bg' ) ),
					'empty_color' => sc_color_field_compact( array( 'label' => __( 'Empty Color', 'fw' ), 'kind' => 'bg' ) ),
					'text_color'  => sc_color_field_compact( array( 'label' => __( 'Label / Value Color', 'fw' ) ) ),
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
