<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Image Accordion — images as side-by-side panels; hovering a panel
 * expands it while the others shrink (CSS flex transition). Stacks into rows on
 * small screens. Best for a small curated set (4–7 images).
 */

$height = max( 120, (int) $g_dp( 'row_height', 340 ) );
$gap    = sc_gallery_gap_css( $g_dp( 'gap', '3' ) );

$wrap_style = sprintf( '--acc-h:%dpx;--gal-gap:%s;', $height, $gap );

$tile_args_acc = array_merge( $tile_args, array( 'media_class' => 'fw-gallery__media--fill', 'rounded' => 'rounded-0' ) );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $container_cls ) : ?><div class="<?php echo esc_attr( $container_cls ); ?>"><?php endif; ?>

		<div class="fw-gallery__accordion" style="<?php echo esc_attr( $wrap_style ); ?>">
			<?php foreach ( $items as $item ) {
				echo sc_gallery_render_tile( $item, $tile_args_acc );
			} ?>
		</div>
	<?php if ( $container_cls ) : ?></div><?php endif; ?>
</div>
