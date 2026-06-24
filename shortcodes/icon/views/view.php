<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 */

/*
|--------------------------------------------------------------------------
| Enqueue icon-v2 frontend CSS (only when icon is used)
|--------------------------------------------------------------------------
*/
if (
    ! empty( $atts['icon'] ) &&
    isset( fw()->backend->option_type( 'icon-v2' )->packs_loader )
) {
    fw()->backend->option_type( 'icon-v2' )->packs_loader->enqueue_pack_for_icon( $atts['icon'] );
}

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
$icon_type = $icon['type'] ?? '';

$icon_classes  = $icon_type === 'icon-font' ? array( trim( (string) ( $icon['icon-class'] ?? '' ) ) ) : array( 'icon-image' );
$icon_classes  = array_merge( $icon_classes, $icon_extras );
$title_classes = array_merge( array( 'list-title' ), $title_extras );
$icon_style_attr  = $icon_style  !== '' ? ' style="' . esc_attr( $icon_style ) . '"'  : '';
$title_style_attr = $title_style !== '' ? ' style="' . esc_attr( $title_style ) . '"' : '';
?>
<span <?php echo fw_attr_to_html( $attr ); ?>>
    <?php if ( $icon_type === 'icon-font' && ! empty( $icon['icon-class'] ) ) : ?>
        <i class="<?php echo esc_attr( trim( implode( ' ', $icon_classes ) ) ); ?>"<?php echo $icon_style_attr; ?> aria-hidden="true"></i>
    <?php elseif ( $icon_type === 'custom-upload' && ! empty( $icon['url'] ) ) : ?>
        <img src="<?php echo esc_url( $icon['url'] ); ?>" alt="<?php echo esc_attr( $icon['alt'] ?? '' ); ?>" class="<?php echo esc_attr( trim( implode( ' ', $icon_classes ) ) ); ?>"<?php echo $icon_style_attr; ?> loading="lazy">
    <?php endif; ?>

    <?php if ( ! empty( $atts['title'] ) ) : ?>
        <span class="<?php echo esc_attr( implode( ' ', $title_classes ) ); ?>"<?php echo $title_style_attr; ?>><?php echo esc_html( $atts['title'] ); ?></span>
    <?php endif; ?>
</span>
