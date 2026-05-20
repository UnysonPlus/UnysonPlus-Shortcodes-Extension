<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 */

$atts['base_class']         = 'code-block';
$atts['unique_id_prefix']   = 'cb-';

$atts['extra_attrs'] = [];

$attr = sc_build_wrapper_attr( $atts );

$needs_wrapper = ! empty( $atts['css_id'] ) || ! empty( $atts['css_class'] );

?>

<?php if ( ! empty( $atts['code'] ) ) : ?>
    <?php if ( $needs_wrapper ) : ?>
        <div <?php echo fw_attr_to_html( $attr ); ?>>
            <?php echo do_shortcode( $atts['code'] ); ?>
        </div>
    <?php else : ?>
        <?php echo do_shortcode( $atts['code'] ); ?>
    <?php endif; ?>
<?php endif; ?>
