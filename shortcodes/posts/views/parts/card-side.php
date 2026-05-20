<?php
if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * Card — Side (image-left or image-right). Used for `side-left`, `side-right`,
 * and `alternating` styles (the effective per-card style is in $sc_style).
 *
 * @var array   $sc_atts
 * @var WP_Post $sc_post
 * @var string  $sc_style
 */

$post_id    = $sc_post->ID;
$cat_pos    = sc_get( 'cat_position', $sc_atts, 'above-title' );
$ratio      = sc_get( 'image_width_ratio', $sc_atts, '40-60' );
$img_va     = sc_get( 'image_vertical_align', $sc_atts, 'stretch' );
$content_va = sc_get( 'content_vertical_align', $sc_atts, 'top' );
$is_right   = $sc_style === 'side-right';

$cat_overlay_html = '';
if ( strpos( $cat_pos, 'image-overlay-' ) === 0 ) {
    $cat_overlay_html = sc_posts_render_cats( $sc_atts, $post_id );
}

$exclude_in_body = [ 'image' ];
if ( strpos( $cat_pos, 'image-overlay-' ) === 0 ) {
    $exclude_in_body[] = 'cats';
}
$body_slugs = sc_posts_get_ordered_slugs( $sc_atts, $exclude_in_body );

$image_visible = in_array( 'image', sc_posts_get_ordered_slugs( $sc_atts ), true );

$article_classes = [
    'posts__card',
    'posts__card--side',
    'posts__card--' . ( $is_right ? 'side-right' : 'side-left' ),
    'posts__card--ratio-' . sanitize_html_class( $ratio ),
    'posts__card--imgva-' . sanitize_html_class( $img_va ),
    'posts__card--contentva-' . sanitize_html_class( $content_va ),
];
if ( ! has_post_thumbnail( $post_id ) ) {
    $article_classes[] = 'posts__card--no-image';
}
?>
<article class="<?php echo esc_attr( implode( ' ', $article_classes ) ); ?>" role="listitem">
    <?php if ( $image_visible && $is_right ) : ?>
        <div class="posts__col posts__col--body">
            <?php foreach ( $body_slugs as $slug ) : ?>
                <?php echo sc_posts_render_block( $slug, $sc_atts, $post_id ); ?>
            <?php endforeach; ?>
        </div>
        <div class="posts__col posts__col--image">
            <?php echo sc_posts_render_image( $sc_atts, $post_id, $cat_overlay_html ); ?>
        </div>
    <?php elseif ( $image_visible ) : ?>
        <div class="posts__col posts__col--image">
            <?php echo sc_posts_render_image( $sc_atts, $post_id, $cat_overlay_html ); ?>
        </div>
        <div class="posts__col posts__col--body">
            <?php foreach ( $body_slugs as $slug ) : ?>
                <?php echo sc_posts_render_block( $slug, $sc_atts, $post_id ); ?>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="posts__col posts__col--body posts__col--body-only">
            <?php foreach ( $body_slugs as $slug ) : ?>
                <?php echo sc_posts_render_block( $slug, $sc_atts, $post_id ); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</article>
