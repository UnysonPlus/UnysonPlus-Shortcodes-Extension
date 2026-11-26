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
		'inline'          => true,
		'value'           => function_exists( 'unysonplus_default_font_size_presets' ) ? unysonplus_default_font_size_presets() : array(),
		'desc'            => __( 'Named text styles offered by the Text Style dropdown in shortcode Styling tabs. Each style is a size PLUS any of weight / line-height / letter-spacing / transform — every field is optional, and a blank field inherits from the element\'s own tag (so a blank weight keeps the heading\'s weight, it does not thin it). Each becomes a <code>.font-{slug}</code> (or your literal Class) utility.', 'fw' ),
		'sortable'        => true,
		'box-duplicate'   => true,
		'attr'            => array( 'class' => 'fw-preset-2col' ),
		'width'           => 'full',
		'size'            => 'medium',
		'add-button-text' => __( 'Add another text style', 'fw' ),
		'box-options'     => array(
			// Grouped ( show_borders => false ) so the fields read as one panel with no
			// divider between rows. The group flattens on save, so every leaf key stays
			// top-level (no migration) and the template still resolves.
			'grp_style' => array(
				'type'         => 'group',
				'show_borders' => false,
				'options'      => array(
					'name'           => array( 'label' => __( 'Name', 'fw' ), 'type' => 'text', 'value' => '', 'dynamic_content' => false, 'help' => __( 'The name shown in the Text Style dropdown.', 'fw' ) ),
					'size'           => array(
						'label' => __( 'Size', 'fw' ),
						'type'  => 'unit-input',
						'units' => array( 'px', 'rem', 'em' ),
						'value' => array( 'value' => '', 'unit' => 'px' ),
						'help'  => __( 'Optional. Blank keeps the element\'s own size (a style-only preset — e.g. an eyebrow that only sets weight + tracking). A bare legacy number (no unit) is still read as px.', 'fw' ),
					),
					'weight'         => array(
						'label'   => __( 'Weight', 'fw' ),
						'type'    => 'select',
						'value'   => '',
						'choices' => array(
							''    => __( 'Inherit (tag default)', 'fw' ),
							'100' => __( '100 · Thin', 'fw' ),
							'200' => __( '200 · Extralight', 'fw' ),
							'300' => __( '300 · Light', 'fw' ),
							'400' => __( '400 · Regular', 'fw' ),
							'500' => __( '500 · Medium', 'fw' ),
							'600' => __( '600 · Semibold', 'fw' ),
							'700' => __( '700 · Bold', 'fw' ),
							'800' => __( '800 · Extrabold', 'fw' ),
							'900' => __( '900 · Black', 'fw' ),
						),
						'help'    => __( 'Blank keeps the heading/tag weight — only override when the style should be heavier or lighter.', 'fw' ),
					),
					'line_height'    => array( 'label' => __( 'Line height', 'fw' ), 'type' => 'text', 'value' => '', 'dynamic_content' => false, 'help' => __( 'Optional. Unitless (e.g. 1.1) or a length. Blank inherits.', 'fw' ) ),
					'letter_spacing' => array(
						'label' => __( 'Letter spacing', 'fw' ),
						'type'  => 'unit-input',
						'units' => array( 'em', 'px', 'rem' ),
						'value' => array( 'value' => '', 'unit' => 'em' ),
						'help'  => __( 'Optional. Tracking — em is relative to the font size (e.g. 0.15em); px is absolute. Blank inherits. A bare legacy number (no unit) is still read as em.', 'fw' ),
					),
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
						'help'    => __( 'Text casing — UPPERCASE, lowercase or Capitalize. Inherit keeps the tag default.', 'fw' ),
					),
					'color'          => function_exists( 'sc_color_field_compact' )
						? sc_color_field_compact( array( 'label' => __( 'Color', 'fw' ) ) )
						: array( 'label' => __( 'Color', 'fw' ), 'type' => 'color-picker', 'value' => '', 'help' => __( 'Optional. Text colour for this style. Blank inherits.', 'fw' ) ),
					// Typography controls (roadmap 7b). Every field is optional: blank keeps the element's own value.
					'weight_exact'   => array( 'label' => __( 'Exact weight', 'fw' ), 'type' => 'short-text', 'value' => '', 'dynamic_content' => false, 'help' => __( 'Optional. Any weight from 1 to 1000 (e.g. 450) for a variable font. Overrides Weight above. A font that is not variable snaps to its nearest weight.', 'fw' ) ),
					'font_width'     => array( 'label' => __( 'Width (%)', 'fw' ), 'type' => 'short-text', 'value' => '', 'dynamic_content' => false, 'help' => __( 'Optional. 50 to 200: below 100 is condensed, above 100 expanded. Needs a font with a width axis (or condensed / expanded faces).', 'fw' ) ),
					'font_style'     => array(
						'label'   => __( 'Style', 'fw' ),
						'type'    => 'short-select',
						'value'   => '',
						'choices' => array(
							''       => __( 'Inherit', 'fw' ),
							'normal' => __( 'Normal', 'fw' ),
							'italic' => __( 'Italic', 'fw' ),
						),
						'help'    => __( 'Italic uses the font\'s italic face. For a variable lean instead, set Slant.', 'fw' ),
					),
					'slant'          => array( 'label' => __( 'Slant (degrees)', 'fw' ), 'type' => 'short-text', 'value' => '', 'dynamic_content' => false, 'help' => __( 'Optional. -90 to 90 degrees of lean (e.g. 8) for a font with a slant axis; other fonts lean the regular face. Overrides Style.', 'fw' ) ),
					'optical_size'   => array(
						'label'   => __( 'Optical size', 'fw' ),
						'type'    => 'short-select',
						'value'   => '',
						'choices' => array(
							''     => __( 'Inherit', 'fw' ),
							'auto' => __( 'Auto (match the size)', 'fw' ),
							'none' => __( 'Off', 'fw' ),
						),
						'help'    => __( 'Fonts with an optical-size axis draw small text sturdier and large text finer. Auto is the browser default.', 'fw' ),
					),
					'opentype'       => array(
						'label'   => __( 'OpenType features', 'fw' ),
						'type'    => 'checkboxes',
						'value'   => array(),
						'choices' => array(
							'tnum'   => __( 'Tabular numbers (equal widths, for tables and prices)', 'fw' ),
							'pnum'   => __( 'Proportional numbers', 'fw' ),
							'onum'   => __( 'Old-style numbers', 'fw' ),
							'lnum'   => __( 'Lining numbers', 'fw' ),
							'zero'   => __( 'Slashed zero', 'fw' ),
							'frac'   => __( 'Fractions (1/2 as ½)', 'fw' ),
							'ordn'   => __( 'Ordinals (1st)', 'fw' ),
							'smcp'   => __( 'Small caps', 'fw' ),
							'c2sc'   => __( 'All small caps', 'fw' ),
							'noliga' => __( 'No ligatures', 'fw' ),
							'dlig'   => __( 'Discretionary ligatures', 'fw' ),
							'ss01'   => __( 'Stylistic set 1', 'fw' ),
							'ss02'   => __( 'Stylistic set 2', 'fw' ),
							'ss03'   => __( 'Stylistic set 3', 'fw' ),
							'ss04'   => __( 'Stylistic set 4', 'fw' ),
							'ss05'   => __( 'Stylistic set 5', 'fw' ),
							'swsh'   => __( 'Swashes', 'fw' ),
							'salt'   => __( 'Stylistic alternates', 'fw' ),
						),
						'help'    => __( 'Features built into the font. A font that does not have a feature simply ignores it.', 'fw' ),
					),
					'decoration_line' => array(
						'label'   => __( 'Decoration', 'fw' ),
						'type'    => 'short-select',
						'value'   => '',
						'choices' => array(
							''             => __( 'Inherit', 'fw' ),
							'none'         => __( 'None', 'fw' ),
							'underline'    => __( 'Underline', 'fw' ),
							'overline'     => __( 'Overline', 'fw' ),
							'line-through' => __( 'Strike-through', 'fw' ),
						),
					),
					'decoration_style' => array(
						'label'   => __( 'Decoration style', 'fw' ),
						'type'    => 'short-select',
						'value'   => '',
						'choices' => array(
							''       => __( 'Inherit', 'fw' ),
							'solid'  => __( 'Solid', 'fw' ),
							'double' => __( 'Double', 'fw' ),
							'dotted' => __( 'Dotted', 'fw' ),
							'dashed' => __( 'Dashed', 'fw' ),
							'wavy'   => __( 'Wavy', 'fw' ),
						),
					),
					'decoration_color' => function_exists( 'sc_color_field_compact' )
						? sc_color_field_compact( array( 'label' => __( 'Decoration colour', 'fw' ) ) )
						: array( 'label' => __( 'Decoration colour', 'fw' ), 'type' => 'color-picker', 'value' => '' ),
					'decoration_thickness' => array(
						'label' => __( 'Decoration thickness', 'fw' ),
						'type'  => 'unit-input',
						'units' => array( 'px', 'em' ),
						'value' => array( 'value' => '', 'unit' => 'px' ),
						'help'  => __( 'Optional. The line\'s thickness.', 'fw' ),
					),
					'underline_offset' => array(
						'label' => __( 'Underline offset', 'fw' ),
						'type'  => 'unit-input',
						'units' => array( 'px', 'em' ),
						'value' => array( 'value' => '', 'unit' => 'px' ),
						'help'  => __( 'Optional. How far the underline sits below the text.', 'fw' ),
					),
					'truncate'       => array(
						'label'   => __( 'Truncate', 'fw' ),
						'type'    => 'short-select',
						'value'   => '',
						'choices' => array(
							''  => __( 'No (show all the text)', 'fw' ),
							'1' => __( 'One line, with …', 'fw' ),
							'2' => __( 'Clamp to 2 lines', 'fw' ),
							'3' => __( 'Clamp to 3 lines', 'fw' ),
							'4' => __( 'Clamp to 4 lines', 'fw' ),
							'5' => __( 'Clamp to 5 lines', 'fw' ),
							'6' => __( 'Clamp to 6 lines', 'fw' ),
						),
						'help'    => __( 'Cuts long text off with an ellipsis (…) after one line, or after 2 to 6 lines. The full text stays in the page for search engines and screen readers.', 'fw' ),
					),
					'class'          => array( 'label' => __( 'Class', 'fw' ), 'type' => 'text', 'value' => '', 'dynamic_content' => false, 'help' => __( 'Optional. If filled, becomes a literal CSS class (e.g. type "display-1" to override Bootstrap\'s .display-1). If blank, auto-derived as a safe .font-NAME class.', 'fw' ) ),
					'custom_css'     => array( 'label' => __( 'Custom CSS', 'fw' ), 'type' => 'textarea', 'value' => '', 'dynamic_content' => false, 'desc' => __( 'Optional. Extra CSS applied to this style for anything the fields above don\'t cover (text-shadow, gradient text, etc.). Use <code>selector</code> to target the style — e.g. <code>selector{ text-shadow:0 1px 2px rgba(0,0,0,.3) }</code>. A bare declaration block (no selector) is wrapped for you.', 'fw' ) ),
				),
			),
		),
		// Size is now a unit-input { value, unit } (tolerating a legacy bare number). Render the value + unit,
		// falling back to the legacy scalar so the box title shows e.g. "· 96px" either way.
		'template'        => '<strong>{{- name }}</strong>{{ var _s = (obj.size && typeof obj.size === "object") ? (obj.size.value ? (obj.size.value + (obj.size.unit || "px")) : "") : (obj.size || ""); if (_s) { }} · {{- _s }}{{ } }}{{ if (obj.weight) { }} · {{- obj.weight }}{{ } }}{{ if (obj["class"]) { }} <code>.{{- obj["class"] }}</code>{{ } }}',
	),
);
