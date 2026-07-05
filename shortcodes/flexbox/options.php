<?php if (!defined('FW')) die('Forbidden');

/**
 * Flexbox options.
 *
 * Direction / Justify / Align are image-pickers built from inline-SVG data-URIs,
 * in the same visual language as the Section / Column alignment glyphs: a blue
 * (#2271b1) container with white (#fff, #dcdcde hairline) item boxes positioned
 * to show the chosen flex behavior. Gap uses the site-wide spacing presets
 * (sc_get_gap_select_choices). Layout options are split into two role groups:
 * Container (how it arranges its children) and Placement (how it sits inside a
 * parent Flexbox).
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

// Fraction-bar thumbnails for the responsive Width Override popover (mirrors the
// Column's width tiles): the chosen portion is one blue bar, the remainder split
// into gray bars. 'none' (Auto / inherit) = a faint full bar; 'custom' = a dashed bar.
$fx_width_bar = function ( $cells_on, $mode, $label ) {
	$track = 60; $pad = 4; $W = $track + 2 * $pad; $gap = 2; $barH = 24; $H = $pad + $barH + 14;
	$blue = '#2271b1'; $gray = '#9b9b9b';
	$reduce = array( 1 => array( 1, 12 ), 2 => array( 1, 6 ), 3 => array( 1, 4 ), 4 => array( 1, 3 ),
		5 => array( 5, 12 ), 6 => array( 1, 2 ), 7 => array( 7, 12 ), 8 => array( 2, 3 ),
		9 => array( 3, 4 ), 10 => array( 5, 6 ), 11 => array( 11, 12 ), 12 => array( 1, 1 ) );
	$rects = '<rect x="0" y="0" width="' . $W . '" height="' . $H . '" fill="#ffffff"/>';
	if ( $mode === 'none' ) {
		$rects .= '<rect x="' . $pad . '" y="' . $pad . '" width="' . $track . '" height="' . $barH . '" fill="#eef0f1" shape-rendering="crispEdges"/>';
	} elseif ( $mode === 'custom' ) {
		$rects .= '<rect x="' . $pad . '" y="' . $pad . '" width="' . $track . '" height="' . $barH . '" fill="#ffffff" stroke="#2271b1" stroke-dasharray="3 2" shape-rendering="crispEdges"/>';
	} else {
		list( $n, $d ) = isset( $reduce[ $cells_on ] ) ? $reduce[ $cells_on ] : array( $cells_on, 12 );
		$fr = array( $n / $d );
		for ( $i = 0; $i < $d - $n; $i++ ) { $fr[] = 1 / $d; }
		$N = count( $fr ); $prev = 0; $cum = 0;
		for ( $i = 0; $i < $N; $i++ ) {
			$cum += $fr[ $i ];
			$b  = ( $i === $N - 1 ) ? $track : (int) round( $cum * $track );
			$bw = ( $N === 1 ) ? ( $b - $prev ) : max( 1, ( $b - $prev ) - $gap );
			$rects .= '<rect x="' . ( $pad + $prev ) . '" y="' . $pad . '" width="' . $bw . '" height="' . $barH . '" fill="' . ( $i === 0 ? $blue : $gray ) . '" shape-rendering="crispEdges"/>';
			$prev = $b;
		}
	}
	$text = '<text x="' . ( $W / 2 ) . '" y="' . ( $pad + $barH + 11 ) . '" text-anchor="middle" font-family="-apple-system,Segoe UI,Roboto,sans-serif" font-size="11" fill="#50575e">' . $label . '</text>';
	$svg  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $W . ' ' . $H . '" width="' . $W . '" height="' . $H . '">' . $rects . $text . '</svg>';
	return 'data:image/svg+xml,' . rawurlencode( $svg );
};

// Width tiles + trigger labels: Auto (none) + 1/12…1/1 (lowest-terms) + Custom.
// Slightly shorter thumbnails (43px) than the other flex glyphs — the width bars
// are wide, so a lower height keeps them compact in the popover.
$fx_width_pick = function ( $uri, $label ) {
	return array(
		'small' => array( 'src' => $uri, 'height' => 43 ),
		'large' => array( 'src' => $uri, 'height' => 130 ),
		'label' => $label,
	);
};
$fx_frac_lbl = array( 1 => '1/12', 2 => '1/6', 3 => '1/4', 4 => '1/3', 5 => '5/12', 6 => '1/2',
	7 => '7/12', 8 => '2/3', 9 => '3/4', 10 => '5/6', 11 => '11/12', 12 => '1/1' );
$fx_width_choices = array( 'none' => $fx_width_pick( $fx_width_bar( 0, 'none', __( 'Auto', 'fw' ) ), __( 'Auto', 'fw' ) ) );
for ( $i = 1; $i <= 12; $i++ ) { $fx_width_choices[ (string) $i ] = $fx_width_pick( $fx_width_bar( $i, 'width', $fx_frac_lbl[ $i ] ), $fx_frac_lbl[ $i ] ); }
$fx_width_choices['custom'] = $fx_width_pick( $fx_width_bar( 0, 'custom', __( 'Custom', 'fw' ) ), __( 'Custom', 'fw' ) );
$fx_width_summary = array( 'none' => __( 'Auto', 'fw' ), 'custom' => __( 'Custom', 'fw' ) );
for ( $i = 1; $i <= 12; $i++ ) { $fx_width_summary[ (string) $i ] = $fx_frac_lbl[ $i ]; }

$options = [
	'tab_layout' => [
		'title'   => __( 'Layout', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_container' => [
				'type'    => 'group',
				'options' => [
					'direction' => [
						'type'    => 'responsive',
						'label'   => __( 'Direction', 'fw' ),
						'desc'    => __( 'Row (default) places children side-by-side — give each child a Width to split the row, e.g. 1/2 + 1/2. Column stacks them. Use the Phone / Tablet / Desktop tabs to change direction per device (e.g. a Row header that stacks to a Column on mobile).', 'fw' ),
						'value'   => [ 'base' => 'row', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'    => 'image-picker',
							'choices' => [
								'row'    => $fx_pick( $fx_dir_uri( 'row', __( 'Row', 'fw' ) ),       __( 'Row', 'fw' ) ),
								'column' => $fx_pick( $fx_dir_uri( 'column', __( 'Column', 'fw' ) ), __( 'Column', 'fw' ) ),
							],
						],
					],
					'gap' => [
						'type'    => 'responsive',
						'label'   => __( 'Gap', 'fw' ),
						'desc'    => __( 'Spacing between children, from the site-wide spacing presets (Theme Settings → Default Gap). Use the Phone / Tablet / Desktop tabs to set a different gap per device (a blank device inherits the smaller one).', 'fw' ),
						'help'    => function_exists( 'sc_styling_help_text' ) ? sc_styling_help_text( 'spacing' ) : '',
						'value'   => [ 'base' => '', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'    => 'short-select',
							'choices' => function_exists( 'sc_get_gap_select_choices' )
								? sc_get_gap_select_choices( __( 'None', 'fw' ) )
								: array( '' => __( 'None', 'fw' ) ),
						],
					],
					'justify_content' => [
						'type'    => 'responsive',
						'label'   => __( 'Justify (main axis)', 'fw' ),
						'desc'    => __( 'How children are distributed along the main axis. Use the Phone / Tablet / Desktop tabs to set a different value per device (a blank device inherits the smaller one).', 'fw' ),
						'value'   => [ 'base' => '', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'    => 'image-picker',
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
					],
					'align_items' => [
						'type'    => 'responsive',
						'label'   => __( 'Align (cross axis)', 'fw' ),
						'desc'    => __( 'How children are aligned on the cross axis. Use the Phone / Tablet / Desktop tabs to set a different value per device (a blank device inherits the smaller one).', 'fw' ),
						'value'   => [ 'base' => '', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'    => 'image-picker',
							'choices' => [
								''         => $fx_pick( $fx_align_uri( 'stretch',  __( 'Default', 'fw' ) ),  __( 'Default', 'fw' ) ),
								'start'    => $fx_pick( $fx_align_uri( 'start',    __( 'Start', 'fw' ) ),    __( 'Start', 'fw' ) ),
								'center'   => $fx_pick( $fx_align_uri( 'center',   __( 'Center', 'fw' ) ),   __( 'Center', 'fw' ) ),
								'end'      => $fx_pick( $fx_align_uri( 'end',      __( 'End', 'fw' ) ),      __( 'End', 'fw' ) ),
								'stretch'  => $fx_pick( $fx_align_uri( 'stretch',  __( 'Stretch', 'fw' ) ),  __( 'Stretch', 'fw' ) ),
								'baseline' => $fx_pick( $fx_align_uri( 'baseline', __( 'Baseline', 'fw' ) ), __( 'Baseline', 'fw' ) ),
							],
						],
					],
					'wrap' => [
						'type'    => 'responsive',
						'label'   => __( 'Wrap', 'fw' ),
						'desc'    => __( 'Allow children to wrap to the next line when they run out of room (rows only). Use the Phone / Tablet / Desktop tabs to toggle wrapping per device (a blank device inherits the smaller one).', 'fw' ),
						'value'   => [ 'base' => 'yes', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'         => 'switch',
							'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
							'right-choice' => [ 'value' => 'yes', 'label' => __( 'On', 'fw' ) ],
						],
					],
					'reverse' => [
						'type'    => 'responsive',
						'label'   => __( 'Reverse Order', 'fw' ),
						'desc'    => __( 'Reverse the order children are laid out (row-reverse / column-reverse) without changing the markup. Use the Phone / Tablet / Desktop tabs to flip only on some devices (a blank device inherits the smaller one) — e.g. image-above-text on phone, text-above-image on desktop.', 'fw' ),
						'value'   => [ 'base' => 'no', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'         => 'switch',
							'left-choice'  => [ 'value' => 'no',  'label' => __( 'Default', 'fw' ) ],
							'right-choice' => [ 'value' => 'yes', 'label' => __( 'Reverse', 'fw' ) ],
						],
					],
					'align_content' => [
						'type'    => 'responsive',
						'label'   => __( 'Align Content (wrapped lines)', 'fw' ),
						'desc'    => __( 'When children wrap onto multiple lines, how those lines are packed on the cross axis. Only has an effect with Wrap on and 2+ lines. Use the Phone / Tablet / Desktop tabs to set it per device (a blank device inherits the smaller one).', 'fw' ),
						'value'   => [ 'base' => '', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'    => 'image-picker',
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
					],
				],
			],
			'group_placement' => [
				'type'    => 'group',
				'options' => [
					'width' => [
						'type'  => 'responsive',
						'label' => __( 'Width Override', 'fw' ),
						'desc'  => __( 'This container\'s own width when it sits inside another Flexbox row. "Auto" leaves it to flow (full width / stacked). Pick a fraction (1/12…1/1) or "Custom…" for an exact size. Use the Phone / Tablet / Desktop tabs to set a different width per device (a blank device inherits the smaller one).', 'fw' ),
						'help'  => __( 'Fractions output the plugin\'s own responsive grid classes; the canvas width handle sets the same base value. "Custom…" reveals an exact px / % / rem / vw input. A blank (Auto) device inherits the next smaller one — e.g. Phone Auto + Desktop 1/2 = full width on phones, half on desktop.', 'fw' ),
						'value' => [ 'base' => [ 'preset' => 'none' ], 'md' => [ 'preset' => 'none' ], 'lg' => [ 'preset' => 'none' ] ],
						'inner' => [
							'type'          => 'popover',
							'value'         => [ 'preset' => 'none' ],
							'summary'       => $fx_width_summary,
							'summary_key'   => 'preset',
							'autoclose'     => false,
							'inner-options' => [
								'wpick' => [
									'type'         => 'multi-picker',
									'label'        => false,
									'desc'         => false,
									'value'        => [ 'preset' => 'none' ],
									'picker'       => [
										'preset' => [ 'type' => 'image-picker', 'label' => false, 'choices' => $fx_width_choices ],
									],
									'choices'      => [
										'custom' => [
											'width_custom' => [
												'type'  => 'unit-input',
												'label' => __( 'Custom Width', 'fw' ),
												'desc'  => __( 'Exact width for this box, e.g. 38% or 320px.', 'fw' ),
												'units' => [ '%', 'px', 'rem', 'vw' ],
												'value' => [ 'value' => '', 'unit' => '%' ],
											],
										],
									],
									'show_borders' => false,
								],
							],
						],
					],
					'flex_grow' => [
						'type'    => 'responsive',
						'label'   => __( 'Grow to Fill', 'fw' ),
						'desc'    => __( 'Let this box grow to absorb the remaining free space inside a Row/Column parent (flex-grow). Overrides the fixed Width above when there is room to grow. Use the Phone / Tablet / Desktop tabs to toggle it per device (a blank device inherits the smaller one).', 'fw' ),
						'value'   => [ 'base' => 'no', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'         => 'switch',
							'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
							'right-choice' => [ 'value' => 'yes', 'label' => __( 'On', 'fw' ) ],
						],
					],
					'align_self' => [
						'type'    => 'responsive',
						'label'   => __( 'Align Self', 'fw' ),
						'desc'    => __( 'Override the parent\'s Align (cross axis) for just this box. Only has an effect inside a Flexbox parent. Use the Phone / Tablet / Desktop tabs to set it per device (a blank device inherits the smaller one).', 'fw' ),
						'value'   => [ 'base' => '', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'    => 'image-picker',
							'choices' => [
								''         => $fx_pick( $fx_alignself_uri( '',         __( 'Default', 'fw' ) ),  __( 'Default', 'fw' ) ),
								'start'    => $fx_pick( $fx_alignself_uri( 'start',    __( 'Start', 'fw' ) ),    __( 'Start', 'fw' ) ),
								'center'   => $fx_pick( $fx_alignself_uri( 'center',   __( 'Center', 'fw' ) ),   __( 'Center', 'fw' ) ),
								'end'      => $fx_pick( $fx_alignself_uri( 'end',      __( 'End', 'fw' ) ),      __( 'End', 'fw' ) ),
								'stretch'  => $fx_pick( $fx_alignself_uri( 'stretch',  __( 'Stretch', 'fw' ) ),  __( 'Stretch', 'fw' ) ),
								'baseline' => $fx_pick( $fx_alignself_uri( 'baseline', __( 'Baseline', 'fw' ) ), __( 'Baseline', 'fw' ) ),
							],
						],
					],
					'order' => [
						'type'    => 'responsive',
						'label'   => __( 'Order', 'fw' ),
						'desc'    => __( 'Reorder this box among its siblings inside a Flexbox parent (1…12), without changing the markup order. Use the Phone / Tablet / Desktop tabs to reorder per device (a blank device inherits the smaller one).', 'fw' ),
						'value'   => [ 'base' => '', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'    => 'short-select',
							'choices' => [
								''      => __( 'Default', 'fw' ),
								'first' => __( 'First', 'fw' ),
								'0' => '0',
								'1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6',
								'7' => '7', '8' => '8', '9' => '9', '10' => '10', '11' => '11', '12' => '12',
								'last'  => __( 'Last', 'fw' ),
							],
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
						'type'  => 'responsive',
						'label' => __( 'Min Height', 'fw' ),
						'desc'  => __( 'Minimum height of the container. Pair with Align (cross axis) = Center to vertically centre content — e.g. 60vh for a hero band. Leave empty to fit content. Use the Phone / Tablet / Desktop tabs to set a different min-height per device (a blank device inherits the smaller one).', 'fw' ),
						'value' => [ 'base' => [ 'value' => '', 'unit' => 'vh' ], 'md' => [ 'value' => '', 'unit' => 'vh' ], 'lg' => [ 'value' => '', 'unit' => 'vh' ] ],
						'inner' => [
							'type'  => 'unit-input',
							'units' => [ 'vh', 'px', 'rem', '%' ],
							'value' => [ 'value' => '', 'unit' => 'vh' ],
						],
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
