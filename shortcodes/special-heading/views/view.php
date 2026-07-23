<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 */

// Overline/title/subtitle Advanced-tab classes
$overline_class = trim( $atts['overline_class'] ?? '' );
$title_class    = trim( $atts['title_class'] ?? '' );
$subtitle_class = trim( $atts['subtitle_class'] ?? '' );

// Capture user's original css_class for the css_class composition below
$user_class = $atts['css_class'] ?? '';

// Master alignment (Layout tab). The `alignment` image-picker (inherit/left/center/right)
// supersedes the legacy `centered` switch, still honored for content saved before
// this option existed (centered:"yes" → center). An empty master ("Inherit") forces
// nothing — the heading follows the theme / parent alignment. The per-element pickers
// below may override per line; an empty per-element value ("Inherit") falls back to
// this master (which may itself be empty, i.e. no forced text-* class anywhere).
$alignment = $atts['alignment'] ?? '';
if ( $alignment === '' && ! empty( $atts['centered'] ) && $atts['centered'] === 'yes' ) {
    $alignment = 'center';
}
$resolve_align = function ( $key ) use ( $atts, $alignment ) {
    $v = $atts[ $key ] ?? '';
    return $v !== '' ? $v : $alignment;
};
$overline_align = $resolve_align( 'overline_align' );
$title_align    = $resolve_align( 'title_align' );
$subtitle_align = $resolve_align( 'subtitle_align' );

// Resolve a unit-input value (array {value,unit}) or a legacy plain string to a
// CSS length, e.g. ['value'=>'720','unit'=>'px'] → "720px". Done inline (no
// option-type class dependency) so it's safe on the front end.
$css_len = function ( $v ) {
    if ( is_array( $v ) ) {
        $num  = isset( $v['value'] ) ? trim( (string) $v['value'] ) : '';
        $unit = isset( $v['unit'] )  ? trim( (string) $v['unit'] )  : '';
        return $num === '' ? '' : $num . $unit;
    }
    return trim( (string) $v );
};

// Layout: vertical spacing preset + heading-block max width.
$element_spacing = $atts['element_spacing'] ?? '';
$block_max_width = $css_len( $atts['block_max_width'] ?? '' );

// Decide whether the wrapper is needed BEFORE injecting the composed heading-<tag>
// class. Per-element alignment lives on the elements themselves (text-* classes), so
// it never forces the wrapper; per-element Color picks route to the inner elements
// too. Element spacing and a block max-width DO need the wrapper (they style the
// container). Mirrors text-block: with no such option set, the heading renders bare.
// A user-provided CSS Class or CSS ID must ALWAYS produce the wrapper div (so the
// class/id has an element to land on) — stated explicitly here so the trigger doesn't
// depend on sc_needs_wrapper's internals. Element spacing / block max-width also need it.
$user_css_id = $atts['css_id'] ?? '';
$should_wrap = sc_needs_wrapper( $atts )
	|| $element_spacing !== '' || $block_max_width !== ''
	|| $user_class !== '' || $user_css_id !== '';

// Set base + unique-id-prefix before sc_build_wrapper_attr
$atts['base_class']       = 'heading';
$atts['unique_id_prefix'] = 'hd-';

// Compose css_class for the wrapper: heading-<tag> [+ spacing preset] [+ user's class]
$composed = 'heading-' . ( $atts['heading'] ?? 'h2' );
if ( $element_spacing === 'tight' || $element_spacing === 'relaxed' ) {
    $composed .= ' heading--space-' . $element_spacing;
}
if ( $user_class !== '' ) {
    $composed .= ' ' . $user_class;
}
$atts['css_class'] = trim( $composed );

// Route per-element color picks to specific inner elements (kept off the wrapper).
// Overline / title / subtitle each have a dedicated pick that overrides on its own
// target. sc_extract_styling_atts gives us both classes AND custom-hex inline styles
// from the compact picker. Note: the title's size comes from the heading tag /
// display_size; the subtitle has its own subtitle_size below — so no wrapper-level
// font_size_preset for this shortcode.
$overline_styling = sc_extract_styling_atts( $atts, array( 'overline_color' ) );
$title_styling    = sc_extract_styling_atts( $atts, array( 'title_color' ) );
$subtitle_styling = sc_extract_styling_atts( $atts, array( 'subtitle_color' ) );
$overline_extras  = $overline_styling['classes'];
$title_extras     = $title_styling['classes'];
$subtitle_extras  = $subtitle_styling['classes'];
$overline_style   = $overline_styling['styles'] ? implode( '; ', $overline_styling['styles'] ) : '';
$title_style      = $title_styling['styles']    ? implode( '; ', $title_styling['styles'] )    : '';
$subtitle_style   = $subtitle_styling['styles'] ? implode( '; ', $subtitle_styling['styles'] ) : '';

// Build wrapper attributes ($should_wrap was decided above, before css_class
// was composed, so the always-present heading-<tag> class doesn't force it).
$attr = sc_build_wrapper_attr( $atts );

// Heading-block max width on the wrapper; auto-centered within its column when the
// master alignment is centered. Merge with any style already set (e.g. custom bg).
if ( $block_max_width !== '' ) {
    $bmw = 'max-width:' . $block_max_width . ';';
    if ( $alignment === 'center' ) {
        $bmw .= 'margin-inline:auto;';
    }
    $attr['style'] = empty( $attr['style'] ) ? $bmw : rtrim( $attr['style'], '; ' ) . ';' . $bmw;
}

// Compose inner-element class lists. Each element gets its resolved text-* alignment
// utility (sc_alignment_class), so per-line alignment works with or without a wrapper.
// Overline modifiers — three INDEPENDENT axes (Layout tab): case + marker +
// container. Each maps to its own modifier class; they compose freely (e.g. a
// pill with no marker, or a dot without a pill).
$overline_classes = array( 'heading-overline' );
$ov_upper  = ( ( $atts['overline_uppercase'] ?? '' ) === 'yes' );
$ov_marker = $atts['overline_marker'] ?? '';                          // '' | rule | dot | lines | bar
$ov_after  = ( ( $atts['overline_marker_position'] ?? 'before' ) === 'after' );
$ov_cont   = $atts['overline_container'] ?? '';                       // '' | pill | pill-outline | underline

// Back-compat: the old single `overline_style` preset (pre-split) still resolves,
// so headings saved before this change render unchanged. Derive the axes from it
// only when the new fields are at their defaults.
$legacy = $atts['overline_style'] ?? '';
if ( $legacy !== '' ) {
    if ( strpos( $legacy, 'uppercase' ) !== false || strpos( $legacy, 'kicker' ) !== false || $legacy === 'pill' ) {
        $ov_upper = true;
    }
    if ( $ov_marker === '' ) {
        if ( strpos( $legacy, 'rule' ) !== false )      { $ov_marker = 'rule'; }
        elseif ( strpos( $legacy, 'lines' ) !== false ) { $ov_marker = 'lines'; }
        elseif ( strpos( $legacy, 'dot' ) !== false || $legacy === 'pill' ) { $ov_marker = 'dot'; }
    }
    if ( $ov_cont === '' ) {
        if ( $legacy === 'pill' )                          { $ov_cont = 'pill'; }
        elseif ( strpos( $legacy, 'underline' ) !== false ) { $ov_cont = 'underline'; }
    }
}

if ( $ov_upper ) { $overline_classes[] = 'heading-overline--kicker'; }
$ov_marker_map = array( 'rule' => 'heading-overline--rule', 'dot' => 'heading-overline--dot', 'lines' => 'heading-overline--lines', 'bar' => 'heading-overline--bar' );
if ( isset( $ov_marker_map[ $ov_marker ] ) ) {
    $overline_classes[] = $ov_marker_map[ $ov_marker ];
    // Trailing position applies only to single (non-flanking) markers.
    if ( $ov_after && $ov_marker !== 'lines' ) {
        $overline_classes[] = 'heading-overline--mark-after';
    }
}
$ov_cont_map = array( 'pill' => 'heading-overline--pill', 'pill-outline' => 'heading-overline--pill-outline', 'underline' => 'heading-overline--underline' );
if ( isset( $ov_cont_map[ $ov_cont ] ) ) {
    $overline_classes[] = $ov_cont_map[ $ov_cont ];
}
// The inner .heading-overline__label span only exists to (a) carry the flanking
// markers and (b) shrink-wrap a pill / underline around the text. With NEITHER a
// marker NOR a container it's redundant, so the text renders bare (one fewer node).
// Base + kicker styling lives on .heading-overline itself, so no extra class is
// needed — a plain eyebrow is just <p class="heading-overline">Eyebrow</p>.
$overline_needs_label = isset( $ov_marker_map[ $ov_marker ] ) || isset( $ov_cont_map[ $ov_cont ] );
if ( ( $oa = sc_alignment_class( $overline_align ) ) !== '' ) {
    $overline_classes[] = $oa;
}
// User class(es) — space-separated; sanitize each token (sc_sanitize_class strips spaces, so a
// multi-class value like "text-uppercase text-sm letter-spacing" must be split first).
if ( $overline_class !== '' ) {
    foreach ( preg_split( '/\s+/', $overline_class ) as $oc ) {
        $oc = sc_sanitize_class( $oc );
        if ( $oc !== '' ) { $overline_classes[] = $oc; }
    }
}
$overline_classes = array_merge( $overline_classes, $overline_extras );

// Title: base + optional display-size override (visual size decoupled from the tag).
$title_classes = array( 'heading-title' );
$display_size  = sc_sanitize_class( $atts['display_size'] ?? '' );
if ( $display_size !== '' ) {
    $title_classes[] = $display_size;
}
if ( ( $ta = sc_alignment_class( $title_align ) ) !== '' ) {
    $title_classes[] = $ta;
}
if ( $title_class !== '' ) {
    foreach ( preg_split( '/\s+/', $title_class ) as $tc ) {
        $tc = sc_sanitize_class( $tc );
        if ( $tc !== '' ) { $title_classes[] = $tc; }
    }
}
$title_classes = array_merge( $title_classes, $title_extras );

// Subtitle: base + optional font-size preset (value is the class) + user class.
$subtitle_classes = array( 'heading-subtitle' );
$subtitle_size    = sc_sanitize_class( $atts['subtitle_size'] ?? '' );
if ( $subtitle_size !== '' ) {
    $subtitle_classes[] = $subtitle_size;
}
if ( ( $sa = sc_alignment_class( $subtitle_align ) ) !== '' ) {
    $subtitle_classes[] = $sa;
}
if ( $subtitle_class !== '' ) {
    foreach ( preg_split( '/\s+/', $subtitle_class ) as $sc_cl ) {
        $sc_cl = sc_sanitize_class( $sc_cl );
        if ( $sc_cl !== '' ) { $subtitle_classes[] = $sc_cl; }
    }
}
$subtitle_classes = array_merge( $subtitle_classes, $subtitle_extras );

// Title readability measure (max-width) — caps the title element only (independent of the
// whole-block Heading Max Width). Centered within its column when the title is center-aligned.
$title_max_width = $css_len( $atts['title_max_width'] ?? '' );
if ( $title_max_width !== '' ) {
    $tmw = 'max-width:' . $title_max_width;
    if ( $title_align === 'center' ) {
        $tmw .= '; margin-inline:auto';
    }
    $title_style = $title_style !== '' ? $title_style . '; ' . $tmw : $tmw;
}

// Subtitle readability measure (max-width). Centered within its column when the
// subtitle itself is center-aligned so the constrained line sits under the title.
$subtitle_max_width = $css_len( $atts['subtitle_max_width'] ?? '' );
if ( $subtitle_max_width !== '' ) {
    $mw = 'max-width:' . $subtitle_max_width;
    if ( $subtitle_align === 'center' ) {
        $mw .= '; margin-inline:auto';
    }
    $subtitle_style = $subtitle_style !== '' ? $subtitle_style . '; ' . $mw : $mw;
}
?>

<?php if ( $should_wrap ) : ?>
    <div <?php echo fw_attr_to_html( $attr ); ?>>
<?php endif; ?>

<?php
$overline_style_attr = $overline_style !== '' ? ' style="' . esc_attr( $overline_style ) . '"' : '';
$title_style_attr    = $title_style    !== '' ? ' style="' . esc_attr( $title_style ) . '"'    : '';
$subtitle_style_attr = $subtitle_style !== '' ? ' style="' . esc_attr( $subtitle_style ) . '"' : '';
?>
<?php
// Each line is emitted as a single string with NO whitespace between the tag and
// its text, so the markup is genuinely clean (no leading/trailing space inside the
// element). Content is trimmed for the same reason.
if ( ! empty( $atts['overline'] ) ) {
    $overline_inner = $overline_needs_label
        ? '<span class="heading-overline__label">' . wp_kses_post( trim( (string) $atts['overline'] ) ) . '</span>'
        : wp_kses_post( trim( (string) $atts['overline'] ) );
    echo '<p class="' . esc_attr( implode( ' ', $overline_classes ) ) . '"' . $overline_style_attr . '>' . $overline_inner . '</p>';
}

if ( ! empty( $atts['title'] ) ) {
    // Whitelist the tag so a missing/stale value can never emit a broken <  > tag.
    $hd_tag = ( isset( $atts['heading'] ) && in_array( $atts['heading'], array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'div' ), true ) ) ? $atts['heading'] : 'h2';

    // Title Icon (Content tab). Rendered INSIDE the heading, before the text, so the
    // title stays clean and semantic. sc_icon_render() handles every icon-v2 shape
    // (font / emoji / svg / uploaded image).
    $title_icon = '';
    if ( isset( $atts['icon'] ) && function_exists( 'sc_icon_render' ) ) {
        $icon_set = function_exists( 'fw_ext_mega_menu_icon_is_set' )
            ? fw_ext_mega_menu_icon_is_set( $atts['icon'] )
            : ! empty( $atts['icon'] );
        if ( $icon_set ) {
            // No trailing space — the icon/title gap is the .heading-title__icon
            // margin-right (a literal space here would add a second, uneven gap).
            $title_icon = sc_icon_render( $atts['icon'], array( 'class' => 'heading-title__icon' ) );
        }
    }

    echo '<' . $hd_tag . ' class="' . esc_attr( implode( ' ', $title_classes ) ) . '"' . $title_style_attr . '>' . $title_icon . wp_kses_post( trim( (string) $atts['title'] ) ) . '</' . $hd_tag . '>';
}

if ( ! empty( $atts['subtitle'] ) ) {
    echo '<p class="' . esc_attr( implode( ' ', $subtitle_classes ) ) . '"' . $subtitle_style_attr . '>' . wp_kses_post( trim( (string) $atts['subtitle'] ) ) . '</p>';
}
?>

<?php if ( $should_wrap ) : ?>
    </div>
<?php endif; ?>
