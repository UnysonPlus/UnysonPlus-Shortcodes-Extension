<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Vector Shape (roadmap 7g): a drawn shape kept as clean inline SVG.
 */
$options = array(
	'tab_content'  => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'svg'   => array(
				'type'  => 'vector-svg',
				'label' => __( 'Shape', 'fw' ),
				'desc'  => __( 'Draw it with Draw / edit shape: pen, rectangle, ellipse, polygon and star, then Union, Subtract, Intersect or Exclude to combine shapes. Or paste SVG code.', 'fw' ),
			),
			'label' => array(
				'type'  => 'text',
				'label' => __( 'Description for screen readers', 'fw' ),
				'desc'  => __( 'What the shape shows, e.g. "Company logo". Leave empty for a decorative shape (hidden from screen readers).', 'fw' ),
				'value' => '',
			),
			'link'  => array(
				'type'  => 'text',
				'label' => __( 'Link', 'fw' ),
				'desc'  => __( 'Optional: make the shape a link.', 'fw' ),
				'value' => '',
			),
		),
	),
	'tab_styling'  => array(
		'title'   => __( 'Styling', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'width'       => array(
				'type'  => 'unit-input',
				'label' => __( 'Width', 'fw' ),
				'units' => array( 'px', '%', 'em', 'rem', 'vw' ),
				'value' => array( 'value' => '160', 'unit' => 'px' ),
				'desc'  => __( 'The height follows the shape proportions.', 'fw' ),
			),
			'align'       => array(
				'type'    => 'select',
				'label'   => __( 'Alignment', 'fw' ),
				'value'   => 'left',
				'choices' => array( 'left' => __( 'Left', 'fw' ), 'center' => __( 'Centre', 'fw' ), 'right' => __( 'Right', 'fw' ) ),
			),
			'color_mode'  => array(
				'type'    => 'select',
				'label'   => __( 'Colours', 'fw' ),
				'value'   => 'own',
				'choices' => array(
					'own'  => __( 'As drawn', 'fw' ),
					'mono' => __( 'One colour (recolour the whole shape)', 'fw' ),
				),
				'desc'    => __( 'One colour paints every filled and outlined part in the colour below, like an icon.', 'fw' ),
			),
			'shape_color' => function_exists( 'sc_color_field_compact' )
				? sc_color_field_compact( array( 'label' => __( 'Shape colour', 'fw' ), 'desc' => __( 'Used by One colour. Empty follows the text colour.', 'fw' ) ) )
				: array( 'type' => 'color-picker', 'label' => __( 'Shape colour', 'fw' ), 'value' => '' ),
		),
	),
	'tab_advanced' => array(
		'title'   => __( 'Advanced', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'advanced_settings' => array(
				'type'    => 'group',
				'options' => function_exists( 'sc_get_advanced_tab' ) ? sc_get_advanced_tab() : array(),
			),
		),
	),
);
