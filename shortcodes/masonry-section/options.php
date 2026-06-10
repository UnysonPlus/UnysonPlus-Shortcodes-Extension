<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(
	'tab_layout' => array(
		'title'   => __( 'Layout', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'masonry_info' => array(
				'type'  => 'html-fixed',
				'label' => __( 'How it packs', 'fw' ),
				'html'  => __( 'Drop in columns at any width (1/2, 1/3, 2/3, 1/4 …) — each keeps its width and they stack vertically like tetris blocks to fill the gaps. No fixed column count.', 'fw' ),
			),
			'gap' => array(
				'label'   => __( 'Gap', 'fw' ),
				'desc'    => __( 'Space between items. "Use Default Gap" matches the site / standard-section gutter.', 'fw' ),
				'type'    => 'select',
				'choices' => array(
					'' => __( 'Use Default Gap', 'fw' ), '0.5rem' => __( 'Extra Small', 'fw' ),
					'1rem'   => __( 'Small', 'fw' ),
					'1.5rem' => __( 'Medium', 'fw' ),
					'2rem'   => __( 'Large', 'fw' ),
					'3rem'   => __( 'Extra Large', 'fw' ),
				),
				'value' => '',
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
			'background' => array(
				'type'  => 'background-pro',
				'label' => __( 'Background', 'fw' ),
				'desc'  => __( 'Color, gradient, image and video background layers (image over gradient over color). Replaces the old Background Color field — existing masonry sections keep their colour.', 'fw' ),
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
