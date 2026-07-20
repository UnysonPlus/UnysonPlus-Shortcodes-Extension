<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Stacked List.
 *
 * A minimal, editorial vertical list — no cards, large quotes separated by
 * hairline dividers, author row inline (no JS). Great for long-form, text-led
 * pages. All variables come from views/view.php.
 */

if ( empty( $testimonials ) ) {
	echo '<div ' . fw_attr_to_html( $attr ) . '><div class="' . esc_attr( $container_cls ) . '"><div class="text-muted small">' . esc_html__( 'No testimonials found.', 'fw' ) . '</div></div></div>';
	return;
}
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<div class="<?php echo esc_attr( $container_cls ); ?>">

		<div class="ts-stacked">
			<?php foreach ( $testimonials as $t ):
				$f      = sc_testimonial_fields( $t );
				$rating = ( $show_rating && function_exists( 'sc_render_rating' ) ) ? sc_render_rating( $f['rating'] ) : '';
				?>
				<figure class="fw-tst-item <?php echo esc_attr( $box_style ); ?> ts-stacked__item">
					<blockquote class="ts-stacked__quote testimonial-quote <?php echo esc_attr( $quote_class_extra ); ?>"><?php echo sc_testimonial_quote_html( $f['content'] ); ?></blockquote>
					<figcaption class="ts-stacked__author">
						<?php if ( $f['avatar'] ): ?>
							<img class="ts-stacked__avatar <?php echo esc_attr( $avatar_shape ); ?>" src="<?php echo esc_url( $f['avatar'] ); ?>" alt="<?php echo esc_attr( $f['author_name'] ); ?>" loading="lazy" />
						<?php endif; ?>
						<span class="ts-stacked__byline">
							<?php if ( $f['author_name'] ) echo '<span class="testimonial-author ' . esc_attr( $author_name_class_extra ) . '">' . esc_html( $f['author_name'] ) . '</span>'; ?>
							<?php
							$meta = array();
							if ( $f['author_job'] )  $meta[] = '<span class="testimonial-job ' . esc_attr( $author_job_class_extra ) . '">' . esc_html( $f['author_job'] ) . '</span>';
							if ( $f['site_name'] && $f['site_url'] ) $meta[] = '<span class="testimonial-site ' . esc_attr( $site_link_class_extra ) . '"><a href="' . esc_url( $f['site_url'] ) . '" rel="nofollow" target="_blank">' . esc_html( $f['site_name'] ) . '</a></span>';
							if ( $meta ) echo '<span class="ts-stacked__meta">' . implode( ' <span class="sep">|</span> ', $meta ) . '</span>';
							?>
						</span>
						<?php if ( $rating ) echo '<span class="ts-stacked__rating">' . $rating . '</span>'; ?>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</div>
