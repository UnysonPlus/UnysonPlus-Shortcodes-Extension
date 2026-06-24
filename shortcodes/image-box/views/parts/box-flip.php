<?php
if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * Part: flip — the image is the card front; on hover the card flips in 3D to a
 * colour back panel carrying the content. The front (in normal flow, sized by
 * the image ratio) defines the card height; the back is absolutely positioned
 * to match. CSS-only (imgbox--part-flip).
 *
 * @var string $sc_img_html
 * @var string $sc_icon_html
 * @var string $sc_subtitle
 * @var string $sc_title
 * @var string $sc_text
 * @var string $sc_button
 */
?>
<div class="imgbox__flip">
    <div class="imgbox__flip-inner">
        <div class="imgbox__flip-front">
            <figure class="imgbox__media">
                <span class="imgbox__media-inner"><?php echo $sc_img_html; ?></span>
            </figure>
        </div>
        <div class="imgbox__flip-back">
            <div class="imgbox__body">
                <?php echo $sc_icon_html; ?>
                <?php echo $sc_subtitle; ?>
                <?php echo $sc_title; ?>
                <?php echo $sc_text; ?>
                <?php echo $sc_button; ?>
            </div>
        </div>
    </div>
</div>
