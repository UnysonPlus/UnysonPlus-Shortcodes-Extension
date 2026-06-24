<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Slideshow / Fade — one full-width image at a time, crossfading, with
 * optional Ken-Burns zoom. Runs on the shared Splide mount (carousel.js) in fade
 * mode. lightbox.js still handles clicks via the standard tile anchor.
 */

$ratio     = $g_dp( 'ratio', '4-3' );
$ratio_css = sc_gallery_ratio_css( $ratio );
$ken_burns = $g_dp( 'ken_burns', 'yes' ) === 'yes';

$autoplay    = $g_dp( 'carousel_autoplay', 'yes' ) === 'yes';
$interval    = (int) $g_dp( 'carousel_interval', 5000 );
$pause_hover = $g_dp( 'carousel_pause_hover', 'yes' ) === 'yes';
$loop        = $g_dp( 'carousel_loop', 'yes' ) === 'yes';
$arrows      = $g_dp( 'carousel_arrows', 'yes' ) === 'yes';
$dots        = $g_dp( 'carousel_dots', 'yes' ) === 'yes';

$show_nav = count( $items ) > 1;

$splide_config = array(
	'type'         => 'fade',
	'rewind'       => true,
	'perPage'      => 1,
	'perMove'      => 1,
	'arrows'       => ( $arrows && $show_nav ),
	'pagination'   => ( $dots && $show_nav ),
	'autoplay'     => $autoplay,
	'interval'     => $interval,
	'pauseOnHover' => $pause_hover,
	'pauseOnFocus' => true,
	'speed'        => 700,
);
if ( ! $loop ) {
	$splide_config['rewind'] = false;
}

$wrap_style = '';
if ( $ratio_css !== '' ) {
	$wrap_style = '--gal-ratio:' . $ratio_css . ';';
}
$slider_class = 'splide fw-gallery__carousel fw-gallery__slideshow'
	. ( $ratio === 'original' ? ' fw-gallery__slideshow--natural' : '' )
	. ( $ken_burns ? ' fw-gallery__slideshow--kb' : '' );

$tile_args_slideshow = array_merge( $tile_args, array( 'media_class' => 'fw-gallery__media--fill', 'rounded' => 'rounded-0', 'hover_zoom' => false ) );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $container_cls ) : ?><div class="<?php echo esc_attr( $container_cls ); ?>"><?php endif; ?>
		<?php if ( $title !== '' ) : ?>
			<h3 class="fw-gallery__title <?php echo esc_attr( $title_class_extra ); ?>"<?php echo $title_style_extra !== '' ? ' style="' . esc_attr( $title_style_extra ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<div class="<?php echo esc_attr( $slider_class ); ?>"<?php echo $wrap_style !== '' ? ' style="' . esc_attr( $wrap_style ) . '"' : ''; ?>
			role="group" aria-label="<?php echo esc_attr( $title !== '' ? $title : __( 'Image slideshow', 'fw' ) ); ?>"
			data-fw-splide data-splide="<?php echo esc_attr( wp_json_encode( $splide_config ) ); ?>">
			<div class="splide__track">
				<ul class="splide__list">
					<?php foreach ( $items as $item ) : ?>
						<li class="splide__slide">
							<?php echo sc_gallery_render_tile( $item, $tile_args_slideshow ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	<?php if ( $container_cls ) : ?></div><?php endif; ?>
</div>
