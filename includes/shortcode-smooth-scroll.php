<?php
/**
 * PHP Version: 7.4 or higher
 *
 * Smooth Scroll (Lenis) — a per-page toggle.
 *
 * Adds a small "Smooth Scroll" switch to the Page/Post editor (via the
 * `fw_post_options` filter) and, on the front end, conditionally enqueues the
 * bundled Lenis library + initializer ONLY on singular pages where the switch
 * is ON. Pages without it ship zero smooth-scroll bytes — same load-only-when-
 * used contract as the GSAP engine.
 *
 * The initializer bridges Lenis into GSAP's ticker + ScrollTrigger when they're
 * present (so pinned / scrubbed effects stay in sync), and respects
 * prefers-reduced-motion + the page-builder editor.
 */
if ( ! defined( 'FW' ) ) die( 'Forbidden' );


/**
 * Per-page toggle in the post editor. Defaults to a side metabox on Pages and
 * Posts; the post-type list is filterable.
 */
if ( ! function_exists( 'sc_smooth_scroll_post_option' ) ) :
function sc_smooth_scroll_post_option( $options, $post_type ) {
    $allowed = apply_filters( 'sc_smooth_scroll_post_types', [ 'page', 'post' ] );
    if ( ! in_array( $post_type, (array) $allowed, true ) ) {
        return $options;
    }

    $options[] = [
        'smooth-scroll-box' => [
            'title'    => __( 'Smooth Scroll', 'fw' ),
            'type'     => 'box',
            'context'  => 'side',
            'priority' => 'low',
            'options'  => [
                'smooth_scroll' => [
                    'label'   => __( 'Smooth Scroll', 'fw' ),
                    'desc'    => __( 'Buttery inertia scrolling (Lenis) for this page. "Use site default" follows the global setting (Shortcodes → Settings). Loads only when on; respects reduced-motion and pairs with the GSAP scroll effects.', 'fw' ),
                    'type'    => 'select',
                    'value'   => '',
                    'choices' => [
                        ''    => __( 'Use site default', 'fw' ),
                        'yes' => __( 'On', 'fw' ),
                        'no'  => __( 'Off', 'fw' ),
                    ],
                ],
            ],
        ],
    ];

    return $options;
}
add_filter( 'fw_post_options', 'sc_smooth_scroll_post_option', 10, 2 );
endif;


/**
 * Conditionally enqueue Lenis + the initializer when the current singular page
 * has Smooth Scroll switched on.
 */
if ( ! function_exists( 'sc_smooth_scroll_enqueue' ) ) :
function sc_smooth_scroll_enqueue() {
    if ( is_admin() || ! is_singular() ) {
        return;
    }

    $post_id = get_queried_object_id();
    if ( ! $post_id ) {
        return;
    }

    // Resolve: per-page On/Off wins; otherwise inherit the site-wide default.
    $page = fw_get_db_post_option( $post_id, 'smooth_scroll' );
    if ( $page === 'yes' ) {
        $on = true;
    } elseif ( $page === 'no' ) {
        $on = false;
    } else {
        $on = ( fw_get_db_ext_settings_option( 'shortcodes', 'smooth_scroll_global' ) === 'yes' );
    }
    if ( ! $on ) {
        return;
    }

    $ext = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' ) : null;
    if ( ! $ext ) {
        return;
    }
    $ver = $ext->manifest->get_version();

    // Lenis base CSS in <head> so html.lenis rules apply without a flash.
    wp_enqueue_style(
        'upw-smooth',
        $ext->get_declared_URI( '/static/css/upw-smooth.css' ),
        [],
        $ver
    );

    // Vendor file is already minified — reference it directly.
    wp_enqueue_script(
        'upw-lenis',
        $ext->get_declared_URI( '/static/js/vendor/lenis/lenis.min.js' ),
        [],
        '1.3.11',
        true
    );
    wp_enqueue_script(
        'upw-smooth',
        $ext->get_declared_URI( '/static/js/upw-smooth.js' ),
        [ 'upw-lenis' ],
        $ver,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'sc_smooth_scroll_enqueue', 20 );
endif;
