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

// Master alignment (Layout tab). The `alignment` image-picker (left/center/right)
// supersedes the legacy `centered` switch, still honored for content saved before
// this option existed (centered:"yes" → center). The per-element pickers below may
// override per line; an empty per-element value ("Inherit") falls back to this master.
$alignment = $atts['alignment'] ?? '';
if ( $alignment === '' ) {
    $alignment = ( ! empty( $atts['centered'] ) && $atts['centered'] === 'yes' ) ? 'center' : 'left';
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
$should_wrap = sc_needs_wrapper( $atts ) || $element_spacing !== '' || $block_max_width !== '';

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
if ( ( $oa = sc_alignment_class( $overline_align ) ) !== '' ) {
    $overline_classes[] = $oa;
}
if ( $overline_class !== '' ) {
    $overline_classes[] = sc_sanitize_class( $overline_class );
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
    $title_classes[] = sc_sanitize_class( $title_class );
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
    $subtitle_classes[] = sc_sanitize_class( $subtitle_class );
}
$subtitle_classes = array_merge( $subtitle_classes, $subtitle_extras );

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
<?php if ( ! empty( $atts['overline'] ) ) : ?>
    <div class="<?php echo esc_attr( implode( ' ', $overline_classes ) ); ?>"<?php echo $overline_style_attr; ?>>
        <span class="heading-overline__label"><?php echo wp_kses_post( $atts['overline'] ); ?></span>
    </div>
<?php endif; ?>

<?php if ( ! empty( $atts['title'] ) ) : ?>
    <<?php echo esc_attr( $atts['heading'] ); ?> class="<?php echo esc_attr( implode( ' ', $title_classes ) ); ?>"<?php echo $title_style_attr; ?>>
        <?php echo wp_kses_post( $atts['title'] ); ?>
    </<?php echo esc_attr( $atts['heading'] ); ?>>
<?php endif; ?>

<?php if ( ! empty( $atts['subtitle'] ) ) : ?>
    <div class="<?php echo esc_attr( implode( ' ', $subtitle_classes ) ); ?>"<?php echo $subtitle_style_attr; ?>>
        <?php echo wp_kses_post( $atts['subtitle'] ); ?>
    </div>
<?php endif; ?>

<?php if ( $should_wrap ) : ?>
    </div>
<?php endif; ?>
