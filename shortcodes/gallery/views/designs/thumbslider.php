<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Thumbnail Slider — a main image slider synced to a thumbnail nav
 * strip (two Splides). Mounted + synced by thumbnail-slider.js. The main slides
 * are standard lightbox tiles; clicking a thumbnail navigates the main slider.
 */

$ratio     = $g_dp( 'ratio', '4-3' );
$ratio_css = sc_gallery_ratio_css( $ratio );

$autoplay    = $g_dp( 'carousel_autoplay', 'no' ) === 'yes';
$interval    = (int) $g_dp( 'carousel_interval', 4000 );
$pause_hover = $g_dp( 'carousel_pause_hover', 'yes' ) === 'yes';
$loop        = $g_dp( 'carousel_loop', 'yes' ) === 'yes';
$arrows      = $g_dp( 'carousel_arrows', 'yes' ) === 'yes';

$show_nav = count( $items ) > 1;

$main_config = array(
	'type'         => $loop ? 'loop' : 'slide',
	'rewind'       => ! $loop,
	'perPage'      => 1,
	'perMove'      => 1,
	'arrows'       => ( $arrows && $show_nav ),
	'pagination'   => false,
	'autoplay'     => $autoplay,
	'interval'     => $interval,
	'pauseOnHover' => $pause_hover,
	'pauseOnFocus' => true,
);
$nav_config = array(
	'type'          => 'slide',
	'rewind'        => ! $loop,
	'fixedWidth'    => 96,
	'fixedHeight'   => 64,
	'gap'           => 8,
	'cover'         => true,
	'focus'         => 'center',
	'isNavigation'  => true,
	'arrows'        => false,
	'pagination'    => false,
	'updateOnMove'  => true,
	'dragMinThreshold' => 10,
);

$wrap_style = '';
if ( $ratio_css !== '' ) {
	$wrap_style = '--gal-ratio:' . $ratio_css . ';';
}
$root_class = 'fw-gallery__tnav' . ( $ratio === 'original' ? ' fw-gallery__tnav--natural' : '' );

$tile_args_main = array_merge( $tile_args, array( 'media_class' => 'fw-gallery__media--fill', 'rounded' => 'rounded-0' ) );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $container_cls ) : ?><div class="<?php echo esc_attr( $container_cls ); ?>"><?php endif; ?>
		<?php if ( $title !== '' ) : ?>
			<h3 class="fw-gallery__title <?php echo esc_attr( $title_class_extra ); ?>"<?php echo $title_style_extra !== '' ? ' style="' . esc_attr( $title_style_extra ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<div class="<?php echo esc_attr( $root_class ); ?>"<?php echo $wrap_style !== '' ? ' style="' . esc_attr( $wrap_style ) . '"' : ''; ?>
			data-thumbnav-main="<?php echo esc_attr( wp_json_encode( $main_config ) ); ?>"
			data-thumbnav-nav="<?php echo esc_attr( wp_json_encode( $nav_config ) ); ?>">

			<div class="splide fw-gallery__tnav-main" role="group" aria-label="<?php echo esc_attr( $title !== '' ? $title : __( 'Image gallery', 'fw' ) ); ?>">
				<div class="splide__track">
					<ul class="splide__list">
						<?php foreach ( $items as $item ) : ?>
							<li class="splide__slide">
								<?php echo sc_gallery_render_tile( $item, $tile_args_main ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>

			<div class="splide fw-gallery__tnav-nav" aria-label="<?php esc_attr_e( 'Gallery thumbnails', 'fw' ); ?>">
				<div class="splide__track">
					<ul class="splide__list">
						<?php foreach ( $items as $idx => $item ) :
							$thumb_src = $item['url'];
							if ( $item['id'] ) {
								$tsrc = wp_get_attachment_image_src( $item['id'], 'thumbnail' );
								if ( $tsrc ) {
									$thumb_src = $tsrc[0];
								}
							}
							?>
							<li class="splide__slide">
								<img src="<?php echo esc_url( $thumb_src ); ?>" alt="<?php echo esc_attr( sprintf( __( 'Thumbnail %d', 'fw' ), $idx + 1 ) ); ?>" loading="lazy" decoding="async" />
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>

		</div>
	<?php if ( $container_cls ) : ?></div><?php endif; ?>
</div>
