<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

$options = array(

	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_content' => array(
				'type'    => 'group',
				'options' => array(
					'text' => array(
						'type'            => 'text',
						'label'           => __( 'Label', 'fw' ),
						'desc'            => __( 'The small cue text (e.g. "Scroll to descend"). Leave empty for an icon-only cue.', 'fw' ),
						'value'           => __( 'Scroll to descend', 'fw' ),
						'dynamic_content' => false,
					),
					'icon' => array(
						'type'         => 'icon-v2',
						'label'        => __( 'Icon', 'fw' ),
						'desc'         => __( 'The cue glyph. Defaults to a chevron-down when left as None.', 'fw' ),
						'preview_size' => 'small',
						'modal_size'   => 'medium',
					),
					'target' => array(
						'type'            => 'text',
						'label'           => __( 'Scroll Target', 'fw' ),
						'desc'            => __( 'An on-page anchor to smooth-scroll to (e.g. #mission). Leave empty to scroll down one screen.', 'fw' ),
						'help'            => __( 'Use the id of the section below the hero (Section → Advanced → CSS ID, then reference it here as #that-id). Empty = scroll the viewport down by ~90%.', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					),
				),
			),
		),
	),

	'tab_design' => array(
		'title'   => __( 'Design', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_design' => array(
				'type'    => 'group',
				'options' => array(
					'layout' => array(
						'type'    => 'select',
						'label'   => __( 'Layout', 'fw' ),
						'value'   => 'stacked',
						'choices' => array(
							'stacked'         => __( 'Label above icon', 'fw' ),
							'stacked-reverse' => __( 'Icon above label', 'fw' ),
							'inline'          => __( 'Label beside icon', 'fw' ),
							'icon-only'       => __( 'Icon only', 'fw' ),
						),
					),
					'animation' => array(
						'type'    => 'select',
						'label'   => __( 'Animation', 'fw' ),
						'value'   => 'bounce',
						'choices' => array(
							'bounce' => __( 'Bounce', 'fw' ),
							'pulse'  => __( 'Pulse (fade)', 'fw' ),
							'nudge'  => __( 'Nudge down', 'fw' ),
							'none'   => __( 'None', 'fw' ),
						),
					),
				),
			),
		),
	),

	'tab_styling' => array(
		'title'   => __( 'Styling', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_colors' => array(
				'type'    => 'group',
				'options' => array(
					'text_color'  => sc_color_field_compact( array( 'label' => __( 'Label Color', 'fw' ) ) ),
					'icon_color'  => sc_color_field_compact( array( 'label' => __( 'Icon Color', 'fw' ) ) ),
					'icon_size'   => array(
						'type'  => 'unit-input',
						'label' => __( 'Icon Size', 'fw' ),
						'desc'  => __( 'The chevron / icon size (Ex: 20px, 1.5rem). Leave empty for the default. Scales font icons and inline SVGs.', 'fw' ),
						'value' => array( 'value' => '', 'unit' => 'px' ),
						'units' => array( 'px', 'rem', 'em' ),
					),
				),
			),
			'group_spacings' => array(
				'type'    => 'group',
				'options' => array(
					'spacing' => array(
						'type'  => 'spacing',
						'label' => __( 'Margin & Padding', 'fw' ),
						'help'  => function_exists( 'sc_styling_help_text' ) ? sc_styling_help_text( 'spacing' ) : '',
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
