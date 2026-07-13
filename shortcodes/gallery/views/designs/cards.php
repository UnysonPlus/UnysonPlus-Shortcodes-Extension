<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Cards — each image in a shadowed, rounded card with the caption on a
 * panel below. Like Grid, but every tile reads as a self-contained card.
 */

$g_c    = $g_cols( 3 );
$cols   = $g_c['desktop'];
$cols_t = $g_c['tablet'];
$cols_m = $g_c['mobile'];
$gap    = sc_gallery_gap_css( $g_dp( 'gap', '3' ) );
$ratio  = $g_dp( 'ratio', '4-3' );
$ratio_css = sc_gallery_ratio_css( $ratio );

$grid_style = sprintf( '--gal-cols:%d;--gal-cols-t:%d;--gal-cols-m:%d;--gal-gap:%s;', $cols, $cols_t, $cols_m, $gap );
if ( $ratio_css !== '' ) {
	$grid_style .= '--gal-ratio:' . $ratio_css . ';';
}
$cards_class = 'fw-gallery__cards' . ( $ratio === 'original' ? ' fw-gallery__cards--natural' : '' );

/* Cards always read with a caption panel below (rounded media on top). */
$tile_args_cards = array_merge( $tile_args, array( 'captions' => 'below', 'rounded' => 'rounded-0' ) );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $container_cls ) : ?><div class="<?php echo esc_attr( $container_cls ); ?>"><?php endif; ?>
		<?php if ( $title !== '' ) : ?>
			<h3 class="fw-gallery__title <?php echo esc_attr( $title_class_extra ); ?>"<?php echo $title_style_extra !== '' ? ' style="' . esc_attr( $title_style_extra ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<div class="<?php echo esc_attr( $cards_class ); ?>" style="<?php echo esc_attr( $grid_style ); ?>">
			<?php foreach ( $items as $item ) {
				echo sc_gallery_render_tile( $item, $tile_args_cards );
			} ?>
		</div>
	<?php if ( $container_cls ) : ?></div><?php endif; ?>
</div>
