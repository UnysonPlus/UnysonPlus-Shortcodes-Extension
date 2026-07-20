<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Marquee Wall.
 *
 * A single continuously-scrolling row of compact testimonial cards (CSS-only
 * infinite loop; pauses on hover; respects prefers-reduced-motion). The item
 * list is duplicated once so the translateX loop is seamless. All variables
 * come from views/view.php.
 */

if ( empty( $testimonials ) ) {
	echo '<div ' . fw_attr_to_html( $attr ) . '><div class="' . esc_attr( $container_cls ) . '"><div class="text-muted small">' . esc_html__( 'No testimonials found.', 'fw' ) . '</div></div></div>';
	return;
}

/* Build one card's markup (reused for the duplicated track). */
$render_card = function ( $t ) use ( $show_rating, $avatar_shape, $avatar_size, $quote_class_extra, $author_name_class_extra, $author_job_class_extra ) {
	$f      = sc_testimonial_fields( $t );
	$rating = ( $show_rating && function_exists( 'sc_render_rating' ) ) ? sc_render_rating( $f['rating'] ) : '';

	$avatar = '';
	if ( $f['avatar'] ) {
		$avatar = '<img class="ts-marquee__avatar ' . esc_attr( $avatar_shape . ' ' . $avatar_size ) . '" src="' . esc_url( $f['avatar'] ) . '" alt="' . esc_attr( $f['author_name'] ) . '" loading="lazy" />';
	}

	$meta = '';
	if ( $f['author_job'] ) {
		$meta = '<span class="testimonial-job ' . esc_attr( $author_job_class_extra ) . '">' . esc_html( $f['author_job'] ) . '</span>';
	}

	ob_start(); ?>
	<figure class="ts-marquee__card">
		<blockquote class="testimonial-quote <?php echo esc_attr( $quote_class_extra ); ?>"><?php echo sc_testimonial_quote_html( $f['content'] ); ?></blockquote>
		<?php if ( $rating ) echo '<div class="ts-marquee__rating">' . $rating . '</div>'; ?>
		<figcaption class="ts-marquee__author">
			<?php echo $avatar; ?>
			<span class="ts-marquee__byline">
				<?php if ( $f['author_name'] ) echo '<span class="testimonial-author ' . esc_attr( $author_name_class_extra ) . '">' . esc_html( $f['author_name'] ) . '</span>'; ?>
				<?php echo $meta; ?>
			</span>
		</figcaption>
	</figure>
	<?php
	return ob_get_clean();
};

$items_html = '';
foreach ( $testimonials as $t ) {
	$items_html .= $render_card( $t );
}
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<div class="<?php echo esc_attr( $container_cls ); ?>">
	</div>
	<?php
	$ts_marquee_dur = ( $marquee_speed === 'slow' ) ? '60s' : ( ( $marquee_speed === 'fast' ) ? '25s' : '40s' );
	$ts_marquee_dir = ( $marquee_direction === 'right' ) ? ' ts-marquee--ltr' : ' ts-marquee--rtl';
	?>
	<div class="ts-marquee<?php echo $ts_marquee_dir; ?>" aria-label="<?php echo esc_attr( __( 'Testimonials', 'fw' ) ); ?>">
		<div class="ts-marquee__track" style="--ts-marquee-duration: <?php echo esc_attr( $ts_marquee_dur ); ?>;">
			<?php echo $items_html; /* original set */ ?>
			<?php echo $items_html; /* duplicate for seamless loop (aria-hidden) */ ?>
		</div>
	</div>
</div>
