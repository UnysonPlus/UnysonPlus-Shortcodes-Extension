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

$rw_valid = array( '1','2','3','4','5','6','7','8','9','10','11','12','auto' );
$col_token = function ( $bp, $v ) {
    $seg = ( $bp === '' ) ? 'fw-col' : 'fw-col-' . $bp;
    return ( $v === 'auto' ) ? $seg : $seg . '-' . $v;
};

$w_phone   = (string) fw_akg( 'w_phone', $atts, '' );
$w_tablet  = (string) fw_akg( 'w_tablet', $atts, '' );
$w_desktop = (string) fw_akg( 'w_desktop', $atts, '' );

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

// Mobile ordering. The row is a flex container, so CSS `order` on the column
// (the flex item) reorders it. `fw-order-{v}` applies at the smallest breakpoint
// (mobile, <576px, where columns are stacked) and `fw-order-sm-0` resets to the
// natural authoring order from `sm` up (where columns go side-by-side). Reuses
// the existing utilities in builder/static/css/frontend-grid.css — no new CSS.
$mobile_order = (string) fw_akg( 'mobile_order', $atts, '' );
if ( in_array( $mobile_order, array( 'first', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', 'last' ), true ) ) {
    $attr['class'] = trim( $attr['class'] . ' fw-order-' . $mobile_order . ' fw-order-sm-0' );
}

/*
| Outer-column layout utilities (offset, breakpoint order, self-alignment,
| content alignment, position). These target the column-as-flex-item / -slot,
| so they belong on the OUTER column — never the inner styling card. Every
| value is whitelisted before it reaches a class name.
*/
$outer_extra  = array();
$offset_valid = array( '1','2','3','4','5','6','7','8','9','10','11' );

// Offsets (phone = fw-offset-N, then md / lg).
$o_phone = (string) fw_akg( 'offset_phone', $atts, '' );
if ( in_array( $o_phone, $offset_valid, true ) ) { $outer_extra[] = 'fw-offset-' . $o_phone; }
$o_tab = (string) fw_akg( 'offset_tablet', $atts, '' );
if ( in_array( $o_tab, $offset_valid, true ) ) { $outer_extra[] = 'fw-offset-md-' . $o_tab; }
$o_desk = (string) fw_akg( 'offset_desktop', $atts, '' );
if ( in_array( $o_desk, $offset_valid, true ) ) { $outer_extra[] = 'fw-offset-lg-' . $o_desk; }

// Column self vertical alignment within the row.
$align_self = (string) fw_akg( 'align_self', $atts, '' );
if ( in_array( $align_self, array( 'start','center','end','stretch' ), true ) ) {
    $outer_extra[] = 'align-self-' . $align_self;
}

// Content alignment — make a flex column and position its content. These must
// land on the element that DIRECTLY contains the content elements: the inner
// wrapper if one exists (Inner Wrapper Class / Border / Background / Full Height),
// otherwise the outer column. If they sat on the outer column while an inner
// wrapper exists, they'd only move the single wrapper block — "Space Between"
// (needs 2+ children) would do nothing. We compute the tokens here and route
// them below, once we know whether an inner wrapper is needed.
$content_v = (string) fw_akg( 'content_v', $atts, '' );
$content_h = (string) fw_akg( 'content_h', $atts, '' );
$cv_ok = in_array( $content_v, array( 'start','center','end','between' ), true );
$ch_ok = in_array( $content_h, array( 'start','center','end' ), true );
$content_align_tokens = array();
if ( $cv_ok || $ch_ok ) {
    $content_align_tokens[] = 'd-flex';
    $content_align_tokens[] = 'flex-column';
    if ( $cv_ok ) { $content_align_tokens[] = 'justify-content-' . $content_v; }
    if ( $ch_ok ) { $content_align_tokens[] = 'align-items-' . $content_h; }
}

// Position (sticky also gets top-0 so it actually sticks).
$position = (string) fw_akg( 'position', $atts, '' );
if ( in_array( $position, array( 'static','relative','absolute','sticky','fixed' ), true ) ) {
    $outer_extra[] = 'position-' . $position;
    if ( $position === 'sticky' ) { $outer_extra[] = 'top-0'; }
}

if ( ! empty( $outer_extra ) ) {
    $attr['class'] = trim( $attr['class'] . ' ' . implode( ' ', $outer_extra ) );
}

// Z-index → inline style on the outer column (no general z-index utility).
$z_index = fw_akg( 'z_index', $atts, '' );
if ( $z_index !== '' && is_numeric( $z_index ) ) {
    $existing_style = isset( $attr['style'] ) ? rtrim( trim( $attr['style'] ), ';' ) : '';
    $attr['style']  = ( $existing_style !== '' ? $existing_style . '; ' : '' ) . 'z-index:' . (int) $z_index . ';';
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

// Border Preset (Styling tab) → a reusable `.colb-{name}` class on the inner
// card wrapper (border + corners + shadow + hover; CSS generated in
// css-tokens.php from Theme Settings → General → Borders). This is the modern
// replacement for the manual border fields above, which still render for any
// column saved before this feature (back-compat).
$border_preset = (string) fw_akg( 'border_preset', $atts, '' );
if ( $border_preset !== '' && preg_match( '/^colb-[a-z0-9_-]+$/i', $border_preset ) ) {
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
$inner_style_attr = $styling_style !== '' ? ' style="' . esc_attr( $styling_style ) . '"' : '';
$needs_inner      = $inner_class !== '' || $styling_style !== '';

// Route content alignment onto whichever element directly holds the content.
if ( ! empty( $content_align_tokens ) ) {
    if ( $needs_inner ) {
        // Distribute / position the REAL elements inside the wrapper. h-100 lets the
        // wrapper fill the column height so vertical alignment (incl. Space Between)
        // has room to work — without it the wrapper is only as tall as its content.
        if ( $cv_ok ) { $content_align_tokens[] = 'h-100'; }
        $inner_class = trim( $inner_class . ' ' . implode( ' ', $content_align_tokens ) );
    } else {
        $attr['class'] = trim( $attr['class'] . ' ' . implode( ' ', $content_align_tokens ) );
    }
}
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
