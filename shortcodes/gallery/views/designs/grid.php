<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Grid — uniform responsive tiles. The default design (covered by the
 * always-enqueued base styles.css + grid.css). All variables come from
 * views/view.php by scope.
 */

$cols   = max( 1, (int) $g_dp( 'columns', 3 ) );
$cols_t = max( 1, (int) $g_dp( 'columns_tablet', 2 ) );
$cols_m = max( 1, (int) $g_dp( 'columns_mobile', 1 ) );
$gap    = sc_gallery_gap_css( $g_dp( 'gap', '3' ) );
$ratio  = $g_dp( 'ratio', '4-3' );
$ratio_css = sc_gallery_ratio_css( $ratio );

$grid_style = sprintf( '--gal-cols:%d;--gal-cols-t:%d;--gal-cols-m:%d;--gal-gap:%s;', $cols, $cols_t, $cols_m, $gap );
if ( $ratio_css !== '' ) {
	$grid_style .= '--gal-ratio:' . $ratio_css . ';';
}
$grid_class = 'fw-gallery__grid' . ( $ratio === 'original' ? ' fw-gallery__grid--natural' : '' );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $container_cls ) : ?><div class="<?php echo esc_attr( $container_cls ); ?>"><?php endif; ?>
		<?php if ( $title !== '' ) : ?>
			<h3 class="fw-gallery__title <?php echo esc_attr( $title_class_extra ); ?>"<?php echo $title_style_extra !== '' ? ' style="' . esc_attr( $title_style_extra ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<div class="<?php echo esc_attr( $grid_class ); ?>" style="<?php echo esc_attr( $grid_style ); ?>">
			<?php foreach ( $items as $item ) {
				echo sc_gallery_render_tile( $item, $tile_args );
			} ?>
		</div>
	<?php if ( $container_cls ) : ?></div><?php endif; ?>
</div>
