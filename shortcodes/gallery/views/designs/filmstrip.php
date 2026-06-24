<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Filmstrip — a single horizontal row with native scroll-snap (no
 * library). Lightweight and touch-friendly; ~per_view images are visible and
 * the user scrolls/swipes through the rest.
 */

$per_view  = max( 1, (int) $g_dp( 'per_view', 3 ) );
$gap       = sc_gallery_gap_css( $g_dp( 'gap', '3' ) );
$ratio     = $g_dp( 'ratio', '4-3' );
$ratio_css = sc_gallery_ratio_css( $ratio );

$wrap_style = sprintf( '--fs-per:%d;--gal-gap:%s;', $per_view, $gap );
if ( $ratio_css !== '' ) {
	$wrap_style .= '--gal-ratio:' . $ratio_css . ';';
}
$wrap_class = 'fw-gallery__filmstrip' . ( $ratio === 'original' ? ' fw-gallery__filmstrip--natural' : '' );

$tile_args_fs = array_merge( $tile_args, array( 'media_class' => 'fw-gallery__media--fill' ) );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $container_cls ) : ?><div class="<?php echo esc_attr( $container_cls ); ?>"><?php endif; ?>
		<?php if ( $title !== '' ) : ?>
			<h3 class="fw-gallery__title <?php echo esc_attr( $title_class_extra ); ?>"<?php echo $title_style_extra !== '' ? ' style="' . esc_attr( $title_style_extra ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<div class="<?php echo esc_attr( $wrap_class ); ?>" style="<?php echo esc_attr( $wrap_style ); ?>">
			<?php foreach ( $items as $item ) {
				echo sc_gallery_render_tile( $item, $tile_args_fs );
			} ?>
		</div>
	<?php if ( $container_cls ) : ?></div><?php endif; ?>
</div>
