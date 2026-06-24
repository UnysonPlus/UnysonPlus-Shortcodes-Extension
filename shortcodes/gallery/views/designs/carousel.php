<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Carousel Slider — the vendored Splide slider (shared with the Carousel
 * shortcode). Each image is one slide; `per_view` controls how many show at once.
 * carousel.js mounts the slider from the data-splide config; lightbox.js still
 * handles clicks (the lightbox anchor markup is identical to other designs).
 */

$per_view = max( 1, (int) $g_dp( 'per_view', 3 ) );
$gap_slug = $g_dp( 'gap', '3' );
$gap_size = sc_gallery_gap_size( $gap_slug ); // concrete length for Splide's JS gap
$ratio    = $g_dp( 'ratio', '4-3' );
$ratio_css = sc_gallery_ratio_css( $ratio );

$autoplay    = $g_dp( 'carousel_autoplay', 'no' ) === 'yes';
$interval    = (int) $g_dp( 'carousel_interval', 4000 );
$pause_hover = $g_dp( 'carousel_pause_hover', 'yes' ) === 'yes';
$loop        = $g_dp( 'carousel_loop', 'yes' ) === 'yes';
$arrows      = $g_dp( 'carousel_arrows', 'yes' ) === 'yes';
$dots        = $g_dp( 'carousel_dots', 'yes' ) === 'yes';

$show_nav = count( $items ) > $per_view;

$splide_config = array(
	'type'         => $loop ? 'loop' : 'slide',
	'perPage'      => $per_view,
	'perMove'      => 1,
	'rewind'       => ! $loop,
	'arrows'       => ( $arrows && $show_nav ),
	'pagination'   => ( $dots && $show_nav ),
	'autoplay'     => $autoplay,
	'interval'     => $interval,
	'pauseOnHover' => $pause_hover,
	'pauseOnFocus' => true,
	'gap'          => $gap_size,
	'breakpoints'  => array(
		992 => array( 'perPage' => max( 1, min( 2, $per_view ) ) ),
		576 => array( 'perPage' => 1 ),
	),
);

$wrap_style = '';
if ( $ratio_css !== '' ) {
	$wrap_style = '--gal-ratio:' . $ratio_css . ';';
}
$slider_class = 'splide fw-gallery__carousel' . ( $ratio === 'original' ? ' fw-gallery__carousel--natural' : '' );

/* Carousel cells get their height from the ratio; image fills it. */
$tile_args_carousel = array_merge( $tile_args, array( 'media_class' => 'fw-gallery__media--fill' ) );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $container_cls ) : ?><div class="<?php echo esc_attr( $container_cls ); ?>"><?php endif; ?>
		<?php if ( $title !== '' ) : ?>
			<h3 class="fw-gallery__title <?php echo esc_attr( $title_class_extra ); ?>"<?php echo $title_style_extra !== '' ? ' style="' . esc_attr( $title_style_extra ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<div class="<?php echo esc_attr( $slider_class ); ?>"<?php echo $wrap_style !== '' ? ' style="' . esc_attr( $wrap_style ) . '"' : ''; ?>
			role="group" aria-label="<?php echo esc_attr( $title !== '' ? $title : __( 'Image gallery', 'fw' ) ); ?>"
			data-fw-splide data-splide="<?php echo esc_attr( wp_json_encode( $splide_config ) ); ?>">
			<div class="splide__track">
				<ul class="splide__list">
					<?php foreach ( $items as $item ) : ?>
						<li class="splide__slide">
							<?php echo sc_gallery_render_tile( $item, $tile_args_carousel ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	<?php if ( $container_cls ) : ?></div><?php endif; ?>
</div>
