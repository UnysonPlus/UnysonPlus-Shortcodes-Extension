<?php if (!defined('FW')) {
	die('Forbidden');
}

/**
 * Inline SVG glyphs for the Content Vertical Align image-picker, in the same
 * visual language as the column alignment glyphs: blue (#2271b1) = the SECTION,
 * gray (#bdbdbd) = the COLUMN, white (#fff) = the CONTENT element with a #8c8c8c
 * line for its content; rounded corners + a #dcdcde hairline throughout. The
 * gray column is full-height; the white element rides at the top / centre /
 * bottom of it to show where the content lands. Returned as a data-URI.
 */
$section_rrect = function ( $x, $y, $w, $h, $rx, $fill, $stroke = '' ) {
	return '<rect x="' . round( $x, 1 ) . '" y="' . round( $y, 1 ) . '" width="' . round( $w, 1 )
		. '" height="' . round( $h, 1 ) . '" rx="' . $rx . '" fill="' . $fill . '"'
		. ( $stroke !== '' ? ' stroke="' . $stroke . '"' : '' ) . '/>';
};

// A white element box + its #8c8c8c content line (mirrors the column's glyph_el).
$section_el = function ( $x, $y, $w, $h ) use ( $section_rrect ) {
	$ly = $y + $h / 2;
	return $section_rrect( $x, $y, $w, $h, 2, '#ffffff', '#dcdcde' )
		. '<line x1="' . round( $x + 6, 1 ) . '" y1="' . round( $ly, 1 ) . '" x2="' . round( $x + $w - 6, 1 )
		. '" y2="' . round( $ly, 1 ) . '" stroke="#8c8c8c" stroke-width="1.5" stroke-linecap="round"/>';
};

// Caption + <svg> wrapper — mirrors the column's $glyph_svg so the section
// thumbnails carry the same baked-in text label below the icon (the image-picker
// itself doesn't render a visible caption). $icon_h = icon band height; the
// caption sits in the 16px below it.
$section_glyph_svg = function ( $inner, $label, $w = 120, $icon_h = 50 ) {
	$h = $icon_h + 16;
	$inner .= '<text x="' . ( $w / 2 ) . '" y="' . ( $icon_h + 11 ) . '" text-anchor="middle" '
		. 'font-family="-apple-system,Segoe UI,Roboto,sans-serif" font-size="11" fill="#50575e">' . $label . '</text>';
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $w . ' ' . $h . '" width="' . $w . '" height="' . $h . '">' . $inner . '</svg>';
	return 'data:image/svg+xml,' . rawurlencode( $svg );
};

$section_valign_uri = function ( $mode, $label ) use ( $section_rrect, $section_el, $section_glyph_svg ) {
	$w = 120; $icon_h = 50;
	$svg = $section_rrect( 1, 1, $w - 2, $icon_h - 2, 4, '#2271b1' ); // blue section

	$gx = 7; $gw = $w - 2 * $gx; $bt = 7; $bb = $icon_h - 7;         // thin, even side padding
	$ex = $gx + 5; $ew = $gw - 10; $eh = 9;                          // white content bar

	if ( $mode === 'stretch' ) {
		// Default / Stretched — the columns fill the section height; content rides at the top.
		$svg .= $section_rrect( $gx, $bt, $gw, $bb - $bt, 3, '#bdbdbd', '#dcdcde' );
		$svg .= $section_el( $ex, $bt + 4, $ew, $eh );
	} else {
		// The whole columns block (gray) sits top / center / bottom within the taller section —
		// mirroring the frontend (section flex-column justify-content) and the Column's own
		// "Column Vertical Alignment" glyph: the block MOVES as a unit, it doesn't stretch.
		$gh = $eh + 8;                                     // content-height columns block
		if ( $mode === 'bottom' )     { $gy = $bb - $gh; }
		elseif ( $mode === 'center' ) { $gy = ( $bt + $bb ) / 2 - $gh / 2; }
		else                          { $gy = $bt; }       // top
		$svg .= $section_rrect( $gx, $gy, $gw, $gh, 3, '#bdbdbd', '#dcdcde' );
		$svg .= $section_el( $ex, $gy + ( $gh - $eh ) / 2, $ew, $eh );
	}

	return $section_glyph_svg( $svg, $label, $w, $icon_h );
};

// Horizontal counterpart: a HALF-width gray column riding at the left / centre /
// right of the blue section, to show where the columns sit across the row.
$section_halign_uri = function ( $mode, $label ) use ( $section_rrect, $section_el, $section_glyph_svg ) {
	$w = 120; $icon_h = 50;
	$svg = $section_rrect( 1, 1, $w - 2, $icon_h - 2, 4, '#2271b1' ); // blue section

	$bt = 7; $bb = $icon_h - 7;                       // full-height column band
	$inner_x = 7; $inner_w = $w - 2 * $inner_x;       // thin, even side padding (matches top/bottom)

	if ( in_array( $mode, array( 'between', 'around', 'evenly' ), true ) ) {
		// Distribute three narrow columns across the track (space-between/around/evenly).
		$n = 3; $cw = $inner_w * 0.18; $free = $inner_w - $n * $cw;
		if ( 'between' === $mode )     { $gap = $free / ( $n - 1 ); $x0 = $inner_x; }
		elseif ( 'around' === $mode )  { $gap = $free / $n;         $x0 = $inner_x + $gap / 2; }
		else /* evenly */              { $gap = $free / ( $n + 1 ); $x0 = $inner_x + $gap; }
		for ( $i = 0; $i < $n; $i++ ) {
			$cx = $x0 + $i * ( $cw + $gap );
			$svg .= $section_rrect( $cx, $bt, $cw, $bb - $bt, 3, '#bdbdbd', '#dcdcde' );
			$ew = $cw - 6; $eh = 9;
			if ( $ew > 3 ) { $svg .= $section_el( $cx + 3, $bt + 4, $ew, $eh ); } // content at top (stretched column, default flow)
		}
	} else {
		$cw = $inner_w * 0.5;                             // half-width column
		$cx = $inner_x;                                   // left / default
		if ( $mode === 'center' )     { $cx = $inner_x + ( $inner_w - $cw ) / 2; }
		elseif ( $mode === 'right' )  { $cx = $inner_x + $inner_w - $cw; }
		$svg .= $section_rrect( $cx, $bt, $cw, $bb - $bt, 3, '#bdbdbd', '#dcdcde' ); // gray column

		$ew = $cw - 10; $eh = 9;                          // white element at TOP of the column (stretched, default flow)
		$svg .= $section_el( $cx + 5, $bt + 4, $ew, $eh );
	}

	return $section_glyph_svg( $svg, $label, $w, $icon_h );
};

// image-picker choice entry (thumb + 3x hover preview), matching the column.
$section_valign_pick = function ( $uri, $label ) {
	return array(
		'small' => array( 'src' => $uri, 'height' => 64 ),
		'large' => array( 'src' => $uri, 'height' => 150 ),
		'label' => $label,
	);
};

// Shape-divider sub-fields, revealed by the Top/Bottom shape picker for any shape but "None".
$divider_fields = [
	'color'  => sc_color_field_compact( [ 'label' => __( 'Color', 'fw' ), 'kind' => 'bg' ] ),
	'height' => [
		'type'  => 'unit-input',
		'label' => __( 'Height', 'fw' ),
		'desc'  => __( 'Height of the divider band.', 'fw' ),
		'units' => [ 'px', 'vh', '%' ],
		'value' => [ 'value' => '100', 'unit' => 'px' ],
	],
	'flip'   => [
		'type'         => 'switch',
		'label'        => __( 'Flip Horizontally', 'fw' ),
		'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
		'left-choice'  => [ 'value' => 'no',  'label' => __( 'No', 'fw' ) ],
		'value'        => 'no',
	],
];
$divider_shapes = [
	'none'     => __( 'None', 'fw' ),
	'tilt'     => __( 'Tilt', 'fw' ),
	'curve'    => __( 'Curve', 'fw' ),
	'wave'     => __( 'Wave', 'fw' ),
	'triangle' => __( 'Triangle', 'fw' ),
];
$divider_reveal = [
	'tilt'     => $divider_fields,
	'curve'    => $divider_fields,
	'wave'     => $divider_fields,
	'triangle' => $divider_fields,
];

$options = [
	'tab_layout' => [
		'title'   => __( 'Layout', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_layout' => [
				'type'    => 'group',
				'options' => [
					'variant' => [
						'type'    => 'select',
						'label'   => __( 'Section Variant', 'fw' ),
						'desc'    => __( 'Named visual preset for this section. Pairs with the Background control on the Styling tab — pick a variant for the overall theme, override the colour in Background if you want a one-off.', 'fw' ),
						'help'    => __( 'Use "Alt" on every other section to create a subtle banded rhythm down the page. "Light"/"Dark" force a fixed scheme regardless of the theme\'s default.', 'fw' ),
						'value'   => '',
						'choices' => [
							''      => __( 'Default', 'fw' ),
							'alt'   => __( 'Alt (subtle off-white for alternating rhythm)', 'fw' ),
							'light' => __( 'Light (force light bg + dark text)', 'fw' ),
							'dark'  => __( 'Dark (force dark bg + light text)', 'fw' ),
						],
					],
					'is_fullwidth' => [
						'label' => __( 'Full Width', 'fw' ),
						'help'  => __( 'On: the section background spans edge-to-edge while content stays in the container. Off: the whole section is constrained to the container width.', 'fw' ),
						'type'  => 'switch',
					],
					// Multi-picker — canonical shape (see demo.php / CLAUDE.md):
					// label/desc/help live on the PICKER sub-option; the top-level
					// label/desc are false; the default is the top-level `value`
					// keyed by the picker id; choice keys are non-empty; `choices`
					// reveals the per-choice sub-options. Plus `show_borders`.
					'min_height' => [
						'type'         => 'multi-picker',
						'label'        => false,
						'desc'         => false,
						'value'        => [ 'preset' => 'auto' ],
						'picker'       => [
							'preset' => [
								'label'   => __( 'Min Height', 'fw' ),
								'desc'    => __( 'Minimum section height. Use a viewport preset (vh) for a hero-style, full-screen section, or pick Custom for an exact value.', 'fw' ),
								'help'    => __( 'Pairs with Columns Vertical Alignment below — give the section a tall min-height, then centre the columns for a classic hero. "Auto" lets the section shrink-wrap its content.', 'fw' ),
								'type'    => 'select',
								'choices' => [
									'auto'   => __( 'Auto (fit content)', 'fw' ),
									'40vh'   => __( '40% of viewport', 'fw' ),
									'60vh'   => __( '60% of viewport', 'fw' ),
									'80vh'   => __( '80% of viewport', 'fw' ),
									'100vh'  => __( 'Full viewport (100vh)', 'fw' ),
									'custom' => __( 'Custom…', 'fw' ),
								],
							],
						],
						'choices'      => [
							// Revealed only when "Custom…" is picked.
							'custom' => [
								'custom_height' => [
									'type'  => 'unit-input',
									'label' => __( 'Custom Height', 'fw' ),
									'desc'  => false,
									'units' => [ 'px', '%', 'vh', 'vw', 'rem', 'em' ],
									'value' => [ 'value' => '600', 'unit' => 'px' ],
								],
							],
						],
						'show_borders' => false,
					],
					'column_halign' => [
						'type'    => 'responsive',
						'label'   => __( 'Columns Horizontal Alignment', 'fw' ),
						'desc'    => __( 'Align this section\'s columns horizontally within the row. Use the Phone / Tablet / Desktop tabs to align differently per device (a blank device inherits the smaller one).', 'fw' ),
						'help'    => __( 'Only has a visible effect when the columns don\'t already fill the row width. "Center" / "Right" position them as a group; "Space Between / Around / Evenly" distribute the gaps between multiple columns.', 'fw' ),
						'value'   => [ 'base' => 'default', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'    => 'image-picker',
							'choices' => [
								'default' => $section_valign_pick( $section_halign_uri( 'default', __( 'Left / Default', 'fw' ) ), __( 'Left / Default', 'fw' ) ),
								'center'  => $section_valign_pick( $section_halign_uri( 'center',  __( 'Center', 'fw' ) ),       __( 'Center', 'fw' ) ),
								'right'   => $section_valign_pick( $section_halign_uri( 'right',   __( 'Right', 'fw' ) ),        __( 'Right', 'fw' ) ),
								'between' => $section_valign_pick( $section_halign_uri( 'between', __( 'Space Between', 'fw' ) ),  __( 'Space Between', 'fw' ) ),
								'around'  => $section_valign_pick( $section_halign_uri( 'around',  __( 'Space Around', 'fw' ) ),   __( 'Space Around', 'fw' ) ),
								'evenly'  => $section_valign_pick( $section_halign_uri( 'evenly',  __( 'Space Evenly', 'fw' ) ),   __( 'Space Evenly', 'fw' ) ),
							],
						],
					],
					'column_valign' => [
						'type'    => 'image-picker',
						'label'   => __( 'Columns Vertical Alignment', 'fw' ),
						'desc'    => __( 'Where the columns sit vertically when the section is taller than its content (most visible with a Min Height set). "Default / Stretched" makes the columns fill the section height; Top / Center / Bottom position the content-height columns as a block.', 'fw' ),
						'value'   => 'stretch',
						'choices' => [
							'stretch' => $section_valign_pick( $section_valign_uri( 'stretch', __( 'Default / Stretched', 'fw' ) ), __( 'Default / Stretched', 'fw' ) ),
							'top'     => $section_valign_pick( $section_valign_uri( 'top',    __( 'Top', 'fw' ) ),    __( 'Top', 'fw' ) ),
							'center'  => $section_valign_pick( $section_valign_uri( 'center', __( 'Center', 'fw' ) ), __( 'Center', 'fw' ) ),
							'bottom'  => $section_valign_pick( $section_valign_uri( 'bottom', __( 'Bottom', 'fw' ) ), __( 'Bottom', 'fw' ) ),
						],
					],
					// Per-device Reverse switch (was an all/tablet/mobile select). Legacy
					// scalar values migrate in the view. Key kept as `reverse_columns`.
					'reverse_columns' => [
						'type'    => 'responsive',
						'label'   => __( 'Column Order', 'fw' ),
						'desc'    => __( 'Show the columns in reverse order (last column first) without changing the markup. Use the Phone / Tablet / Desktop tabs to reverse only on some devices.', 'fw' ),
						'help'    => __( 'On phones — where the columns stack — it flips the stacked order (e.g. put an image above the text on mobile). On tablet/desktop it swaps the row. A blank device inherits the smaller one; it only reorders the visual output, not the markup.', 'fw' ),
						'value'   => [ 'base' => 'no', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'         => 'switch',
							'left-choice'  => [ 'value' => 'no',  'label' => __( 'Default', 'fw' ) ],
							'right-choice' => [ 'value' => 'yes', 'label' => __( 'Reverse', 'fw' ) ],
						],
					],
				],
			],
		],
	],

	'tab_styling' => [
		'title'   => __( 'Styling', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_background' => [
				'type'    => 'group',
				'options' => [
					'background' => [
						'type'  => 'background-pro',
						'label' => __( 'Background', 'fw' ),
						'desc'  => __( 'Color, gradient, image and video background layers (they stack: image over gradient over color). Replaces the old separate Background Color / Image / Video fields — existing sections are migrated automatically.', 'fw' ),
						'help'  => __( 'Image attachment "Fixed" gives a parallax effect. Video renders a muted, looping background via the section\'s video player; set a poster/fallback image for while it loads or where autoplay is blocked.', 'fw' ),
					],
				],
			],
			'group_dividers' => [
				'type'    => 'group',
				'options' => [
					'divider_top' => [
						'type'         => 'multi-picker',
						'label'        => false,
						'desc'         => false,
						'value'        => [ 'shape' => 'none' ],
						'picker'       => [
							'shape' => [
								'type'    => 'select',
								'label'   => __( 'Top Shape Divider', 'fw' ),
								'desc'    => __( 'A shaped SVG edge at the TOP of the section — pick a shape and its Color / Height / Flip appear.', 'fw' ),
								'choices' => $divider_shapes,
							],
						],
						'choices'      => $divider_reveal,
						'show_borders' => false,
					],
					'divider_bottom' => [
						'type'         => 'multi-picker',
						'label'        => false,
						'desc'         => false,
						'value'        => [ 'shape' => 'none' ],
						'picker'       => [
							'shape' => [
								'type'    => 'select',
								'label'   => __( 'Bottom Shape Divider', 'fw' ),
								'desc'    => __( 'A shaped SVG edge at the BOTTOM of the section.', 'fw' ),
								'choices' => $divider_shapes,
							],
						],
						'choices'      => $divider_reveal,
						'show_borders' => false,
					],
				],
			],
			'group_spacings' => [
				'type'    => 'group',
				'options' => [
					'padding_top' => sc_spacing_field( [
						'label'  => __( 'Top Spacing', 'fw' ),
						'prefix' => 'pt',
						'desc'   => __( 'Vertical breathing room above the section content.', 'fw' ),
					] ),
					'padding_bottom' => sc_spacing_field( [
						'label'  => __( 'Bottom Spacing', 'fw' ),
						'prefix' => 'pb',
						'desc'   => __( 'Vertical breathing room below the section content.', 'fw' ),
					] ),
					// Column-gap overrides for this section's rows. Stored
					// as scale slugs (e.g. "3"). The view emits matching
					// modifier classes (section--gap-{slug},
					// section--gap-x-{slug}, section--gap-y-{slug}) which
					// css-tokens.php turns into --bs-gutter-x / --bs-gutter-y
					// overrides on every .row inside this section.
					// Empty = inherit Theme Settings → Default Gap.
					'gap' => [
						'type'    => 'responsive',
						'label'   => __( 'Gap', 'fw' ),
						'desc'    => __( 'Override the site-wide Default Gap for every Bootstrap row inside this section (both horizontal and vertical column gap). Use the Phone / Tablet / Desktop tabs to set a different gap per device (a blank device inherits the smaller one).', 'fw' ),
						'help'    => function_exists( 'sc_styling_help_text' ) ? sc_styling_help_text( 'spacing' ) : '',
						'value'   => [ 'base' => '', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'    => 'short-select',
							'choices' => function_exists( 'sc_get_gap_select_choices' )
								? sc_get_gap_select_choices( __( 'Use Default Gap', 'fw' ) )
								: array( '' => __( 'Use Default Gap', 'fw' ) ),
						],
					],
					'gap_x' => [
						'type'    => 'short-select',
						'label'   => __( 'Gap X (override)', 'fw' ),
						'desc'    => __( 'Overrides Gap on the horizontal axis only. Leave at "Use Section Gap" to inherit.', 'fw' ),
						'help'    => __( 'Use this when you want wider/narrower space between columns side-to-side without changing the vertical gap. Only takes effect once Gap above is set.', 'fw' ),
						'value'   => '',
						'choices' => function_exists( 'sc_get_gap_select_choices' )
							? sc_get_gap_select_choices( __( 'Use Section Gap', 'fw' ) )
							: array( '' => __( 'Use Section Gap', 'fw' ) ),
					],
					'gap_y' => [
						'type'    => 'short-select',
						'label'   => __( 'Gap Y (override)', 'fw' ),
						'desc'    => __( 'Overrides Gap on the vertical axis only. Leave at "Use Section Gap" to inherit.', 'fw' ),
						'help'    => __( 'Controls the vertical space between columns once they wrap onto new lines (e.g. on tablet/mobile). Only takes effect once Gap above is set.', 'fw' ),
						'value'   => '',
						'choices' => function_exists( 'sc_get_gap_select_choices' )
							? sc_get_gap_select_choices( __( 'Use Section Gap', 'fw' ) )
							: array( '' => __( 'Use Section Gap', 'fw' ) ),
					],
				],
			],
		],
	],
	'tab_animation' => [
		'title'   => __( 'Animations', 'fw' ),
		'type'    => 'tab',
		'options' => sc_get_animation_fields(),
	],
	'tab_advanced' => [
		'title'   => __( 'Advanced', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'advanced_settings' => [
				'type'    => 'group',
				'options' => sc_get_advanced_tab(),
			],
		],
	],
];
