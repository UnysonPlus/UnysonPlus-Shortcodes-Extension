<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Components → Section Styles.
 *
 * A library of reusable section "skins". Each produces a `.section--{name}` class the
 * user picks on a Section (Layout → Section Variant). The three defaults (Alt / Light /
 * Dark) reproduce the built-in variants exactly; users can retint them or add their own.
 *
 * @var array $options       Filled with the option schema (loaded via upw_ts_get_options()).
 * @var array $color_choices slug => array( label, color ) from the Color Presets.
 */

$cc = isset( $color_choices ) && is_array( $color_choices )
	? $color_choices
	: ( function_exists( 'unysonplus_components_color_choices' ) ? unysonplus_components_color_choices() : array() );

// Compact color-preset picker (palette + custom), falling back to a plain picker if the
// shortcodes helper is unavailable.
$compact = function ( $label, $desc = '' ) use ( $cc ) {
	if ( function_exists( 'sc_color_field_compact' ) ) {
		return sc_color_field_compact( array( 'label' => $label, 'desc' => $desc, 'kind' => 'text' ) );
	}
	return array( 'label' => $label, 'desc' => $desc, 'type' => 'color-picker', 'value' => '' );
};

// Palette choices for the inline border-row color child (same presets sc_color_field_compact uses).
$compact_choices = array();
$compact_picker  = 'color-picker';
if ( function_exists( 'sc_color_field_compact' ) ) {
	$cf0             = sc_color_field_compact( array( 'kind' => 'text' ) );
	$compact_choices = isset( $cf0['choices'] ) ? $cf0['choices'] : array();
	$compact_picker  = isset( $cf0['picker'] )  ? $cf0['picker']  : 'color-picker';
}

// Multi-select Border Sides tiles (top / right / bottom / left) — a mini box with an
// accent line on the relevant edge, caption baked in (inline data-URI SVG). Mirrors the
// theme's unysonplus_hf_border_sides_field() so the control matches the Footer / Custom
// Styling borders. Value is an ARRAY; default all four edges = the legacy all-around border.
$sides_svg = function ( $side, $lbl ) {
	$accent = '#2271b1'; $line = '#c3c4c7';
	$box    = '<rect x="30" y="8" width="44" height="26" rx="3" fill="none" stroke="' . $line . '" stroke-width="1.5"/>';
	$edges  = array(
		'top'    => '<rect x="30" y="7"  width="44" height="3" rx="1.5" fill="' . $accent . '"/>',
		'bottom' => '<rect x="30" y="32" width="44" height="3" rx="1.5" fill="' . $accent . '"/>',
		'left'   => '<rect x="29" y="8"  width="3" height="26" rx="1.5" fill="' . $accent . '"/>',
		'right'  => '<rect x="72" y="8"  width="3" height="26" rx="1.5" fill="' . $accent . '"/>',
	);
	$edge = isset( $edges[ $side ] ) ? $edges[ $side ] : '';
	$text = '<text x="52" y="47" text-anchor="middle" font-family="-apple-system,Segoe UI,Roboto,sans-serif" font-size="10" fill="#50575e">' . $lbl . '</text>';
	return 'data:image/svg+xml,' . rawurlencode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 104 52" width="104" height="52">' . $box . $edge . $text . '</svg>' );
};
$sides_tile = function ( $v, $l ) use ( $sides_svg ) {
	$u = $sides_svg( $v, $l );
	return array( 'small' => array( 'height' => 52, 'src' => $u ), 'large' => array( 'height' => 74, 'src' => $u ) );
};

$options = array(
	'section_style_presets' => array(
		'label'           => __( 'Section Styles', 'fw' ),
		'type'            => 'addable-box',
		'width'           => 'full',
		'value'           => function_exists( 'unysonplus_default_section_style_presets' ) ? unysonplus_default_section_style_presets() : array(),
		'desc'            => __( 'Reusable section skins. Each produces a <code>.section--{name}</code> class you pick on a Section (Layout → Section Variant). The three defaults (Alt / Light / Dark) match the built-in variants — retint them, or add your own branded bands.', 'fw' ),
		'sortable'        => true,
		'add-button-text' => __( 'Add Section Style', 'fw' ),
		'box-options'     => array(
			'id'            => array( 'type' => 'unique' ),
			'style_name'    => array(
				'label' => __( 'Name', 'fw' ),
				'type'  => 'text',
				'value' => '',
				'desc'  => __( 'Becomes the CSS class suffix (e.g. <code>Dark</code> → <code>.section--dark</code>) and the label shown in the Section Variant dropdown.', 'fw' ),
			),
			'background'    => array(
				'label' => __( 'Background', 'fw' ),
				'type'  => 'background-pro',
				'desc'  => __( 'Color / gradient / image layers for the section band. A section can still override this with a one-off Background on its Styling tab.', 'fw' ),
			),
			'text_color'    => $compact( __( 'Text color', 'fw' ), __( 'Body text color inside sections using this style.', 'fw' ) ),
			'heading_color' => $compact( __( 'Heading color', 'fw' ), __( 'Optional. Color for h1–h6 inside this style.', 'fw' ) ),
			'link_color'    => $compact( __( 'Link color', 'fw' ), __( 'Optional. Color for links inside this style.', 'fw' ) ),
			// Width · Style · Color on one inline row (the shared multi-inline border
			// control). Consumed by css-tokens.php, which also tolerates the legacy flat
			// border_style/border_width/border_color for pre-combine saved presets.
			'border'        => array(
				'type'  => 'multi-inline',
				'label' => __( 'Border', 'fw' ),
				'desc'  => __( 'Width · style · colour for the band edge (like the CSS shorthand 1px solid #000). Pick a style and set a width/colour to show it.', 'fw' ),
				'value' => array(
					'width' => array( 'value' => '', 'unit' => 'px' ),
					'style' => '',
					'color' => array( 'predefined' => '', 'custom' => '' ),
				),
				'fw_multi_options' => array(
					'width' => array( 'type' => 'unit-input', 'title' => __( 'Width', 'fw' ), 'units' => array( 'px', 'em', 'rem' ), 'min' => 0 ),
					'style' => array(
						'type'    => 'select',
						'title'   => __( 'Style', 'fw' ),
						'choices' => array(
							''       => __( 'None', 'fw' ),
							'solid'  => __( 'Solid', 'fw' ),
							'dashed' => __( 'Dashed', 'fw' ),
							'dotted' => __( 'Dotted', 'fw' ),
						),
					),
					'color' => array( 'type' => 'predefined-colors-color-picker-compact', 'title' => __( 'Color', 'fw' ), 'picker' => $compact_picker, 'choices' => $compact_choices ),
				),
			),
			// Which edge(s) the border applies to — any combination of top/right/bottom/left
			// (multi-select image-picker, array value). Default all four = the previous
			// all-around border. css-tokens.php maps this to per-edge borders.
			'border_sides'  => array(
				'type'     => 'image-picker',
				'multiple' => true,
				'label'    => __( 'Border Sides', 'fw' ),
				'desc'     => __( 'Check any combination of edges the border applies to. All four = a full box (the default).', 'fw' ),
				'value'    => array( 'top', 'right', 'bottom', 'left' ),
				'choices'  => array(
					'top'    => $sides_tile( 'top',    __( 'Top', 'fw' ) ),
					'right'  => $sides_tile( 'right',  __( 'Right', 'fw' ) ),
					'bottom' => $sides_tile( 'bottom', __( 'Bottom', 'fw' ) ),
					'left'   => $sides_tile( 'left',   __( 'Left', 'fw' ) ),
				),
			),
			// How far the horizontal (top/bottom) border runs — Full (edge to edge), Container
			// (aligned with site content), or Custom (an exact centered width). Left/right are
			// vertical and unaffected. INLINE multi-picker (label/desc on the picker).
			'border_extent' => array(
				'type'   => 'multi-picker',
				'label'  => false,
				'desc'   => false,
				'picker' => array(
					'mode' => array(
						'label'   => __( 'Border Extent', 'fw' ),
						'desc'    => __( 'How far the top/bottom border runs across the section. Full spans edge to edge; Container aligns it with the site content; Custom sets an exact centered width. Left/right borders are unaffected.', 'fw' ),
						'type'    => 'select',
						'choices' => array(
							'full'      => __( 'Full Width', 'fw' ),
							'container' => __( 'Container Width', 'fw' ),
							'custom'    => __( 'Custom Width', 'fw' ),
						),
					),
				),
				'value'   => array( 'mode' => 'full' ),
				'choices' => array(
					'custom' => array(
						'border_extent_width' => array(
							'label' => __( 'Custom Border Width', 'fw' ),
							'desc'  => __( 'Maximum width of the centered border line, e.g. 800px or 60%.', 'fw' ),
							'type'  => 'unit-input',
							'units' => array( 'px', 'rem', 'em', '%' ),
							'value' => array( 'value' => '', 'unit' => 'px' ),
							'min'   => 0,
						),
					),
				),
				'show_borders' => false,
			),
			'border_radius' => array( 'label' => __( 'Border radius', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', 'em', 'rem', '%' ), 'min' => 0 ),
			'padding'       => array(
				'type'  => 'spacing',
				'mode'  => 'padding',
				'label' => __( 'Padding', 'fw' ),
				'desc'  => __( 'Inner padding for the section band. Uses your Spacing scale. A section\'s own Top/Bottom Spacing overrides this whenever it\'s set.', 'fw' ),
			),
		),
		'template'        => '<span class="section-style-preview-{{- id }}">{{- style_name }}</span>',
	),
);
