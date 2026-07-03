<?php
if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Part: overlap — the content panel overlaps the image EDGE (a CSS-grid overlap,
 * editorial/magazine look). Unlike the `overlay` part, the body is a sibling of
 * the media (not inside the figure), so it can extend BEYOND the image. Serves:
 * overlay-offset. All look is CSS via .imgbox--design-overlay-offset.
 *
 * Content Alignment (imgbox--is-right) mirrors it (content right / image left).
 * Overlay Colour / Opacity render the legibility panel behind the content.
 *
 * @var string $sc_img_html
 * @var string $sc_icon_html
 * @var string $sc_subtitle
 * @var string $sc_title
 * @var string $sc_text
 * @var string $sc_button
 */
?>
<div class="imgbox__overlap">
	<?php if ( $sc_img_html !== '' ) : ?>
		<figure class="imgbox__media">
			<span class="imgbox__media-inner"><?php echo $sc_img_html; ?></span>
		</figure>
	<?php endif; ?>

	<div class="imgbox__body">
		<div class="imgbox__overlap-panel">
			<?php echo $sc_icon_html; ?>
			<?php echo $sc_subtitle; ?>
			<?php echo $sc_title; ?>
			<?php echo $sc_text; ?>
			<?php echo $sc_button; ?>
		</div>
	</div>
</div>
