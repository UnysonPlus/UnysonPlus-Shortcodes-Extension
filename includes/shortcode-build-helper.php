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

if ( ! function_exists( 'sc_element_unique_class' ) ) :
/**
 * Derive the element's prefixed unique class — e.g. `bt-1a2b3c4d`.
 *
 * Single source of truth for the `{unique_id_prefix}{unique_id}` class that
 * sc_build_wrapper_attr() puts on the wrapper. Returns '' when either the
 * prefix or the unique_id is missing.
 *
 * @param array $atts Shortcode attributes (expects unique_id_prefix, unique_id, optional unique_length).
 * @return string Sanitized class without the leading dot, or '' .
 */
function sc_element_unique_class( $atts ) {
	$prefix = ! empty( $atts['unique_id_prefix'] ) ? $atts['unique_id_prefix'] : '';
	if ( empty( $prefix ) || empty( $atts['unique_id'] ) ) {
		return '';
	}
	$length = ! empty( $atts['unique_length'] ) ? (int) $atts['unique_length'] : 8;
	$slug   = substr(
		sanitize_key( strtolower( preg_replace( '/\s+/', '-', trim( $atts['unique_id'] ) ) ) ),
		0,
		$length
	);
	return sanitize_html_class( $prefix . $slug );
}
endif;

if ( ! function_exists( 'sc_element_scope_class' ) ) :
/**
 * Derive a prefix-independent scope class for per-element Custom CSS — e.g.
 * `u1a2b3c4d`. Derived from `unique_id` ALONE (fixed 8-char slug, leading
 * `u` so it's a valid class start) so the front-end wrapper and the per-page
 * CSS aggregator (framework/includes/dynamic-css.php) compute the SAME class
 * without needing to know each shortcode's type-specific unique_id_prefix.
 *
 * The per-element Custom CSS field's `selector` token resolves to `.{scope}`.
 *
 * @param array $atts Shortcode attributes (expects unique_id).
 * @return string Sanitized class without the leading dot, or '' .
 */
function sc_element_scope_class( $atts ) {
	if ( empty( $atts['unique_id'] ) ) {
		return '';
	}
	$slug = substr(
		sanitize_key( strtolower( preg_replace( '/\s+/', '-', trim( $atts['unique_id'] ) ) ) ),
		0,
		8
	);
	if ( $slug === '' ) {
		return '';
	}
	return sanitize_html_class( 'u' . $slug );
}
endif;

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
    $unique_class = sc_element_unique_class( $atts );
    if ( $unique_class !== '' ) {
        $classes[] = $unique_class;
    }

    // Add the prefix-independent scope class only when this element carries
    // per-element Custom CSS, so the CSS aggregator's `.u{hash}` rules have a
    // matching target. Markup stays clean for elements without custom CSS.
    if ( ! empty( $atts['custom_css'] ) ) {
        $scope_class = sc_element_scope_class( $atts );
        if ( $scope_class !== '' ) {
            $classes[] = $scope_class;
        }
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
