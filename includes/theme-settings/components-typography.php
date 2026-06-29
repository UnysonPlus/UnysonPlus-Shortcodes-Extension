<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Components → Typography (font-size presets).
 *
 * @var array $options Filled with the option schema (loaded via upw_ts_get_options()).
 */

$options = array(
	'font_sizes' => array(
		'label'           => __( 'Font Size Presets', 'fw' ),
		'type'            => 'addable-box',
		'value'           => function_exists( 'unysonplus_default_font_size_presets' ) ? unysonplus_default_font_size_presets() : array(),
		'desc'            => __( 'Font sizes offered by the Font Size Preset dropdown in shortcode Styling tabs. Each becomes a <code>.font-{slug}</code> (or your literal Class) utility.', 'fw' ),
		'sortable'        => true,
		'box-duplicate'   => true,
		'attr'            => array( 'class' => 'fw-preset-2col' ),
		'width'           => 'full',
		'size'            => 'medium',
		'add-button-text' => __( 'Add another preset', 'fw' ),
		'box-options'     => array(
			'name'  => array( 'label' => __( 'Name', 'fw' ), 'type' => 'text', 'value' => '' ),
			'size'  => array( 'label' => __( 'Size', 'fw' ), 'type' => 'text', 'value' => '', 'desc' => __( 'Enter value in pixels. Don\'t include the \'px\' unit.', 'fw' ) ),
			'class' => array( 'label' => __( 'Class', 'fw' ), 'type' => 'text', 'value' => '', 'desc' => __( 'Optional. If filled, becomes a literal CSS class (e.g. type "display-1" to override Bootstrap\'s .display-1). If blank, auto-derived as a safe .font-NAME class.', 'fw' ) ),
		),
		'template'        => '<strong>{{- size }}px</strong> - {{- name }}{{ if (obj["class"]) { }} <code>.{{- obj["class"] }}</code>{{ } }}',
	),
);
