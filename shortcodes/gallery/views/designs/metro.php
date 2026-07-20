<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Metro / Bento — a dense grid where every Nth cell spans 2×2 (and a few
 * span 2 wide / 2 tall) for an editorial mosaic. The span pattern is driven by
 * nth-child rules in metro.css, so the template just emits uniform tiles into a
 * `grid-auto-flow: dense` container.
 */

$cols = max( 2, (int) $g_dp( 'columns', 4 ) );
$gap  = sc_gallery_gap_css( $g_dp( 'gap', '3' ) );

$wrap_style = sprintf( '--gal-cols:%d;--gal-gap:%s;', $cols, $gap );

/* Cells are sized by the grid (object-fit cover fills them) — drop any ratio. */
$tile_args_metro = array_merge( $tile_args, array( 'media_class' => 'fw-gallery__media--fill' ) );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $container_cls ) : ?><div class="<?php echo esc_attr( $container_cls ); ?>"><?php endif; ?>

		<div class="fw-gallery__metro" style="<?php echo esc_attr( $wrap_style ); ?>">
			<?php foreach ( $items as $item ) {
				echo sc_gallery_render_tile( $item, $tile_args_metro );
			} ?>
		</div>
	<?php if ( $container_cls ) : ?></div><?php endif; ?>
</div>
