<?php
if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
    'fw-shortcode-posts',
    fw_min_uri($shortcodes_extension->get_declared_URI( '/shortcodes/posts/static/css/styles.css' ))
);

wp_enqueue_script(
    'fw-shortcode-posts',
    fw_min_uri($shortcodes_extension->get_declared_URI( '/shortcodes/posts/static/js/scripts.js' )),
    [ 'jquery' ],
    false,
    true
);

wp_localize_script( 'fw-shortcode-posts', 'fwScPosts', [
    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
    'nonce'   => wp_create_nonce( 'fw_sc_posts' ),
] );

/* ---------------------------------------------------------------------------
 * Per-design CSS — only the CARD STYLE actually used by each instance loads.
 * The base styles.css above carries the shared/structural CSS (grid modes,
 * card base, side-layout, overlay, meta, pagination) used by every design;
 * a design's OWN CSS lives in static/css/card/<style>.css and is enqueued here
 * only for instances that pick it (via the per-instance action, which receives
 * the instance atts). Convention: add a design CSS file as static/css/card/
 * <card-style-key>.css and it auto-loads when that style is used — no list to
 * maintain. Original structural styles (standard/side/overlay/minimal/hero/
 * alternating) have no file and are covered by the base.
 * ------------------------------------------------------------------------- */
if ( ! function_exists( '_fw_posts_enqueue_design_css' ) ) :
    function _fw_posts_enqueue_design_css( $data ) {
        $atts = shortcode_parse_atts( $data['atts_string'] );
        if ( ! is_array( $atts ) ) {
            return;
        }
        $post_id = ( isset( $data['post'] ) && isset( $data['post']->ID ) ) ? $data['post']->ID : 0;
        $atts    = fw_ext_shortcodes_decode_attr( $atts, 'posts', $post_id );
        if ( is_wp_error( $atts ) || ! is_array( $atts ) ) {
            return;
        }

        // Resolve the card style: new picker path → legacy flat key → default.
        $style = fw_akg( 'card/style', $atts, null );
        if ( ! is_string( $style ) || $style === '' ) {
            $style = ( isset( $atts['card_style'] ) && is_string( $atts['card_style'] ) ) ? $atts['card_style'] : 'standard';
        }
        $style = sanitize_file_name( $style );
        if ( $style === '' ) {
            return;
        }

        $rel  = '/shortcodes/posts/static/css/card/' . $style . '.css';
        $path = dirname( __FILE__ ) . '/static/css/card/' . $style . '.css';
        if ( file_exists( $path ) ) {
            $ext = fw_ext( 'shortcodes' );
            wp_enqueue_style(
                'fw-shortcode-posts-card-' . $style,
                $ext->get_declared_URI( $rel ),
                array( 'fw-shortcode-posts' ),
                $ext->manifest->get_version()
            );
        }
    }
    add_action( 'fw_ext_shortcodes_enqueue_static:posts', '_fw_posts_enqueue_design_css' );
endif;

/* ---------------------------------------------------------------------------
 * Per-MODE CSS — only the LAYOUT MODE actually used loads. The base carries the
 * shared grid structure; each non-grid mode's own CSS (Slider is the heaviest —
 * Splide dots/arrows/track) lives in static/css/mode/<mode>.css and is enqueued
 * only for instances that pick it. The default 'grid' mode has no file (base).
 * ------------------------------------------------------------------------- */
if ( ! function_exists( '_fw_posts_enqueue_mode_css' ) ) :
    function _fw_posts_enqueue_mode_css( $data ) {
        $atts = shortcode_parse_atts( $data['atts_string'] );
        if ( ! is_array( $atts ) ) {
            return;
        }
        $post_id = ( isset( $data['post'] ) && isset( $data['post']->ID ) ) ? $data['post']->ID : 0;
        $atts    = fw_ext_shortcodes_decode_attr( $atts, 'posts', $post_id );
        if ( is_wp_error( $atts ) || ! is_array( $atts ) ) {
            return;
        }

        // Resolve the layout mode: new picker path → legacy flat key → default.
        $mode = fw_akg( 'design/mode', $atts, null );
        if ( ! is_string( $mode ) || $mode === '' ) {
            $mode = ( isset( $atts['layout_mode'] ) && is_string( $atts['layout_mode'] ) ) ? $atts['layout_mode'] : 'grid';
        }
        $mode = sanitize_file_name( $mode );
        if ( $mode === '' ) {
            return;
        }

        $rel  = '/shortcodes/posts/static/css/mode/' . $mode . '.css';
        $path = dirname( __FILE__ ) . '/static/css/mode/' . $mode . '.css';
        if ( file_exists( $path ) ) {
            $ext = fw_ext( 'shortcodes' );
            wp_enqueue_style(
                'fw-shortcode-posts-mode-' . $mode,
                $ext->get_declared_URI( $rel ),
                array( 'fw-shortcode-posts' ),
                $ext->manifest->get_version()
            );
        }
    }
    add_action( 'fw_ext_shortcodes_enqueue_static:posts', '_fw_posts_enqueue_mode_css' );
endif;
