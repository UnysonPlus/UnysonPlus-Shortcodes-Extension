<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Honeycomb — hexagon-tiled mosaic (CSS clip-path). Hexagons are sized
 * to the chosen column count; rows interlock via negative margins, and every
 * other row is shifted by half a cell (handled in CSS from --hc-cols).
 */

$cols = max( 2, (int) $g_dp( 'columns', 4 ) );
$gap  = sc_gallery_gap_css( $g_dp( 'gap', '3' ) );

$wrap_style = sprintf( '--hc-cols:%d;--gal-gap:%s;', $cols, $gap );

$tile_args_hc = array_merge( $tile_args, array( 'media_class' => 'fw-gallery__media--fill', 'rounded' => 'rounded-0' ) );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $container_cls ) : ?><div class="<?php echo esc_attr( $container_cls ); ?>"><?php endif; ?>
		<?php if ( $title !== '' ) : ?>
			<h3 class="fw-gallery__title <?php echo esc_attr( $title_class_extra ); ?>"<?php echo $title_style_extra !== '' ? ' style="' . esc_attr( $title_style_extra ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<div class="fw-gallery__honeycomb" style="<?php echo esc_attr( $wrap_style ); ?>">
			<?php foreach ( $items as $item ) {
				echo sc_gallery_render_tile( $item, $tile_args_hc );
			} ?>
		</div>
	<?php if ( $container_cls ) : ?></div><?php endif; ?>
</div>
