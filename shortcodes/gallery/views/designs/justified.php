<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Justified Rows — Flickr-style equal-height rows. Computed server-side
 * from each image's real aspect ratio (no layout-shift): every item gets a
 * `--ar` (width/height) custom property and justified.css uses flexbox grow so
 * each row fills the available width at roughly the target row height.
 */

$row = max( 80, (int) $g_dp( 'row_height', 220 ) );
$gap = sc_gallery_gap_css( $g_dp( 'gap', '3' ) );

$wrap_style = sprintf( '--gal-row:%dpx;--gal-gap:%s;', $row, $gap );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $container_cls ) : ?><div class="<?php echo esc_attr( $container_cls ); ?>"><?php endif; ?>

		<div class="fw-gallery__justified" style="<?php echo esc_attr( $wrap_style ); ?>">
			<?php foreach ( $items as $item ) {
				$ar = ( $item['h'] > 0 ) ? ( $item['w'] / $item['h'] ) : 1.5;
				$ar = max( 0.3, min( 4.0, $ar ) );
				$ar_str = rtrim( rtrim( number_format( $ar, 4, '.', '' ), '0' ), '.' );

				echo sc_gallery_render_tile( $item, array_merge( $tile_args, array(
					'item_style'  => '--ar:' . $ar_str,
					'media_class' => 'fw-gallery__media--fill',
				) ) );
			} ?>
		</div>
	<?php if ( $container_cls ) : ?></div><?php endif; ?>
</div>
