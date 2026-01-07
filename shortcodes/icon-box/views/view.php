<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}
/**
 * @var array $atts
 */

// Always set these before building attributes
$atts['base_class']       = 'iconbox';
$atts['unique_id_prefix'] = 'ib-';

// Prepend style + clearfix into css_class (so the helper manages classes consistently)
if ( ! empty( $atts['style'] ) ) {
    $atts['css_class'] = trim( $atts['style'] . ' clearfix ' . ( $atts['css_class'] ?? '' ) );
} else {
    $atts['css_class'] = trim( 'clearfix ' . ( $atts['css_class'] ?? '' ) );
}

$attr = sc_build_wrapper_attr( $atts );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
    <div class="iconbox-image">
        <i class="<?php echo esc_attr( $atts['icon'] ); ?>"></i>
    </div>
    <div class="iconbox-aside">
        <div class="iconbox-title">
            <h3><?php echo esc_html( $atts['title'] ); ?></h3>
        </div>
        <div class="iconbox-text">
            <p><?php echo wp_kses_post( $atts['content'] ); ?></p>
        </div>
    </div>
</div>
