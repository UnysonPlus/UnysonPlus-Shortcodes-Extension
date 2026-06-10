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

$section_valign_uri = function ( $mode ) use ( $section_rrect, $section_el ) {
	$w = 120; $icon_h = 50;
	$svg = $section_rrect( 1, 1, $w - 2, $icon_h - 2, 4, '#2271b1' ); // blue section

	$gx = 17; $gw = $w - 2 * $gx; $bt = 7; $bb = $icon_h - 7;        // column band
	$svg .= $section_rrect( $gx, $bt, $gw, $bb - $bt, 3, '#bdbdbd', '#dcdcde' ); // gray full-height column

	$ex = $gx + 5; $ew = $gw - 10; $eh = 9;   // white element
	$top = $bt + 4; $bottom = $bb - 4;
	$ey = $top;
	if ( $mode === 'center' )     { $ey = ( $top + $bottom ) / 2 - $eh / 2; }
	elseif ( $mode === 'bottom' ) { $ey = $bottom - $eh; }
	$svg .= $section_el( $ex, $ey, $ew, $eh );

	$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $w . ' ' . $icon_h . '" width="' . $w . '" height="' . $icon_h . '">' . $svg . '</svg>';
	return 'data:image/svg+xml,' . rawurlencode( $svg );
};

// image-picker choice entry (thumb + 3x hover preview), matching the column.
$section_valign_pick = function ( $uri, $label ) {
	return array(
		'small' => array( 'src' => $uri, 'height' => 64 ),
		'large' => array( 'src' => $uri, 'height' => 150 ),
		'label' => $label,
	);
};

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
						'desc'    => __( 'Named visual preset for this section. Pairs with the Background control below — pick a variant for the overall theme, override the colour in Background if you want a one-off.', 'fw' ),
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
								'help'    => __( 'Pairs with Content Vertical Align below — give the section a tall min-height, then centre the content for a classic hero. "Auto" lets the section shrink-wrap its content.', 'fw' ),
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
					'content_valign' => [
						'type'    => 'image-picker',
						'label'   => __( 'Content Vertical Align', 'fw' ),
						'desc'    => __( 'Where the content sits when the section is taller than its content (most visible with a Min Height set).', 'fw' ),
						'value'   => 'top',
						'choices' => [
							'top'    => $section_valign_pick( $section_valign_uri( 'top' ),    __( 'Top', 'fw' ) ),
							'center' => $section_valign_pick( $section_valign_uri( 'center' ), __( 'Center', 'fw' ) ),
							'bottom' => $section_valign_pick( $section_valign_uri( 'bottom' ), __( 'Bottom', 'fw' ) ),
						],
					],
					'background' => [
						'type'  => 'background-pro',
						'label' => __( 'Background', 'fw' ),
						'desc'  => __( 'Color, gradient, image and video background layers (they stack: image over gradient over color). Replaces the old separate Background Color / Image / Video fields — existing sections are migrated automatically.', 'fw' ),
						'help'  => __( 'Image attachment "Fixed" gives a parallax effect. Video renders a muted, looping background via the section\'s video player; set a poster/fallback image for while it loads or where autoplay is blocked.', 'fw' ),
					],
				],
			],
		],
	],

	'tab_styling' => [
		'title'   => __( 'Spacing', 'fw' ),
		'type'    => 'tab',
		'options' => [
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
						'type'    => 'short-select',
						'label'   => __( 'Gap', 'fw' ),
						'desc'    => __( 'Override the site-wide Default Gap for every Bootstrap row inside this section. Sets both horizontal and vertical column gap.', 'fw' ),
						'help'    => function_exists( 'sc_styling_help_text' ) ? sc_styling_help_text( 'spacing' ) : '',
						'value'   => '',
						'choices' => function_exists( 'sc_get_gap_select_choices' )
							? sc_get_gap_select_choices( __( 'Use Default Gap', 'fw' ) )
							: array( '' => __( 'Use Default Gap', 'fw' ) ),
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
