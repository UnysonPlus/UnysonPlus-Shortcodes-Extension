<?php
/**
 * PHP Version: 7.4 or higher
 */
if (!defined('FW')) die('Forbidden');

/**
 * Generate HTML attributes for a shortcode wrapper
 *
 * Handles unique class, CSS class, CSS ID, inline styles, and extra attributes like
 * target, rel, data-* and aria-*.
 *
 * @param array  $atts           Shortcode attributes array.
 * @param string $base_class     Optional base class for the block.
 * @param int    $unique_length  Length of unique class suffix (default 7).
 * @param array  $extra_attrs    Additional custom attributes (key => value).
 *
 * @return array Attributes array suitable for fw_attr_to_html().
 */

function sc_build_wrapper_attr( $atts ) {

    $base_class       = ! empty( $atts['base_class'] ) ? $atts['base_class'] : '';
    $unique_id_prefix = ! empty( $atts['unique_id_prefix'] ) ? $atts['unique_id_prefix'] : '';
    $unique_length    = ! empty( $atts['unique_length'] ) ? (int) $atts['unique_length'] : 8;
    $extra_attrs      = ! empty( $atts['extra_attrs'] ) ? (array) $atts['extra_attrs'] : [];

    $classes = [];

    // Normalize for IDs: lowercase + spaces → dashes
    $normalize_id = function( $string ) {
        $string = strtolower( trim( $string ) );
        $string = preg_replace( '/\s+/', '-', $string );
        return $string;
    };

    // Normalize for classes: lowercase only
    $normalize_class = function( $string ) {
        return strtolower( trim( $string ) );
    };

    // Add base class if provided
    if ( $base_class ) {
        $classes[] = sanitize_html_class( $base_class );
    }

    // Add unique class only if both prefix and unique_id are defined
    if ( ! empty( $unique_id_prefix ) && ! empty( $atts['unique_id'] ) ) {
        $unique_class = substr(
            sanitize_key( $normalize_id( $atts['unique_id'] ) ),
            0,
            $unique_length
        );

        $unique_class = sanitize_html_class( $unique_id_prefix . $unique_class );
        $classes[] = $unique_class;
    }

    // Add user-defined CSS class
    if ( ! empty( $atts['css_class'] ) ) {
        // Split by spaces, lowercase each, sanitize
        $user_classes = preg_split( '/\s+/', $atts['css_class'] );
        foreach ( $user_classes as $user_class ) {
            if ( ! empty( $user_class ) ) {
                $classes[] = sanitize_html_class( $normalize_class( $user_class ) );
            }
        }
    }

    $attr = $extra_attrs;

    // Add CSS ID if provided
    if ( ! empty( $atts['css_id'] ) ) {
        $attr['id'] = sanitize_html_class( $normalize_id( $atts['css_id'] ) );
    }

    // Add inline style if provided
    if ( ! empty( $atts['css_style'] ) ) {
        $attr['style'] = esc_attr( $atts['css_style'] );
    }

    // Add class attribute if classes exist
    if ( ! empty( $classes ) ) {
        $attr['class'] = esc_attr( implode( ' ', $classes ) );
    }

    return apply_filters( 'sc_build_wrapper_attr', $attr, $atts );
}
