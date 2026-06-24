<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Spotlight Coverflow.
 *
 * A center-focused slider — the active card is full size while the neighbours
 * (revealed via Splide `focus:center` + side padding) scale down and dim (CSS).
 * Reuses the base .testimonials-splide mount (static/js/scripts.js), so no extra
 * JS. Honours the Carousel options. All variables come from views/view.php.
 */

if ( empty( $testimonials ) ) {
	echo '<div ' . fw_attr_to_html( $attr ) . '><div class="' . esc_attr( $container_cls ) . '"><div class="text-muted small">' . esc_html__( 'No testimonials found.', 'fw' ) . '</div></div></div>';
	return;
}

$show_nav      = count( $testimonials ) > 1;
$splide_config = array(
	'type'         => $carousel_wrap ? 'loop' : 'slide',
	'focus'        => 'center',
	'perPage'      => 1,
	'perMove'      => 1,
	'padding'      => array( 'left' => '16%', 'right' => '16%' ),
	'gap'          => '1.25rem',
	'rewind'       => ! $carousel_wrap,
	'arrows'       => ( $carousel_controls && $show_nav ),
	'pagination'   => ( $carousel_indicators && $indicator_style !== 'none' && $show_nav ),
	'autoplay'     => $carousel_autoplay,
	'interval'     => $carousel_interval,
	'pauseOnHover' => $carousel_pause_hover,
	'pauseOnFocus' => true,
	'speed'        => 600,
	'breakpoints'  => array(
		768 => array( 'padding' => array( 'left' => '8%',    'right' => '8%' ) ),
		576 => array( 'padding' => array( 'left' => '1rem',  'right' => '1rem' ) ),
	),
);
$splide_modifier = ( $indicator_style === 'lines' ) ? ' testimonials-splide--lines' : ' testimonials-splide--dots';
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<div class="<?php echo esc_attr( $container_cls ); ?>">
		<?php if ( $title ): ?>
			<h3 class="testimonials-title <?php echo esc_attr( trim( $text_align . ' ' . $title_class_extra ) ); ?>"<?php echo $title_style_extra !== '' ? ' style="' . esc_attr( $title_style_extra ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<div class="splide testimonials-splide ts-spotlight<?php echo esc_attr( $splide_modifier ); ?>"
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
							<div class="ts-spotlight__card text-center">
								<?php if ( $f['avatar'] ): ?>
									<div class="ts-spotlight__avatar">
										<img src="<?php echo esc_url( $f['avatar'] ); ?>" alt="<?php echo esc_attr( $f['author_name'] ); ?>" class="<?php echo esc_attr( $avatar_shape ); ?>" loading="lazy" />
									</div>
								<?php endif; ?>
								<?php if ( $rating ) echo '<div class="ts-spotlight__rating">' . $rating . '</div>'; ?>
								<blockquote class="testimonial-quote <?php echo esc_attr( $quote_class_extra ); ?>"><?php echo sc_testimonial_quote_html( $f['content'] ); ?></blockquote>
								<div class="ts-spotlight__author">
									<?php if ( $f['author_name'] ) echo '<span class="testimonial-author ' . esc_attr( $author_name_class_extra ) . '">' . esc_html( $f['author_name'] ) . '</span>'; ?>
									<?php if ( $f['author_job'] ) echo '<span class="testimonial-job ' . esc_attr( $author_job_class_extra ) . '">' . esc_html( $f['author_job'] ) . '</span>'; ?>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</div>
