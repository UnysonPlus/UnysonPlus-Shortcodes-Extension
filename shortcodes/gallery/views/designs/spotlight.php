<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Spotlight — the first image is shown large, with the rest tiled in a
 * smaller grid beside it (featured side configurable). Stacks on mobile.
 */

$side = $g_dp( 'feature_side', 'left' );
if ( $side !== 'right' ) {
	$side = 'left';
}
$cols = max( 1, (int) $g_dp( 'columns', 3 ) );
$gap  = sc_gallery_gap_css( $g_dp( 'gap', '3' ) );

$feature = $items[0];
$rest    = array_slice( $items, 1 );

$wrap_style = sprintf( '--gal-cols:%d;--gal-gap:%s;', $cols, $gap );
$wrap_class = 'fw-gallery__spotlight fw-gallery__spotlight--' . $side
	. ( empty( $rest ) ? ' fw-gallery__spotlight--solo' : '' );

$tile_args_sp = $tile_args; // aspect handled by spotlight.css (feature vs grid)
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $container_cls ) : ?><div class="<?php echo esc_attr( $container_cls ); ?>"><?php endif; ?>

		<div class="<?php echo esc_attr( $wrap_class ); ?>" style="<?php echo esc_attr( $wrap_style ); ?>">
			<div class="fw-gallery__spotlight-feature">
				<?php echo sc_gallery_render_tile( $feature, $tile_args_sp ); ?>
			</div>
			<?php if ( ! empty( $rest ) ) : ?>
				<div class="fw-gallery__spotlight-grid">
					<?php foreach ( $rest as $item ) {
						echo sc_gallery_render_tile( $item, $tile_args_sp );
					} ?>
				</div>
			<?php endif; ?>
		</div>
	<?php if ( $container_cls ) : ?></div><?php endif; ?>
</div>
