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
		<?php if ( $title ): ?>
			<h3 class="testimonials-title <?php echo esc_attr( trim( $text_align . ' ' . $title_class_extra ) ); ?>"<?php echo $title_style_extra !== '' ? ' style="' . esc_attr( $title_style_extra ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<div class="ts-thumbnav"
		     aria-label="<?php echo esc_attr( $title !== '' ? $title : __( 'Testimonials', 'fw' ) ); ?>"
		     data-thumbnav-main="<?php echo esc_attr( wp_json_encode( $main_config ) ); ?>"
		     data-thumbnav-nav="<?php echo esc_attr( wp_json_encode( $nav_config ) ); ?>">

			<div class="ts-thumbnav__main splide">
				<div class="splide__track">
					<ul class="splide__list">
						<?php foreach ( $testimonials as $t ):
							$f      = sc_testimonial_fields( $t );
							$rating = ( $show_rating && function_exists( 'sc_render_rating' ) ) ? sc_render_rating( $f['rating'] ) : '';
							?>
							<li class="splide__slide">
								<div class="ts-thumbnav__quotewrap text-center">
									<?php if ( $rating ) echo '<div class="ts-thumbnav__rating">' . $rating . '</div>'; ?>
									<blockquote class="testimonial-quote <?php echo esc_attr( $quote_class_extra ); ?>"><?php echo sc_testimonial_quote_html( $f['content'] ); ?></blockquote>
									<div class="ts-thumbnav__author">
										<?php if ( $f['author_name'] ) echo '<span class="testimonial-author ' . esc_attr( $author_name_class_extra ) . '">' . esc_html( $f['author_name'] ) . '</span>'; ?>
										<?php if ( $f['author_job'] ) echo '<span class="testimonial-job ' . esc_attr( $author_job_class_extra ) . '">' . esc_html( $f['author_job'] ) . '</span>'; ?>
									</div>
								</div>
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
