<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Image Split Slider.
 *
 * One large featured testimonial per slide — avatar/image on one side, big
 * quote + author on the other. Reuses the base Splide mount (the shared
 * .testimonials-splide JS in static/js/scripts.js), so no extra JS is needed.
 * Honours the Carousel tab options. All variables come from views/view.php.
 */

if ( empty( $testimonials ) ) {
	echo '<div ' . fw_attr_to_html( $attr ) . '><div class="' . esc_attr( $container_cls ) . '"><div class="text-muted small">' . esc_html__( 'No testimonials found.', 'fw' ) . '</div></div></div>';
	return;
}

$show_nav      = count( $testimonials ) > 1;
$splide_config = array(
	'type'         => $carousel_wrap ? 'loop' : 'slide',
	'perPage'      => 1,
	'perMove'      => 1,
	'rewind'       => ! $carousel_wrap,
	'arrows'       => ( $carousel_controls && $show_nav ),
	'pagination'   => ( $carousel_indicators && $indicator_style !== 'none' && $show_nav ),
	'autoplay'     => $carousel_autoplay,
	'interval'     => $carousel_interval,
	'pauseOnHover' => $carousel_pause_hover,
	'pauseOnFocus' => true,
	'speed'        => 700,
);
$splide_modifier = ( $indicator_style === 'lines' ) ? ' testimonials-splide--lines' : ' testimonials-splide--dots';
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<div class="<?php echo esc_attr( $container_cls ); ?>">

		<div class="splide testimonials-splide ts-split<?php echo esc_attr( $splide_modifier ); ?>"
		     role="group"
		     aria-label="<?php echo esc_attr( __( 'Testimonials', 'fw' ) ); ?>"
		     data-splide="<?php echo esc_attr( wp_json_encode( $splide_config ) ); ?>">
			<div class="splide__track">
				<ul class="splide__list">
					<?php foreach ( $testimonials as $t ):
						$f      = sc_testimonial_fields( $t );
						$rating = ( $show_rating && function_exists( 'sc_render_rating' ) ) ? sc_render_rating( $f['rating'] ) : '';
						?>
						<li class="splide__slide">
							<div class="ts-split__inner">
								<?php if ( $f['avatar'] ): ?>
									<div class="ts-split__media">
										<img src="<?php echo esc_url( $f['avatar'] ); ?>" alt="<?php echo esc_attr( $f['author_name'] ); ?>" loading="lazy" />
									</div>
								<?php endif; ?>
								<div class="ts-split__body">
									<?php if ( $rating ) echo '<div class="ts-split__rating">' . $rating . '</div>'; ?>
									<blockquote class="testimonial-quote <?php echo esc_attr( $quote_class_extra ); ?>"><?php echo sc_testimonial_quote_html( $f['content'] ); ?></blockquote>
									<div class="ts-split__author">
										<?php if ( $f['author_name'] ) echo '<span class="testimonial-author ' . esc_attr( $author_name_class_extra ) . '">' . esc_html( $f['author_name'] ) . '</span>'; ?>
										<?php
										$meta = array();
										if ( $f['author_job'] )  $meta[] = '<span class="testimonial-job ' . esc_attr( $author_job_class_extra ) . '">' . esc_html( $f['author_job'] ) . '</span>';
										if ( $f['site_name'] && $f['site_url'] ) $meta[] = '<span class="testimonial-site ' . esc_attr( $site_link_class_extra ) . '"><a href="' . esc_url( $f['site_url'] ) . '" rel="nofollow" target="_blank">' . esc_html( $f['site_name'] ) . '</a></span>';
										if ( $meta ) echo '<span class="ts-split__meta">' . implode( ' <span class="sep">|</span> ', $meta ) . '</span>';
										?>
									</div>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</div>
