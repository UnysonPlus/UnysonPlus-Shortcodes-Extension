<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Masonry Wall.
 *
 * A Pinterest-style staggered wall using CSS multi-column layout (no JS), so
 * cards of varying quote length tile naturally. Column count follows the
 * Layout tab's Grid Columns value (reused here). All variables come from
 * views/view.php.
 */

if ( empty( $testimonials ) ) {
	echo '<div ' . fw_attr_to_html( $attr ) . '><div class="' . esc_attr( $container_cls ) . '"><div class="text-muted small">' . esc_html__( 'No testimonials found.', 'fw' ) . '</div></div></div>';
	return;
}

/* Column count from this design's own Columns option (1–4). */
$cols = max( 1, min( 4, (int) $masonry_columns ) );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<div class="<?php echo esc_attr( $container_cls ); ?>">

		<div class="ts-masonry ts-masonry--cols-<?php echo (int) $cols; ?>">
			<?php foreach ( $testimonials as $t ):
				$f      = sc_testimonial_fields( $t );
				$rating = ( $show_rating && function_exists( 'sc_render_rating' ) ) ? sc_render_rating( $f['rating'] ) : '';
				?>
				<figure class="fw-tst-item <?php echo esc_attr( $box_style ); ?> ts-masonry__item <?php echo esc_attr( $card_style ); ?>">
					<?php if ( $rating ) echo '<div class="ts-masonry__rating">' . $rating . '</div>'; ?>
					<blockquote class="testimonial-quote <?php echo esc_attr( $quote_class_extra ); ?>"><?php echo sc_testimonial_quote_html( $f['content'] ); ?></blockquote>
					<figcaption class="ts-masonry__author">
						<?php if ( $f['avatar'] ): ?>
							<img class="ts-masonry__avatar <?php echo esc_attr( $avatar_shape . ' ' . $avatar_size ); ?>" src="<?php echo esc_url( $f['avatar'] ); ?>" alt="<?php echo esc_attr( $f['author_name'] ); ?>" loading="lazy" />
						<?php endif; ?>
						<span class="ts-masonry__byline">
							<?php if ( $f['author_name'] ) echo '<span class="testimonial-author ' . esc_attr( $author_name_class_extra ) . '">' . esc_html( $f['author_name'] ) . '</span>'; ?>
							<?php if ( $f['author_job'] ) echo '<span class="testimonial-job ' . esc_attr( $author_job_class_extra ) . '">' . esc_html( $f['author_job'] ) . '</span>'; ?>
						</span>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</div>
