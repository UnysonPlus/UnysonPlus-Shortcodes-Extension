<?php if (!defined('FW')) { die('Forbidden'); }

/*
 * Pickers for the responsive column controls — all image-pickers with inline
 * data-URI SVG thumbnails (no asset files). The large hover preview is 2x the
 * thumbnail height (set in $pick()), so it's a genuine zoom, not a redundant copy.
 *  - Width / Offset → a 12-cell bar drawn on a 60-unit grid (every cell lands on
 *    a whole pixel) with shape-rendering="crispEdges", so even the 12-cell bars
 *    stay sharp instead of blurring. The fraction is baked in as a label.
 *  - Alignment → text-align / vertical-box glyphs.
 * Choice keys stay the plain strings the view whitelists ('default' / 'none' /
 * 'auto' are the unset / flex sentinels).
 */

// Column-style fraction bar (mirrors the page-builder width thumbnails): the
// chosen column is ONE blue bar, the remainder is split into 1/denominator gray
// bars — so 1/2 = blue + gray, 2/3 = wide blue + narrow gray, etc. (much cleaner
// than a 12-cell strip). k/12 is reduced to lowest terms first. A white
// background keeps it visible on the dark hover-preview tooltip; the fraction
// stays as a label below. $mode: 'width' | 'offset' | 'auto' | 'default'.
$col_bar_uri = function ( $cells_on, $mode, $label ) {
    // Bars are drawn inside a 60-unit "track" (keeps every cell on a whole pixel)
    // with a uniform $pad of white margin on top + both sides, so the tile looks
    // like a centered card. Canvas width = track + 2*pad.
    $track = 60; $pad = 4; $W = $track + 2 * $pad;
    $gap = 2; $barH = 24; $H = $pad + $barH + 14;
    $blue = '#2271b1'; $gray = '#9b9b9b';
    $reduce = array(
        1 => array( 1, 12 ), 2 => array( 1, 6 ),  3 => array( 1, 4 ),  4 => array( 1, 3 ),
        5 => array( 5, 12 ), 6 => array( 1, 2 ),  7 => array( 7, 12 ), 8 => array( 2, 3 ),
        9 => array( 3, 4 ),  10 => array( 5, 6 ), 11 => array( 11, 12 ), 12 => array( 1, 1 ),
    );

    // White backdrop so the tile is opaque on the dark hover tooltip.
    $rects = '<rect x="0" y="0" width="' . $W . '" height="' . $H . '" fill="#ffffff"/>';

    if ( $mode === 'auto' || $mode === 'default' ) {
        $bg = ( $mode === 'auto' ) ? '#46b450' : '#eef0f1';
        $rects .= '<rect x="' . $pad . '" y="' . $pad . '" width="' . $track . '" height="' . $barH . '" fill="' . $bg . '" shape-rendering="crispEdges"/>';
    } elseif ( $mode === 'offset' ) {
        // gap (gray) then the column (blue)
        $b = (int) round( $cells_on / 12 * $track );
        $rects .= '<rect x="' . $pad . '" y="' . $pad . '" width="' . max( 1, $b - $gap ) . '" height="' . $barH . '" fill="' . $gray . '" shape-rendering="crispEdges"/>';
        $rects .= '<rect x="' . ( $pad + $b ) . '" y="' . $pad . '" width="' . ( $track - $b ) . '" height="' . $barH . '" fill="' . $blue . '" shape-rendering="crispEdges"/>';
    } else { // width: blue column + gray remainder cells
        list( $n, $d ) = isset( $reduce[ $cells_on ] ) ? $reduce[ $cells_on ] : array( $cells_on, 12 );
        $fr = array( $n / $d );
        for ( $i = 0; $i < $d - $n; $i++ ) { $fr[] = 1 / $d; }
        $N = count( $fr ); $cum = 0; $prev = 0;
        for ( $i = 0; $i < $N; $i++ ) {
            $cum += $fr[ $i ];
            $b   = ( $i === $N - 1 ) ? $track : (int) round( $cum * $track );
            $bw  = ( $N === 1 ) ? ( $b - $prev ) : max( 1, ( $b - $prev ) - $gap );
            $rects .= '<rect x="' . ( $pad + $prev ) . '" y="' . $pad . '" width="' . $bw . '" height="' . $barH . '" fill="' . ( $i === 0 ? $blue : $gray ) . '" shape-rendering="crispEdges"/>';
            $prev = $b;
        }
    }

    $text = '<text x="' . ( $W / 2 ) . '" y="' . ( $pad + $barH + 11 ) . '" text-anchor="middle" font-family="-apple-system,Segoe UI,Roboto,sans-serif" font-size="11" fill="#50575e">' . $label . '</text>';
    $svg  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $W . ' ' . $H . '" width="' . $W . '" height="' . $H . '">' . $rects . $text . '</svg>';
    return 'data:image/svg+xml,' . rawurlencode( $svg );
};

// Shared visual language for the alignment glyphs (matches the hand-drawn
// reference): blue (#2271b1) = the page-builder SECTION, gray (#bdbdbd) = the
// COLUMN, white (#fff) = the ELEMENT with a #8c8c8c line for its text. Rounded
// corners + a #dcdcde hairline throughout; a small caption sits under each icon.
$rrect = function ( $x, $y, $w, $h, $rx, $fill, $stroke = '' ) {
    return '<rect x="' . round( $x, 1 ) . '" y="' . round( $y, 1 ) . '" width="' . round( $w, 1 )
        . '" height="' . round( $h, 1 ) . '" rx="' . $rx . '" fill="' . $fill . '"'
        . ( $stroke !== '' ? ' stroke="' . $stroke . '"' : '' ) . '/>';
};

// A white element box + its #8c8c8c text line.
$glyph_el = function ( $x, $y, $w, $h ) use ( $rrect ) {
    $ly = $y + $h / 2;
    return $rrect( $x, $y, $w, $h, 2, '#ffffff', '#dcdcde' )
        . '<line x1="' . round( $x + 6, 1 ) . '" y1="' . round( $ly, 1 ) . '" x2="' . round( $x + $w - 6, 1 )
        . '" y2="' . round( $ly, 1 ) . '" stroke="#8c8c8c" stroke-width="1.5" stroke-linecap="round"/>';
};

// Caption + <svg> wrapper. $icon_h = icon band height; the caption sits in the
// 16px below it.
$glyph_svg = function ( $inner, $label, $w = 120, $icon_h = 50 ) {
    $h = $icon_h + 16;
    $inner .= '<text x="' . ( $w / 2 ) . '" y="' . ( $icon_h + 11 ) . '" text-anchor="middle" '
        . 'font-family="-apple-system,Segoe UI,Roboto,sans-serif" font-size="11" fill="#50575e">' . $label . '</text>';
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $w . ' ' . $h . '" width="' . $w . '" height="' . $h . '">' . $inner . '</svg>';
    return 'data:image/svg+xml,' . rawurlencode( $svg );
};

// Horizontal content-alignment glyph: blue SECTION → full gray COLUMN → a white
// ELEMENT at the TOP of the column, placed left / center / right (a half-width
// bar). 'default' fills the width — identical to every other alignment's default.
$align_uri = function ( $align, $label ) use ( $rrect, $glyph_el, $glyph_svg ) {
    $w = 120; $icon_h = 50;
    $svg = $rrect( 1, 1, $w - 2, $icon_h - 2, 4, '#2271b1' );            // blue section
    $gx = 7; $bt = 7; $bb = $icon_h - 7; $gw = $w - 2 * $gx; // thin, even side padding (matches top/bottom)
    $svg .= $rrect( $gx, $bt, $gw, $bb - $bt, 3, '#bdbdbd', '#dcdcde' ); // full gray column

    // Element at the TOP of the column; horizontal position shows the alignment.
    $ix = $gx + 5; $iw = $gw - 10; $eh = 9; $ey = $bt + 4;
    if ( in_array( $align, array( 'between', 'around', 'evenly' ), true ) ) {
        // Distribute three elements across the width (space-between/around/evenly).
        $n = 3; $ew = $iw * 0.2; $free = $iw - $n * $ew;
        if ( 'between' === $align )     { $gap = $free / ( $n - 1 ); $x0 = $ix; }
        elseif ( 'around' === $align )  { $gap = $free / $n;         $x0 = $ix + $gap / 2; }
        else /* evenly */               { $gap = $free / ( $n + 1 ); $x0 = $ix + $gap; }
        for ( $i = 0; $i < $n; $i++ ) {
            $svg .= $glyph_el( $x0 + $i * ( $ew + $gap ), $ey, $ew, $eh );
        }
    } else {
        if ( $align === 'center' )    { $ew = $iw * 0.5; $ex = $ix + ( $iw - $ew ) / 2; }
        elseif ( $align === 'start' ) { $ew = $iw * 0.5; $ex = $ix; }
        elseif ( $align === 'end' )   { $ew = $iw * 0.5; $ex = $ix + $iw - $ew; }
        else                          { $ew = $iw; $ex = $ix; } // default → fills the width

        $svg .= $glyph_el( $ex, $ey, $ew, $eh );
    }
    return $glyph_svg( $svg, $label, $w, $icon_h );
};

// image-picker choice entry. Same SVG for thumb + hover; the large (hover)
// preview is rendered at 3x the thumbnail height.
$pick = function ( $uri, $label, $small_h = 45, $large_h = null ) {
    if ( $large_h === null ) { $large_h = $small_h * 3; }
    return array(
        'small' => array( 'src' => $uri, 'height' => $small_h ),
        'large' => array( 'src' => $uri, 'height' => $large_h ),
        'label' => $label,
    );
};

// Content horizontal alignment choices (text-align style glyphs).
$halign_choices = array(
    'default' => $pick( $align_uri( 'default', __( 'Default', 'fw' ) ), __( 'Default', 'fw' ), 64, 150 ),
    'start'   => $pick( $align_uri( 'start',   __( 'Left', 'fw' ) ),    __( 'Left', 'fw' ), 64, 150 ),
    'center'  => $pick( $align_uri( 'center',  __( 'Center', 'fw' ) ),  __( 'Center', 'fw' ), 64, 150 ),
    'end'     => $pick( $align_uri( 'end',     __( 'Right', 'fw' ) ),   __( 'Right', 'fw' ), 64, 150 ),
    // Distribute — only apply on the main axis (Content Direction = Inline/Row); the view
    // guards them so they never emit an invalid align-items-{between…} in the stacked default.
    'between' => $pick( $align_uri( 'between', __( 'Space Between', 'fw' ) ), __( 'Space Between', 'fw' ), 64, 150 ),
    'around'  => $pick( $align_uri( 'around',  __( 'Space Around', 'fw' ) ),  __( 'Space Around', 'fw' ), 64, 150 ),
    'evenly'  => $pick( $align_uri( 'evenly',  __( 'Space Evenly', 'fw' ) ),  __( 'Space Evenly', 'fw' ), 64, 150 ),
);

// Vertical-alignment glyph: blue section → gray column → white element(s).
// Column VA ('self') moves / stretches the GRAY COLUMN itself within the section;
// Content VA ('content') keeps a full-height column and moves the white ELEMENT
// inside it. The default state of every alignment (Column Stretched, Content
// Top/Default, Horizontal Default) looks identical: full column, element at top.
$valign_uri = function ( $variant, $mode, $label ) use ( $rrect, $glyph_el, $glyph_svg ) {
    $w = 120; $icon_h = 50;
    $svg = $rrect( 1, 1, $w - 2, $icon_h - 2, 4, '#2271b1' ); // blue section

    $gx = 7; $gw = $w - 2 * $gx;             // column x / width (thin, even side padding)
    $bt = 7; $bb = $icon_h - 7;              // column travel band within the section (7..43)
    $ex = $gx + 5; $ew = $gw - 10; $eh = 9;  // white element x / width / height
    $etop = $bt + 4;                         // element "top" y (the shared default look)

    if ( $variant === 'self' ) {
        // Column VA: the gray column itself moves / stretches; element rides inside.
        if ( $mode === 'stretch' ) {
            $gy = $bt; $gh = $bb - $bt; $wy = $etop;          // full column, element at top
        } else {
            $gh = $eh + 8;                                     // short column hugging the element
            if ( $mode === 'bottom' )     { $gy = $bb - $gh; }
            elseif ( $mode === 'middle' ) { $gy = ( $bt + $bb ) / 2 - $gh / 2; }
            else                          { $gy = $bt; }       // top
            $wy = $gy + ( $gh - $eh ) / 2;                     // element centered in the short column
        }
        $svg .= $rrect( $gx, $gy, $gw, $gh, 3, '#bdbdbd', '#dcdcde' );
        $svg .= $glyph_el( $ex, $wy, $ew, $eh );
    } else {
        // Content VA: a full-height column; the white element(s) move inside it.
        $svg .= $rrect( $gx, $bt, $gw, $bb - $bt, 3, '#bdbdbd', '#dcdcde' );
        $top = $etop; $bottom = $bb - 4;
        if ( $mode === 'between' ) {
            $svg .= $glyph_el( $ex, $top, $ew, $eh );
            $svg .= $glyph_el( $ex, $bottom - $eh, $ew, $eh );
        } else {
            $ey = $top;
            if ( $mode === 'middle' )     { $ey = ( $top + $bottom ) / 2 - $eh / 2; }
            elseif ( $mode === 'bottom' ) { $ey = $bottom - $eh; }
            $svg .= $glyph_el( $ex, $ey, $ew, $eh );
        }
    }

    return $glyph_svg( $svg, $label, $w, $icon_h );
};

// Column self vertical-alignment choices (one bar in a box).
$colvalign_choices = array(
    // Glyph uses 'stretch' (full-height blue box) because a column's default
    // align-self IS stretch — it fills the row height. Stored key stays 'default'.
    'default' => $pick( $valign_uri( 'self', 'stretch', __( 'Default / Stretched', 'fw' ) ), __( 'Default', 'fw' ), 64, 150 ),
    'start'   => $pick( $valign_uri( 'self', 'top',     __( 'Top', 'fw' ) ),     __( 'Top', 'fw' ), 64, 150 ),
    'center'  => $pick( $valign_uri( 'self', 'middle',  __( 'Middle', 'fw' ) ),  __( 'Middle', 'fw' ), 64, 150 ),
    'end'     => $pick( $valign_uri( 'self', 'bottom',  __( 'Bottom', 'fw' ) ),  __( 'Bottom', 'fw' ), 64, 150 ),
    // 'Stretch' removed — it's identical to 'Default' (the row's default
    // align-items is stretch), so it can't be visibly distinguished.
);

// Content vertical-alignment choices (two stacked bars in a box).
$contentvalign_choices = array(
    // 'Top / Default' (key 'default') = natural top, the unset/no-flex sentinel.
    // The old separate 'start' (Top) choice was dropped — it was identical to this.
    'default' => $pick( $valign_uri( 'content', 'top',     __( 'Top / Default', 'fw' ) ), __( 'Top / Default', 'fw' ), 64, 150 ),
    'center'  => $pick( $valign_uri( 'content', 'middle',  __( 'Middle', 'fw' ) ),        __( 'Middle', 'fw' ), 64, 150 ),
    'end'     => $pick( $valign_uri( 'content', 'bottom',  __( 'Bottom', 'fw' ) ),        __( 'Bottom', 'fw' ), 64, 150 ),
    'between' => $pick( $valign_uri( 'content', 'between', __( 'Space Between', 'fw' ) ),  __( 'Space Between', 'fw' ), 64, 150 ),
);

// Content-direction glyph: blue section → gray column → white elements either
// STACKED (three bars top-to-bottom = the default vertical flow) or INLINE (three
// boxes side-by-side = a flex row). Same visual language as the alignment glyphs.
$dir_uri = function ( $mode, $label ) use ( $rrect, $glyph_svg ) {
    $w = 120; $icon_h = 50;
    $svg = $rrect( 1, 1, $w - 2, $icon_h - 2, 4, '#2271b1' );            // blue section
    $gx = 7; $gw = $w - 2 * $gx; $bt = 7; $bb = $icon_h - 7;             // thin, even side padding (matches top/bottom)
    $svg .= $rrect( $gx, $bt, $gw, $bb - $bt, 3, '#bdbdbd', '#dcdcde' ); // gray column
    $ix = $gx + 5; $iw = $gw - 10; $iy = $bt + 4; $ih = ( $bb - 4 ) - ( $bt + 4 );
    $n = 3;
    if ( $mode === 'row' ) {
        $gap = 4; $ew = ( $iw - ( $n - 1 ) * $gap ) / $n;
        for ( $i = 0; $i < $n; $i++ ) {
            $svg .= $rrect( $ix + $i * ( $ew + $gap ), $iy, $ew, $ih, 2, '#ffffff', '#dcdcde' );
        }
    } else {
        $gap = 5; $eh = ( $ih - ( $n - 1 ) * $gap ) / $n;
        for ( $i = 0; $i < $n; $i++ ) {
            $svg .= $rrect( $ix, $iy + $i * ( $eh + $gap ), $iw, $eh, 2, '#ffffff', '#dcdcde' );
        }
    }
    return $glyph_svg( $svg, $label, $w, $icon_h );
};

// Content-direction choices (Stacked = default first, then Inline). Keys stay the
// 'column'/'row' the view whitelists — same scalar values the old switch stored,
// so existing saved columns map straight through (no migration).
$direction_choices = array(
    'column' => $pick( $dir_uri( 'column', __( 'Stacked', 'fw' ) ), __( 'Stacked', 'fw' ), 64, 150 ),
    'row'    => $pick( $dir_uri( 'row',    __( 'Inline', 'fw' ) ),   __( 'Inline', 'fw' ), 64, 150 ),
);

// Responsive width choices: Default + 1/12…12/12 + Auto (image-picker).
// 45px thumbnails (1.5× the 30px base) so the bars read clearly.
$width_choices = array( 'default' => $pick( $col_bar_uri( 0, 'default', __( 'Default', 'fw' ) ), __( 'Default', 'fw' ), 45 ) );
for ( $i = 1; $i <= 12; $i++ ) {
    $width_choices[ (string) $i ] = $pick( $col_bar_uri( $i, 'width', $i . '/12' ), $i . '/12', 45 );
}
$width_choices['auto'] = $pick( $col_bar_uri( 0, 'auto', __( 'Auto', 'fw' ) ), __( 'Auto', 'fw' ), 45 );

// Offset choices: None + 1/12…11/12 (image-picker).
$offset_choices = array( 'none' => $pick( $col_bar_uri( 0, 'default', __( 'None', 'fw' ) ), __( 'None', 'fw' ), 45 ) );
for ( $i = 1; $i <= 11; $i++ ) {
    $offset_choices[ (string) $i ] = $pick( $col_bar_uri( $i, 'offset', $i . '/12' ), $i . '/12', 45 );
}

// Trigger labels (value => label) for the popover-collapsed width/offset pickers.
$frac_name     = array( 3 => '1/4', 4 => '1/3', 6 => '1/2', 8 => '2/3', 9 => '3/4', 12 => __( 'Full', 'fw' ) );
$width_summary = array( 'default' => __( 'Default', 'fw' ), 'auto' => __( 'Auto', 'fw' ) );
for ( $i = 1; $i <= 12; $i++ ) {
    $width_summary[ (string) $i ] = $i . '/12' . ( isset( $frac_name[ $i ] ) ? ' (' . $frac_name[ $i ] . ')' : '' );
}
$offset_summary = array( 'none' => __( 'None', 'fw' ) );
for ( $i = 1; $i <= 11; $i++ ) {
    $offset_summary[ (string) $i ] = $i . '/12';
}

// Mobile Order stays a plain select (First/Last are textual).
$order_choices = array(
    ''      => __( 'Default', 'fw' ),
    'first' => __( 'First', 'fw' ),
    '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6',
    '7' => '7', '8' => '8', '9' => '9', '10' => '10', '11' => '11', '12' => '12',
    'last'  => __( 'Last', 'fw' ),
);

$options = array(

	/*
    // --- Layout Tab ---
    'tab_layout' => array(
        'title'   => __('Layout', 'fw'),
        'type'    => 'tab',
        'options' => array(
            
            // Column Width
            'column_width' => array(
                'type'    => 'select',
                'label'   => __('Column Width', 'fw'),
                'desc'    => __('Set the width of the column at each breakpoint.', 'fw'),
                'value'   => '',
                'choices' => array(
                    ''  => __('Auto', 'fw'),
                    'col-1' => '1/12',
                    'col-2' => '2/12',
                    'col-3' => '3/12',
                    'col-4' => '4/12',
                    'col-5' => '5/12',
                    'col-6' => '6/12',
                    'col-7' => '7/12',
                    'col-8' => '8/12',
                    'col-9' => '9/12',
                    'col-10'=> '10/12',
                    'col-11'=> '11/12',
                    'col-12'=> '12/12',
                )
            ),

            // Offset
            'offset' => array(
                'type' => 'text',
                'label' => __('Offset (e.g., offset-md-2)', 'fw'),
                'desc'  => __('Add offset classes manually if needed.', 'fw'),
            ),

            // Order
            'order' => array(
                'type' => 'text',
                'label' => __('Order (e.g., order-lg-1)', 'fw'),
                'desc'  => __('Add order classes manually if needed.', 'fw'),
            ),

            // Content Alignment
            'alignment' => array(
                'type'    => 'select',
                'label'   => __('Content Alignment', 'fw'),
                'desc'    => __('Align content horizontally in the column.', 'fw'),
                'value'   => '',
                'choices' => array(
                    '' => __('None', 'fw'),
                    'text-start' => __('Start', 'fw'),
                    'text-center' => __('Center', 'fw'),
                    'text-end' => __('End', 'fw'),
                ),
            ),

            // Vertical Alignment
            'vertical_align' => array(
                'type'    => 'select',
                'label'   => __('Vertical Alignment', 'fw'),
                'desc'    => __('Align items vertically using flex utilities.', 'fw'),
                'value'   => '',
                'choices' => array(
                    '' => __('None', 'fw'),
                    'align-items-start' => __('Top', 'fw'),
                    'align-items-center' => __('Center', 'fw'),
                    'align-items-end' => __('Bottom', 'fw'),
                    'align-items-stretch' => __('Stretch', 'fw'),
                ),
            ),

            // Height
            'height' => array(
                'type'         => 'switch',
                'label'        => __('Height', 'fw'),
                'desc'         => __('Set the height of the column.', 'fw'),
                'value'        => '',
                'left-choice'  => array('value'=>'', 'label'=>__('Auto', 'fw')),
                'right-choice' => array('value'=>'h-100', 'label'=>__('Full', 'fw')),
            ),

        ),
    ),

    // --- Spacing Tab ---
    'tab_spacing' => array(
        'title'   => __('Spacing', 'fw'),
        'type'    => 'tab',
        'options' => array(
            'spacing' => sc_option_spacing(array('all'=>array('value'=>'py-2')))
        ),
    ),

    // --- Display / Visibility Tab ---
    'tab_display' => array(
        'title'   => __('Display', 'fw'),
        'type'    => 'tab',
        'options' => array(
            'display' => array(
                'type'    => 'multi-picker',
                'label'   => false,
                'desc'    => false,
                'value'   => array('selected'=>'d-none','d-none'=>''),
                'picker'  => array(
                    'selected' => array(
                        'type'    => 'select',
                        'label'   => __('Display', 'fw'),
                        'choices' => array(
                            '' => __('Default', 'fw'),
                            'd-none' => __('None', 'fw'),
                            'd-block' => __('Block', 'fw'),
                            'd-inline' => __('Inline', 'fw'),
                            'd-inline-block' => __('Inline Block', 'fw'),
                            'd-flex' => __('Flex', 'fw'),
                        ),
                    ),
                ),
                'choices' => array(
                    'd-none' => array(),
                    'd-block' => array(),
                    'd-flex' => array(),
                ),
            ),
            'visibility' => sc_option_visibility(),
        ),
    ),

    // --- Background Tab ---
    'tab_background' => array(
        'title'   => __('Background', 'fw'),
        'type'    => 'tab',
        'options' => array(
            'bg' => sc_option_color_select('Background', 'bg'),
        ),
    ),

    // --- Border Tab ---
    'tab_border' => array(
        'title'   => __('Border', 'fw'),
        'type'    => 'tab',
        'options' => array(
            'border' => array(
                'type' => 'multi',
                'label'=> false,
                'value'=> array(),
                'desc' => false,
                'inner-options' => array(
                    'side'   => sc_option_box_border('Border Sides'),
                    'color'  => sc_option_color_select('Border', 'border'),
                    'width'  => array(
                        'type'  => 'short-text',
                        'label' => __('Width (px)', 'fw'),
                        'value' => '',
                        'desc'  => __('Set border width in pixels.', 'fw'),
                    ),
                    'radius' => sc_option_box_border_radius('Border Radius'),
                ),
            ),
        ),
    ),

    // --- Text Tab ---
    'tab_text' => array(
        'title'   => __('Text', 'fw'),
        'type'    => 'tab',
        'options' => array(
            'text_color' => sc_option_color_select('Text Color', 'text'),
            'font_weight' => array(
                'type' => 'select',
                'label'=> __('Font Weight', 'fw'),
                'value'=> '',
                'choices'=> array(
                    '' => __('Default', 'fw'),
                    'fw-light'=>__('Light','fw'),
                    'fw-normal'=>__('Normal','fw'),
                    'fw-bold'=>__('Bold','fw'),
                ),
            ),
            'font_style' => array(
                'type' => 'select',
                'label'=> __('Font Style', 'fw'),
                'value'=> '',
                'choices'=> array(
                    '' => __('Default','fw'),
                    'fst-italic'=>__('Italic','fw'),
                    'fst-normal'=>__('Normal','fw'),
                ),
            ),
        ),
    ),

    // --- Effects Tab ---
    'tab_effects' => array(
        'title'   => __('Effects', 'fw'),
        'type'    => 'tab',
        'options' => array(
            'shadow' => array(
                'type'  => 'select',
                'label' => __('Box Shadow', 'fw'),
                'value' => '',
                'choices'=> array(
                    '' => __('None','fw'),
                    'shadow-sm'=>__('Small','fw'),
                    'shadow'=>__('Medium','fw'),
                    'shadow-lg'=>__('Large','fw'),
                ),
            ),
            'opacity' => array(
                'type'=>'select',
                'label'=>__('Opacity','fw'),
                'value'=>'',
                'choices'=>array(
                    ''=>'Default',
                    'opacity-25'=>'25%',
                    'opacity-50'=>'50%',
                    'opacity-75'=>'75%',
                    'opacity-100'=>'100%',
                ),
            ),
        ),
    ),

    // --- Position Tab ---
    'tab_position' => array(
        'title'   => __('Position', 'fw'),
        'type'    => 'tab',
        'options' => array(
            'position' => array(
                'type'=>'select',
                'label'=>__('Position','fw'),
                'value'=>'',
                'choices'=>array(
                    ''=>'Default',
                    'position-static'=>'Static',
                    'position-relative'=>'Relative',
                    'position-absolute'=>'Absolute',
                    'position-fixed'=>'Fixed',
                    'position-sticky'=>'Sticky',
                ),
            ),
        ),
    ),
	*/

    'tab_layout' => [
        'title'   => __( 'Layout', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'full_height' => [
                'type'  => 'switch',
                'label' => __( 'Full Height', 'fw' ),
                'desc'  => __( 'Stretches this column\'s inner content area to the full height of its row, so colored cards line up at equal heights next to sibling columns. Adds the Bootstrap `h-100` class to the column\'s inner wrapper; the wrapper is auto-created when no other Styling pick triggers it.', 'fw' ),
                'help'  => __( 'Turn this on for every column in a row when you want matching card heights regardless of text length. Has no visible effect on a single-column row.', 'fw' ),
                'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                'left-choice'  => [ 'value' => 'no',  'label' => __( 'No',  'fw' ) ],
                'value'        => 'no',
            ],

            // ---- Alignment (content + column) — placed first, right after Full
            // Height, because Content Alignment is the primary, recommended way to
            // align a whole column's content at once (especially to center it). ----
            'group_alignment' => [
                'type'    => 'group',
                'options' => [
                    'content_direction' => [
                        'type'    => 'image-picker',
                        'label'   => __( 'Content Direction', 'fw' ),
                        'desc'    => __( 'Stack the column\'s elements vertically (default) or lay them out inline in a row.', 'fw' ),
                        'help'    => __( 'Inline places elements side by side — e.g. two buttons — wrapping onto the next line if they don\'t fit. Content Alignment still positions them horizontally; Content Vertical Alignment positions them on the cross axis. Pair with Gap for spacing between them.', 'fw' ),
                        'value'   => 'column',
                        'choices' => $direction_choices,
                    ],
                    'content_order' => [
                        'type'    => 'select',
                        'label'   => __( 'Content Order', 'fw' ),
                        'desc'    => __( 'Show this column\'s elements in reverse order without changing the markup. It flips the stack (Stacked direction) or swaps the row (Inline direction). "On mobile only" reverses just on phones — handy for e.g. an image above the text on mobile without splitting them into separate columns.', 'fw' ),
                        'value'   => '',
                        'choices' => array(
                            ''       => __( 'Default', 'fw' ),
                            'all'    => __( 'Reversed (all screens)', 'fw' ),
                            'tablet' => __( 'Reversed on tablet and mobile', 'fw' ),
                            'mobile' => __( 'Reversed on mobile only', 'fw' ),
                        ),
                    ],
                    'content_h' => [
                        'type'    => 'image-picker',
                        'label'   => __( 'Content Alignment', 'fw' ),
                        'desc'    => __( 'Align the whole column\'s content at once — the simplest way to center it.', 'fw' ),
                        'help'    => __( 'Set this to Center and the whole column\'s content centers as one, including a Special Heading\'s overline. This is usually all a centered section needs — only reach for an element\'s own alignment when a single item has to differ from the rest of the column.', 'fw' ),
                        'value'   => 'default',
                        'choices' => $halign_choices,
                    ],
                    'content_v' => [
                        'type'    => 'image-picker',
                        'label'   => __( 'Content Vertical Alignment', 'fw' ),
                        'desc'    => __( 'Position the elements within the column height.', 'fw' ),
                        'help'    => __( '"Top / Default" keeps them at the top. Middle / Bottom / Space Between only show when the column is taller than its content (equal-height row or Full Height on); Space Between spreads 2+ elements from top to bottom.', 'fw' ),
                        'value'   => 'default',
                        'choices' => $contentvalign_choices,
                    ],
                    'content_gap' => [
                        'type'    => 'short-select',
                        'label'   => __( 'Gap', 'fw' ),
                        'desc'    => __( 'Space between the column\'s elements. Uses the site Gap Scale (Theme Settings → General → Spacing → Gaps).', 'fw' ),
                        'help'    => __( 'Works in both Stacked and Inline directions. Sizes follow your Gap Scale, so changing the scale in Theme Settings updates every column at once. Only takes effect once the column has 2+ elements.', 'fw' ),
                        'value'   => '',
                        'choices' => function_exists( 'sc_get_gap_select_choices' )
                            ? sc_get_gap_select_choices( __( 'None', 'fw' ) )
                            : array( '' => __( 'None', 'fw' ) ),
                    ],
                    'align_self' => [
                        'type'    => 'image-picker',
                        'label'   => __( 'Column Vertical Alignment', 'fw' ),
                        'desc'    => __( 'Align this column against its row siblings.', 'fw' ),
                        'help'    => __( 'Only visible when the row has unequal-height columns. Default stretches to match the tallest; Top / Middle / Bottom position the column without stretching.', 'fw' ),
                        'value'   => 'default',
                        'choices' => $colvalign_choices,
                    ],
                ],
            ],

            'mobile_order' => [
                'type'    => 'short-select',
                'label'   => __( 'Mobile Order', 'fw' ),
                'desc'    => __( 'Reorder this column on mobile (under 768px). Columns return to their normal order on larger screens.', 'fw' ),
                'help'    => __( '"Default" keeps the natural order. Lower numbers appear first; "First"/"Last" jump to the ends. For predictable results, set a value on each column in the row (columns left at Default group together first).', 'fw' ),
                'value'   => '',
                'choices' => $order_choices,
            ],

            // ---- Responsive width (overrides the width picker per device) ----
            'group_responsive_width' => [
                'type'    => 'group',
                'options' => [
                    'w_phone' => [
                        'type'          => 'popover',
                        'label'         => __( 'Width — Phone', 'fw' ),
                        'desc'          => __( 'Custom Column width overrides on phones.', 'fw' ),
                        'help'          => __( 'Phones (under 576px). "Default" inherits full width. The width picker sets the small/tablet-up default.', 'fw' ),
                        'value'         => 'default',
                        'summary'       => $width_summary,
                        'inner-options' => [
                            'width' => [ 'type' => 'image-picker', 'label' => false, 'value' => 'default', 'choices' => $width_choices ],
                        ],
                    ],
                    'w_tablet' => [
                        'type'          => 'popover',
                        'label'         => __( 'Width — Tablet', 'fw' ),
                        'desc'          => __( 'Custom Column width overrides on tablets & up.', 'fw' ),
                        'help'          => __( 'Overrides the column width from the tablet breakpoint up (md, ≥768px). "Default" keeps the inherited width.', 'fw' ),
                        'value'         => 'default',
                        'summary'       => $width_summary,
                        'inner-options' => [
                            'width' => [ 'type' => 'image-picker', 'label' => false, 'value' => 'default', 'choices' => $width_choices ],
                        ],
                    ],
                    'w_desktop' => [
                        'type'          => 'popover',
                        'label'         => __( 'Width — Desktop', 'fw' ),
                        'desc'          => __( 'Custom Column width overrides on desktops & up.', 'fw' ),
                        'help'          => __( 'Overrides the column width from the desktop breakpoint up (lg, ≥992px). "Default" keeps the inherited width.', 'fw' ),
                        'value'         => 'default',
                        'summary'       => $width_summary,
                        'inner-options' => [
                            'width' => [ 'type' => 'image-picker', 'label' => false, 'value' => 'default', 'choices' => $width_choices ],
                        ],
                    ],
                ],
            ],

            // ---- Offset (indent the column) ----
            'group_offset' => [
                'type'    => 'group',
                'options' => [
                    'offset_phone' => [
                        'type'          => 'popover',
                        'label'         => __( 'Offset — Phone', 'fw' ),
                        'desc'          => __( 'Indent the column on phones.', 'fw' ),
                        'help'          => __( 'Pushes the column right by this many 12ths on phones (under 576px). "None" = no indent.', 'fw' ),
                        'value'         => 'none',
                        'summary'       => $offset_summary,
                        'inner-options' => [
                            'offset' => [ 'type' => 'image-picker', 'label' => false, 'value' => 'none', 'choices' => $offset_choices ],
                        ],
                    ],
                    'offset_tablet' => [
                        'type'          => 'popover',
                        'label'         => __( 'Offset — Tablet', 'fw' ),
                        'desc'          => __( 'Indent from tablet up.', 'fw' ),
                        'help'          => __( 'Pushes the column right by this many 12ths from the tablet breakpoint up (md, ≥768px). "None" keeps the inherited offset.', 'fw' ),
                        'value'         => 'none',
                        'summary'       => $offset_summary,
                        'inner-options' => [
                            'offset' => [ 'type' => 'image-picker', 'label' => false, 'value' => 'none', 'choices' => $offset_choices ],
                        ],
                    ],
                    'offset_desktop' => [
                        'type'          => 'popover',
                        'label'         => __( 'Offset — Desktop', 'fw' ),
                        'desc'          => __( 'Indent from desktop up.', 'fw' ),
                        'help'          => __( 'Pushes the column right by this many 12ths from the desktop breakpoint up (lg, ≥992px). "None" keeps the inherited offset.', 'fw' ),
                        'value'         => 'none',
                        'summary'       => $offset_summary,
                        'inner-options' => [
                            'offset' => [ 'type' => 'image-picker', 'label' => false, 'value' => 'none', 'choices' => $offset_choices ],
                        ],
                    ],
                ],
            ],

        ],
    ],
    'tab_styling' => [
        'title'   => __( 'Styling', 'fw' ),
        'type'    => 'tab',
        // Column is a layout container — Typography & Colors on the wrapper
        // would cascade to every nested shortcode/element, which is rarely
        // what the editor wants. Background + Margins/Paddings stay; child
        // shortcodes own their own typography.
        'options' => [
            'group_colors' => [
                'type'    => 'group',
                'options' => [
                    'bg_color' => sc_color_field_compact( array( 'label' => __( 'Background Color', 'fw' ), 'kind' => 'bg' ) ),
                ],
            ],
            // Border Preset — a reusable border + corners + shadow style (with a
            // Default/Hover state) defined in Theme Settings → General → Borders.
            // Lands on the column's inner card wrapper as a `.boxp-{name}` class
            // (auto-creates the inner wrapper). Replaces the old manual Border /
            // Color / Width / Rounded / Shadow selects (those still RENDER for any
            // column saved before this, but are no longer editable here).
            'group_border_effects' => [
                'type'    => 'group',
                'options' => [
                    'border_preset' => [
                        'type'         => 'border-style-picker',
                        'label'        => __( 'Box Preset', 'fw' ),
                        'desc'         => __( 'Apply a reusable box style — border, corners, shadow and an optional background fill (with a hover state) — to the column card. Each option previews the real style next to its name.', 'fw' ),
                        'help'         => __( 'Box Presets live in Theme Settings → Components → Box Presets — the theme ships a few (Card, Outline, Soft Shadow, Hover Lift) and you can add your own with full Default/Hover control, a background fill and Custom CSS. Change a preset there and every element using it updates. Each preset is a .boxp-{name} class on the column\'s inner card wrapper.', 'fw' ),
                        'value'        => '',
                        'choices'      => function_exists( 'sc_get_border_preset_choices' ) ? sc_get_border_preset_choices() : array( '' => __( 'None', 'fw' ) ),
                        'preview_text' => __( 'Box', 'fw' ),
                        'placeholder'  => __( '— Select a box style —', 'fw' ),
                    ],
                ],
            ],
            'group_spacings' => [
                'type'    => 'group',
                'options' => [
                    'spacing'  => array(
                        'type'  => 'spacing',
                        'label' => __( 'Margin & Padding', 'fw' ),
                        'desc'  => __( 'All Sides applies to every side at once; any per-side value (Top, Right, Bottom, Left) overrides it for that direction.', 'fw' ),
                        'help'  => sc_styling_help_text( 'spacing' ),
                    ),
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
        'title'   => __('Advanced', 'fw'),
        'type'    => 'tab',
        'options' => [
            'advanced_settings' => [
                'type'    => 'group',
                'options' => ( function () {
                    // Insert Inner Wrapper Class right AFTER CSS Class inside the
                    // Advanced tab's group_css (i.e. before Custom CSS). group_css
                    // now ends with custom_css, so a plain append would drop the
                    // field below Custom CSS — rebuild the options to place it
                    // directly after css_class regardless of group_css length.
                    $advanced = sc_get_advanced_tab();
                    $inner_class_field = array(
                        'type'  => 'text',
                        'label' => __( 'Inner Wrapper Class', 'fw' ),
                        'desc'  => __( 'Optional. When set (or when any Styling-tab background / spacing pick is made), an inner &lt;div&gt; is rendered around the column content carrying these classes — useful for borders / padding / background around the content without affecting the Bootstrap grid wrapper. Leave blank and pick no Styling values to skip the inner div entirely.', 'fw' ),
                        'help'  => __( 'Example: type "card p-4 rounded-3" to wrap the content in a padded, rounded card without disturbing the column\'s grid alignment. Space-separate multiple classes.', 'fw' ),
                        'value' => '',
                    );
                    $rebuilt = array();
                    foreach ( $advanced['group_css']['options'] as $key => $field ) {
                        $rebuilt[ $key ] = $field;
                        if ( 'css_class' === $key ) {
                            $rebuilt['inner_class'] = $inner_class_field;
                        }
                    }
                    if ( ! isset( $rebuilt['inner_class'] ) ) {
                        $rebuilt['inner_class'] = $inner_class_field; // fallback: css_class not present
                    }
                    $advanced['group_css']['options'] = $rebuilt;
                    return $advanced;
                } )(),
            ],
        ],
    ],

);
