<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}
/**
 * @var array $atts
 */

// Build wrapper attributes
$atts['base_class']       = 'widget-area';
$atts['unique_id_prefix'] = 'wa-';
$attr = sc_build_wrapper_attr( $atts );

if ( ! empty( $atts['sidebar'] ) && is_active_sidebar( $atts['sidebar'] ) ) {
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
    <?php
        dynamic_sidebar( $atts['sidebar'] );
    ?>
</div>
<?php } ?>