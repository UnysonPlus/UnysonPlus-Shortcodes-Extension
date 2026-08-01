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
				?>
				<div class="fw-tst-item <?php echo esc_attr( $box_style ); ?> ts-zigzag__row">
					<?php if ( $f['avatar'] ): ?>
						<div class="ts-zigzag__media">
							<img src="<?php echo esc_url( $f['avatar'] ); ?>" alt="<?php echo esc_attr( $f['author_name'] ); ?>" loading="lazy" />
						</div>
					<?php endif; ?>
					<div class="ts-zigzag__body"><?php echo sc_render_card( $t, array_merge( $card_args, array( 'filter_slots' => $card_filter ) ) ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
