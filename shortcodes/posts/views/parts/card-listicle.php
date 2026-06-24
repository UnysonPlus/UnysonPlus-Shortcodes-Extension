<?php
if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * Card — Numbered Listicle.
 *
 * A big rank number (01, 02…) beside a horizontal image+content card. Built for
 * "Top 10 / Best of" posts.
 *
 * @var array   $sc_atts
 * @var WP_Post $sc_post
 * @var string  $sc_style
 * @var int     $sc_index  Zero-based position in the result set.
 */

$post_id = $sc_post->ID;
$num     = str_pad( (string) ( (int) $sc_index + 1 ), 2, '0', STR_PAD_LEFT );

$body_slugs    = sc_posts_get_ordered_slugs( $sc_atts, [ 'image' ] );
$image_visible = in_array( 'image', sc_posts_get_ordered_slugs( $sc_atts ), true );

$article_class = 'posts__card posts__card--listicle';
if ( ! has_post_thumbnail( $post_id ) ) {
    $article_class .= ' posts__card--no-image';
}
?>
<article class="<?php echo esc_attr( $article_class ); ?>" role="listitem">
    <span class="posts__listicle-num" aria-hidden="true"><?php echo esc_html( $num ); ?></span>

    <?php if ( $image_visible ) : ?>
        <div class="posts__listicle-media">
            <?php echo sc_posts_render_image( $sc_atts, $post_id ); ?>
        </div>
    <?php endif; ?>

    <div class="posts__body entry-content">
        <?php foreach ( $body_slugs as $slug ) : ?>
            <?php echo sc_posts_render_block( $slug, $sc_atts, $post_id ); ?>
        <?php endforeach; ?>
    </div>
</article>
