<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Components → Spacing + Gap.
 *
 * @var array    $options     Filled with the option schema (loaded via upw_ts_get_options()).
 * @var callable $gap_choices function( $empty_label ) => choices array for the Default Gap selects.
 */

$gap = isset( $gap_choices ) && is_callable( $gap_choices )
	? $gap_choices
	: function ( $empty_label ) {
		return function_exists( 'sc_get_gap_select_choices' )
			? sc_get_gap_select_choices( $empty_label )
			: array( '' => $empty_label );
	};

$options = array(
	'spacing_scale' => array(
		'label'           => __( 'Spacing Scale', 'fw' ),
		'type'            => 'addable-box',
		'value'           => function_exists( 'unysonplus_default_spacing_scale' ) ? unysonplus_default_spacing_scale() : array(),
		'desc'            => __( 'Values behind Bootstrap-style margin/padding classes. Each entry produces a complete set of utilities (<code>.m-NAME</code>, <code>.p-NAME</code>, <code>.mt-NAME</code>, <code>.mx-NAME</code>, etc.).', 'fw' ),
		'sortable'        => true,
		'box-duplicate'   => true,
		'attr'            => array( 'class' => 'fw-preset-2col' ),
		'width'           => 'full',
		'add-button-text' => __( 'Add spacer', 'fw' ),
		'box-options'     => array(
			'name' => array( 'label' => __( 'Name', 'fw' ), 'type' => 'text', 'value' => '', 'dynamic_content' => false, 'desc' => __( 'Becomes the slot suffix (e.g. "3" → <code>.m-3</code> / <code>.p-3</code>). Avoid Bootstrap-reserved names: <code>sm md lg xl xxl n1–n5 auto</code>.', 'fw' ) ),
			'size' => array( 'label' => __( 'Value', 'fw' ), 'type' => 'text', 'value' => '', 'dynamic_content' => false, 'desc' => __( 'Any CSS length: <code>0.5rem</code>, <code>8px</code>, <code>calc(1rem + 2px)</code>…', 'fw' ) ),
		),
		'template'        => '<strong>{{- name }}</strong> ({{- size }})',
	),
	'group_gaps' => array(
		'title'   => __( 'Gaps', 'fw' ),
		'type'    => 'group',
		'options' => array(
			'gap_scale' => array(
				'label'           => __( 'Gap Scale', 'fw' ),
				'type'            => 'addable-box',
				'value'           => function_exists( 'unysonplus_default_gap_scale' ) ? unysonplus_default_gap_scale() : array(),
				'desc'            => __( 'Values available in every column-gap dropdown (Default Gap below and the per-section Gap field on the Section shortcode).', 'fw' ),
				'sortable'        => true,
				'box-duplicate'   => true,
				'attr'            => array( 'class' => 'fw-preset-2col' ),
				'width'           => 'full',
				'add-button-text' => __( 'Add gap', 'fw' ),
				'box-options'     => array(
					'name' => array( 'label' => __( 'Name', 'fw' ), 'type' => 'text', 'value' => '', 'dynamic_content' => false ),
					'size' => array( 'label' => __( 'Value', 'fw' ), 'type' => 'text', 'value' => '', 'dynamic_content' => false, 'desc' => __( 'Any CSS length: <code>0.5rem</code>, <code>8px</code>, <code>1.25rem</code>…', 'fw' ) ),
				),
				'template'        => '<strong>{{- name }}</strong> ({{- size }})',
			),
			'default_gap' => array(
				'label'   => __( 'Default Gap', 'fw' ),
				'type'    => 'short-select',
				'value'   => '',
				'choices' => $gap( __( 'None (use Bootstrap default — 1.5rem horizontal, 0 vertical)', 'fw' ) ),
				'desc'    => __( 'Sets both horizontal and vertical gap on every Bootstrap row site-wide.', 'fw' ),
			),
			'default_gap_x' => array(
				'label'   => __( 'Default Gap X', 'fw' ),
				'type'    => 'short-select',
				'value'   => '',
				'choices' => $gap( __( 'Use Default Gap', 'fw' ) ),
				'desc'    => __( 'Overrides Default Gap on the horizontal axis only.', 'fw' ),
			),
			'default_gap_y' => array(
				'label'   => __( 'Default Gap Y', 'fw' ),
				'type'    => 'short-select',
				'value'   => '',
				'choices' => $gap( __( 'Use Default Gap', 'fw' ) ),
				'desc'    => __( 'Overrides Default Gap on the vertical axis only.', 'fw' ),
			),
		),
	),
);
