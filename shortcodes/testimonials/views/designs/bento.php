<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Bento Featured Grid.
 *
 * An asymmetric "hero + satellites" tile layout (CSS grid, no JS): the FIRST
 * testimonial is the large featured tile, the rest fill smaller cells. All
 * variables come from views/view.php.
 */

if ( empty( $testimonials ) ) {
	echo '<div ' . fw_attr_to_html( $attr ) . '><div class="' . esc_attr( $container_cls ) . '"><div class="text-muted small">' . esc_html__( 'No testimonials found.', 'fw' ) . '</div></div></div>';
	return;
}
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<div class="<?php echo esc_attr( $container_cls ); ?>">
		<?php if ( $title ): ?>
			<h3 class="testimonials-title <?php echo esc_attr( trim( $text_align . ' ' . $title_class_extra ) ); ?>"<?php echo $title_style_extra !== '' ? ' style="' . esc_attr( $title_style_extra ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<div class="ts-bento">
			<?php foreach ( $testimonials as $i => $t ):
				$f        = sc_testimonial_fields( $t );
				$rating   = ( $show_rating && function_exists( 'sc_render_rating' ) ) ? sc_render_rating( $f['rating'] ) : '';
				$featured = ( $i === 0 ) ? ' ts-bento__item--featured' : '';
				?>
				<figure class="ts-bento__item<?php echo $featured; ?>">
					<?php if ( $rating ) echo '<div class="ts-bento__rating">' . $rating . '</div>'; ?>
					<blockquote class="testimonial-quote <?php echo esc_attr( $quote_class_extra ); ?>"><?php echo sc_testimonial_quote_html( $f['content'] ); ?></blockquote>
					<figcaption class="ts-bento__author">
						<?php if ( $f['avatar'] ): ?>
							<img class="ts-bento__avatar <?php echo esc_attr( $avatar_shape ); ?>" src="<?php echo esc_url( $f['avatar'] ); ?>" alt="<?php echo esc_attr( $f['author_name'] ); ?>" loading="lazy" />
						<?php endif; ?>
						<span class="ts-bento__byline">
							<?php if ( $f['author_name'] ) echo '<span class="testimonial-author ' . esc_attr( $author_name_class_extra ) . '">' . esc_html( $f['author_name'] ) . '</span>'; ?>
							<?php if ( $f['author_job'] ) echo '<span class="testimonial-job ' . esc_attr( $author_job_class_extra ) . '">' . esc_html( $f['author_job'] ) . '</span>'; ?>
						</span>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</div>
