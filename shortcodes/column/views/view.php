<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 * @var string $content
 */

// Base column class from the width picker (e.g. "fw-col-12 fw-col-sm-{N}" —
// full on phone, N from `sm` up). The width picker is the small/default width;
// the Layout-tab "Width — Phone/Tablet/Desktop" fields override specific
// breakpoints on top of it, reusing the existing fw-col-{bp}-{N} utilities.
$width_class = fw_ext_builder_get_item_width( 'page-builder', $atts['width'] . '/frontend_class' );

// Twelfths ('1'..'12') + fifths ('15'/'25'/'35'/'45' = 1/5..4/5, mapping to the
// fw-col-*-{15,25,35,45} classes) + 'auto'. The fifth keys reuse the {numerator}5
// convention already established by the built-in 1/5 (fw-col-15).
$rw_valid = array( '1','2','3','4','5','6','7','8','9','10','11','12','15','25','35','45','auto' );
$col_token = function ( $bp, $v ) {
    $seg = ( $bp === '' ) ? 'fw-col' : 'fw-col-' . $bp;
    return ( $v === 'auto' ) ? $seg : $seg . '-' . $v;
};

// Per-device width overrides — now one responsive control `col_width` (base / md / lg).
// Falls back to the legacy per-device atts (w_phone / w_tablet / w_desktop) so columns
// saved before the merge keep rendering identically. Emit logic below is unchanged.
$cw = fw_akg( 'col_width', $atts, null );
if ( ! is_array( $cw ) ) {
    $cw = array(
        'base' => (string) fw_akg( 'w_phone', $atts, '' ),
        'md'   => (string) fw_akg( 'w_tablet', $atts, '' ),
        'lg'   => (string) fw_akg( 'w_desktop', $atts, '' ),
    );
}
$w_phone   = (string) ( isset( $cw['base'] ) ? $cw['base'] : '' );
$w_tablet  = (string) ( isset( $cw['md'] )   ? $cw['md']   : '' );
$w_desktop = (string) ( isset( $cw['lg'] )   ? $cw['lg']   : '' );

$w_tokens = array_values( array_filter( preg_split( '/\s+/', trim( (string) $width_class ) ) ) );

if ( in_array( $w_phone, $rw_valid, true ) ) {
    // Replace the base phone (xs) token (`fw-col` / `fw-col-12`); keep `fw-col-sm-*`.
    $w_tokens = array_values( array_filter( $w_tokens, function ( $t ) {
        return ! preg_match( '/^fw-col(-\d+)?$/', $t );
    } ) );
    $w_tokens[] = $col_token( '', $w_phone );
}
if ( in_array( $w_tablet, $rw_valid, true ) )  { $w_tokens[] = $col_token( 'md', $w_tablet ); }
if ( in_array( $w_desktop, $rw_valid, true ) ) { $w_tokens[] = $col_token( 'lg', $w_desktop ); }

$width_class = implode( ' ', $w_tokens );

/*
|--------------------------------------------------------------------------
| Route styling + custom CSS class to the INNER wrapper
|--------------------------------------------------------------------------
| The outer <div> is the Bootstrap grid column — only the width class
| (`fw-col-12` etc.) should live there. Any background / spacing pick from
| the Styling tab, plus the user's Advanced-tab CSS Class, gets moved onto
| an inner <div> so it forms a contained "card" inside the grid column
| without fighting Bootstrap's gutter system.
|
| Animation classes from the Animations tab, responsive-hide, and the
| custom_attrs / css_id from the Advanced tab intentionally STAY on the
| outer column — they target the column-as-layout-slot, not the inner
| content area.
*/

// 1. Extract every Styling-tab pick that should land on the inner div.
//    Both helpers also UNSET their keys from $atts so the
//    sc_build_wrapper_attr filter chain doesn't re-apply them to the outer.
//    `bg_color` is still a flat key on its own; spacing now lives under a
//    single nested `spacing` att produced by the composite spacing option
//    type (see framework/includes/option-types/spacing/README.md).
//    sc_extract_styling_atts returns BOTH preset-class picks AND custom-hex
//    inline-style picks (compact picker custom-color path).
$bg_color_atts  = sc_extract_styling_atts( $atts, array( 'bg_color' ) );
$styling_extras = $bg_color_atts['classes'];
$styling_style  = $bg_color_atts['styles'] ? implode( '; ', $bg_color_atts['styles'] ) : '';
$styling_extras = array_merge( $styling_extras, sc_extract_spacing_classes( $atts ) );

// 2. Capture the user's custom CSS class (Advanced tab) and strip it off
//    $atts so sc_build_wrapper_attr doesn't put it on the outer. Will be
//    sanitised + appended to the inner along with the rest.
$user_css_class = isset( $atts['css_class'] ) ? (string) $atts['css_class'] : '';
$atts['css_class'] = '';

// 3. Build outer attributes. With styling + css_class stripped, the wrapper
//    only carries width (added below), css_id, responsive-hide, custom_attrs,
//    and any animation classes (handled by the animation filter).
$attr = sc_build_wrapper_attr( $atts );

// Prepend width class as the FIRST class on the outer column.
if ( ! empty( $width_class ) ) {
    if ( ! empty( $attr['class'] ) ) {
        $attr['class'] = esc_attr( $width_class ) . ' ' . $attr['class'];
    } else {
        $attr['class'] = esc_attr( $width_class );
    }
}

// Order. The row is a flex container, so CSS `order` on the column (the flex item)
// reorders it. Now a per-device value: array( base, md, lg ), each emitting a
// `fw-order{-bp}-{v}` utility (frontend-grid.css). A LEGACY scalar meant "order <v>
// below md, natural (0) from md up", so it migrates to { base:<v>, md:'0' } to
// reproduce that exactly. base applies at all widths; md/lg override upward.
$order_resp = fw_akg( 'mobile_order', $atts, array() );
if ( ! is_array( $order_resp ) ) {
    $order_resp = ( (string) $order_resp !== '' )
        ? array( 'base' => (string) $order_resp, 'md' => '0' )
        : array();
}
$order_valid   = array( 'first', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', 'last' );
$order_classes = array();
foreach ( array( 'base' => '', 'md' => '-md', 'lg' => '-lg' ) as $layer => $infix ) {
    $ov = isset( $order_resp[ $layer ] ) ? (string) $order_resp[ $layer ] : '';
    if ( in_array( $ov, $order_valid, true ) ) {
        $order_classes[] = 'fw-order' . $infix . '-' . $ov;
    }
}
if ( ! empty( $order_classes ) ) {
    $attr['class'] = trim( $attr['class'] . ' ' . implode( ' ', $order_classes ) );
}

/*
| Outer-column layout utilities (offset, breakpoint order, self-alignment,
| content alignment, position). These target the column-as-flex-item / -slot,
| so they belong on the OUTER column — never the inner styling card. Every
| value is whitelisted before it reaches a class name.
*/
$outer_extra  = array();
// Twelfths ('1'..'11') + fifths ('15'/'25'/'35'/'45' = 1/5..4/5 → fw-offset-*-{15,25,35,45}).
$offset_valid = array( '1','2','3','4','5','6','7','8','9','10','11','15','25','35','45' );

// Offsets — now one responsive control `col_offset` (base / md / lg). Falls back to the
// legacy per-device atts (offset_phone / offset_tablet / offset_desktop). base =
// fw-offset-N (all widths), then md / lg overrides.
$co = fw_akg( 'col_offset', $atts, null );
if ( ! is_array( $co ) ) {
    $co = array(
        'base' => (string) fw_akg( 'offset_phone', $atts, '' ),
        'md'   => (string) fw_akg( 'offset_tablet', $atts, '' ),
        'lg'   => (string) fw_akg( 'offset_desktop', $atts, '' ),
    );
}
$o_phone = (string) ( isset( $co['base'] ) ? $co['base'] : '' );
$o_tab   = (string) ( isset( $co['md'] )   ? $co['md']   : '' );
$o_desk  = (string) ( isset( $co['lg'] )   ? $co['lg']   : '' );
if ( in_array( $o_phone, $offset_valid, true ) ) { $outer_extra[] = 'fw-offset-' . $o_phone; }
if ( in_array( $o_tab, $offset_valid, true ) )   { $outer_extra[] = 'fw-offset-md-' . $o_tab; }
if ( in_array( $o_desk, $offset_valid, true ) )  { $outer_extra[] = 'fw-offset-lg-' . $o_desk; }

// Column self vertical alignment within the row — now per-device: array( base, md, lg ).
// Each set layer emits align-self{-bp}-{v}. 'default' maps to no class (natural stretch).
// A legacy scalar folds into base.
$as_resp = fw_akg( 'align_self', $atts, array() );
if ( ! is_array( $as_resp ) ) { $as_resp = array( 'base' => (string) $as_resp ); }
$as_valid = array( 'start', 'center', 'end', 'stretch' );
foreach ( array( 'base' => '', 'md' => '-md', 'lg' => '-lg' ) as $layer => $infix ) {
    $av = isset( $as_resp[ $layer ] ) ? (string) $as_resp[ $layer ] : '';
    if ( in_array( $av, $as_valid, true ) ) {
        $outer_extra[] = 'align-self' . $infix . '-' . $av;
    }
}

// Content alignment — make a flex column and position its content. These must
// land on the element that DIRECTLY contains the content elements: the inner
// wrapper if one exists (Inner Wrapper Class / Border / Background / Full Height),
// otherwise the outer column. If they sat on the outer column while an inner
// wrapper exists, they'd only move the single wrapper block — "Space Between"
// (needs 2+ children) would do nothing. We compute the tokens here and route
// them below, once we know whether an inner wrapper is needed.
// Content Alignment + Content Vertical Alignment are now per-device values:
// array( base, md, lg ). The BASE token drives the (unchanged) axis-aware mapping
// below; the md / lg tokens are appended as breakpoint-infixed override utilities
// further down. A legacy scalar (pre-responsive save) folds into the base layer.
$content_h_resp = fw_akg( 'content_h', $atts, array() );
if ( ! is_array( $content_h_resp ) ) { $content_h_resp = array( 'base' => (string) $content_h_resp ); }
$content_h = isset( $content_h_resp['base'] ) ? (string) $content_h_resp['base'] : '';
$ch_md     = isset( $content_h_resp['md'] )   ? (string) $content_h_resp['md']   : '';
$ch_lg     = isset( $content_h_resp['lg'] )   ? (string) $content_h_resp['lg']   : '';

$content_v_resp = fw_akg( 'content_v', $atts, array() );
if ( ! is_array( $content_v_resp ) ) { $content_v_resp = array( 'base' => (string) $content_v_resp ); }
$content_v = isset( $content_v_resp['base'] ) ? (string) $content_v_resp['base'] : '';
$cv_md     = isset( $content_v_resp['md'] )   ? (string) $content_v_resp['md']   : '';
$cv_lg     = isset( $content_v_resp['lg'] )   ? (string) $content_v_resp['lg']   : '';
$direction = (string) fw_akg( 'content_direction', $atts, 'column' );
$is_row    = ( $direction === 'row' );
// Content Order — reverse the elements. Adapts to the direction below.
// Content Order → per-device "Reverse" switch: array( base, md, lg ) of yes/no/''.
// A LEGACY select value migrates: all → reverse everywhere; mobile → reverse < md (base
// on, md off); tablet → reverse < lg (base on, lg off). base applies at all widths; md/lg
// override upward. Reverse is literal — it no longer compensates the alignment.
$order_raw = fw_akg( 'content_order', $atts, array() );
if ( ! is_array( $order_raw ) ) {
    $legacy = (string) $order_raw;
    if ( $legacy === 'all' )        { $order_raw = array( 'base' => 'yes' ); }
    elseif ( $legacy === 'mobile' ) { $order_raw = array( 'base' => 'yes', 'md' => 'no' ); }
    elseif ( $legacy === 'tablet' ) { $order_raw = array( 'base' => 'yes', 'lg' => 'no' ); }
    else                            { $order_raw = array(); }
}
$rev_base  = ( isset( $order_raw['base'] ) && $order_raw['base'] === 'yes' );
$rev_md_st = ( isset( $order_raw['md'] ) && $order_raw['md'] !== '' );
$rev_lg_st = ( isset( $order_raw['lg'] ) && $order_raw['lg'] !== '' );
$rev_md    = $rev_md_st ? ( $order_raw['md'] === 'yes' ) : $rev_base;
$rev_lg    = $rev_lg_st ? ( $order_raw['lg'] === 'yes' ) : $rev_md;
$order_ok  = ( $rev_base || $rev_md_st || $rev_lg_st ); // any Reverse layer set
// Cross-axis values (align-items) accept only start/center/end; main-axis values
// (justify-content) additionally accept the distribute set (between/around/evenly).
$cv_ok      = in_array( $content_v, array( 'start','center','end','between','around','evenly' ), true ); // content_v on main axis (column)
$ch_cross   = in_array( $content_h, array( 'start','center','end' ), true );                             // content_h on cross axis (column)
$ch_main    = in_array( $content_h, array( 'start','center','end','between','around','evenly' ), true ); // content_h on main axis (row)

// Gap between elements → flex `gap` via the theme-aware `--gap-{slug}` CSS
// variable (generated from the Gap Scale in css-tokens.php). Slug is
// sanitised so a tampered att can't inject anything into the var() ref.
// Gap is now a per-device value: array( base, md, lg ) of Gap-Scale slugs. Each set
// layer emits a `sc-cgap{-bp}-{slug}` utility (generated in css-tokens.php →
// gap:var(--gap-{slug})). A legacy scalar folds into base.
$gap_resp = fw_akg( 'content_gap', $atts, array() );
if ( ! is_array( $gap_resp ) ) { $gap_resp = array( 'base' => (string) $gap_resp ); }
$gap_base = preg_replace( '/[^a-z0-9_-]/i', '', (string) ( isset( $gap_resp['base'] ) ? $gap_resp['base'] : '' ) );
$gap_md   = preg_replace( '/[^a-z0-9_-]/i', '', (string) ( isset( $gap_resp['md'] )   ? $gap_resp['md']   : '' ) );
$gap_lg   = preg_replace( '/[^a-z0-9_-]/i', '', (string) ( isset( $gap_resp['lg'] )   ? $gap_resp['lg']   : '' ) );
$gap_ok   = ( $gap_base !== '' || $gap_md !== '' || $gap_lg !== '' );

$content_align_tokens = array();
$content_align_style  = '';
if ( $cv_ok || $ch_main || $is_row || $gap_ok || $order_ok || $ch_md !== '' || $ch_lg !== '' || $cv_md !== '' || $cv_lg !== '' ) {
    $content_align_tokens[] = 'd-flex';

    // Direction + Reverse (Content Order). Direction is a single value; Reverse is now a
    // per-device switch. Each layer picks flex-{dir} or flex-{dir}-reverse, emitted only
    // when its effective reverse differs from the smaller layer (mobile-first cascade).
    $dir = $is_row ? 'row' : 'column';
    $content_align_tokens[] = $rev_base ? 'flex-' . $dir . '-reverse' : 'flex-' . $dir;
    if ( $rev_md !== $rev_base ) {
        $content_align_tokens[] = $rev_md ? 'flex-md-' . $dir . '-reverse' : 'flex-md-' . $dir;
    }
    if ( $rev_lg !== $rev_md ) {
        $content_align_tokens[] = $rev_lg ? 'flex-lg-' . $dir . '-reverse' : 'flex-lg-' . $dir;
    }

    if ( $is_row ) { $content_align_tokens[] = 'flex-wrap'; }

    // Axis-aware mapping: a row swaps the flex axes, so "Content Alignment" drives
    // justify-content in a row but align-items in a column — and vice-versa for "Content
    // Vertical Alignment". Reverse is now LITERAL (no alignment compensation), so Order and
    // Alignment are fully independent. The distribute values (between/around/evenly) exist
    // only on the main axis (justify-content).
    if ( $is_row ) {
        if ( $ch_main ) { $content_align_tokens[] = 'justify-content-' . $content_h; }
        if ( in_array( $content_v, array( 'start','center','end' ), true ) ) {
            $content_align_tokens[] = 'align-items-' . $content_v;
        }
    } else {
        if ( $cv_ok ) { $content_align_tokens[] = 'justify-content-' . $content_v; }
        if ( $ch_cross ) { $content_align_tokens[] = 'align-items-' . $content_h; }
    }

    // Responsive Content-alignment overrides (md / lg) — additive breakpoint-infixed
    // utilities layered ON TOP of the base mapping above, so existing (base-only)
    // columns render byte-for-byte as before. Axis-aware like the base: a row swaps
    // the flex axes, so Content Alignment drives justify-content in a row / align-items
    // in a column, and Content Vertical Alignment does the opposite. Only emitted for a
    // device the user actually set.
    $ch_emit = function ( $token, $bp ) use ( $is_row ) {
        $infix = '-' . $bp;
        if ( $is_row ) {
            $ok = in_array( $token, array( 'start', 'center', 'end', 'between', 'around', 'evenly' ), true );
            return $ok ? 'justify-content' . $infix . '-' . $token : '';
        }
        $ok = in_array( $token, array( 'start', 'center', 'end' ), true );
        return $ok ? 'align-items' . $infix . '-' . $token : '';
    };
    // content_v is the opposite axis of content_h: main (justify) in a column, cross
    // (align-items) in a row.
    $cv_emit = function ( $token, $bp ) use ( $is_row ) {
        $infix = '-' . $bp;
        if ( $is_row ) {
            $ok = in_array( $token, array( 'start', 'center', 'end' ), true );
            return $ok ? 'align-items' . $infix . '-' . $token : '';
        }
        $ok = in_array( $token, array( 'start', 'center', 'end', 'between', 'around', 'evenly' ), true );
        return $ok ? 'justify-content' . $infix . '-' . $token : '';
    };
    foreach ( array( 'md' => $ch_md, 'lg' => $ch_lg ) as $bp => $tok ) {
        if ( $tok === '' ) { continue; }
        $cls = $ch_emit( $tok, $bp );
        if ( $cls !== '' ) { $content_align_tokens[] = $cls; }
    }
    foreach ( array( 'md' => $cv_md, 'lg' => $cv_lg ) as $bp => $tok ) {
        if ( $tok === '' ) { continue; }
        $cls = $cv_emit( $tok, $bp );
        if ( $cls !== '' ) { $content_align_tokens[] = $cls; }
    }

    // Gap → per-breakpoint flex-gap utilities (base + md/lg), replacing the old inline
    // gap style so a different gap per device is possible.
    if ( $gap_base !== '' ) { $content_align_tokens[] = 'sc-cgap-' . $gap_base; }
    if ( $gap_md !== '' )   { $content_align_tokens[] = 'sc-cgap-md-' . $gap_md; }
    if ( $gap_lg !== '' )   { $content_align_tokens[] = 'sc-cgap-lg-' . $gap_lg; }
}

// Position + Z-Index now come from the shared Advanced-tab control (element_position),
// applied to this column's outer $attr as an inline style by sc_build_wrapper_attr().

if ( ! empty( $outer_extra ) ) {
    $attr['class'] = trim( $attr['class'] . ' ' . implode( ' ', $outer_extra ) );
}

// 4. Build the INNER class list: Inner Wrapper Class field tokens +
//    extracted styling + user's custom css_class tokens. Sanitisation
//    mirrors how sc_build_wrapper_attr handles css_class (split → lower →
//    sanitize_html_class per token).
$inner_tokens = array();

$normalize_class_tokens = function ( $raw ) {
    $out = array();
    if ( $raw === '' ) {
        return $out;
    }
    foreach ( preg_split( '/\s+/', (string) $raw ) as $t ) {
        $t = sanitize_html_class( strtolower( trim( $t ) ) );
        if ( $t !== '' ) {
            $out[] = $t;
        }
    }
    return $out;
};

if ( ! empty( $atts['inner_class'] ) ) {
    $inner_tokens = array_merge( $inner_tokens, $normalize_class_tokens( $atts['inner_class'] ) );
}
if ( $user_css_class !== '' ) {
    $inner_tokens = array_merge( $inner_tokens, $normalize_class_tokens( $user_css_class ) );
}
if ( ! empty( $styling_extras ) ) {
    $inner_tokens = array_merge( $inner_tokens, $styling_extras );
}

// Border / corners / shadow (Styling tab) → the inner card wrapper, alongside
// background + spacing. Setting any of these auto-creates the inner wrapper.
$border_side_map = array(
    'all'    => 'border',
    'top'    => 'border-top',
    'end'    => 'border-end',
    'bottom' => 'border-bottom',
    'start'  => 'border-start',
);
$border_sides = (string) fw_akg( 'border_sides', $atts, '' );
if ( isset( $border_side_map[ $border_sides ] ) ) {
    $inner_tokens[] = $border_side_map[ $border_sides ];

    $border_color = (string) fw_akg( 'border_color', $atts, '' );
    if ( in_array( $border_color, array( 'primary','secondary','success','danger','warning','info','light','dark','white' ), true ) ) {
        $inner_tokens[] = 'border-' . $border_color;
    }
    $border_width = (string) fw_akg( 'border_width', $atts, '' );
    if ( in_array( $border_width, array( '1','2','3','4','5' ), true ) ) {
        $inner_tokens[] = 'border-' . $border_width;
    }
}
$rounded = (string) fw_akg( 'rounded', $atts, '' );
if ( in_array( $rounded, array( 'rounded-1','rounded','rounded-3','rounded-pill','rounded-circle' ), true ) ) {
    $inner_tokens[] = $rounded;
}
$shadow = (string) fw_akg( 'shadow', $atts, '' );
if ( in_array( $shadow, array( 'shadow-sm','shadow','shadow-lg' ), true ) ) {
    $inner_tokens[] = $shadow;
}

// Box Preset (Styling tab) → a reusable `.boxp-{name}` class on the inner
// card wrapper (border + corners + shadow + background fill + hover; CSS generated
// in css-tokens.php from Theme Settings → Components → Box Presets). This is the
// modern replacement for the manual border fields above, which still render for any
// column saved before this feature (back-compat). The option key stays `border_preset`.
$border_preset = (string) fw_akg( 'border_preset', $atts, '' );
if ( $border_preset !== '' && preg_match( '/^boxp-[a-z0-9_-]+$/i', $border_preset ) ) {
    $inner_tokens[] = $border_preset;
}

// Full Height (Layout tab) — adds `h-100` to the inner wrapper so the
// styled card stretches to the column's row-height. Outer column is
// already stretched by Bootstrap flex defaults; this fills the inside.
if ( ! empty( $atts['full_height'] ) && $atts['full_height'] === 'yes' ) {
    $inner_tokens[] = 'h-100';
}

// De-dup while preserving order.
$inner_tokens = array_values( array_unique( $inner_tokens ) );
$inner_class  = implode( ' ', $inner_tokens );
?>

<?php
$needs_inner = $inner_class !== '' || $styling_style !== '';

// Route content alignment (classes) + gap (inline style) onto whichever
// element directly holds the content — the inner wrapper if one exists,
// otherwise the outer column.
if ( ! empty( $content_align_tokens ) ) {
    // Content Vertical Alignment (column main axis) needs the content container to
    // FILL the column height, or there is nothing to align within. h-100 lets it fill
    // the column's row-stretched height. Applied on whichever element holds the content
    // (inner wrapper OR the outer column) so vertical alignment works WITHOUT requiring
    // Full Height to be enabled separately — this is why "Middle" previously only took
    // effect once Full Height was on (that path added the inner wrapper + h-100). Only
    // in column mode; a row aligns on the cross axis and doesn't need the full height.
    // Fire for ANY device the user set Content Vertical Alignment on (base / md / lg) —
    // a Desktop-only "Middle" (justify-content-lg-center) needs the fill just as much as
    // a base one, and without it the wrapper stays at content height and never centres.
    $cv_dist  = array( 'start', 'center', 'end', 'between', 'around', 'evenly' );
    $cv_any   = $cv_ok || in_array( $cv_md, $cv_dist, true ) || in_array( $cv_lg, $cv_dist, true );
    if ( $cv_any && ! $is_row ) { $content_align_tokens[] = 'h-100'; }
    if ( $needs_inner ) {
        $inner_class = trim( $inner_class . ' ' . implode( ' ', $content_align_tokens ) );
        if ( $content_align_style !== '' ) {
            $styling_style = ( $styling_style !== '' ? rtrim( $styling_style, '; ' ) . '; ' : '' ) . $content_align_style;
        }
    } else {
        $attr['class'] = trim( $attr['class'] . ' ' . implode( ' ', $content_align_tokens ) );
        if ( $content_align_style !== '' ) {
            $existing      = isset( $attr['style'] ) ? rtrim( trim( $attr['style'] ), ';' ) : '';
            $attr['style'] = ( $existing !== '' ? $existing . '; ' : '' ) . $content_align_style;
        }
    }
}

$inner_style_attr = $styling_style !== '' ? ' style="' . esc_attr( $styling_style ) . '"' : '';
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
    <?php if ( $needs_inner ) : ?>
        <div class="<?php echo esc_attr( $inner_class ); ?>"<?php echo $inner_style_attr; ?>>
            <?php echo do_shortcode( $content ); ?>
        </div>
    <?php else : ?>
        <?php echo do_shortcode( $content ); ?>
    <?php endif; ?>
</div>
