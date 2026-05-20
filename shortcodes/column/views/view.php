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

// Optional inner-wrapper class. Sanitisation mirrors how sc_build_wrapper_attr()
// handles css_class (split → lowercase → sanitize_html_class per token).
// Empty value → no inner <div>, so existing columns render exactly as before.
$inner_class = '';
if ( ! empty( $atts['inner_class'] ) ) {
    $tokens = preg_split( '/\s+/', (string) $atts['inner_class'] );
    $clean  = array();
    foreach ( $tokens as $t ) {
        $t = sanitize_html_class( strtolower( trim( $t ) ) );
        if ( $t !== '' ) { $clean[] = $t; }
    }
    $inner_class = implode( ' ', $clean );
}
?>

<div <?php echo fw_attr_to_html( $attr ); ?>>
    <?php if ( $inner_class !== '' ) : ?>
        <div class="<?php echo esc_attr( $inner_class ); ?>">
            <?php echo do_shortcode( $content ); ?>
        </div>
    <?php else : ?>
        <?php echo do_shortcode( $content ); ?>
    <?php endif; ?>
</div>
