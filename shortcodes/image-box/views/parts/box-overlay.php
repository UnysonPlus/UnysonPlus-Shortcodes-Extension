<?php
if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * Part: overlay — content sits ON TOP of the image. Serves the hover-overlay
 * family (overlay-fade / slide / center / frame / scrim) and caption-bar. The
 * reveal-on-hover behavior and the scrim are CSS, keyed off the design /
 * imgbox--hover-reveal wrapper classes.
 *
 * @var string $sc_img_html
 * @var string $sc_icon_html
 * @var string $sc_subtitle
 * @var string $sc_title
 * @var string $sc_text
 * @var string $sc_button
 */
?>
<figure class="imgbox__media">
    <span class="imgbox__media-inner"><?php echo $sc_img_html; ?></span>
    <span class="imgbox__scrim" aria-hidden="true"></span>
    <span class="imgbox__frame-line" aria-hidden="true"></span>

    <div class="imgbox__overlay">
        <div class="imgbox__overlay-inner">
            <?php echo $sc_icon_html; ?>
            <?php echo $sc_subtitle; ?>
            <?php echo $sc_title; ?>
            <?php echo $sc_text; ?>
            <?php echo $sc_button; ?>
        </div>
    </div>
</figure>
