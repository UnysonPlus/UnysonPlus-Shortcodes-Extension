<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$col_choices = array(
	'1' => __( '1 Column', 'fw' ),
	'2' => __( '2 Columns', 'fw' ),
	'3' => __( '3 Columns', 'fw' ),
	'4' => __( '4 Columns', 'fw' ),
	'5' => __( '5 Columns', 'fw' ),
	'6' => __( '6 Columns', 'fw' ),
);

$options = array(
	'tab_layout' => array(
		'title'   => __( 'Layout', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'masonry_columns' => array(
				'type'    => 'group',
				'options' => array(
					'cols_lg' => array(
						'label'   => __( 'Columns (Desktop)', 'fw' ),
						'help'    => __( 'Number of masonry columns on large screens (≥ 992px).', 'fw' ),
						'type'    => 'select',
						'choices' => $col_choices,
						'value'   => '3',
					),
					'cols_md' => array(
						'label'   => __( 'Columns (Tablet)', 'fw' ),
						'help'    => __( 'Columns between 576px and 991px.', 'fw' ),
						'type'    => 'select',
						'choices' => $col_choices,
						'value'   => '2',
					),
					'cols_sm' => array(
						'label'   => __( 'Columns (Phone)', 'fw' ),
						'help'    => __( 'Columns below 576px. Usually 1.', 'fw' ),
						'type'    => 'select',
						'choices' => $col_choices,
						'value'   => '1',
					),
				),
			),
			'gap' => array(
				'label'   => __( 'Gap', 'fw' ),
				'desc'    => __( 'Space between items (horizontal and vertical)', 'fw' ),
				'type'    => 'select',
				'choices' => array(
					'0.5rem' => __( 'Extra Small', 'fw' ),
					'1rem'   => __( 'Small', 'fw' ),
					'1.5rem' => __( 'Medium', 'fw' ),
					'2rem'   => __( 'Large', 'fw' ),
					'3rem'   => __( 'Extra Large', 'fw' ),
				),
				'value' => '1.5rem',
			),
			'is_fullwidth' => array(
				'label' => __( 'Full Width', 'fw' ),
				'help'  => __( 'On: the grid spans the full container-fluid width. Off: constrained to the site container width.', 'fw' ),
				'type'  => 'switch',
				'value' => 'no',
			),
		),
	),

	'tab_styling' => array(
		'title'   => __( 'Styling', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'background_color' => array(
				'label' => __( 'Background Color', 'fw' ),
				'desc'  => __( 'Section background color', 'fw' ),
				'type'  => 'color-picker',
				'value' => '',
			),
			'padding_top' => array(
				'label' => __( 'Padding Top', 'fw' ),
				'desc'  => __( 'e.g. 40px or 3rem', 'fw' ),
				'type'  => 'text',
				'value' => '',
			),
			'padding_bottom' => array(
				'label' => __( 'Padding Bottom', 'fw' ),
				'desc'  => __( 'e.g. 40px or 3rem', 'fw' ),
				'type'  => 'text',
				'value' => '',
			),
		),
	),
);

if ( function_exists( 'sc_get_animation_fields' ) ) {
	$options['tab_animation'] = array(
		'title'   => __( 'Animations', 'fw' ),
		'type'    => 'tab',
		'options' => sc_get_animation_fields(),
	);
}

if ( function_exists( 'sc_get_advanced_tab' ) ) {
	$options['tab_advanced'] = array(
		'title'   => __( 'Advanced', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'advanced_settings' => array(
				'type'    => 'group',
				'options' => sc_get_advanced_tab(),
			),
		),
	);
}
