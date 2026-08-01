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
	echo '<div ' . fw_attr_to_html( $attr ) . '><div class="' . esc_attr( $container_cls ) . '"><div class="text-muted small">' . esc_html__( 'No testimonials found.', 'fw' ) . '</div></div></div>';
	return;
}

$cols = max( 1, min( 4, (int) $bubble_columns ) );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<div class="<?php echo esc_attr( $container_cls ); ?>">

		<div class="ts-bubble-grid ts-bubble-grid--cols-<?php echo (int) $cols; ?>">
			<?php foreach ( $testimonials as $t ):
				$f      = sc_testimonial_fields( $t );
				?>
				<div class="fw-tst-item <?php echo esc_attr( $box_style ); ?> ts-bubble">
					<blockquote class="ts-bubble__quote testimonial-quote <?php echo esc_attr( $quote_class_extra ); ?>">
						<?php echo sc_testimonial_quote_html( $f['content'] ); ?>
					</blockquote>
					<div class="ts-bubble__author"><?php echo sc_render_card( $t, array_merge( $card_args, array( 'filter_slots' => $card_filter ) ) ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
