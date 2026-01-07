<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}





/**
 * @var array $atts
 */

// Build wrapper attributes
$atts['base_class']       = 'icon';
$atts['unique_id_prefix'] = 'ic-';
$attr = sc_build_wrapper_attr( $atts );
fw_print($atts['icon']);
?>
<span <?php echo fw_attr_to_html( $attr ); ?>>
	<i class="<?php echo esc_attr( $atts['icon'] ); ?>"></i>
	<?php if ( ! empty( $atts['title'] ) ) : ?>
		<span class="list-title"><?php echo esc_html( $atts['title'] ); ?></span>
	<?php endif; ?>
</span>
