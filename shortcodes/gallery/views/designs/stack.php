<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Stack / Banners — each image is a full-width stacked strip cropped to
 * a wide cinematic ratio, with a subtle zoom on hover. Great for landscape
 * photography where each shot should read big.
 */

$ratio     = $g_dp( 'banner_ratio', '21-9' );
$ratio_css = sc_gallery_ratio_css( $ratio );
$gap       = sc_gallery_gap_css( $g_dp( 'gap', '3' ) );

$wrap_style = sprintf( '--gal-gap:%s;', $gap );
if ( $ratio_css !== '' ) {
	$wrap_style .= '--gal-ratio:' . $ratio_css . ';';
}
$wrap_class = 'fw-gallery__stack' . ( $ratio === 'original' ? ' fw-gallery__stack--natural' : '' );

$tile_args_stack = array_merge( $tile_args, array( 'media_class' => 'fw-gallery__media--fill' ) );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $container_cls ) : ?><div class="<?php echo esc_attr( $container_cls ); ?>"><?php endif; ?>
		<?php if ( $title !== '' ) : ?>
			<h3 class="fw-gallery__title <?php echo esc_attr( $title_class_extra ); ?>"<?php echo $title_style_extra !== '' ? ' style="' . esc_attr( $title_style_extra ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<div class="<?php echo esc_attr( $wrap_class ); ?>" style="<?php echo esc_attr( $wrap_style ); ?>">
			<?php foreach ( $items as $item ) {
				echo sc_gallery_render_tile( $item, $tile_args_stack );
			} ?>
		</div>
	<?php if ( $container_cls ) : ?></div><?php endif; ?>
</div>
