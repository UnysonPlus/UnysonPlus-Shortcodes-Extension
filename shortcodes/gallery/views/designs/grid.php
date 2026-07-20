<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Grid — uniform responsive tiles. The default design (covered by the
 * always-enqueued base styles.css + grid.css). All variables come from
 * views/view.php by scope.
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
// Column Ratio (split-slider) — per-column widths for a FEATURED / dominant image. Emits an
// explicit desktop grid-template-columns (`--gal-tpl`, fr units); the count of segments is the
// desktop column count. Only applied when the user actually made the columns UNEQUAL (an equal
// split falls back to the plain `--gal-cols` grid). Tablet/mobile stay equal (media queries).
$col_ratio  = $g_c['col_ratio'];
$feat_col   = -1;   // 0-based column of the dominant (widest) tile in featured mode; -1 = off.
$feat_cols  = 0;    // number of columns in featured mode (= segment count).
if ( is_array( $col_ratio ) && count( $col_ratio ) >= 2 ) {
	$ws = array();
	foreach ( $col_ratio as $seg ) { $ws[] = isset( $seg['w'] ) ? max( 1, (float) $seg['w'] ) : 1; }
	if ( ( max( $ws ) - min( $ws ) ) > 2 ) { // meaningfully unequal → a real featured ratio (an equal split falls back to --gal-cols)
		$fr = array();
		foreach ( $ws as $w ) { $fr[] = rtrim( rtrim( number_format( $w, 2, '.', '' ), '0' ), '.' ) . 'fr'; }
		$grid_style .= '--gal-tpl:' . implode( ' ', $fr ) . ';';
		// The widest tile keeps its aspect-ratio (it sets the row height); the narrower
		// tiles stretch to that height (fw-gallery__media--fill) so no dead space shows.
		$feat_col  = array_search( max( $ws ), $ws, true );
		$feat_cols = count( $ws );
	}
}
$grid_class = 'fw-gallery__grid' . ( $ratio === 'original' ? ' fw-gallery__grid--natural' : '' );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $container_cls ) : ?><div class="<?php echo esc_attr( $container_cls ); ?>"><?php endif; ?>

		<div class="<?php echo esc_attr( $grid_class ); ?>" style="<?php echo esc_attr( $grid_style ); ?>">
			<?php foreach ( $items as $i => $item ) {
				$ta = $tile_args;
				if ( $feat_col >= 0 && ( $i % $feat_cols ) !== $feat_col ) {
					// Narrower tile in featured mode → stretch it to the featured tile's row height.
					$ta['media_class'] = trim( ( isset( $tile_args['media_class'] ) ? $tile_args['media_class'] . ' ' : '' ) . 'fw-gallery__media--fill' );
				}
				echo sc_gallery_render_tile( $item, $ta );
			} ?>
		</div>
	<?php if ( $container_cls ) : ?></div><?php endif; ?>
</div>
