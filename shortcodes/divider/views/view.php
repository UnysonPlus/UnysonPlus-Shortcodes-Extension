<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$atts['base_class']       = 'divider-space';
$atts['unique_id_prefix'] = 'ds-';

// Build base attributes for wrapper
$attr = sc_build_wrapper_attr( $atts );

if ( isset( $atts['style']['ruler_type'] ) && 'line' === $atts['style']['ruler_type'] ) :
    // For <hr>, include only id + css_class (if provided)
    if ( empty( $atts['css_class'] ) ) {
        unset( $attr['class'] ); // remove auto classes
    } else {
        $attr['class'] = esc_attr( $atts['css_class'] ); // use only user-defined css_class
    }
    ?>
    <hr <?php echo fw_attr_to_html( $attr ); ?> />
<?php elseif ( isset( $atts['style']['ruler_type'] ) && 'space' === $atts['style']['ruler_type'] ) : ?>
    <?php
    if ( ! empty( $atts['style']['space']['height'] ) ) {
        $height = (int) $atts['style']['space']['height'];
        $attr['style'] = trim( ($attr['style'] ?? '') . "padding-top: {$height}px;" );
    }
    ?>
    <div <?php echo fw_attr_to_html( $attr ); ?>></div>
<?php endif; ?>
