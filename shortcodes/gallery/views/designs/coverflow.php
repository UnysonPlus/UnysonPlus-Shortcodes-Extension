<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Coverflow — a centred slider where the focused slide is full size and
 * the neighbours scale/fade back. Runs on the shared Splide mount (carousel.js)
 * with focus:center; the scale/opacity falloff is CSS (.is-active).
 */

$per_view  = max( 1, (int) $g_dp( 'per_view', 3 ) );
$gap_slug  = $g_dp( 'gap', '3' );
$gap_size  = sc_gallery_gap_size( $gap_slug );
$ratio     = $g_dp( 'ratio', '4-3' );
$ratio_css = sc_gallery_ratio_css( $ratio );

$autoplay    = $g_dp( 'carousel_autoplay', 'no' ) === 'yes';
$interval    = (int) $g_dp( 'carousel_interval', 4000 );
$pause_hover = $g_dp( 'carousel_pause_hover', 'yes' ) === 'yes';
$loop        = $g_dp( 'carousel_loop', 'yes' ) === 'yes';
$arrows      = $g_dp( 'carousel_arrows', 'yes' ) === 'yes';
$dots        = $g_dp( 'carousel_dots', 'yes' ) === 'yes';

$show_nav = count( $items ) > 1;

$splide_config = array(
	'type'         => $loop ? 'loop' : 'slide',
	'rewind'       => ! $loop,
	'focus'        => 'center',
	'perPage'      => $per_view,
	'perMove'      => 1,
	'updateOnMove' => true,
	'arrows'       => ( $arrows && $show_nav ),
	'pagination'   => ( $dots && $show_nav ),
	'autoplay'     => $autoplay,
	'interval'     => $interval,
	'pauseOnHover' => $pause_hover,
	'pauseOnFocus' => true,
	'gap'          => $gap_size,
	'breakpoints'  => array(
		992 => array( 'perPage' => max( 1, min( 3, $per_view ) ) ),
		576 => array( 'perPage' => 1 ),
	),
);

$wrap_style = '';
if ( $ratio_css !== '' ) {
	$wrap_style = '--gal-ratio:' . $ratio_css . ';';
}
$slider_class = 'splide fw-gallery__carousel fw-gallery__coverflow'
	. ( $ratio === 'original' ? ' fw-gallery__coverflow--natural' : '' );

$tile_args_cf = array_merge( $tile_args, array( 'media_class' => 'fw-gallery__media--fill' ) );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $container_cls ) : ?><div class="<?php echo esc_attr( $container_cls ); ?>"><?php endif; ?>

		<div class="<?php echo esc_attr( $slider_class ); ?>"<?php echo $wrap_style !== '' ? ' style="' . esc_attr( $wrap_style ) . '"' : ''; ?>
			role="group" aria-label="<?php echo esc_attr( __( 'Image gallery', 'fw' ) ); ?>"
			data-fw-splide data-splide="<?php echo esc_attr( wp_json_encode( $splide_config ) ); ?>">
			<div class="splide__track">
				<ul class="splide__list">
					<?php foreach ( $items as $item ) : ?>
						<li class="splide__slide">
							<?php echo sc_gallery_render_tile( $item, $tile_args_cf ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	<?php if ( $container_cls ) : ?></div><?php endif; ?>
</div>
