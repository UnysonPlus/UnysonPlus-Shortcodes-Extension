<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Masonry — CSS multi-column staggered tiling (no JS). Images keep their
 * natural proportions; columns balance via CSS `column-count`.
 */

$g_c    = $g_cols( 3 );
$cols   = $g_c['desktop'];
$cols_t = $g_c['tablet'];
$gap    = sc_gallery_gap_css( $g_dp( 'gap', '3' ) );

$masonry_style = sprintf( '--gal-cols:%d;--gal-cols-t:%d;--gal-gap:%s;', $cols, $cols_t, $gap );

/* Masonry always shows natural proportions — override any cropping ratio. */
$tile_args_masonry = array_merge( $tile_args, array( 'media_class' => 'fw-gallery__media--natural' ) );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $container_cls ) : ?><div class="<?php echo esc_attr( $container_cls ); ?>"><?php endif; ?>
		<?php if ( $title !== '' ) : ?>
			<h3 class="fw-gallery__title <?php echo esc_attr( $title_class_extra ); ?>"<?php echo $title_style_extra !== '' ? ' style="' . esc_attr( $title_style_extra ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<div class="fw-gallery__masonry" style="<?php echo esc_attr( $masonry_style ); ?>">
			<?php foreach ( $items as $item ) {
				echo sc_gallery_render_tile( $item, $tile_args_masonry );
			} ?>
		</div>
	<?php if ( $container_cls ) : ?></div><?php endif; ?>
</div>
