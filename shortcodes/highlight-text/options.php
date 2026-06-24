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
					'prefix' => array(
						'type'  => 'text',
						'label' => __( 'Prefix (plain)', 'fw' ),
						'desc'  => __( 'Optional plain text BEFORE the highlighted text, on the same line.', 'fw' ),
						'help'  => __( 'Lets you emphasise just one phrase inside a heading, e.g. prefix "Build it" + highlighted "visually". A trailing space is added automatically.', 'fw' ),
					),
					'text' => array(
						'type'  => 'textarea',
						'label' => __( 'Highlighted Text', 'fw' ),
						'value' => __( 'Make it stand out.', 'fw' ),
						'help'  => __( 'The part that receives the effect. Basic inline HTML (bold, italic, links) is allowed. For Drop cap, enter a full paragraph.', 'fw' ),
					),
					'suffix' => array(
						'type'  => 'text',
						'label' => __( 'Suffix (plain)', 'fw' ),
						'desc'  => __( 'Optional plain text AFTER the highlighted text, on the same line.', 'fw' ),
						'help'  => __( 'A leading space is added automatically.', 'fw' ),
					),
					'tag' => array(
						'type'    => 'select',
						'label'   => __( 'HTML Tag', 'fw' ),
						'value'   => 'h2',
						'choices' => array(
							'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6', 'p' => __( 'Paragraph', 'fw' ), 'span' => __( 'Span (inline)', 'fw' ), 'div' => 'Div',
						),
						'help'    => __( 'Use Paragraph for the Drop cap effect.', 'fw' ),
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
					'fx' => call_user_func( function () {
						$registry = require dirname( __FILE__ ) . '/views/parts/registry.php';
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/highlight-text/static/img/design' );
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
							'label'   => __( 'Effect', 'fw' ),
							'value'   => 'marker',
							'choices' => $choices,
						);
					} ),
				),
			),
			'group_layout' => array(
				'type'    => 'group',
				'options' => array(
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
					'accent_color'=> sc_color_field_compact( array( 'label' => __( 'Accent Color', 'fw' ), 'kind' => 'bg', 'desc' => __( 'Marker / gradient start / underline / glow / drop-cap.', 'fw' ) ) ),
					'accent2_color'=> sc_color_field_compact( array( 'label' => __( 'Accent Color 2 (gradient end)', 'fw' ), 'kind' => 'bg' ) ),
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
