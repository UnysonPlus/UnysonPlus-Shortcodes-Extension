<?php
if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * Card — Polaroid (white-framed, slightly tilted photo with caption below).
 * Image-top structure; the frame + tilt are CSS.
 *
 * @var array $sc_atts  @var WP_Post $sc_post  @var string $sc_style  @var int $sc_index
 */

$post_id = $sc_post->ID;
$cat_pos = sc_get( 'cat_position', $sc_atts, 'above-title' );
$cat_overlay_html = '';
if ( strpos( $cat_pos, 'image-overlay-' ) === 0 ) {
    $cat_overlay_html = sc_posts_render_cats( $sc_atts, $post_id );
}
$exclude_in_body = [ 'image' ];
if ( strpos( $cat_pos, 'image-overlay-' ) === 0 ) $exclude_in_body[] = 'cats';
$body_slugs    = sc_posts_get_ordered_slugs( $sc_atts, $exclude_in_body );
$image_visible = in_array( 'image', sc_posts_get_ordered_slugs( $sc_atts ), true );

$article_class = 'posts__card posts__card--polaroid';
if ( ! has_post_thumbnail( $post_id ) ) $article_class .= ' posts__card--no-image';
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
