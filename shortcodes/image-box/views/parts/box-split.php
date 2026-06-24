<?php
if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * Part: split — image on one half, a solid colour content panel on the other,
 * equal-height. The image/panel order and media width (--imgbox-media-w) are
 * handled in CSS via the imgbox--design-split-* wrapper class.
 *
 * @var string $sc_img_html
 * @var string $sc_icon_html
 * @var string $sc_subtitle
 * @var string $sc_title
 * @var string $sc_text
 * @var string $sc_button
 */
?>
<div class="imgbox__row imgbox__split">
    <?php if ( $sc_img_html !== '' ) : ?>
        <figure class="imgbox__media">
            <span class="imgbox__media-inner"><?php echo $sc_img_html; ?></span>
        </figure>
    <?php endif; ?>

    <div class="imgbox__body imgbox__split-panel">
        <div class="imgbox__split-inner">
            <?php echo $sc_icon_html; ?>
            <?php echo $sc_subtitle; ?>
            <?php echo $sc_title; ?>
            <?php echo $sc_text; ?>
            <?php echo $sc_button; ?>
        </div>
    </div>
</div>
