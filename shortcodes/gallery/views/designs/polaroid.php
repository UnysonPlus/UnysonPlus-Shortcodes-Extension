<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Polaroid Scatter — framed photo cards with a white border + caption
 * plate and an optional slight random tilt that straightens on hover.
 */

$g_c    = $g_cols( 4 );
$cols   = $g_c['desktop'];
$cols_t = $g_c['tablet'];
$gap    = sc_gallery_gap_css( $g_dp( 'gap', '3' ) );
$tilt   = $g_dp( 'tilt', 'yes' ) === 'yes';

$wrap_style = sprintf( '--gal-cols:%d;--gal-cols-t:%d;--gal-gap:%s;', $cols, $cols_t, $gap );
$wrap_class = 'fw-gallery__polaroid' . ( $tilt ? ' fw-gallery__polaroid--tilt' : '' );

/* Polaroids always read as photos with a caption plate; force a below caption
   from the chosen source and a square-ish crop, regardless of the cross-design
   caption mode. */
$tilt_steps = array( '-2.4deg', '1.8deg', '-1.2deg', '2.6deg', '-1.8deg', '1.2deg', '-2.8deg', '2deg' );
$i = 0;
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $container_cls ) : ?><div class="<?php echo esc_attr( $container_cls ); ?>"><?php endif; ?>

		<div class="<?php echo esc_attr( $wrap_class ); ?>" style="<?php echo esc_attr( $wrap_style ); ?>">
			<?php foreach ( $items as $item ) {
				$item_style = $tilt ? '--gal-tilt:' . $tilt_steps[ $i % count( $tilt_steps ) ] : '';
				$i++;
				echo sc_gallery_render_tile( $item, array_merge( $tile_args, array(
					'rounded'     => 'rounded-0',
					'captions'    => 'below',
					'media_class' => 'fw-gallery__media--fill',
					'item_style'  => $item_style,
				) ) );
			} ?>
		</div>
	<?php if ( $container_cls ) : ?></div><?php endif; ?>
</div>
