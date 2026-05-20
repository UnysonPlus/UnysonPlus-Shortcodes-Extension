<?php
if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * Card — Minimal (no image). Best for news lists and announcement feeds.
 *
 * @var array   $sc_atts
 * @var WP_Post $sc_post
 * @var string  $sc_style
 */

$post_id = $sc_post->ID;

/* Image always suppressed; image-overlay category placements collapse to above-title. */
$cat_pos = sc_get( 'cat_position', $sc_atts, 'above-title' );
if ( strpos( $cat_pos, 'image-overlay-' ) === 0 ) {
    $sc_atts['cat_position'] = 'above-title';
}

$body_slugs = sc_posts_get_ordered_slugs( $sc_atts, [ 'image' ] );
?>
<article class="posts__card posts__card--minimal" role="listitem">
    <div class="posts__body entry-content">
        <?php foreach ( $body_slugs as $slug ) : ?>
            <?php echo sc_posts_render_block( $slug, $sc_atts, $post_id ); ?>
        <?php endforeach; ?>
    </div>
</article>
