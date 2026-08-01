<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Thumbnail Nav Slider.
 *
 * A large main quote slider synced to a row of avatar thumbnails used as
 * navigation (Apple-style). Two Splide instances synced by the per-design
 * script static/js/designs/thumbnav.js. All variables come from views/view.php.
 */

if ( empty( $testimonials ) ) {
	echo '<div ' . fw_attr_to_html( $attr ) . '><div class="' . esc_attr( $container_cls ) . '"><div class="text-muted small">' . esc_html__( 'No testimonials found.', 'fw' ) . '</div></div></div>';
	return;
}

$main_config = array(
	'type'         => $carousel_wrap ? 'loop' : 'slide',
	'perPage'      => 1,
	'perMove'      => 1,
	'rewind'       => ! $carousel_wrap,
	'pagination'   => false,
	'arrows'       => $carousel_controls,
	'autoplay'     => $carousel_autoplay,
	'interval'     => $carousel_interval,
	'pauseOnHover' => $carousel_pause_hover,
	'pauseOnFocus' => true,
	'speed'        => 600,
);
$nav_config = array(
	'type'              => $carousel_wrap ? 'loop' : 'slide',
	'rewind'            => ! $carousel_wrap,
	'fixedWidth'        => 72,
	'fixedHeight'       => 72,
	'gap'               => 12,
	'cover'             => true,
	'focus'             => 'center',
	'pagination'        => false,
	'arrows'            => false,
	'isNavigation'      => true,
	'dragMinThreshold'  => array( 'mouse' => 4, 'touch' => 10 ),
);
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<div class="<?php echo esc_attr( $container_cls ); ?>">

		<div class="ts-thumbnav"
		     aria-label="<?php echo esc_attr( __( 'Testimonials', 'fw' ) ); ?>"
		     data-thumbnav-main="<?php echo esc_attr( wp_json_encode( $main_config ) ); ?>"
		     data-thumbnav-nav="<?php echo esc_attr( wp_json_encode( $nav_config ) ); ?>">

			<div class="ts-thumbnav__main splide">
				<div class="splide__track">
					<ul class="splide__list">
						<?php foreach ( $testimonials as $t ):
							$f      = sc_testimonial_fields( $t );
							?>
							<li class="splide__slide">
								<div class="ts-thumbnav__quotewrap text-center"><?php echo sc_render_card( $t, array_merge( $card_args, array( 'filter_slots' => $card_filter ) ) ); ?></div>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>

			<div class="ts-thumbnav__nav splide">
				<div class="splide__track">
					<ul class="splide__list">
						<?php foreach ( $testimonials as $t ):
							$f = sc_testimonial_fields( $t );
							?>
							<li class="splide__slide">
								<?php if ( $f['avatar'] ): ?>
									<img src="<?php echo esc_url( $f['avatar'] ); ?>" alt="<?php echo esc_attr( $f['author_name'] ); ?>" loading="lazy" />
								<?php else: ?>
									<span class="ts-thumbnav__initial"><?php echo esc_html( mb_substr( $f['author_name'] !== '' ? $f['author_name'] : '·', 0, 1 ) ); ?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>

		</div>
	</div>
</div>
