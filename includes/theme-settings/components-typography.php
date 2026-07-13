<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Components → Text Styles.
 *
 * A Text Style is a named, reusable typographic token: a size PLUS optional weight,
 * line-height, letter-spacing and text-transform. Every property is OPT-IN — a style
 * emits only the fields you fill in (scoped to its own class), so any blank property
 * INHERITS from the element's tag token (a blank weight is NOT thin). Stored under the
 * legacy `font_sizes` key (a size-only Text Style) for wiring compatibility.
 *
 * @var array $options Filled with the option schema (loaded via upw_ts_get_options()).
 */

$options = array(
	'font_sizes' => array(
		'label'           => __( 'Text Style Presets', 'fw' ),
		'type'            => 'addable-box',
		'value'           => function_exists( 'unysonplus_default_font_size_presets' ) ? unysonplus_default_font_size_presets() : array(),
		'desc'            => __( 'Named text styles offered by the Text Style dropdown in shortcode Styling tabs. Each style is a size PLUS any of weight / line-height / letter-spacing / transform — every field is optional, and a blank field inherits from the element\'s own tag (so a blank weight keeps the heading\'s weight, it does not thin it). Each becomes a <code>.font-{slug}</code> (or your literal Class) utility.', 'fw' ),
		'sortable'        => true,
		'box-duplicate'   => true,
		'attr'            => array( 'class' => 'fw-preset-2col' ),
		'width'           => 'full',
		'size'            => 'medium',
		'add-button-text' => __( 'Add another text style', 'fw' ),
		'box-options'     => array(
			'name'           => array( 'label' => __( 'Name', 'fw' ), 'type' => 'text', 'value' => '' ),
			'size'           => array( 'label' => __( 'Size', 'fw' ), 'type' => 'text', 'value' => '', 'desc' => __( 'Optional. Pixels, without the "px" unit. Blank keeps the element\'s own size (a style-only preset — e.g. an eyebrow that only sets weight + tracking).', 'fw' ) ),
			'weight'         => array(
				'label'   => __( 'Weight', 'fw' ),
				'type'    => 'select',
				'value'   => '',
				'choices' => array(
					''    => __( 'Inherit (tag default)', 'fw' ),
					'300' => __( '300 · Light', 'fw' ),
					'400' => __( '400 · Regular', 'fw' ),
					'500' => __( '500 · Medium', 'fw' ),
					'600' => __( '600 · Semibold', 'fw' ),
					'700' => __( '700 · Bold', 'fw' ),
					'800' => __( '800 · Extrabold', 'fw' ),
					'900' => __( '900 · Black', 'fw' ),
				),
				'desc'    => __( 'Blank keeps the heading/tag weight — only override when the style should be heavier or lighter.', 'fw' ),
			),
			'line_height'    => array( 'label' => __( 'Line height', 'fw' ), 'type' => 'text', 'value' => '', 'desc' => __( 'Optional. Unitless (e.g. 1.1) or a length. Blank inherits.', 'fw' ) ),
			'letter_spacing' => array( 'label' => __( 'Letter spacing', 'fw' ), 'type' => 'text', 'value' => '', 'desc' => __( 'Optional. A bare number is read as em (e.g. -0.02 = tracking-tight); or include a unit (0.15em, 1px). Blank inherits.', 'fw' ) ),
			'transform'      => array(
				'label'   => __( 'Transform', 'fw' ),
				'type'    => 'select',
				'value'   => '',
				'choices' => array(
					''           => __( 'Inherit', 'fw' ),
					'none'       => __( 'None', 'fw' ),
					'uppercase'  => __( 'UPPERCASE', 'fw' ),
					'lowercase'  => __( 'lowercase', 'fw' ),
					'capitalize' => __( 'Capitalize', 'fw' ),
				),
			),
			'class'          => array( 'label' => __( 'Class', 'fw' ), 'type' => 'text', 'value' => '', 'desc' => __( 'Optional. If filled, becomes a literal CSS class (e.g. type "display-1" to override Bootstrap\'s .display-1). If blank, auto-derived as a safe .font-NAME class.', 'fw' ) ),
		),
		'template'        => '<strong>{{- name }}</strong>{{ if (obj.size) { }} · {{- obj.size }}px{{ } }}{{ if (obj.weight) { }} · {{- obj.weight }}{{ } }}{{ if (obj["class"]) { }} <code>.{{- obj["class"] }}</code>{{ } }}',
	),
);
