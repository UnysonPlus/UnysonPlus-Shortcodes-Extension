<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Zigzag Alternating.
 *
 * Full-width rows where the photo alternates left/right down the page (CSS, no
 * JS). The first photo's side is configurable (`zigzag_start`). All variables
 * come from views/view.php.
 */

if ( empty( $testimonials ) ) {
	echo '<div ' . fw_attr_to_html( $attr ) . '><div class="' . esc_attr( $container_cls ) . '"><div class="text-muted small">' . esc_html__( 'No testimonials found.', 'fw' ) . '</div></div></div>';
	return;
}

$zigzag_start = sc_get( 'design_settings/zigzag/zigzag_start', $atts, 'left' );
$zigzag_start = ( $zigzag_start === 'right' ) ? 'right' : 'left';
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<div class="<?php echo esc_attr( $container_cls ); ?>">

		<div class="ts-zigzag ts-zigzag--start-<?php echo esc_attr( $zigzag_start ); ?>">
			<?php foreach ( $testimonials as $t ):
				$f      = sc_testimonial_fields( $t );
				$rating = ( $show_rating && function_exists( 'sc_render_rating' ) ) ? sc_render_rating( $f['rating'] ) : '';
				?>
				<div class="fw-tst-item <?php echo esc_attr( $box_style ); ?> ts-zigzag__row">
					<?php if ( $f['avatar'] ): ?>
						<div class="ts-zigzag__media">
							<img src="<?php echo esc_url( $f['avatar'] ); ?>" alt="<?php echo esc_attr( $f['author_name'] ); ?>" loading="lazy" />
						</div>
					<?php endif; ?>
					<div class="ts-zigzag__body">
						<?php if ( $rating ) echo '<div class="ts-zigzag__rating">' . $rating . '</div>'; ?>
						<blockquote class="testimonial-quote <?php echo esc_attr( $quote_class_extra ); ?>"><?php echo sc_testimonial_quote_html( $f['content'] ); ?></blockquote>
						<div class="ts-zigzag__author">
							<?php if ( $f['author_name'] ) echo '<span class="testimonial-author ' . esc_attr( $author_name_class_extra ) . '">' . esc_html( $f['author_name'] ) . '</span>'; ?>
							<?php
							$meta = array();
							if ( $f['author_job'] )  $meta[] = '<span class="testimonial-job ' . esc_attr( $author_job_class_extra ) . '">' . esc_html( $f['author_job'] ) . '</span>';
							if ( $f['site_name'] && $f['site_url'] ) $meta[] = '<span class="testimonial-site ' . esc_attr( $site_link_class_extra ) . '"><a href="' . esc_url( $f['site_url'] ) . '" rel="nofollow" target="_blank">' . esc_html( $f['site_name'] ) . '</a></span>';
							if ( $meta ) echo '<span class="ts-zigzag__meta">' . implode( ' <span class="sep">|</span> ', $meta ) . '</span>';
							?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
