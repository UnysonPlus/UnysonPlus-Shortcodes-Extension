<?php
if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Part: stacked — image + content in a vertical flex column whose three blocks
 * (media / head = icon+eyebrow+title / body = text+button) can be reordered by
 * the Stacking Order option via CSS `order` (imgbox--stack-*). Serves: stacked.
 *
 * @var string $sc_img_html
 * @var string $sc_icon_html
 * @var string $sc_subtitle
 * @var string $sc_title
 * @var string $sc_text
 * @var string $sc_button
 */

$sc_head = $sc_icon_html . $sc_subtitle . $sc_title;
$sc_flow = $sc_text . $sc_button;
?>
<div class="imgbox__stack">
	<?php if ( $sc_img_html !== '' ) : ?>
		<figure class="imgbox__media">
			<span class="imgbox__media-inner"><?php echo $sc_img_html; ?></span>
		</figure>
	<?php endif; ?>

	<?php if ( $sc_head !== '' ) : ?>
		<div class="imgbox__head"><?php echo $sc_head; ?></div>
	<?php endif; ?>

	<?php if ( $sc_flow !== '' ) : ?>
		<div class="imgbox__body"><?php echo $sc_flow; ?></div>
	<?php endif; ?>
</div>
