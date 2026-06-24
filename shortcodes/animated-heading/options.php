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
					'before_text' => array(
						'type'  => 'text',
						'label' => __( 'Before Text', 'fw' ),
						'value' => __( 'We build', 'fw' ),
						'desc'  => __( 'Static text before the rotating words.', 'fw' ),
					),
					'words' => array(
						'type'  => 'textarea',
						'label' => __( 'Rotating Words', 'fw' ),
						'value' => "websites\nbrands\nideas",
						'desc'  => __( 'One word / phrase per line.', 'fw' ),
					),
					'after_text' => array(
						'type'  => 'text',
						'label' => __( 'After Text', 'fw' ),
						'desc'  => __( 'Static text after the rotating words.', 'fw' ),
					),
					'tag' => array(
						'type'    => 'select',
						'label'   => __( 'HTML Tag', 'fw' ),
						'value'   => 'h2',
						'choices' => array(
							'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6', 'p' => __( 'Paragraph', 'fw' ), 'div' => 'Div',
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
					'anim' => call_user_func( function () {
						$registry = require dirname( __FILE__ ) . '/views/parts/registry.php';
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/animated-heading/static/img/design' );
						$choices  = array();
						foreach ( (array) $registry as $key => $meta ) {
							$choices[ $key ] = array( 'small' => array(
								'src'    => $base . '/' . ( isset( $meta['thumb'] ) ? $meta['thumb'] : $key . '.svg' ),
								'height' => 48,
								'title'  => isset( $meta['label'] ) ? $meta['label'] : $key,
							) );
						}
						return array(
							'type'    => 'image-picker',
							'label'   => __( 'Animation', 'fw' ),
							'value'   => 'typewriter',
							'choices' => $choices,
						);
					} ),
				),
			),
			'group_behavior' => array(
				'type'    => 'group',
				'options' => array(
					'speed' => array(
						'type'    => 'select',
						'label'   => __( 'Speed', 'fw' ),
						'value'   => 'normal',
						'choices' => array( 'slow' => __( 'Slow', 'fw' ), 'normal' => __( 'Normal', 'fw' ), 'fast' => __( 'Fast', 'fw' ) ),
					),
					'highlight' => array(
						'type'    => 'select',
						'label'   => __( 'Word Highlight', 'fw' ),
						'value'   => 'color',
						'choices' => array(
							'none'      => __( 'None', 'fw' ),
							'color'     => __( 'Accent color', 'fw' ),
							'underline' => __( 'Underline', 'fw' ),
							'marker'    => __( 'Marker background', 'fw' ),
						),
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
					'text_color'  => sc_color_field_compact( array( 'label' => __( 'Text Color', 'fw' ) ) ),
					'accent_color'=> sc_color_field_compact( array( 'label' => __( 'Highlight / Accent Color', 'fw' ), 'kind' => 'bg' ) ),
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
