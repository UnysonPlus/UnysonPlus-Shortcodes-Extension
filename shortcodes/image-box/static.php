<?php
if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
    'fw-shortcode-image-box',
    fw_min_uri( $shortcodes_extension->get_declared_URI( '/shortcodes/image-box/static/css/styles.css' ) ),
    array(),
    $shortcodes_extension->manifest->get_version()
);

wp_enqueue_script(
    'fw-shortcode-image-box',
    fw_min_uri( $shortcodes_extension->get_declared_URI( '/shortcodes/image-box/static/js/scripts.js' ) ),
    array(),
    $shortcodes_extension->manifest->get_version(),
    true
);

/* ---------------------------------------------------------------------------
 * Per-design CSS — only the DESIGN actually used by each instance loads.
 * The base styles.css above carries the shared/structural CSS used by every
 * design; a design's OWN extra CSS can live in static/css/design/<design>.css
 * and is enqueued here only for instances that pick it. Convention: add a file
 * static/css/design/<design-key>.css and it auto-loads when that design is used
 * — no list to maintain. Designs covered by the base have no file.
 * ------------------------------------------------------------------------- */
if ( ! function_exists( '_fw_imgbox_enqueue_design_css' ) ) :
    function _fw_imgbox_enqueue_design_css( $data ) {
        $atts = shortcode_parse_atts( $data['atts_string'] );
        if ( ! is_array( $atts ) ) {
            return;
        }
        $post_id = ( isset( $data['post'] ) && isset( $data['post']->ID ) ) ? $data['post']->ID : 0;
        $atts    = fw_ext_shortcodes_decode_attr( $atts, 'image_box', $post_id );
        if ( is_wp_error( $atts ) || ! is_array( $atts ) ) {
            return;
        }

        $design = isset( $atts['design'] ) && is_string( $atts['design'] ) ? $atts['design'] : 'stacked';
        $design = sanitize_file_name( $design );
        if ( $design === '' ) {
            return;
        }

        $rel  = '/shortcodes/image-box/static/css/design/' . $design . '.css';
        $path = dirname( __FILE__ ) . '/static/css/design/' . $design . '.css';
        if ( file_exists( $path ) ) {
            $ext = fw_ext( 'shortcodes' );
            wp_enqueue_style(
                'fw-shortcode-image-box-design-' . $design,
                $ext->get_declared_URI( $rel ),
                array( 'fw-shortcode-image-box' ),
                $ext->manifest->get_version()
            );
        }
    }
    add_action( 'fw_ext_shortcodes_enqueue_static:image_box', '_fw_imgbox_enqueue_design_css' );
endif;
