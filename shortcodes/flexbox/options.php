<?php if (!defined('FW')) die('Forbidden');

/**
 * Flexbox options.
 *
 * Direction / Justify / Align are image-pickers built from inline-SVG data-URIs,
 * in the same visual language as the Section / Column alignment glyphs: a blue
 * (#2271b1) container with white (#fff, #dcdcde hairline) item boxes positioned
 * to show the chosen flex behavior. Gap uses the site-wide spacing presets
 * (sc_get_gap_select_choices). Width sits directly under HTML Tag.
 */

$fx_rrect = function ( $x, $y, $w, $h, $rx, $fill, $stroke = '' ) {
	return '<rect x="' . round( $x, 1 ) . '" y="' . round( $y, 1 ) . '" width="' . round( $w, 1 )
		. '" height="' . round( $h, 1 ) . '" rx="' . $rx . '" fill="' . $fill . '"'
		. ( $stroke !== '' ? ' stroke="' . $stroke . '"' : '' ) . '/>';
};

// Caption + <svg> wrapper (mirrors the section's $section_glyph_svg).
$fx_glyph = function ( $inner, $label, $w = 120, $icon_h = 50 ) {
	$h = $icon_h + 16;
	$inner .= '<text x="' . ( $w / 2 ) . '" y="' . ( $icon_h + 11 ) . '" text-anchor="middle" '
		. 'font-family="-apple-system,Segoe UI,Roboto,sans-serif" font-size="11" fill="#50575e">' . $label . '</text>';
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $w . ' ' . $h . '" width="' . $w . '" height="' . $h . '">' . $inner . '</svg>';
	return 'data:image/svg+xml,' . rawurlencode( $svg );
};

$fx_pick = function ( $uri, $label ) {
	return array(
		'small' => array( 'src' => $uri, 'height' => 60 ),
		'large' => array( 'src' => $uri, 'height' => 140 ),
		'label' => $label,
	);
};

// Direction: three identical SQUARES arranged along the main axis, plus a faint
// flow arrow — Row = squares left→right with a → arrow; Column = squares top→bottom
// with a ↓ arrow. Neutral squares (not pillars/bars) keep the arrangement, not the
// box shape, as the only cue, so Row no longer reads like "columns" and vice-versa.
$fx_dir_uri = function ( $mode, $label ) use ( $fx_rrect, $fx_glyph ) {
	$w = 120; $icon_h = 50;
	$svg = $fx_rrect( 1, 1, $w - 2, $icon_h - 2, 4, '#2271b1' );
	if ( $mode === 'row' ) {
		$s = 17; $g = 7; $total = 3 * $s + 2 * $g; $x0 = ( $w - $total ) / 2; $y = 9;
		for ( $i = 0; $i < 3; $i++ ) { $svg .= $fx_rrect( $x0 + $i * ( $s + $g ), $y, $s, $s, 3, '#ffffff', '#dcdcde' ); }
		$ay = $y + $s + 7; $x2 = $x0 + $total;
		$svg .= '<path d="M' . $x0 . ' ' . $ay . 'H' . $x2 . 'M' . ( $x2 - 4 ) . ' ' . ( $ay - 3 ) . 'L' . $x2 . ' ' . $ay . 'L' . ( $x2 - 4 ) . ' ' . ( $ay + 3 ) . '" stroke="#bcd4ec" stroke-width="1.4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>';
	} else {
		$s = 10; $g = 4; $total = 3 * $s + 2 * $g; $y0 = ( $icon_h - $total ) / 2; $x = ( $w - $s ) / 2 - 7;
		for ( $i = 0; $i < 3; $i++ ) { $svg .= $fx_rrect( $x, $y0 + $i * ( $s + $g ), $s, $s, 3, '#ffffff', '#dcdcde' ); }
		$ax = $x + $s + 10; $y2 = $y0 + $total;
		$svg .= '<path d="M' . $ax . ' ' . $y0 . 'V' . $y2 . 'M' . ( $ax - 3 ) . ' ' . ( $y2 - 4 ) . 'L' . $ax . ' ' . $y2 . 'L' . ( $ax + 3 ) . ' ' . ( $y2 - 4 ) . '" stroke="#bcd4ec" stroke-width="1.4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>';
	}
	return $fx_glyph( $svg, $label, $w, $icon_h );
};

// Justify (main axis): 3 item boxes positioned along the horizontal track.
$fx_justify_uri = function ( $mode, $label ) use ( $fx_rrect, $fx_glyph ) {
	$w = 120; $icon_h = 50; $n = 3; $bw = 15; $bh = 28; $y = ( $icon_h - $bh ) / 2;
	$tx = 8; $tw = $w - 16; $g = 4; $xs = array();
	$tot = $n * $bw + ( $n - 1 ) * $g;
	if ( $mode === 'start' )        { for ( $i = 0; $i < $n; $i++ ) { $xs[] = $tx + $i * ( $bw + $g ); } }
	elseif ( $mode === 'center' )   { $s = $tx + ( $tw - $tot ) / 2; for ( $i = 0; $i < $n; $i++ ) { $xs[] = $s + $i * ( $bw + $g ); } }
	elseif ( $mode === 'end' )      { $s = $tx + $tw - $tot; for ( $i = 0; $i < $n; $i++ ) { $xs[] = $s + $i * ( $bw + $g ); } }
	elseif ( $mode === 'between' )  { for ( $i = 0; $i < $n; $i++ ) { $xs[] = $tx + $i * ( ( $tw - $bw ) / ( $n - 1 ) ); } }
	elseif ( $mode === 'around' )   { $sp = ( $tw - $n * $bw ) / $n; for ( $i = 0; $i < $n; $i++ ) { $xs[] = $tx + $sp / 2 + $i * ( $bw + $sp ); } }
	else /* evenly */               { $sp = ( $tw - $n * $bw ) / ( $n + 1 ); for ( $i = 0; $i < $n; $i++ ) { $xs[] = $tx + $sp * ( $i + 1 ) + $i * $bw; } }
	$svg = $fx_rrect( 1, 1, $w - 2, $icon_h - 2, 4, '#2271b1' );
	foreach ( $xs as $x ) { $svg .= $fx_rrect( $x, $y, $bw, $bh, 2, '#ffffff', '#dcdcde' ); }
	return $fx_glyph( $svg, $label, $w, $icon_h );
};

// Align (cross axis): 3 item boxes positioned along the vertical track.
$fx_align_uri = function ( $mode, $label ) use ( $fx_rrect, $fx_glyph ) {
	$w = 120; $icon_h = 50; $n = 3; $bw = 20; $g = 8; $total = $n * $bw + ( $n - 1 ) * $g; $x0 = ( $w - $total ) / 2;
	$top = 8; $bot = $icon_h - 8; $th = $bot - $top;
	$svg = $fx_rrect( 1, 1, $w - 2, $icon_h - 2, 4, '#2271b1' );
	for ( $i = 0; $i < $n; $i++ ) {
		$bh = ( $mode === 'stretch' ) ? $th : 16;
		if ( $mode === 'baseline' ) { $bh = 10 + $i * 5; }
		$y = $top;
		if ( $mode === 'center' )        { $y = $top + ( $th - $bh ) / 2; }
		elseif ( $mode === 'end' )       { $y = $bot - $bh; }
		elseif ( $mode === 'baseline' )  { $y = $bot - $bh; }
		$svg .= $fx_rrect( $x0 + $i * ( $bw + $g ), $y, $bw, $bh, 2, '#ffffff', '#dcdcde' );
	}
	return $fx_glyph( $svg, $label, $w, $icon_h );
};

// Align Content (cross axis, WRAPPED lines): two rows of item boxes positioned to
// show how multiple wrapped lines are packed on the cross axis.
$fx_aligncontent_uri = function ( $mode, $label ) use ( $fx_rrect, $fx_glyph ) {
	$w = 120; $icon_h = 50;
	$svg = $fx_rrect( 1, 1, $w - 2, $icon_h - 2, 4, '#2271b1' );
	$top = 7; $bot = $icon_h - 7; $track = $bot - $top;
	$bw = 22; $g = 6; $total = 3 * $bw + 2 * $g; $x0 = ( $w - $total ) / 2;
	$lineH = 8;
	$ys = array();
	if ( $mode === 'stretch' ) {
		$lineH = ( $track - 6 ) / 2;
		$ys = array( $top, $top + $lineH + 6 );
	} elseif ( $mode === 'start' ) {
		$ys = array( $top, $top + $lineH + 4 );
	} elseif ( $mode === 'end' ) {
		$ys = array( $bot - 2 * $lineH - 4, $bot - $lineH );
	} elseif ( $mode === 'center' ) {
		$block = 2 * $lineH + 4; $s = $top + ( $track - $block ) / 2;
		$ys = array( $s, $s + $lineH + 4 );
	} elseif ( $mode === 'between' ) {
		$ys = array( $top, $bot - $lineH );
	} else { // around
		$space = ( $track - 2 * $lineH ) / 2;
		$ys = array( $top + $space / 2, $top + $space / 2 + $lineH + $space );
	}
	foreach ( $ys as $ly ) {
		for ( $i = 0; $i < 3; $i++ ) {
			$svg .= $fx_rrect( $x0 + $i * ( $bw + $g ), $ly, $bw, $lineH, 2, '#ffffff', '#dcdcde' );
		}
	}
	return $fx_glyph( $svg, $label, $w, $icon_h );
};

// Align Self: one highlighted box (this item) positioned on the cross axis among
// full-height faint siblings — shows how THIS box aligns against its row siblings.
$fx_alignself_uri = function ( $mode, $label ) use ( $fx_rrect, $fx_glyph ) {
	$w = 120; $icon_h = 50;
	$svg = $fx_rrect( 1, 1, $w - 2, $icon_h - 2, 4, '#2271b1' );
	$top = 8; $bot = $icon_h - 8; $th = $bot - $top;
	$bw = 22; $g = 8; $n = 3; $total = $n * $bw + ( $n - 1 ) * $g; $x0 = ( $w - $total ) / 2;
	for ( $i = 0; $i < $n; $i++ ) {
		$x = $x0 + $i * ( $bw + $g );
		if ( $i === 1 ) {
			$bh = ( $mode === 'stretch' || $mode === '' ) ? $th : 16;
			$y  = $top;
			if ( $mode === 'center' )       { $y = $top + ( $th - $bh ) / 2; }
			elseif ( $mode === 'end' )      { $y = $bot - $bh; }
			elseif ( $mode === 'baseline' ) { $y = $top + 6; }
			$svg .= $fx_rrect( $x, $y, $bw, $bh, 2, '#ffffff', '#7da9d6' );
		} else {
			$svg .= $fx_rrect( $x, $top, $bw, $th, 2, 'rgba(255,255,255,0.45)', '#dcdcde' );
		}
	}
	return $fx_glyph( $svg, $label, $w, $icon_h );
};

// 1/12 … 12/12 reduced to lowest terms (6/12 → 1/2, 8/12 → 2/3, …) — the labels
// users see. $first = the leading "None/Inherit" choice. Keys stay 1…12. Pass
// $with_custom = true to append a "Custom…" choice (reveals a % input).
$fx_col_choices = function ( $first_key, $first_label, $with_custom = false ) {
	$reduced = array(
		'1' => '1/12', '2' => '1/6',  '3' => '1/4', '4' => '1/3',  '5' => '5/12', '6' => '1/2',
		'7' => '7/12', '8' => '2/3',  '9' => '3/4', '10' => '5/6', '11' => '11/12', '12' => '1/1 (full)',
	);
	$out = array( $first_key => $first_label ) + $reduced;
	if ( $with_custom ) { $out['custom'] = __( 'Custom…', 'fw' ); }
	return $out;
};

$options = [
	'tab_layout' => [
		'title'   => __( 'Layout', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_tag' => [
				'type'    => 'group',
				'options' => [
					'html_tag' => [
						'type'    => 'select',
						'label'   => __( 'HTML Tag', 'fw' ),
						'desc'    => __( 'The semantic element this container outputs.', 'fw' ),
						'value'   => 'div',
						'choices' => [
							'div'     => 'div',
							'section' => 'section',
							'header'  => 'header',
							'main'    => 'main',
							'article' => 'article',
							'aside'   => 'aside',
							'footer'  => 'footer',
							'nav'     => 'nav',
						],
					],
					// Multi-picker: the Width select reveals the Custom Width input only
					// when "Custom…" is chosen. Saved shape:
					//   [ 'preset' => 'none'|'1'..'12'|'custom', 'custom' => [ 'width_custom' => {value,unit} ] ]
					'width' => [
						'type'         => 'multi-picker',
						'label'        => false,
						'desc'         => false,
						'value'        => [ 'preset' => 'none' ],
						'picker'       => [
							'preset' => [
								'label'   => __( 'Width', 'fw' ),
								'desc'    => __( 'This container\'s own width when it sits inside another Flexbox row. "Auto" = full width. 1/12…1/1 output a Bootstrap col-md-* class (the canvas width handle sets the same value). Pick "Custom…" for an exact percentage.', 'fw' ),
								'type'    => 'select',
								'choices' => $fx_col_choices( 'none', __( 'Auto (full width)', 'fw' ), true ),
							],
						],
						'choices'      => [
							'custom' => [
								'width_custom' => [
									'type'  => 'unit-input',
									'label' => __( 'Custom Width', 'fw' ),
									'desc'  => __( 'Exact width for this box, e.g. 38%.', 'fw' ),
									'units' => [ '%', 'px', 'rem', 'vw' ],
									'value' => [ 'value' => '', 'unit' => '%' ],
								],
							],
						],
						'show_borders' => false,
					],
					'width_phone' => [
						'type'    => 'select',
						'label'   => __( 'Phone Width', 'fw' ),
						'desc'    => __( 'Width on phones (below the tablet breakpoint). "Inherit" leaves the element full-width (stacked) on phones — the usual responsive behavior. The main Width above applies on tablet and desktop.', 'fw' ),
						'value'   => '',
						'choices' => $fx_col_choices( '', __( 'Inherit (full width)', 'fw' ) ),
					],
					'flex_grow' => [
						'type'         => 'switch',
						'label'        => __( 'Grow to Fill', 'fw' ),
						'desc'         => __( 'Let this box grow to absorb the remaining free space inside a Row/Column parent (flex-grow). Overrides the fixed Width above when there is room to grow.', 'fw' ),
						'value'        => 'no',
						'right-choice' => [ 'value' => 'yes', 'label' => __( 'On', 'fw' ) ],
						'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
					],
					'align_self' => [
						'type'    => 'image-picker',
						'label'   => __( 'Align Self', 'fw' ),
						'desc'    => __( 'Override the parent\'s Align (cross axis) for just this box. Only has an effect inside a Flexbox parent.', 'fw' ),
						'value'   => '',
						'choices' => [
							''         => $fx_pick( $fx_alignself_uri( '',         __( 'Default', 'fw' ) ),  __( 'Default', 'fw' ) ),
							'start'    => $fx_pick( $fx_alignself_uri( 'start',    __( 'Start', 'fw' ) ),    __( 'Start', 'fw' ) ),
							'center'   => $fx_pick( $fx_alignself_uri( 'center',   __( 'Center', 'fw' ) ),   __( 'Center', 'fw' ) ),
							'end'      => $fx_pick( $fx_alignself_uri( 'end',      __( 'End', 'fw' ) ),      __( 'End', 'fw' ) ),
							'stretch'  => $fx_pick( $fx_alignself_uri( 'stretch',  __( 'Stretch', 'fw' ) ),  __( 'Stretch', 'fw' ) ),
							'baseline' => $fx_pick( $fx_alignself_uri( 'baseline', __( 'Baseline', 'fw' ) ), __( 'Baseline', 'fw' ) ),
						],
					],
					'order' => [
						'type'    => 'select',
						'label'   => __( 'Order', 'fw' ),
						'desc'    => __( 'Reorder this box among its siblings inside a Flexbox parent (1…12, matching the column grid), without changing the markup order. Useful for swapping order responsively.', 'fw' ),
						'value'   => '',
						'choices' => [
							''      => __( 'Default', 'fw' ),
							'first' => __( 'First', 'fw' ),
							'1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6',
							'7' => '7', '8' => '8', '9' => '9', '10' => '10', '11' => '11', '12' => '12',
							'last'  => __( 'Last', 'fw' ),
						],
					],
				],
			],
			'group_flex' => [
				'type'    => 'group',
				'options' => [
					'direction' => [
						'type'    => 'image-picker',
						'label'   => __( 'Direction', 'fw' ),
						'desc'    => __( 'Row (default) places children side-by-side — give each child a Width to split the row, e.g. 1/2 + 1/2. Column stacks them.', 'fw' ),
						'value'   => 'row',
						'choices' => [
							'row'    => $fx_pick( $fx_dir_uri( 'row', __( 'Row', 'fw' ) ),       __( 'Row', 'fw' ) ),
							'column' => $fx_pick( $fx_dir_uri( 'column', __( 'Column', 'fw' ) ), __( 'Column', 'fw' ) ),
						],
					],
					'reverse' => [
						'type'         => 'switch',
						'label'        => __( 'Reverse Order', 'fw' ),
						'desc'         => __( 'Reverse the order children are laid out (row-reverse / column-reverse). Handy for flipping a layout on a specific device without re-ordering the markup.', 'fw' ),
						'value'        => 'no',
						'right-choice' => [ 'value' => 'yes', 'label' => __( 'On', 'fw' ) ],
						'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
					],
					'wrap' => [
						'type'         => 'switch',
						'label'        => __( 'Wrap', 'fw' ),
						'desc'         => __( 'Allow children to wrap to the next line when they run out of room (rows only).', 'fw' ),
						'value'        => 'yes',
						'right-choice' => [ 'value' => 'yes', 'label' => __( 'On', 'fw' ) ],
						'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
					],
					'gap' => [
						'type'    => 'short-select',
						'label'   => __( 'Gap', 'fw' ),
						'desc'    => __( 'Spacing between children, from the site-wide spacing presets (Theme Settings → Default Gap).', 'fw' ),
						'help'    => function_exists( 'sc_styling_help_text' ) ? sc_styling_help_text( 'spacing' ) : '',
						'value'   => '',
						'choices' => function_exists( 'sc_get_gap_select_choices' )
							? sc_get_gap_select_choices( __( 'None', 'fw' ) )
							: array( '' => __( 'None', 'fw' ) ),
					],
					'justify_content' => [
						'type'    => 'image-picker',
						'label'   => __( 'Justify (main axis)', 'fw' ),
						'desc'    => __( 'How children are distributed along the main axis.', 'fw' ),
						'value'   => '',
						'choices' => [
							''        => $fx_pick( $fx_justify_uri( 'start',   __( 'Default', 'fw' ) ), __( 'Default', 'fw' ) ),
							'start'   => $fx_pick( $fx_justify_uri( 'start',   __( 'Start', 'fw' ) ),   __( 'Start', 'fw' ) ),
							'center'  => $fx_pick( $fx_justify_uri( 'center',  __( 'Center', 'fw' ) ),  __( 'Center', 'fw' ) ),
							'end'     => $fx_pick( $fx_justify_uri( 'end',     __( 'End', 'fw' ) ),     __( 'End', 'fw' ) ),
							'between' => $fx_pick( $fx_justify_uri( 'between', __( 'Space between', 'fw' ) ), __( 'Space between', 'fw' ) ),
							'around'  => $fx_pick( $fx_justify_uri( 'around',  __( 'Space around', 'fw' ) ),  __( 'Space around', 'fw' ) ),
							'evenly'  => $fx_pick( $fx_justify_uri( 'evenly',  __( 'Space evenly', 'fw' ) ),  __( 'Space evenly', 'fw' ) ),
						],
					],
					'align_items' => [
						'type'    => 'image-picker',
						'label'   => __( 'Align (cross axis)', 'fw' ),
						'desc'    => __( 'How children are aligned on the cross axis.', 'fw' ),
						'value'   => '',
						'choices' => [
							''         => $fx_pick( $fx_align_uri( 'stretch',  __( 'Default', 'fw' ) ),  __( 'Default', 'fw' ) ),
							'start'    => $fx_pick( $fx_align_uri( 'start',    __( 'Start', 'fw' ) ),    __( 'Start', 'fw' ) ),
							'center'   => $fx_pick( $fx_align_uri( 'center',   __( 'Center', 'fw' ) ),   __( 'Center', 'fw' ) ),
							'end'      => $fx_pick( $fx_align_uri( 'end',      __( 'End', 'fw' ) ),      __( 'End', 'fw' ) ),
							'stretch'  => $fx_pick( $fx_align_uri( 'stretch',  __( 'Stretch', 'fw' ) ),  __( 'Stretch', 'fw' ) ),
							'baseline' => $fx_pick( $fx_align_uri( 'baseline', __( 'Baseline', 'fw' ) ), __( 'Baseline', 'fw' ) ),
						],
					],
					'align_content' => [
						'type'    => 'image-picker',
						'label'   => __( 'Align Content (wrapped lines)', 'fw' ),
						'desc'    => __( 'When children wrap onto multiple lines, how those lines are packed on the cross axis. Only has an effect with Wrap on and 2+ lines.', 'fw' ),
						'value'   => '',
						'choices' => [
							// Default == CSS stretch (the initial value), so its glyph shows
							// stretch and the redundant explicit "Stretch" is omitted. Start
							// (lines packed at top, NOT stretched) is kept — it is distinct.
							''        => $fx_pick( $fx_aligncontent_uri( 'stretch', __( 'Default', 'fw' ) ),       __( 'Default', 'fw' ) ),
							'start'   => $fx_pick( $fx_aligncontent_uri( 'start',   __( 'Start', 'fw' ) ),         __( 'Start', 'fw' ) ),
							'center'  => $fx_pick( $fx_aligncontent_uri( 'center',  __( 'Center', 'fw' ) ),        __( 'Center', 'fw' ) ),
							'end'     => $fx_pick( $fx_aligncontent_uri( 'end',     __( 'End', 'fw' ) ),           __( 'End', 'fw' ) ),
							'between' => $fx_pick( $fx_aligncontent_uri( 'between', __( 'Space between', 'fw' ) ),  __( 'Space between', 'fw' ) ),
							'around'  => $fx_pick( $fx_aligncontent_uri( 'around',  __( 'Space around', 'fw' ) ),   __( 'Space around', 'fw' ) ),
						],
					],
					'responsive_note' => [
						'type'  => 'html-fixed',
						'label' => __( 'Responsive', 'fw' ),
						'html'  => __( 'Override Direction / Justify on smaller screens — e.g. a Row header that stacks to a Column on mobile. Leave as Inherit to keep the larger-screen value.', 'fw' ),
					],
					'direction_mobile' => [
						'type'    => 'select',
						'label'   => __( 'Direction — Mobile', 'fw' ),
						'desc'    => __( 'Below 768px.', 'fw' ),
						'value'   => '',
						'choices' => [ '' => __( 'Inherit', 'fw' ), 'row' => __( 'Row', 'fw' ), 'column' => __( 'Column', 'fw' ) ],
					],
					'justify_content_mobile' => [
						'type'    => 'select',
						'label'   => __( 'Justify — Mobile', 'fw' ),
						'value'   => '',
						'choices' => [ '' => __( 'Inherit', 'fw' ), 'start' => __( 'Start', 'fw' ), 'center' => __( 'Center', 'fw' ), 'end' => __( 'End', 'fw' ), 'between' => __( 'Space between', 'fw' ), 'around' => __( 'Space around', 'fw' ), 'evenly' => __( 'Space evenly', 'fw' ) ],
					],
					'direction_tablet' => [
						'type'    => 'select',
						'label'   => __( 'Direction — Tablet', 'fw' ),
						'desc'    => __( '768–991px.', 'fw' ),
						'value'   => '',
						'choices' => [ '' => __( 'Inherit', 'fw' ), 'row' => __( 'Row', 'fw' ), 'column' => __( 'Column', 'fw' ) ],
					],
					'justify_content_tablet' => [
						'type'    => 'select',
						'label'   => __( 'Justify — Tablet', 'fw' ),
						'value'   => '',
						'choices' => [ '' => __( 'Inherit', 'fw' ), 'start' => __( 'Start', 'fw' ), 'center' => __( 'Center', 'fw' ), 'end' => __( 'End', 'fw' ), 'between' => __( 'Space between', 'fw' ), 'around' => __( 'Space around', 'fw' ), 'evenly' => __( 'Space evenly', 'fw' ) ],
					],
				],
			],
		],
	],
	'tab_styling' => [
		'title'   => __( 'Styling', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_styling' => [
				'type'    => 'group',
				'options' => [
					'background'    => [
						'type'  => 'background-pro',
						'label' => __( 'Background', 'fw' ),
						'desc'  => __( 'Color, gradient, image and video background layers (they stack: image over gradient over color). Replaces the old Background Color field — existing flexboxes are migrated automatically.', 'fw' ),
						'help'  => __( 'Image attachment "Fixed" gives a parallax effect. Video renders a muted, looping background; set a poster/fallback image for while it loads or where autoplay is blocked.', 'fw' ),
					],
					'border_preset' => [
						'type'         => 'border-style-picker',
						'label'        => __( 'Border / Box Style', 'fw' ),
						'desc'         => __( 'Apply a reusable box style — border, corners, shadow and an optional background fill (with a hover state) — to this flexbox. Each option previews the real style next to its name. Manage presets in Theme Settings → Styling → Borders.', 'fw' ),
						'value'        => '',
						'show_borders' => false,
						'choices'      => function_exists( 'sc_get_border_preset_choices' ) ? sc_get_border_preset_choices() : array( '' => __( 'None', 'fw' ) ),
					],
					'min_height' => [
						'type'  => 'unit-input',
						'label' => __( 'Min Height', 'fw' ),
						'desc'  => __( 'Minimum height of the container. Pair with Align (cross axis) = Center to vertically centre content — e.g. 60vh for a hero band. Leave empty to fit content.', 'fw' ),
						'units' => [ 'vh', 'px', 'rem', '%' ],
						'value' => [ 'value' => '', 'unit' => 'vh' ],
					],
					'spacing'    => [
						'type'  => 'spacing',
						'label' => __( 'Margin & Padding', 'fw' ),
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
