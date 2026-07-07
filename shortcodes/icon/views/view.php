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

// Central icon render: font <i> keeps the icon-color classes/style; a custom
// upload keeps its 'icon-image' base class. Pack CSS is auto-enqueued.
$icon_html = sc_icon_render( $icon, array(
    'class'     => implode( ' ', $icon_extras ),
    'img_class' => 'icon-image',
    'style'     => $icon_style,
) );
?>
<span <?php echo fw_attr_to_html( $attr ); ?>>
    <?php echo $icon_html; // already escaped by sc_icon_render() ?>

    <?php if ( ! empty( $atts['title'] ) ) : ?>
        <span class="<?php echo esc_attr( implode( ' ', $title_classes ) ); ?>"<?php echo $title_style_attr; ?>><?php echo esc_html( $atts['title'] ); ?></span>
    <?php endif; ?>
</span>
