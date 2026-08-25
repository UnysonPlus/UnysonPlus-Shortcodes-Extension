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
				),
			),
		),
		'template'        => '<span style="background-color:{{- color}}; width:50px; height:10px; display:inline-block"></span> {{- name }}',
	),
);
