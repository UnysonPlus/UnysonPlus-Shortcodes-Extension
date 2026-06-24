<?php
if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * Card — Quote-led. The excerpt reads as a large pull-quote (giant quotation
 * mark via CSS); image small or omitted. Suits opinion / editorial feeds.
 *
 * @var array $sc_atts  @var WP_Post $sc_post  @var string $sc_style  @var int $sc_index
 */

$post_id       = $sc_post->ID;
$body_slugs    = sc_posts_get_ordered_slugs( $sc_atts, [ 'image' ] );
$image_visible = in_array( 'image', sc_posts_get_ordered_slugs( $sc_atts ), true );

$article_class = 'posts__card posts__card--quote';
if ( ! has_post_thumbnail( $post_id ) ) $article_class .= ' posts__card--no-image';
?>
<article class="<?php echo esc_attr( $article_class ); ?>" role="listitem">
    <div class="posts__body entry-content">
        <?php foreach ( $body_slugs as $slug ) : ?>
            <?php echo sc_posts_render_block( $slug, $sc_atts, $post_id ); ?>
        <?php endforeach; ?>
    </div>
    <?php if ( $image_visible ) : ?>
        <div class="posts__quote-media">
            <?php echo sc_posts_render_image( $sc_atts, $post_id ); ?>
        </div>
    <?php endif; ?>
</article>
