<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Pull-Quote Editorial.
 *
 * One oversized, type-led statement at a time with a giant quotation mark,
 * crossfading between items (Splide `type:fade`, supported by the core CSS).
 * Reuses the base .testimonials-splide mount — no extra JS. All variables come
 * from views/view.php.
 */

if ( empty( $testimonials ) ) {
	echo '<div ' . fw_attr_to_html( $attr ) . '><div class="' . esc_attr( $container_cls ) . '"><div class="text-muted small">' . esc_html__( 'No testimonials found.', 'fw' ) . '</div></div></div>';
	return;
}

$show_nav      = count( $testimonials ) > 1;
$splide_config = array(
	'type'         => 'fade',
	'rewind'       => true,
	'perPage'      => 1,
	'arrows'       => ( $carousel_controls && $show_nav ),
	'pagination'   => ( $carousel_indicators && $show_nav ),
	'autoplay'     => $carousel_autoplay,
	'interval'     => $carousel_interval,
	'pauseOnHover' => $carousel_pause_hover,
	'pauseOnFocus' => true,
	'speed'        => 600,
);
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<div class="<?php echo esc_attr( $container_cls ); ?>">
		<?php if ( $title ): ?>
			<h3 class="testimonials-title <?php echo esc_attr( trim( $text_align . ' ' . $title_class_extra ) ); ?>"<?php echo $title_style_extra !== '' ? ' style="' . esc_attr( $title_style_extra ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<div class="splide testimonials-splide ts-pullquote testimonials-splide--dots"
		     role="group"
		     aria-label="<?php echo esc_attr( $title !== '' ? $title : __( 'Testimonials', 'fw' ) ); ?>"
		     data-splide="<?php echo esc_attr( wp_json_encode( $splide_config ) ); ?>">
			<div class="splide__track">
				<ul class="splide__list">
					<?php foreach ( $testimonials as $t ):
						$f      = sc_testimonial_fields( $t );
						$rating = ( $show_rating && function_exists( 'sc_render_rating' ) ) ? sc_render_rating( $f['rating'] ) : '';
						?>
						<li class="splide__slide">
							<figure class="ts-pullquote__item">
								<blockquote class="ts-pullquote__quote testimonial-quote <?php echo esc_attr( $quote_class_extra ); ?>"><?php echo sc_testimonial_quote_html( $f['content'] ); ?></blockquote>
								<?php if ( $rating ) echo '<div class="ts-pullquote__rating">' . $rating . '</div>'; ?>
								<figcaption class="ts-pullquote__author">
									<?php if ( $f['avatar'] ): ?>
										<img class="ts-pullquote__avatar <?php echo esc_attr( $avatar_shape ); ?>" src="<?php echo esc_url( $f['avatar'] ); ?>" alt="<?php echo esc_attr( $f['author_name'] ); ?>" loading="lazy" />
									<?php endif; ?>
									<span class="ts-pullquote__byline">
										<?php if ( $f['author_name'] ) echo '<span class="testimonial-author ' . esc_attr( $author_name_class_extra ) . '">' . esc_html( $f['author_name'] ) . '</span>'; ?>
										<?php
										$meta = array();
										if ( $f['author_job'] )  $meta[] = '<span class="testimonial-job ' . esc_attr( $author_job_class_extra ) . '">' . esc_html( $f['author_job'] ) . '</span>';
										if ( $f['site_name'] && $f['site_url'] ) $meta[] = '<span class="testimonial-site ' . esc_attr( $site_link_class_extra ) . '"><a href="' . esc_url( $f['site_url'] ) . '" rel="nofollow" target="_blank">' . esc_html( $f['site_name'] ) . '</a></span>';
										if ( $meta ) echo '<span class="ts-pullquote__meta">' . implode( ' <span class="sep">|</span> ', $meta ) . '</span>';
										?>
									</span>
								</figcaption>
							</figure>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</div>
