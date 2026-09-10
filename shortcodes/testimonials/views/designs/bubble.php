<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Speech Bubble.
 *
 * A responsive grid of chat-style cards — the quote sits in a rounded bubble
 * with a little tail, and the avatar + author sit below it (no JS). Column
 * count follows the Layout tab's Grid Columns value. All variables come from
 * views/view.php.
 */

if ( empty( $testimonials ) ) {
	echo '<div ' . fw_attr_to_html( $attr ) . '>' . $ts_container_open . '<div class="text-muted small">' . esc_html__( 'No testimonials found.', 'fw' ) . '</div>' . $ts_container_close . '</div>';
	return;
}

$cols = max( 1, min( 4, (int) $bubble_columns ) );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>><?php echo $ts_container_open; ?>

		<div class="ts-bubble-grid ts-bubble-grid--cols-<?php echo (int) $cols; ?>">
			<?php foreach ( $testimonials as $t ):
				$f      = sc_testimonial_fields( $t );
				$rating = ( $show_rating && function_exists( 'sc_render_rating' ) ) ? sc_render_rating( $f['rating'] ) : '';
				?>
				<div class="fw-tst-item ts-bubble">
					<blockquote class="ts-bubble__quote testimonial-quote <?php echo esc_attr( $quote_class_extra ); ?>">
						<?php echo sc_testimonial_quote_html( $f['content'] ); ?>
					</blockquote>
					<figcaption class="ts-bubble__author">
						<?php if ( $f['avatar'] ) : ?>
							<img class="ts-bubble__avatar <?php echo esc_attr( $avatar_shape ); ?>" src="<?php echo esc_url( $f['avatar'] ); ?>" alt="<?php echo esc_attr( $f['author_name'] ); ?>" loading="lazy" decoding="async" />
						<?php endif; ?>
						<span class="ts-bubble__byline">
							<?php if ( $f['author_name'] ) : ?><span class="testimonial-author <?php echo esc_attr( $author_name_class_extra ); ?>"><?php echo esc_html( $f['author_name'] ); ?></span><?php endif; ?>
							<?php if ( $f['author_job'] ) : ?><span class="testimonial-job <?php echo esc_attr( $author_job_class_extra ); ?>"><?php echo esc_html( $f['author_job'] ); ?></span><?php endif; ?>
							<?php if ( $rating ) : ?><span class="ts-bubble__rating"><?php echo $rating; ?></span><?php endif; ?>
						</span>
					</figcaption>
				</div>
			<?php endforeach; ?>
		</div>
<?php echo $ts_container_close; ?>
</div>
