<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}
/**
 * @var array $atts
 */

if ( empty( $atts['sidebar'] ) || ! is_active_sidebar( $atts['sidebar'] ) ) {
    return;
}

// Build wrapper attributes
$atts['base_class']       = 'widget-area';
$atts['unique_id_prefix'] = 'wa-';
$attr = sc_build_wrapper_attr( $atts );

/*
 * Drop the wrapper <div> entirely when nothing styling / animation / advanced
 * is set. In that case sc_build_wrapper_attr() returns only the base class
 * (`widget-area`) + the auto unique class (`wa-xxxx`) and no other attribute,
 * so the wrapper would be pure DOM noise around the widget output — render the
 * widgets bare instead. Anything beyond that baseline keeps the wrapper:
 * a text/background color, font-size preset or spacing (Styling), an animation
 * (Animations), or a custom CSS class/ID, custom attribute or custom CSS
 * (Advanced) — each of which adds a class / id / style / data-attr here.
 */
$baseline_classes = array_filter( array( 'widget-area', sc_element_unique_class( $atts ) ) );
$attr_classes     = isset( $attr['class'] ) ? array_filter( preg_split( '/\s+/', trim( $attr['class'] ) ) ) : array();
$extra_classes    = array_diff( $attr_classes, $baseline_classes );
$other_attrs      = $attr;
unset( $other_attrs['class'] );

$needs_wrapper = ! empty( $other_attrs ) || ! empty( $extra_classes );

if ( $needs_wrapper ) {
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
    <?php dynamic_sidebar( $atts['sidebar'] ); ?>
</div>
<?php
} else {
    dynamic_sidebar( $atts['sidebar'] );
}
