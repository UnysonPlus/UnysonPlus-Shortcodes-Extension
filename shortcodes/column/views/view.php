<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 * @var string $content
 */

// Base column class from width
$width_class = fw_ext_builder_get_item_width( 'page-builder', $atts['width'] . '/frontend_class' );

// Build attributes for wrapper
$attr = sc_build_wrapper_attr( $atts );

// Add width class as a separate class
if ( ! empty( $width_class ) ) {
    if ( isset( $attr['class'] ) ) {
        $attr['class'] = esc_attr( $width_class ) . ' ' . $attr['class'];
    } else {
        $attr['class'] = esc_attr( $width_class );
    }
}
?>

<div <?php echo fw_attr_to_html( $attr ); ?>>
    <?php echo do_shortcode( $content ); ?>
</div>
