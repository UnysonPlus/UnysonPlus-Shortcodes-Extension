<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 */

$atts['base_class']         = 'accordion';
$atts['unique_id_prefix']   = 'ac-';

// Build attributes for wrapper
$attr = sc_build_wrapper_attr( $atts );
?>

<?php if ( ! empty( $atts['tabs'] ) ) : ?>
	<div <?php echo fw_attr_to_html( $attr ); ?>>
		<?php foreach ( fw_akg( 'tabs', $atts, array() ) as $tab ) : ?>
			<h3 class="accordion-title"><?php echo $tab['tab_title']; ?></h3>
			<div class="accordion-content">
				<p><?php echo do_shortcode( $tab['tab_content'] ); ?></p>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>





