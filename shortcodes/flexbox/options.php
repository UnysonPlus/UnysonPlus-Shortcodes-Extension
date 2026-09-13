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

if ( ! function_exists( 'fw_upw_icon_palette' ) ) {
	$fw_upw_icon_palette_file = dirname( __FILE__ ) . '/../../../../includes/icon-palette.php';
	if ( file_exists( $fw_upw_icon_palette_file ) ) {
		require_once $fw_upw_icon_palette_file;
	}
}
// Shared UnysonPlus icon palette -- same legend and rules as the section glyphs.
// Falls back to the previous literals if it is unavailable, so a partial install
// degrades to the old glyphs instead of fataling.
$pal = function_exists( 'fw_upw_icon_palette' ) ? fw_upw_icon_palette() : array(
	'field_strong' => '#3858e9', 'structure' => '#dadada', 'structure_strong' => '#9b9b9b', 'structure_line' => '#dcdcde',
	'structure_soft' => '#ececec', 'content' => '#ffffff', 'ink' => '#1f2430',
	'accent_light' => '#7b90ff', 'caption' => '#50575e',
);

$fx_rrect = function ( $x, $y, $w, $h, $rx, $fill, $stroke = '' ) {
	return '<rect x="' . round( $x, 1 ) . '" y="' . round( $y, 1 ) . '" width="' . round( $w, 1 )
		. '" height="' . round( $h, 1 ) . '" rx="' . $rx . '" fill="' . $fill . '"'
		. ( $stroke !== '' ? ' stroke="' . $stroke . '"' : '' ) . '/>';
};

// Caption + <svg> wrapper (mirrors the section's $section_glyph_svg).
$fx_glyph = function ( $inner, $label, $w = 120, $icon_h = 50 ) use ( $pal ) {
	$h = $icon_h + 16;
	$inner .= '<text x="' . ( $w / 2 ) . '" y="' . ( $icon_h + 11 ) . '" text-anchor="middle" '
		. 'font-family="-apple-system,Segoe UI,Roboto,sans-serif" font-size="11" fill="' . $pal['caption'] . '">' . $label . '</text>';
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
$fx_dir_uri = function ( $mode, $label ) use ($fx_rrect, $fx_glyph, $pal) {
	$w = 120; $icon_h = 50;
	$svg = $fx_rrect( 1, 1, $w - 2, $icon_h - 2, 4, $pal['field_strong'] );
	if ( $mode === 'row' ) {
		$s = 17; $g = 7; $total = 3 * $s + 2 * $g; $x0 = ( $w - $total ) / 2; $y = 9;
		for ( $i = 0; $i < 3; $i++ ) { $svg .= $fx_rrect( $x0 + $i * ( $s + $g ), $y, $s, $s, 3, $pal['content'], $pal['structure_line'] ); }
		$ay = $y + $s + 7; $x2 = $x0 + $total;
		$svg .= '<path d="M' . $x0 . ' ' . $ay . 'H' . $x2 . 'M' . ( $x2 - 4 ) . ' ' . ( $ay - 3 ) . 'L' . $x2 . ' ' . $ay . 'L' . ( $x2 - 4 ) . ' ' . ( $ay + 3 ) . '" stroke="' . $pal['accent_light'] . '" stroke-width="1.4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>';
	} else {
		$s = 10; $g = 4; $total = 3 * $s + 2 * $g; $y0 = ( $icon_h - $total ) / 2; $x = ( $w - $s ) / 2 - 7;
		for ( $i = 0; $i < 3; $i++ ) { $svg .= $fx_rrect( $x, $y0 + $i * ( $s + $g ), $s, $s, 3, $pal['content'], $pal['structure_line'] ); }
		$ax = $x + $s + 10; $y2 = $y0 + $total;
		$svg .= '<path d="M' . $ax . ' ' . $y0 . 'V' . $y2 . 'M' . ( $ax - 3 ) . ' ' . ( $y2 - 4 ) . 'L' . $ax . ' ' . $y2 . 'L' . ( $ax + 3 ) . ' ' . ( $y2 - 4 ) . '" stroke="' . $pal['accent_light'] . '" stroke-width="1.4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>';
	}
	return $fx_glyph( $svg, $label, $w, $icon_h );
};

// Justify (main axis): 3 item boxes positioned along the horizontal track.
$fx_justify_uri = function ( $mode, $label ) use ($fx_rrect, $fx_glyph, $pal) {
	$w = 120; $icon_h = 50; $n = 3; $bw = 15; $bh = 28; $y = ( $icon_h - $bh ) / 2;
	$tx = 8; $tw = $w - 16; $g = 4; $xs = array();
	$tot = $n * $bw + ( $n - 1 ) * $g;
	if ( $mode === 'start' )        { for ( $i = 0; $i < $n; $i++ ) { $xs[] = $tx + $i * ( $bw + $g ); } }
	elseif ( $mode === 'center' )   { $s = $tx + ( $tw - $tot ) / 2; for ( $i = 0; $i < $n; $i++ ) { $xs[] = $s + $i * ( $bw + $g ); } }
	elseif ( $mode === 'end' )      { $s = $tx + $tw - $tot; for ( $i = 0; $i < $n; $i++ ) { $xs[] = $s + $i * ( $bw + $g ); } }
	elseif ( $mode === 'between' )  { for ( $i = 0; $i < $n; $i++ ) { $xs[] = $tx + $i * ( ( $tw - $bw ) / ( $n - 1 ) ); } }
	elseif ( $mode === 'around' )   { $sp = ( $tw - $n * $bw ) / $n; for ( $i = 0; $i < $n; $i++ ) { $xs[] = $tx + $sp / 2 + $i * ( $bw + $sp ); } }
	else /* evenly */               { $sp = ( $tw - $n * $bw ) / ( $n + 1 ); for ( $i = 0; $i < $n; $i++ ) { $xs[] = $tx + $sp * ( $i + 1 ) + $i * $bw; } }
	$svg = $fx_rrect( 1, 1, $w - 2, $icon_h - 2, 4, $pal['field_strong'] );
	foreach ( $xs as $x ) { $svg .= $fx_rrect( $x, $y, $bw, $bh, 2, $pal['content'], $pal['structure_line'] ); }
	return $fx_glyph( $svg, $label, $w, $icon_h );
};

// Align (cross axis): 3 item boxes positioned along the vertical track.
$fx_align_uri = function ( $mode, $label ) use ($fx_rrect, $fx_glyph, $pal) {
	$w = 120; $icon_h = 50; $n = 3; $bw = 20; $g = 8; $total = $n * $bw + ( $n - 1 ) * $g; $x0 = ( $w - $total ) / 2;
	$top = 8; $bot = $icon_h - 8; $th = $bot - $top;
	$svg = $fx_rrect( 1, 1, $w - 2, $icon_h - 2, 4, $pal['field_strong'] );
	for ( $i = 0; $i < $n; $i++ ) {
		$bh = ( $mode === 'stretch' ) ? $th : 16;
		if ( $mode === 'baseline' ) { $bh = 10 + $i * 5; }
		$y = $top;
		if ( $mode === 'center' )        { $y = $top + ( $th - $bh ) / 2; }
		elseif ( $mode === 'end' )       { $y = $bot - $bh; }
		elseif ( $mode === 'baseline' )  { $y = $bot - $bh; }
		$svg .= $fx_rrect( $x0 + $i * ( $bw + $g ), $y, $bw, $bh, 2, $pal['content'], $pal['structure_line'] );
	}
	return $fx_glyph( $svg, $label, $w, $icon_h );
};

// Align Content (cross axis, WRAPPED lines): two rows of item boxes positioned to
// show how multiple wrapped lines are packed on the cross axis.
$fx_aligncontent_uri = function ( $mode, $label ) use ($fx_rrect, $fx_glyph, $pal) {
	$w = 120; $icon_h = 50;
	$svg = $fx_rrect( 1, 1, $w - 2, $icon_h - 2, 4, $pal['field_strong'] );
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
			$svg .= $fx_rrect( $x0 + $i * ( $bw + $g ), $ly, $bw, $lineH, 2, $pal['content'], $pal['structure_line'] );
		}
	}
	return $fx_glyph( $svg, $label, $w, $icon_h );
};

// Align Self: one highlighted box (this item) positioned on the cross axis among
// full-height faint siblings — shows how THIS box aligns against its row siblings.
$fx_alignself_uri = function ( $mode, $label ) use ($fx_rrect, $fx_glyph, $pal) {
	$w = 120; $icon_h = 50;
	$svg = $fx_rrect( 1, 1, $w - 2, $icon_h - 2, 4, $pal['field_strong'] );
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
			$svg .= $fx_rrect( $x, $y, $bw, $bh, 2, $pal['content'], $pal['accent_light'] );
		} else {
			$svg .= $fx_rrect( $x, $top, $bw, $th, 2, 'rgba(255,255,255,0.45)', $pal['structure_line'] );
		}
	}
	return $fx_glyph( $svg, $label, $w, $icon_h );
};

// Fraction-bar thumbnails for the responsive Width Override popover (mirrors the
// Column's width tiles): the chosen portion is one blue bar, the remainder split
// into gray bars. 'none' (Auto / inherit) = a faint full bar; 'custom' = a dashed bar.
$fx_width_bar = function ( $cells_on, $mode, $label ) use ( $pal ) {
	$track = 60; $pad = 4; $W = $track + 2 * $pad; $gap = 2; $barH = 24; $H = $pad + $barH + 14;
	$blue = $pal['field_strong']; $gray = $pal['structure_strong'];
	$reduce = array( 1 => array( 1, 12 ), 2 => array( 1, 6 ), 3 => array( 1, 4 ), 4 => array( 1, 3 ),
		5 => array( 5, 12 ), 6 => array( 1, 2 ), 7 => array( 7, 12 ), 8 => array( 2, 3 ),
		9 => array( 3, 4 ), 10 => array( 5, 6 ), 11 => array( 11, 12 ), 12 => array( 1, 1 ) );
	$rects = '<rect x="0" y="0" width="' . $W . '" height="' . $H . '" fill="' . $pal['content'] . '"/>';
	if ( $mode === 'none' ) {
		$rects .= '<rect x="' . $pad . '" y="' . $pad . '" width="' . $track . '" height="' . $barH . '" fill="' . $pal['structure_soft'] . '" shape-rendering="crispEdges"/>';
	} elseif ( $mode === 'custom' ) {
		$rects .= '<rect x="' . $pad . '" y="' . $pad . '" width="' . $track . '" height="' . $barH . '" fill="' . $pal['content'] . '" stroke="' . $pal['field_strong'] . '" stroke-dasharray="3 2" shape-rendering="crispEdges"/>';
	} else {
		// $cells_on is twelfths (int, via $reduce) OR an explicit [numerator, denominator] pair
		// (used for fifths, which aren't twelfths).
		if ( is_array( $cells_on ) ) { list( $n, $d ) = $cells_on; }
		else { list( $n, $d ) = isset( $reduce[ $cells_on ] ) ? $reduce[ $cells_on ] : array( $cells_on, 12 ); }
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
	$text = '<text x="' . ( $W / 2 ) . '" y="' . ( $pad + $barH + 11 ) . '" text-anchor="middle" font-family="-apple-system,Segoe UI,Roboto,sans-serif" font-size="11" fill="' . $pal['caption'] . '">' . $label . '</text>';
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
// Content-sizing tiles: a centred blue box representing "sized to content" — Min narrow, Fit
// medium, Max wider — so the intrinsic-sizing intent reads at a glance vs. the fraction bars.
$fx_content_bar = function ( $w_frac, $label ) use ( $pal ) {
	$track = 60; $pad = 4; $W = $track + 2 * $pad; $barH = 24; $H = $pad + $barH + 14;
	$bw = max( 6, (int) round( $w_frac * $track ) );
	$x  = $pad + ( $track - $bw ) / 2;
	$svg  = '<rect x="0" y="0" width="' . $W . '" height="' . $H . '" fill="' . $pal['content'] . '"/>';
	$svg .= '<rect x="' . round( $x, 1 ) . '" y="' . $pad . '" width="' . $bw . '" height="' . $barH . '" rx="3" fill="' . $pal['field_strong'] . '"/>';
	$svg .= '<text x="' . ( $W / 2 ) . '" y="' . ( $pad + $barH + 11 ) . '" text-anchor="middle" font-family="-apple-system,Segoe UI,Roboto,sans-serif" font-size="11" fill="' . $pal['caption'] . '">' . $label . '</text>';
	$out  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $W . ' ' . $H . '" width="' . $W . '" height="' . $H . '">' . $svg . '</svg>';
	return 'data:image/svg+xml,' . rawurlencode( $out );
};
$fx_frac_lbl = array( 1 => '1/12', 2 => '1/6', 3 => '1/4', 4 => '1/3', 5 => '5/12', 6 => '1/2',
	7 => '7/12', 8 => '2/3', 9 => '3/4', 10 => '5/6', 11 => '11/12', 12 => '1/1' );
// Fifths (exact % under the hood) + content-sizing keywords (fit/max/min-content) — see view.php.
$fx_fifths  = array( '1_5' => array( 1, 5, '1/5' ), '2_5' => array( 2, 5, '2/5' ), '3_5' => array( 3, 5, '3/5' ), '4_5' => array( 4, 5, '4/5' ) );
$fx_content = array( 'fit' => array( 0.38, __( 'Fit', 'fw' ) ), 'max' => array( 0.55, __( 'Max', 'fw' ) ), 'min' => array( 0.16, __( 'Min', 'fw' ) ) );
$fx_width_choices = array( 'none' => $fx_width_pick( $fx_width_bar( 0, 'none', __( 'Auto', 'fw' ) ), __( 'Auto', 'fw' ) ) );
for ( $i = 1; $i <= 12; $i++ ) { $fx_width_choices[ (string) $i ] = $fx_width_pick( $fx_width_bar( $i, 'width', $fx_frac_lbl[ $i ] ), $fx_frac_lbl[ $i ] ); }
foreach ( $fx_fifths as $k => $f )  { $fx_width_choices[ $k ] = $fx_width_pick( $fx_width_bar( array( $f[0], $f[1] ), 'width', $f[2] ), $f[2] ); }
foreach ( $fx_content as $k => $c ) { $fx_width_choices[ $k ] = $fx_width_pick( $fx_content_bar( $c[0], $c[1] ), $c[1] ); }
$fx_width_choices['custom'] = $fx_width_pick( $fx_width_bar( 0, 'custom', __( 'Custom', 'fw' ) ), __( 'Custom', 'fw' ) );
$fx_width_summary = array( 'none' => __( 'Auto', 'fw' ), 'custom' => __( 'Custom', 'fw' ) );
for ( $i = 1; $i <= 12; $i++ ) { $fx_width_summary[ (string) $i ] = $fx_frac_lbl[ $i ]; }
foreach ( $fx_fifths as $k => $f )  { $fx_width_summary[ $k ] = $f[2]; }
foreach ( $fx_content as $k => $c ) { $fx_width_summary[ $k ] = $c[1]; }

// HTML Tag choices — context-aware. In the Theme Builder (Header / Body / Footer part editors)
// the full semantic set is offered (that is what page chrome is built from); on normal pages /
// posts only the content-appropriate tags — div / section / article / aside — with no
// header / footer / main / nav (those are page landmarks, not content boxes).
$fx_in_tb       = function_exists( 'up_theme_builder_current_admin_post_type' )
	&& in_array( up_theme_builder_current_admin_post_type(), array( 'up_header', 'up_body', 'up_footer' ), true );
$fx_tag_choices = $fx_in_tb
	? array( 'div' => 'div', 'section' => 'section', 'main' => 'main', 'article' => 'article', 'header' => 'header', 'footer' => 'footer', 'aside' => 'aside', 'nav' => 'nav' )
	// On a Page only the content-appropriate tags — div / section / article / aside. The landmark /
	// chrome elements (header, footer, nav, main) are OMITTED here: a page has exactly one <main>, and
	// its <header> / <footer> / <nav> landmarks are built in the Theme / Header-Footer Builder — dropping
	// duplicates of them inside page-body content produces invalid, a11y-hostile markup. They remain
	// available in the Theme Builder branch above (where site chrome is actually authored). Existing
	// boxes saved with one of the omitted tags still RENDER (view.php keeps the broad allowed_tags set);
	// they simply aren't offered for new page content.
	: array( 'div' => 'div', 'section' => 'section', 'article' => 'article', 'aside' => 'aside' );

// Shape-divider / pattern / section-style sources (shared with the classic Section). The shape,
// pattern and style choice/reveal maps come from the Theme-Settings preset libraries, so user-added
// entries appear automatically; each falls back to a built-in set if the getter is unavailable.
$fx_divider_fields = [
	'color'  => function_exists( 'sc_color_field_compact' ) ? sc_color_field_compact( [ 'label' => __( 'Color', 'fw' ), 'kind' => 'bg' ] ) : [ 'type' => 'color-picker', 'label' => __( 'Color', 'fw' ) ],
	'height' => [ 'type' => 'unit-input', 'label' => __( 'Height', 'fw' ), 'units' => [ 'px', 'vh', '%' ], 'value' => [ 'value' => '100', 'unit' => 'px' ] ],
	'flip'   => [ 'type' => 'switch', 'label' => __( 'Flip Horizontally', 'fw' ), 'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ], 'left-choice' => [ 'value' => 'no', 'label' => __( 'No', 'fw' ) ], 'value' => 'no' ],
];
$fx_divider_shapes = function_exists( 'unysonplus_shape_divider_select_choices' ) ? unysonplus_shape_divider_select_choices() : [ 'none' => __( 'None', 'fw' ) ];
$fx_divider_reveal = [];
foreach ( array_keys( $fx_divider_shapes ) as $fx_ds ) { if ( $fx_ds !== 'none' ) { $fx_divider_reveal[ $fx_ds ] = $fx_divider_fields; } }
$fx_divider_ip_top    = function_exists( 'unysonplus_shape_divider_imagepicker_choices' ) ? unysonplus_shape_divider_imagepicker_choices( 'top', 38 ) : [ 'none' => [ 'label' => __( 'None', 'fw' ) ] ];
$fx_divider_ip_bottom = function_exists( 'unysonplus_shape_divider_imagepicker_choices' ) ? unysonplus_shape_divider_imagepicker_choices( 'bottom', 38 ) : [ 'none' => [ 'label' => __( 'None', 'fw' ) ] ];
$fx_pattern_choices   = function_exists( 'unysonplus_pattern_imagepicker_choices' ) ? unysonplus_pattern_imagepicker_choices( 76 ) : [ 'none' => [ 'label' => __( 'None', 'fw' ) ] ];
$fx_variant_choices   = function_exists( 'unysonplus_section_style_choices' ) ? unysonplus_section_style_choices() : [ '' => __( 'Default', 'fw' ), 'alt' => __( 'Alt', 'fw' ), 'light' => __( 'Light', 'fw' ), 'dark' => __( 'Dark', 'fw' ) ];

// Edge Fade size — the same unit-input is revealed under every "Edges" choice, so build it once.
// Percent is the sensible default: the fade then scales with the box instead of being a fixed
// bite out of a narrow one.
$mask_size_field = function () {
	return [
		'type'  => 'unit-input',
		'label' => __( 'Fade Size', 'fw' ),
		'desc'  => __( 'How far in from each edge the fade reaches.', 'fw' ),
		'units' => [ '%', 'px', 'rem' ],
		'value' => [ 'value' => '12', 'unit' => '%' ],
	];
};

$options = [
	'tab_layout' => [
		'title'   => __( 'Layout', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_box' => [
				'type'    => 'group',
				'options' => [
					'html_tag' => [
						'type'    => 'select',
						'label'   => __( 'HTML Tag', 'fw' ),
						'desc'    => __( 'The semantic HTML element this box outputs.', 'fw' ),
						'help'    => __( 'div = plain layout, section = a titled content band, article = self-contained repeatable content (cards, posts), aside = complementary content (callouts, pull-quotes).', 'fw' ),
						'value'   => 'div',
						'choices' => $fx_tag_choices,
					],
					'display' => [
						'type'    => 'select',
						'label'   => __( 'Display', 'fw' ),
						'desc'    => __( 'How this box arranges its children.', 'fw' ),
						'help'    => __( 'Flex lays children on one axis (rows / columns). Grid arranges them in a two-axis grid (set Grid Columns below). Block is normal document flow. Direction / Justify / Align + Gap apply to Flex and Grid; Grid also uses the Columns settings.', 'fw' ),
						'value'   => 'flex',
						'choices' => [
							'flex'  => __( 'Flex', 'fw' ),
							'grid'  => __( 'Grid', 'fw' ),
							'block' => __( 'Block', 'fw' ),
						],
					],
				],
			],
			'group_grid' => [
				'type'    => 'group',
				'options' => [
					'grid_columns' => [
						'type'        => 'text',
						'label'       => __( 'Grid Columns', 'fw' ),
						'desc'        => __( 'The grid track layout (Display = Grid).', 'fw' ),
						'help'        => __( 'A number (e.g. 3 = three equal columns) or a raw grid-template-columns value (e.g. 2fr 1fr). Set 12 for a spanning grid: each child cell then uses its own Width as a track span (1/3 = 4 tracks, 2/3 = 8), so 1/3 + 2/3 tiles exactly.', 'fw' ),
						'value'       => '3',
						'placeholder' => '3',
					],
					'grid_autofit' => [
						'type'         => 'switch',
						'label'        => __( 'Auto-fit Columns', 'fw' ),
						'desc'         => __( 'Fit as many equal columns as the width allows.', 'fw' ),
						'help'         => __( 'Display = Grid only. Ignores the column count and fits as many columns as fit, each at least the Min Column Width below, reflowing responsively with no breakpoints (repeat auto-fit minmax).', 'fw' ),
						'value'        => 'no',
						'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
						'left-choice'  => [ 'value' => 'no',  'label' => __( 'No', 'fw' ) ],
					],
					'grid_min' => [
						'type'  => 'unit-input',
						'label' => __( 'Min Column Width', 'fw' ),
						'desc'  => __( 'Smallest a column gets before Auto-fit drops one.', 'fw' ),
						'help'  => __( 'The min in minmax(min, 1fr). Only used with Display = Grid and Auto-fit on.', 'fw' ),
						'units' => [ 'px', 'rem', 'em', '%' ],
						'value' => [ 'value' => '240', 'unit' => 'px' ],
					],
					'grid_dense' => [
						'type'         => 'switch',
						'label'        => __( 'Dense Packing', 'fw' ),
						'desc'         => __( 'Backfill gaps left by spanned cells (Display = Grid).', 'fw' ),
						'help'         => __( 'grid-auto-flow: dense; pulls later items up into holes left by different-sized / spanned cells. Can reorder items visually.', 'fw' ),
						'value'        => 'no',
						'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
						'right-choice' => [ 'value' => 'yes', 'label' => __( 'On', 'fw' ) ],
					],
				],
			],
			'group_container' => [
				'type'    => 'group',
				'options' => [
					'full_width' => [
						'type'         => 'switch',
						'label'        => __( 'Full-Width Band', 'fw' ),
						'desc'         => __( 'Stretch a Section edge-to-edge while its content stays contained.', 'fw' ),
						'help'         => __( 'Section-tag Div only. ON: the band (its background) spans the full window width, while its content stays contained to the Content Width below — or the site container width if that is empty. This is the classic full-bleed hero / colour-band pattern. OFF (default): the whole Section, background included, is contained to the site container width.', 'fw' ),
						'value'        => 'no',
						'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
						'right-choice' => [ 'value' => 'yes', 'label' => __( 'On', 'fw' ) ],
					],
					// Content Width — a NAMED preset from the shared Container Width library
					// (Narrow / Medium / Wide / Content 1400 / your own), or Custom for an exact
					// max-width. Same control + preset source as the classic Section, so the two
					// stay in sync. 'inherit' = full width (no cap). A legacy flat { value, unit }
					// (saved before this became a preset picker) still resolves — view.php + the
					// editor JS treat it as a Custom width.
					'content_width' => [
						'type'         => 'multi-picker',
						'label'        => false,
						'desc'         => false,
						'value'        => [ 'preset' => 'inherit' ],
						'picker'       => [
							'preset' => [
								'label'   => __( 'Content Width', 'fw' ),
								'desc'    => __( 'Constrain this box\'s content to a centred max-width — pick a named width or Custom. "Inherit" leaves it full width.', 'fw' ),
								'help'    => __( 'Like a container. A section-tag Div contains its content to the site width automatically. For a full-bleed background band WITH contained content, turn ON "Full-Width Band" above (this Content Width then sets how wide the contained content is). Manage the named widths in Theme Settings → Components → Container Widths.', 'fw' ),
								'type'    => 'select',
								'choices' => function_exists( 'unysonplus_container_width_choices' )
									? unysonplus_container_width_choices()
									: array( 'inherit' => __( 'Inherit (global width)', 'fw' ), 'narrow' => __( 'Narrow (768px)', 'fw' ), 'medium' => __( 'Medium (896px)', 'fw' ), 'wide' => __( 'Wide (1024px)', 'fw' ), 'custom' => __( 'Custom', 'fw' ) ),
							],
						],
						'choices'      => [
							// Revealed only when "Custom…" is picked.
							'custom' => [
								'custom_width' => [
									'type'  => 'unit-input',
									'label' => __( 'Custom Width', 'fw' ),
									'desc'  => false,
									'units' => [ 'px', 'rem', '%', 'vw' ],
									'value' => [ 'value' => '900', 'unit' => 'px' ],
								],
							],
						],
						'show_borders' => false,
					],
					'responsive_collapse' => [
						'type'         => 'switch',
						'label'        => __( 'Responsive Collapse', 'fw' ),
						'desc'         => __( 'Automatically stack columns down on smaller screens.', 'fw' ),
						'help'         => __( 'On (default): a multi-column Grid or Flex row steps down on its own — to 2 columns on tablets and a single stacked column on phones — so nothing is cramped on mobile, with no per-device setup. A Grid using Auto-fit already reflows on its own and is left alone. Turn this off to keep the exact column count at every screen size (then use the per-device Width / Direction tabs to tune it by hand).', 'fw' ),
						'value'        => 'yes',
						'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
						'right-choice' => [ 'value' => 'yes', 'label' => __( 'On', 'fw' ) ],
					],
				],
			],
			// Flex-only arrangement — Direction / Wrap / Reverse have no meaning under Grid or Block,
			// so this group is revealed by the reactive JS ONLY when Display = Flex.
			'group_flex' => [
				'type'    => 'group',
				'options' => [
					'direction' => [
						'type'    => 'responsive',
						'label'   => __( 'Direction', 'fw' ),
						'desc'    => __( 'Lay children in a Row or stack them in a Column.', 'fw' ),
						'help'    => __( 'Row places children side-by-side (give each child a Width to split the row, e.g. 1/2 + 1/2); Column stacks them. Use the Phone / Tablet / Desktop tabs to change direction per device, e.g. a Row header that stacks to a Column on mobile.', 'fw' ),
						'value'   => [ 'base' => 'row', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'    => 'image-picker',
							'choices' => [
								'row'    => $fx_pick( $fx_dir_uri( 'row', __( 'Row', 'fw' ) ),       __( 'Row', 'fw' ) ),
								'column' => $fx_pick( $fx_dir_uri( 'column', __( 'Column', 'fw' ) ), __( 'Column', 'fw' ) ),
							],
						],
					],
					'wrap' => [
						'type'    => 'responsive',
						'label'   => __( 'Wrap', 'fw' ),
						'desc'    => __( 'Let children wrap onto the next line when they run out of room.', 'fw' ),
						'help'    => __( 'Rows only. Per-device via the Phone / Tablet / Desktop tabs (a blank device inherits the smaller one).', 'fw' ),
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
						'desc'    => __( 'Reverse the visual order of children.', 'fw' ),
						'help'    => __( 'row-reverse / column-reverse, without changing the markup. Per-device, e.g. image-above-text on phone, text-above-image on desktop.', 'fw' ),
						'value'   => [ 'base' => 'no', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'         => 'switch',
							'left-choice'  => [ 'value' => 'no',  'label' => __( 'Default', 'fw' ) ],
							'right-choice' => [ 'value' => 'yes', 'label' => __( 'Reverse', 'fw' ) ],
						],
					],
				],
			],
			// Shared arrangement — Gap and the Justify / Align distribution apply to BOTH Flex and
			// Grid containers, so this group is revealed whenever Display = Flex OR Grid (hidden for
			// Block). Previously these lived in the flex-only group, so a Grid could not reach Gap.
			'group_arrange' => [
				'type'    => 'group',
				'options' => [
					'gap' => [
						'type'    => 'responsive',
						'label'   => __( 'Gap', 'fw' ),
						'desc'    => __( 'Spacing between children, from the spacing-scale presets.', 'fw' ),
						'help'    => function_exists( 'sc_styling_help_text' ) ? sc_styling_help_text( 'spacing' ) : '',
						'value'   => [ 'base' => '', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'    => 'short-select',
							'choices' => function_exists( 'sc_get_gap_select_choices' )
								? sc_get_gap_select_choices( __( 'None', 'fw' ) )
								: array( '' => __( 'None', 'fw' ) ),
						],
					],
					'row_gap' => [
						'type'    => 'responsive',
						'label'   => __( 'Row Gap', 'fw' ),
						'desc'    => __( 'Vertical spacing between rows; overrides Gap on that axis.', 'fw' ),
						'help'    => __( 'Wrapped Flex lines or Grid rows. Leave Inherit to use the single Gap for both axes. Per-device via the Phone / Tablet / Desktop tabs (a blank device inherits the smaller one).', 'fw' ),
						'value'   => [ 'base' => '', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'    => 'short-select',
							'choices' => function_exists( 'sc_get_gap_select_choices' )
								? sc_get_gap_select_choices( __( 'Inherit', 'fw' ) )
								: array( '' => __( 'Inherit', 'fw' ) ),
						],
					],
					'col_gap' => [
						'type'    => 'responsive',
						'label'   => __( 'Column Gap', 'fw' ),
						'desc'    => __( 'Horizontal spacing between columns; overrides Gap on that axis.', 'fw' ),
						'help'    => __( 'Leave Inherit to use the single Gap for both axes. Per-device via the Phone / Tablet / Desktop tabs (a blank device inherits the smaller one).', 'fw' ),
						'value'   => [ 'base' => '', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'    => 'short-select',
							'choices' => function_exists( 'sc_get_gap_select_choices' )
								? sc_get_gap_select_choices( __( 'Inherit', 'fw' ) )
								: array( '' => __( 'Inherit', 'fw' ) ),
						],
					],
					'justify_content' => [
						'type'    => 'responsive',
						'label'   => __( 'Justify (main axis)', 'fw' ),
						'desc'    => __( 'Distribute children along the main axis.', 'fw' ),
						'help'    => __( 'Start / Center / End / Space between / around / evenly. Per-device via the Phone / Tablet / Desktop tabs (a blank device inherits the smaller one).', 'fw' ),
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
						'desc'    => __( 'Align children on the cross axis.', 'fw' ),
						'help'    => __( 'Start / Center / End / Stretch / Baseline. Per-device via the Phone / Tablet / Desktop tabs (a blank device inherits the smaller one).', 'fw' ),
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
					'align_content' => [
						'type'    => 'responsive',
						'label'   => __( 'Align Content (wrapped lines)', 'fw' ),
						'desc'    => __( 'Pack wrapped lines on the cross axis.', 'fw' ),
						'help'    => __( 'Only affects a wrapped container with 2+ lines (Wrap on). Per-device via the Phone / Tablet / Desktop tabs (a blank device inherits the smaller one).', 'fw' ),
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
						'desc'  => __( 'This box size inside its parent: a Flex width or a Grid span.', 'fw' ),
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
						'desc'    => __( 'Let this box grow to fill leftover space in the row.', 'fw' ),
						'help'    => __( 'flex-grow; overrides a fixed Width when there is room to grow. Per-device via the Phone / Tablet / Desktop tabs (a blank device inherits the smaller one).', 'fw' ),
						'value'   => [ 'base' => 'no', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'         => 'switch',
							'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
							'right-choice' => [ 'value' => 'yes', 'label' => __( 'On', 'fw' ) ],
						],
					],
					'no_shrink' => [
						'type'         => 'switch',
						'label'        => __( 'Prevent Shrinking', 'fw' ),
						'desc'         => __( 'Stop this box shrinking in a Flex row.', 'fw' ),
						'help'         => __( 'flex-shrink: 0; good for a fixed-width sidebar or a logo beside a flexible area. Only applies inside a Flex parent.', 'fw' ),
						'value'        => 'no',
						'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
						'right-choice' => [ 'value' => 'yes', 'label' => __( 'On', 'fw' ) ],
					],
					'flex_basis' => [
						'type'  => 'responsive',
						'label' => __( 'Flex Basis', 'fw' ),
						'desc'  => __( 'The box\'s ideal size before it grows or shrinks along the row.', 'fw' ),
						'help'  => __( 'flex-basis. Unlike Width Override (a hard fixed size), Basis is the STARTING size that "Grow to Fill" can expand and shrinking can reduce — the `flex: 1 1 300px` card pattern (Basis 300px + Grow On + a Min Width so the cards wrap onto new rows cleanly). Only inside a Flex parent. Per-device via the Phone / Tablet / Desktop tabs (a blank device inherits the smaller one).', 'fw' ),
						'value' => [ 'base' => [ 'value' => '', 'unit' => 'px' ], 'md' => [ 'value' => '', 'unit' => 'px' ], 'lg' => [ 'value' => '', 'unit' => 'px' ] ],
						'inner' => [
							'type'  => 'unit-input',
							'units' => [ 'px', 'rem', '%', 'vw' ],
							'value' => [ 'value' => '', 'unit' => 'px' ],
						],
					],
					'min_width' => [
						'type'  => 'responsive',
						'label' => __( 'Min Width', 'fw' ),
						'desc'  => __( 'Smallest this box may shrink to along the row.', 'fw' ),
						'help'  => __( 'min-width. Stops a flexible box collapsing too narrow, and forces it onto the next row when there is no room — the clean way to make a card grid wrap. Per-device via the Phone / Tablet / Desktop tabs (a blank device inherits the smaller one).', 'fw' ),
						'value' => [ 'base' => [ 'value' => '', 'unit' => 'px' ], 'md' => [ 'value' => '', 'unit' => 'px' ], 'lg' => [ 'value' => '', 'unit' => 'px' ] ],
						'inner' => [
							'type'  => 'unit-input',
							'units' => [ 'px', 'rem', '%', 'vw' ],
							'value' => [ 'value' => '', 'unit' => 'px' ],
						],
					],
					'align_self' => [
						'type'    => 'responsive',
						'label'   => __( 'Align Self', 'fw' ),
						'desc'    => __( 'Override the parent cross-axis Align for just this box.', 'fw' ),
						'help'    => __( 'Only inside a Flex parent. Per-device via the Phone / Tablet / Desktop tabs (a blank device inherits the smaller one).', 'fw' ),
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
						'desc'    => __( 'Reorder this box among its siblings.', 'fw' ),
						'help'    => __( '1 to 12 (or First / Last), without changing the markup order. Only inside a Flex parent. Per-device via the Phone / Tablet / Desktop tabs.', 'fw' ),
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
					'col_start' => [
						'type'    => 'responsive',
						'label'   => __( 'Grid Column Start', 'fw' ),
						'desc'    => __( 'Which grid column this box begins at (1–12).', 'fw' ),
						'help'    => __( 'Only inside a Grid parent. Lets you place an item at an exact column — e.g. start at 6 — WITHOUT leaving empty spacer cells before it. Combine with Width Override to set how many columns it spans. Auto lets the grid flow it into the next free cell. Per-device via the Phone / Tablet / Desktop tabs.', 'fw' ),
						'value'   => [ 'base' => '', 'md' => '', 'lg' => '' ],
						'inner'   => [
							'type'    => 'short-select',
							'choices' => [
								''   => __( 'Auto', 'fw' ),
								'1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6',
								'7' => '7', '8' => '8', '9' => '9', '10' => '10', '11' => '11', '12' => '12',
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
			// --- Background: base background layers (always shown, any HTML tag).
			'group_background' => [
				'type'    => 'group',
				'options' => [
					'background'         => [
						'type'  => 'background-pro',
						'label' => __( 'Background', 'fw' ),
						'desc'  => __( 'Color, gradient, image and video background layers (they stack: image over gradient over color). Replaces the old Background Color field — existing flexboxes are migrated automatically.', 'fw' ),
						'help'  => __( 'Image attachment "Fixed" gives a parallax effect. Video renders a muted, looping background; set a poster/fallback image for while it loads or where autoplay is blocked.', 'fw' ),
					],
				],
			],
			// --- Section Style (section-only): a decorative background pattern and a named
			// section-style variant. Parity with the classic Section — the reactive JS reveals this
			// whole group only when HTML Tag = section (both options need the full-band context).
			'group_section_style' => [
				'type'    => 'group',
				'options' => [
					'background_pattern' => [
						'type'         => 'multi-picker',
						'label'        => __( 'Background Pattern', 'fw' ),
						'desc'         => __( 'Overlay a decorative SVG pattern above the background layers (dots, grid, waves…). Manage the set in Theme Settings → Components → Background Patterns.', 'fw' ),
						'popover'      => true,
						'value'        => [ 'pattern' => 'none' ],
						'picker'       => [
							'pattern' => [
								'type'    => 'image-picker',
								'label'   => false,
								'choices' => $fx_pattern_choices,
							],
						],
						'choices'      => [],
						'show_borders' => false,
					],
					'variant'            => [
						'type'    => 'select',
						'label'   => __( 'Section Variant', 'fw' ),
						'desc'    => __( 'Apply a named section style (Alt / Light / Dark …) that themes the background and text colours together. Manage the set in Theme Settings → Components → Section Styles.', 'fw' ),
						'help'    => __( 'Variants add a `section--<slug>` class so a whole band can be recoloured from one place. Leave as Default to inherit the page colours.', 'fw' ),
						'value'   => '',
						'choices' => $fx_variant_choices,
					],
				],
			],
			// --- Shape Dividers (section-only): angled / curved SVG shapes on the top and bottom
			// edges. Parity with the classic Section; revealed only when HTML Tag = section.
			'group_dividers' => [
				'type'    => 'group',
				'options' => [
					'divider_top'    => [
						'type'         => 'multi-picker',
						'label'        => __( 'Top Shape Divider', 'fw' ),
						'desc'         => __( 'Add an angled or curved SVG shape to the top edge of this section.', 'fw' ),
						'popover'      => true,
						'value'        => [ 'shape' => 'none' ],
						'picker'       => [
							'shape' => [
								'type'    => 'image-picker',
								'label'   => false,
								'choices' => $fx_divider_ip_top,
							],
						],
						'choices'      => $fx_divider_reveal,
						'show_borders' => false,
					],
					'divider_bottom' => [
						'type'         => 'multi-picker',
						'label'        => __( 'Bottom Shape Divider', 'fw' ),
						'desc'         => __( 'Add an angled or curved SVG shape to the bottom edge of this section.', 'fw' ),
						'popover'      => true,
						'value'        => [ 'shape' => 'none' ],
						'picker'       => [
							'shape' => [
								'type'    => 'image-picker',
								'label'   => false,
								'choices' => $fx_divider_ip_bottom,
							],
						],
						'choices'      => $fx_divider_reveal,
						'show_borders' => false,
					],
				],
			],
			// --- Box Style: border/shadow preset, minimum height and aspect ratio.
			'group_boxstyle' => [
				'type'    => 'group',
				'options' => [
					'border_preset' => [
						'type'         => 'border-style-picker',
						'label'        => __( 'Border / Box Style', 'fw' ),
						'desc'         => __( 'Apply a reusable box style — border, corners, shadow and an optional background fill (with a hover state) — to this flexbox. Each option previews the real style next to its name. Manage presets in Theme Settings → Styling → Borders.', 'fw' ),
						'value'        => '',
						'show_borders' => false,
						'choices'      => function_exists( 'sc_get_border_preset_choices' ) ? sc_get_border_preset_choices() : array( '' => __( 'None', 'fw' ) ),
					],
					'backdrop_blur' => [
						'type'  => 'unit-input',
						'label' => __( 'Backdrop Blur (Glass)', 'fw' ),
						'desc'  => __( 'Blur whatever shows through this box — the frosted-glass effect.', 'fw' ),
						'help'  => __( 'CSS backdrop-filter: blur(). Pair with a semi-transparent Background (e.g. white at 60%) and a subtle border for a glass card. Empty / 0 = off. Leave the box Background translucent or the blur has nothing to reveal. Older browsers that lack backdrop-filter just show the solid background.', 'fw' ),
						'units' => [ 'px', 'rem' ],
						'value' => [ 'value' => '', 'unit' => 'px' ],
					],
					'min_height' => [
						'type'  => 'responsive',
						'label' => __( 'Min Height', 'fw' ),
						'desc'  => __( 'Minimum height of this box.', 'fw' ),
						'help'  => __( 'Pair with Align (cross axis) = Center to vertically centre content, e.g. 60vh for a hero band. Empty = fit content. Per-device via the Phone / Tablet / Desktop tabs.', 'fw' ),
						'value' => [ 'base' => [ 'value' => '', 'unit' => 'vh' ], 'md' => [ 'value' => '', 'unit' => 'vh' ], 'lg' => [ 'value' => '', 'unit' => 'vh' ] ],
						'inner' => [
							'type'  => 'unit-input',
							'units' => [ 'vh', 'px', 'rem', '%' ],
							'value' => [ 'value' => '', 'unit' => 'vh' ],
						],
					],
					'aspect_ratio' => [
						'type'        => 'text',
						'label'       => __( 'Aspect Ratio', 'fw' ),
						'desc'        => __( 'Lock the box to a width : height ratio.', 'fw' ),
						'help'        => __( 'e.g. 16 / 9, 4 / 3, or 1 for a square. Empty = natural height. Pairs well with a background image / media, or a Grid cell you want kept square.', 'fw' ),
						'value'       => '',
						'placeholder' => '16 / 9',
					],
					// --- Compositing: how this box blends with, is clipped by, or fades into what
					// sits behind it. All three are single scoped declarations keyed to the box's
					// fx-* class (see views/view.php) — no extra markup, no library.
					'blend_mode' => [
						'type'    => 'select',
						'label'   => __( 'Blend Mode', 'fw' ),
						'desc'    => __( 'How this box composites with whatever sits behind it.', 'fw' ),
						'help'    => __( 'CSS mix-blend-mode. Normal = no blending. Overlay / Multiply / Screen tint an image wash into the band colour behind it; Difference gives the inverted "knockout" look. Needs something behind it to blend WITH — a parent background, an image, or another layer. Pair with a Background image + reduced opacity for the classic photo-wash hero.', 'fw' ),
						'value'   => '',
						'choices' => [
							''             => __( 'Normal (no blending)', 'fw' ),
							'multiply'     => __( 'Multiply', 'fw' ),
							'screen'       => __( 'Screen', 'fw' ),
							'overlay'      => __( 'Overlay', 'fw' ),
							'darken'       => __( 'Darken', 'fw' ),
							'lighten'      => __( 'Lighten', 'fw' ),
							'color-dodge'  => __( 'Color Dodge', 'fw' ),
							'color-burn'   => __( 'Color Burn', 'fw' ),
							'hard-light'   => __( 'Hard Light', 'fw' ),
							'soft-light'   => __( 'Soft Light', 'fw' ),
							'difference'   => __( 'Difference', 'fw' ),
							'exclusion'    => __( 'Exclusion', 'fw' ),
							'hue'          => __( 'Hue', 'fw' ),
							'saturation'   => __( 'Saturation', 'fw' ),
							'color'        => __( 'Color', 'fw' ),
							'luminosity'   => __( 'Luminosity', 'fw' ),
						],
					],
					// Clip Shape / Edge Fade follow the SAME multi-picker shape as Content Width above:
					// label:false on the wrapper + the real label/desc/help on the picker sub-option +
					// show_borders:false, so they render as one aligned row (label | control) that lines
					// up with Blend Mode and the rest — not a bordered box with a redundant inner label.
					'clip_shape' => [
						'type'         => 'multi-picker',
						'label'        => false,
						'desc'         => false,
						'value'        => [ 'shape' => 'none' ],
						'picker'       => [
							'shape' => [
								'type'    => 'select',
								'label'   => __( 'Clip Shape', 'fw' ),
								'desc'    => __( 'Cut the box to a shape instead of a rectangle.', 'fw' ),
								'help'    => __( 'CSS clip-path. The named shapes cover the common cases (a circle avatar, a diagonal band edge, a chevron, a corner notch); Custom takes any clip-path value, e.g. polygon(0 0, 100% 0, 100% 85%, 0 100%). The clip applies to the box AND its background — content outside the shape is hidden, so keep padding generous on angled shapes.', 'fw' ),
								'value'   => 'none',
								'choices' => [
									'none'         => __( 'None', 'fw' ),
									'circle'       => __( 'Circle', 'fw' ),
									'ellipse'      => __( 'Ellipse', 'fw' ),
									'diagonal'     => __( 'Diagonal (bottom-left rise)', 'fw' ),
									'diagonal-rev' => __( 'Diagonal (bottom-right rise)', 'fw' ),
									'chevron'      => __( 'Chevron (pointed base)', 'fw' ),
									'notch'        => __( 'Corner notch', 'fw' ),
									'custom'       => __( 'Custom…', 'fw' ),
								],
							],
						],
						'choices'      => [
							'custom' => [
								'clip_custom' => [
									'type'        => 'text',
									'label'       => __( 'clip-path value', 'fw' ),
									'desc'        => __( 'Any CSS clip-path value.', 'fw' ),
									'value'       => '',
									'placeholder' => 'polygon(0 0, 100% 0, 100% 85%, 0 100%)',
								],
							],
						],
						'show_borders' => false,
					],
					'mask_fade' => [
						'type'         => 'multi-picker',
						'label'        => false,
						'desc'         => false,
						'value'        => [ 'edges' => 'none' ],
						'picker'       => [
							'edges' => [
								'type'    => 'select',
								'label'   => __( 'Edge Fade', 'fw' ),
								'desc'    => __( 'Dissolve the box into the background at its edges.', 'fw' ),
								'help'    => __( 'CSS mask-image. Use for logo strips and scrolling rails that should fade out at the ends, or an image that should melt into the section below it. Fade Size is how far in from each edge the fade reaches.', 'fw' ),
								'value'   => 'none',
								'choices' => [
									'none'   => __( 'None', 'fw' ),
									'x'      => __( 'Left + right', 'fw' ),
									'y'      => __( 'Top + bottom', 'fw' ),
									'top'    => __( 'Top only', 'fw' ),
									'bottom' => __( 'Bottom only', 'fw' ),
									'all'    => __( 'All edges', 'fw' ),
								],
							],
						],
						'choices'      => [
							'x'      => [ 'mask_size_x' => $mask_size_field() ],
							'y'      => [ 'mask_size_y' => $mask_size_field() ],
							'top'    => [ 'mask_size_top' => $mask_size_field() ],
							'bottom' => [ 'mask_size_bottom' => $mask_size_field() ],
							'all'    => [ 'mask_size_all' => $mask_size_field() ],
						],
						'show_borders' => false,
					],
				],
			],
			// --- Text: alignment of inline/text content (applies to any HTML tag).
			'group_text' => [
				'type'    => 'group',
				'options' => [
					'text_align' => function_exists( 'sc_alignment_field' )
						? sc_alignment_field( array(
							'label'   => __( 'Text Alignment', 'fw' ),
							'inherit' => true,
							'desc'    => __( 'Horizontal alignment of the text content inside this box.', 'fw' ),
							'help'    => __( 'Inherit keeps the page / parent alignment. This aligns inline & text content; use the Flex / Grid controls on the Layout tab to position child boxes.', 'fw' ),
						) )
						: [
							'type'    => 'select',
							'label'   => __( 'Text Alignment', 'fw' ),
							'value'   => '',
							'choices' => [ '' => __( 'Inherit', 'fw' ), 'left' => __( 'Left', 'fw' ), 'center' => __( 'Center', 'fw' ), 'right' => __( 'Right', 'fw' ) ],
						],
				],
			],
			// --- Spacing: outer margin + inner padding.
			'group_spacing' => [
				'type'    => 'group',
				'options' => [
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
