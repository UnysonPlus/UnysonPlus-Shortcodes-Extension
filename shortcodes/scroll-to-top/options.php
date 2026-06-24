<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(

	/* ========================== GENERAL ========================== */
	'tab_content' => array(
		'title'   => __( 'General', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_toggles' => array(
				'type'    => 'group',
				'options' => array(
					'show_button' => array(
						'type'  => 'switch',
						'label' => __( 'Back-to-Top Button', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'On', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'Off', 'fw' ) ),
						'value' => 'yes',
					),
					'show_progress' => array(
						'type'  => 'switch',
						'label' => __( 'Reading Progress Bar', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'On', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'Off', 'fw' ) ),
						'value' => 'no',
					),
				),
			),
			'group_button' => array(
				'type'    => 'group',
				'options' => array(
					'icon' => array(
						'type'         => 'icon-v2',
						'label'        => __( 'Button Icon', 'fw' ),
						'preview_size' => 'small',
						'desc'         => __( 'Defaults to an up-arrow when empty.', 'fw' ),
					),
					'position' => array(
						'type'    => 'select',
						'label'   => __( 'Button Position', 'fw' ),
						'value'   => 'bottom-right',
						'choices' => array(
							'bottom-right' => __( 'Bottom right', 'fw' ),
							'bottom-left'  => __( 'Bottom left', 'fw' ),
						),
					),
					'shape' => array(
						'type'    => 'select',
						'label'   => __( 'Button Shape', 'fw' ),
						'value'   => 'circle',
						'choices' => array(
							'circle'  => __( 'Circle', 'fw' ),
							'rounded' => __( 'Rounded', 'fw' ),
							'square'  => __( 'Square', 'fw' ),
						),
					),
					'show_after' => array(
						'type'  => 'text',
						'label' => __( 'Show After (px scrolled)', 'fw' ),
						'value' => '300',
						'desc'  => __( 'The button fades in once the page is scrolled this far.', 'fw' ),
					),
				),
			),
			'group_progress' => array(
				'type'    => 'group',
				'options' => array(
					'progress_position' => array(
						'type'    => 'select',
						'label'   => __( 'Progress Bar Position', 'fw' ),
						'value'   => 'top',
						'choices' => array(
							'top'    => __( 'Top of viewport', 'fw' ),
							'bottom' => __( 'Bottom of viewport', 'fw' ),
						),
					),
					'progress_height' => array(
						'type'  => 'text',
						'label' => __( 'Progress Bar Height (px)', 'fw' ),
						'value' => '4',
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
					'accent_color'    => sc_color_field_compact( array( 'label' => __( 'Accent (button / bar)', 'fw' ), 'kind' => 'bg' ) ),
					'icon_color'      => sc_color_field_compact( array( 'label' => __( 'Button Icon Color', 'fw' ) ) ),
					'button_size'     => array(
						'type'    => 'select',
						'label'   => __( 'Button Size', 'fw' ),
						'value'   => 'md',
						'choices' => array( 'sm' => __( 'Small', 'fw' ), 'md' => __( 'Medium', 'fw' ), 'lg' => __( 'Large', 'fw' ) ),
					),
				),
			),
		),
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
