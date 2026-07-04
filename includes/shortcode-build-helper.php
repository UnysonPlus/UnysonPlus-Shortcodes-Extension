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

/**
 * Build the inline CSS for the shared "Position" control (Advanced tab → element_position, a
 * multi-picker). Emits position + offsets + z-index ONLY for a positioned value; offsets and
 * z-index are omitted for static (they do nothing there). Offset values are whitelisted to safe
 * CSS lengths (px/%/em/rem/vh/vw/vmin/vmax, auto, 0, negatives) so nothing arbitrary reaches style.
 *
 * @param array $atts shortcode atts
 * @return string e.g. "position:absolute;top:20px;right:0;z-index:5;" or '' when Default/Static-less.
 */
if ( ! function_exists( 'sc_position_style' ) ) :
function sc_position_style( $atts ) {
    $mp = isset( $atts['element_position'] ) ? $atts['element_position'] : null;
    if ( ! is_array( $mp ) ) { return ''; }
    $pos = isset( $mp['position'] ) ? (string) $mp['position'] : 'default';
    if ( ! in_array( $pos, [ 'static', 'relative', 'absolute', 'fixed', 'sticky' ], true ) ) { return ''; }
    $style = 'position:' . $pos . ';';
    if ( $pos !== 'static' ) { // offsets + z-index don't apply to static
        $sub = ( isset( $mp[ $pos ] ) && is_array( $mp[ $pos ] ) ) ? $mp[ $pos ] : [];

        // Offsets: the position-box control saves a per-side { value, unit } map under
        // 'pos_offsets'. Fall back to the legacy flat 'pos_top' / 'pos_right' / … strings
        // (pre-position-box saves) so older builder JSON still renders.
        $offsets = ( isset( $sub['pos_offsets'] ) && is_array( $sub['pos_offsets'] ) ) ? $sub['pos_offsets'] : null;
        foreach ( [ 'top', 'right', 'bottom', 'left' ] as $side ) {
            if ( $offsets !== null ) {
                $sv   = ( isset( $offsets[ $side ] ) && is_array( $offsets[ $side ] ) ) ? $offsets[ $side ] : [];
                $unit = isset( $sv['unit'] ) ? trim( (string) $sv['unit'] ) : '';
                $num  = isset( $sv['value'] ) ? trim( (string) $sv['value'] ) : '';
                if ( $unit === 'auto' ) {
                    $v = 'auto';
                } elseif ( $num !== '' && is_numeric( $num ) ) {
                    $v = $num . $unit;
                } else {
                    $v = '';
                }
            } else {
                $v = isset( $sub[ 'pos_' . $side ] ) ? trim( (string) $sub[ 'pos_' . $side ] ) : '';
            }
            if ( $v !== '' && preg_match( '/^(?:auto|-?0|-?\d+(?:\.\d+)?(?:px|%|em|rem|vh|vw|vmin|vmax))$/', $v ) ) {
                $style .= $side . ':' . $v . ';';
            }
        }

        // Z-Index. The `number` option type casts an untouched field to 0 on save, so treat 0 as
        // unset (z-index:0 has no practical effect) to keep the DOM clean.
        $z = isset( $sub['element_zindex'] ) ? trim( (string) $sub['element_zindex'] ) : '';
        if ( $z !== '' && preg_match( '/^-?\d+$/', $z ) && (int) $z !== 0 ) { $style .= 'z-index:' . (int) $z . ';'; }
    }
    return $style;
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

    // Position + Z-Index (shared Advanced tab → element_position) → merge onto the wrapper style.
    $pos_style = function_exists( 'sc_position_style' ) ? sc_position_style( $atts ) : '';
    if ( $pos_style !== '' ) {
        $attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], '; ' ) . ';' : '' ) . $pos_style;
    }

    // Overflow (shared Advanced tab → element_overflow) → inline style, value whitelisted.
    $ov = isset( $atts['element_overflow'] ) ? (string) $atts['element_overflow'] : '';
    if ( in_array( $ov, array( 'hidden', 'auto', 'scroll', 'clip' ), true ) ) {
        $attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], '; ' ) . ';' : '' ) . 'overflow:' . $ov . ';';
    }

    // Add class attribute if classes exist (de-duplicated — e.g. a base_class of
    // "heading" plus a user/converter css_class of "heading" collapse to one token).
    if ( ! empty( $classes ) ) {
        $classes = array_values( array_unique( array_filter( $classes ) ) );
        $attr['class'] = esc_attr( implode( ' ', $classes ) );
    }

    return apply_filters( 'sc_build_wrapper_attr', $attr, $atts );
}
