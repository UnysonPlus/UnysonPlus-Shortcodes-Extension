<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * Design pack: Quote Cards (installed via ZIP → uploads).
 *
 * A design-pack render partial for [testimonials]. It inherits the testimonials
 * dispatcher's scope by `include` — the same contract the built-in partials use
 * (see views/designs/bento.php): $testimonials, $attr, $container_cls,
 * $show_rating, $box_style, $quote_class_extra, $avatar_shape,
 * $author_name_class_extra, $author_job_class_extra — plus the shared helpers
 * sc_testimonial_fields(), sc_render_rating(), sc_testimonial_quote_html().
 */

if ( empty( $testimonials ) ) {
	echo '<div ' . fw_attr_to_html( $attr ) . '><div class="' . esc_attr( $container_cls ) . '"><div class="text-muted small">' . esc_html__( 'No testimonials found.', 'fw' ) . '</div></div></div>';
	return;
}
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<div class="<?php echo esc_attr( $container_cls ); ?>">
		<div class="ts-qcards">
			<?php foreach ( $testimonials as $t ) :
				$f      = sc_testimonial_fields( $t );
				$rating = ( $show_rating && function_exists( 'sc_render_rating' ) ) ? sc_render_rating( $f['rating'] ) : '';
				?>
				<figure class="fw-tst-item <?php echo esc_attr( $box_style ); ?> ts-qcard">
					<span class="ts-qcard__mark" aria-hidden="true">&ldquo;</span>
					<blockquote class="testimonial-quote <?php echo esc_attr( $quote_class_extra ); ?>"><?php echo sc_testimonial_quote_html( $f['content'] ); ?></blockquote>
					<?php if ( $rating ) { echo '<div class="ts-qcard__rating">' . $rating . '</div>'; } ?>
					<figcaption class="ts-qcard__author">
						<?php if ( $f['avatar'] ) : ?>
							<img class="ts-qcard__avatar <?php echo esc_attr( $avatar_shape ); ?>" src="<?php echo esc_url( $f['avatar'] ); ?>" alt="<?php echo esc_attr( $f['author_name'] ); ?>" loading="lazy" />
						<?php endif; ?>
						<span class="ts-qcard__byline">
							<?php if ( $f['author_name'] ) { echo '<span class="testimonial-author ' . esc_attr( $author_name_class_extra ) . '">' . esc_html( $f['author_name'] ) . '</span>'; } ?>
							<?php if ( $f['author_job'] ) { echo '<span class="testimonial-job ' . esc_attr( $author_job_class_extra ) . '">' . esc_html( $f['author_job'] ) . '</span>'; } ?>
						</span>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</div>
