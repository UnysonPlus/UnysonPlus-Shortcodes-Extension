<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 */

// Icon pack CSS is enqueued by sc_icon_render() below (single source of truth).

// Route per-element color picks to specific inner elements (kept out of wrapper).
// Keeps the Styling tab's general "Text Color" as the wrapper-level base; named
// overrides take precedence on their target elements.
// sc_extract_styling_atts returns both preset classes AND custom-hex
// inline-style fragments from the compact picker.
$title_styling = sc_extract_styling_atts( $atts, array( 'title_color' ) );
$icon_styling  = sc_extract_styling_atts( $atts, array( 'icon_color' ) );
$title_extras  = $title_styling['classes'];
$icon_extras   = $icon_styling['classes'];
$title_style   = $title_styling['styles'] ? implode( '; ', $title_styling['styles'] ) : '';
$icon_style    = $icon_styling['styles']  ? implode( '; ', $icon_styling['styles'] )  : '';

// Build wrapper attributes
$atts['base_class']       = 'icon';
$atts['unique_id_prefix'] = 'ic-';
$attr = sc_build_wrapper_attr( $atts );

// icon-v2 stores its value as an array: ['type' => 'icon-font'|'custom-upload'|'none', ...]
$icon      = is_array( $atts['icon'] ?? null ) ? $atts['icon'] : array();
$title_classes = array_merge( array( 'list-title' ), $title_extras );
$title_style_attr = $title_style !== '' ? ' style="' . esc_attr( $title_style ) . '"' : '';

// Icon Size — a unit-input compiled to a CSS length applied as font-size on the glyph.
// The `.sc-icon-glyph svg{width:1em;height:1em}` rule (static.css) makes inline SVGs scale
// with that font-size too, so one control resizes both font icons and SVGs. Tolerates a
// legacy bare-length string.
$icon_size = '';
if ( isset( $atts['icon_size'] ) ) {
    $raw = $atts['icon_size'];
    if ( is_array( $raw ) && isset( $raw['value'] ) && trim( (string) $raw['value'] ) !== '' ) {
        $icon_size = class_exists( 'FW_Option_Type_Unit_Input' )
            ? FW_Option_Type_Unit_Input::to_string( $raw )
            : ( trim( (string) $raw['value'] ) . ( isset( $raw['unit'] ) ? preg_replace( '/[^a-z%]/', '', (string) $raw['unit'] ) : 'px' ) );
    } elseif ( is_string( $raw ) && trim( $raw ) !== '' ) {
        $icon_size = trim( $raw );
    }
}
if ( $icon_size !== '' ) {
    $icon_style = trim( $icon_style . ( $icon_style !== '' ? '; ' : '' ) . 'font-size:' . $icon_size );
}

// Central icon render: font <i> keeps the icon-color classes/style; a custom
// upload keeps its 'icon-image' base class. Pack CSS is auto-enqueued. The
// `sc-icon-glyph` class (font_class → applied to both the <i> and the SVG wrapper)
// is the hook the Icon Size CSS normalises SVGs to 1em on.
$icon_html = sc_icon_render( $icon, array(
    'class'      => implode( ' ', $icon_extras ),
    'font_class' => 'sc-icon-glyph',
    'img_class'  => 'icon-image',
    'style'      => $icon_style,
) );
?>
<span <?php echo fw_attr_to_html( $attr ); ?>>
    <?php echo $icon_html; // already escaped by sc_icon_render() ?>

    <?php // A meaningful icon with no visible Title gets a screen-reader-only name so it
          // is exposed to assistive tech / AI agents (the glyph itself stays aria-hidden). ?>
    <?php if ( empty( $atts['title'] ) && ! empty( $atts['aria_label'] ) ) : ?>
        <span class="screen-reader-text"><?php echo esc_html( $atts['aria_label'] ); ?></span>
    <?php endif; ?>

    <?php if ( ! empty( $atts['title'] ) ) : ?>
        <span class="<?php echo esc_attr( implode( ' ', $title_classes ) ); ?>"<?php echo $title_style_attr; ?>><?php echo esc_html( $atts['title'] ); ?></span>
    <?php endif; ?>
</span>
