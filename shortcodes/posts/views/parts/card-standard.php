<?php
if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * Card — Standard (image top, content below).
 *
 * Receives in scope from view.php:
 *
 * @var array   $sc_atts   Full shortcode attributes.
 * @var WP_Post $sc_post   Current post object.
 * @var string  $sc_style  Effective card style for this post.
 */

$post_id  = $sc_post->ID;
$cat_pos  = sc_get( 'cat_position', $sc_atts, 'above-title' );

/* Cats rendered as image overlay? Render once, pass to image helper. */
$cat_overlay_html = '';
if ( strpos( $cat_pos, 'image-overlay-' ) === 0 ) {
    $cat_overlay_html = sc_posts_render_cats( $sc_atts, $post_id );
}

/* Build the list of slugs to render in the body (everything except `image`). */
$exclude_in_body = [ 'image' ];
/* If cats are overlaid on the image, suppress the standalone cats block in body. */
if ( strpos( $cat_pos, 'image-overlay-' ) === 0 ) {
    $exclude_in_body[] = 'cats';
}

$body_slugs = sc_posts_get_ordered_slugs( $sc_atts, $exclude_in_body );

/* Image is always emitted at the top for the standard card style — its presence
   in element_order determines visibility only. */
$image_visible = in_array( 'image', sc_posts_get_ordered_slugs( $sc_atts ), true );

$article_class = 'posts__card posts__card--standard';
if ( ! has_post_thumbnail( $post_id ) ) {
    $article_class .= ' posts__card--no-image';
}
?>
<article class="<?php echo esc_attr( $article_class ); ?>">
    <?php if ( $image_visible ) : ?>
        <?php echo sc_posts_render_image( $sc_atts, $post_id, $cat_overlay_html ); ?>
    <?php endif; ?>

    <div class="posts__body entry-content">
        <?php foreach ( $body_slugs as $slug ) : ?>
            <?php echo sc_posts_render_block( $slug, $sc_atts, $post_id ); ?>
        <?php endforeach; ?>
    </div>
</article>
