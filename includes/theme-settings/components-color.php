<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Components → Color Presets.
 *
 * @var array $options Filled with the option schema (loaded via upw_ts_get_options()).
 */

$options = array(
	'theme_colors' => array(
		'label'           => __( 'Color Presets', 'fw' ),
		'type'            => 'addable-box',
		'inline'          => true,
		'value'           => function_exists( 'unysonplus_default_color_presets' ) ? unysonplus_default_color_presets() : array(),
		'desc'            => __( 'Swatches used by the Text Color / Background Color dropdowns in every shortcode\'s Styling tab, and by the Button / Border / Table preset color fields. Each becomes <code>.text-{slug}</code> / <code>.bg-{slug}</code> and a <code>--color-{slug}</code> CSS variable.', 'fw' ),
		'sortable'        => true,
		'box-duplicate'   => true,
		'attr'            => array( 'class' => 'fw-preset-2col' ),
		'width'           => 'full',
		'add-button-text' => __( 'Add another colour', 'fw' ),
		'box-options'     => array(
			// Wrapped in a group ( show_borders => false ) so the two fields read as one
			// panel with no divider between them. The group flattens on save, so the
			// stored value keys ( name, color ) stay top-level and the template's
			// {{- name }} / {{- color }} still resolve.
			'grp_color' => array(
				'type'         => 'group',
				'show_borders' => false,
				'options'      => array(
					'name'  => array( 'label' => __( 'Color', 'fw' ), 'type' => 'text', 'value' => '', 'dynamic_content' => false ),
					'color' => array( 'label' => '', 'type' => 'color-picker', 'value' => '' ),
					// Wide-gamut colour (roadmap 7d). The colour above stays the colour on every screen that cannot show more.
					'wide_gamut' => array(
						'label'   => __( 'Vivid on wide-gamut screens', 'fw' ),
						'type'    => 'short-select',
						'value'   => '',
						'choices' => array(
							''       => __( 'Off (the same colour everywhere)', 'fw' ),
							'subtle' => __( 'Subtle boost', 'fw' ),
							'vivid'  => __( 'Most vivid', 'fw' ),
							'custom' => __( 'Custom P3 value', 'fw' ),
						),
						'help'    => __( 'Phones, tablets and laptops with a Display P3 screen can show brighter colours than ordinary screens. Subtle boost and Most vivid keep this colour\'s hue and lightness and make it more saturated on those screens only; every other screen keeps the colour above. Greys are left as they are.', 'fw' ),
					),
					'p3' => array(
						'label'           => __( 'P3 value', 'fw' ),
						'type'            => 'text',
						'value'           => '',
						'dynamic_content' => false,
						'help'            => __( 'For Custom: a colour such as <code>color(display-p3 0 0.85 0.55)</code> or <code>oklch(0.75 0.2 165)</code>.', 'fw' ),
					),
				),
			),
		),
		'template'        => '<span style="background-color:{{- color}}; width:50px; height:10px; display:inline-block"></span> {{- name }}',
	),
);
