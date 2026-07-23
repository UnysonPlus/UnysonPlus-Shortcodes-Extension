<?php
if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * Card — Compact News List.
 *
 * Small square thumbnail + dense title/meta in tight rows. Ideal for
 * Related / sidebar / news feeds. Excerpt is intentionally omitted for density.
 *
 * @var array   $sc_atts
 * @var WP_Post $sc_post
 * @var string  $sc_style
 */

$post_id = $sc_post->ID;

/* Image-overlay category positions have no image wrapper in this layout — the chips
   would be absolutely-positioned with no anchor. Collapse them to above-title. */
$cat_pos = sc_get( 'cat_position', $sc_atts, 'above-title' );
if ( strpos( $cat_pos, 'image-overlay-' ) === 0 ) {
    $sc_atts['cat_position'] = 'above-title';
}

/* Compact: drop the image (rendered separately) and the excerpt for density. */
$body_slugs    = sc_posts_get_ordered_slugs( $sc_atts, [ 'image', 'excerpt' ] );
$image_visible = in_array( 'image', sc_posts_get_ordered_slugs( $sc_atts ), true );

$article_class = 'posts__card posts__card--newslist';
// Image Position (Left / Right) — flip the horizontal layout when set to right.
if ( sc_get( 'image_position', $sc_atts, 'left' ) === 'right' ) { $article_class .= ' posts__card--img-right'; }
if ( ! has_post_thumbnail( $post_id ) ) {
    $article_class .= ' posts__card--no-image';
}
?>
<article class="<?php echo esc_attr( $article_class ); ?>">
    <?php if ( $image_visible ) : ?>
        <div class="posts__newslist-media">
            <?php echo sc_posts_render_image( $sc_atts, $post_id ); ?>
        </div>
    <?php endif; ?>

    <div class="posts__body entry-content">
        <?php foreach ( $body_slugs as $slug ) : ?>
            <?php echo sc_posts_render_block( $slug, $sc_atts, $post_id ); ?>
        <?php endforeach; ?>
    </div>
</article>
