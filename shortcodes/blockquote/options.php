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
					'quote' => array(
						'type'  => 'textarea',
						'label' => __( 'Quote', 'fw' ),
						'value' => __( 'Design is not just what it looks like and feels like. Design is how it works.', 'fw' ),
						'help'  => __( 'Basic inline HTML (bold, italic, links) is allowed.', 'fw' ),
					),
					'author' => array(
						'type'  => 'text',
						'label' => __( 'Author', 'fw' ),
						'desc'  => __( 'Who said it (optional).', 'fw' ),
					),
					'role' => array(
						'type'  => 'text',
						'label' => __( 'Author Role / Source', 'fw' ),
						'desc'  => __( 'e.g. "CEO, Acme" or a book title (optional).', 'fw' ),
					),
					'source_url' => array(
						'type'  => 'text',
						'label' => __( 'Source Link', 'fw' ),
						'desc'  => __( 'Makes the author a link (optional).', 'fw' ),
					),
					'show_mark' => array(
						'type'  => 'switch',
						'label' => __( 'Show Quote Mark', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'yes',
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
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/blockquote/static/img/design' );
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
							'label'   => __( 'Design', 'fw' ),
							'value'   => 'classic',
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
					'max_width' => array(
						'type'  => 'text',
						'label' => __( 'Max Width', 'fw' ),
						'value' => '',
						'desc'  => __( 'Optional, e.g. 700px. Blank = full width.', 'fw' ),
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
					'quote_color'  => sc_color_field_compact( array( 'label' => __( 'Quote Color', 'fw' ) ) ),
					'accent_color' => sc_color_field_compact( array( 'label' => __( 'Accent (border / mark)', 'fw' ), 'kind' => 'bg' ) ),
					'author_color' => sc_color_field_compact( array( 'label' => __( 'Author Color', 'fw' ) ) ),
					'bg_color'     => sc_color_field_compact( array( 'label' => __( 'Background', 'fw' ), 'kind' => 'bg' ) ),
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
