<?php
if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * Part: frame — a decorative frame around the image with a caption beneath.
 * Serves polaroid, postcard and badge (the frame look is CSS via the
 * imgbox--design-* wrapper class).
 *
 * @var string $sc_img_html
 * @var string $sc_icon_html
 * @var string $sc_subtitle
 * @var string $sc_title
 * @var string $sc_text
 * @var string $sc_button
 */
?>
<div class="imgbox__frame-box">
    <?php if ( $sc_img_html !== '' ) : ?>
        <figure class="imgbox__media">
            <span class="imgbox__media-inner"><?php echo $sc_img_html; ?></span>
        </figure>
    <?php endif; ?>

    <div class="imgbox__body">
        <?php echo $sc_icon_html; ?>
        <?php echo $sc_subtitle; ?>
        <?php echo $sc_title; ?>
        <?php echo $sc_text; ?>
        <?php echo $sc_button; ?>
    </div>
</div>
