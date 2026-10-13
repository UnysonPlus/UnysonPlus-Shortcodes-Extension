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

if ( ! function_exists( 'sc_section_dynamic_css' ) ) :
/**
 * Builds per-page CSS for a section's custom min-height and container-width values (named presets use utility classes instead).
 *
 * Per-instance Section styling that would otherwise be written as INLINE style="…" on the markup —
 * the section's Min Height and Container Width — resolved to a scoped CSS rule targeting the element's
 * `.u{hash}` scope class, so it rides the per-page dynamic CSS FILE (dynamic-css.php) instead of the
 * HTML. Both the front-end view (to decide whether to add the scope class) and the CSS aggregator (to
 * emit the rule) call this, so they stay byte-identical. Presets AND custom values both route here —
 * nothing about section sizing lands inline anymore.
 *
 *   Min Height    → `.u{hash}{min-height:<preset-vh | custom value>}`
 *   Container Width → `.u{hash} .fw-container{max-width:calc(<w> + 2*gutter)}`  (beats the grid's
 *                     `body .fw-container` at 0,2,0 vs 0,1,1, the reason this used to need inline)
 *
 * @param array  $atts  section shortcode atts (expects min_height / container_width / unique_id)
 * @param string $scope the element scope class (sc_element_scope_class), without the leading dot
 * @return string CSS (no <style> tag), or '' when the section sets neither.
 */
function sc_section_dynamic_css( $atts, $scope ) {
	$scope = trim( (string) $scope );
	if ( $scope === '' || ! is_array( $atts ) ) { return ''; }
	$css = '';

	// --- Min Height: a FIXED-ENUM viewport preset (40/60/80/100vh) is a PREDEFINED utility class
	// (`.section--minh-*` in the section stylesheet, added by the view) — NOT emitted here. Only a
	// CUSTOM {value,unit} (an arbitrary height with no predefined class) routes to the per-page file. ---
	$mh  = isset( $atts['min_height'] ) ? $atts['min_height'] : '';
	$mhv = '';
	if ( is_array( $mh ) && isset( $mh['preset'] ) && $mh['preset'] === 'custom' ) {
		$uv = ( isset( $mh['custom']['custom_height'] ) && is_array( $mh['custom']['custom_height'] ) ) ? $mh['custom']['custom_height'] : array();
		$n  = isset( $uv['value'] ) ? trim( (string) $uv['value'] ) : '';
		$u  = isset( $uv['unit'] ) ? preg_replace( '/[^a-z%]/', '', (string) $uv['unit'] ) : 'px';
		if ( $n !== '' && is_numeric( $n ) ) { $mhv = $n . $u; }
	} elseif ( is_string( $mh ) && $mh !== '' && $mh !== 'auto' && ! preg_match( '/^(40|60|80|100)vh$/', $mh ) ) {
		$mhv = trim( $mh ); // legacy plain-string CUSTOM value (a preset-vh string uses the class instead)
	}
	if ( $mhv !== '' && preg_match( '/^-?\d*\.?\d+(px|rem|em|vh|vw|svh|dvh|%)$/', $mhv ) ) {
		$css .= '.' . $scope . '{min-height:' . $mhv . ';}';
	}

	// --- Container Width: only a CUSTOM (arbitrary) width routes here. Named LIBRARY presets are
	// generated ONCE as reusable `.section--cw-{slug}` classes in presets-{hash}.css (css-tokens.php)
	// and added to the section by the view — so they're not duplicated per-instance in every page file. ---
	$cwv = isset( $atts['container_width'] ) ? $atts['container_width'] : '';
	if ( is_array( $cwv ) && isset( $cwv['preset'] ) && $cwv['preset'] === 'custom' ) {
		$cuv = ( isset( $cwv['custom']['custom_width'] ) && is_array( $cwv['custom']['custom_width'] ) ) ? $cwv['custom']['custom_width'] : array();
		$cn  = isset( $cuv['value'] ) ? trim( (string) $cuv['value'] ) : '';
		$cu  = isset( $cuv['unit'] ) ? preg_replace( '/[^a-z%]/', '', (string) $cuv['unit'] ) : 'px';
		if ( $cn !== '' && is_numeric( $cn ) ) {
			$max = $cn . $cu;
			if ( preg_match( '/^\d*\.?\d+(px|rem|em|%|vw)$/', $max ) ) {
				$css .= '.' . $scope . ' .fw-container{max-width:calc(' . $max . ' + 2 * var(--container-gutter, clamp(1.25rem, 3vw, 2rem)));margin-left:auto;margin-right:auto;}';
			}
		}
	}

	// NOTE: Columns Vertical Alignment (top/center/bottom/stretch) is a FIXED ENUM → predefined
	// `.section--valign-*` utility classes in the section stylesheet (added by the view), not routed here.
	return $css;
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
if ( ! function_exists( 'sc_position_mp_layer' ) ) :
/**
 * Extract one device layer's multi-picker value from the (now responsive) element_position att.
 * Tolerates the legacy FLAT multi-picker shape ({ position, relative:{…} }) by treating it as the
 * `base` layer, so elements positioned before this control went responsive still render.
 *
 * @param mixed  $mp    The element_position att (responsive { base, md, lg, xl } or legacy flat).
 * @param string $layer base|md|lg|xl
 * @return array The multi-picker value for that layer (possibly empty).
 */
function sc_position_mp_layer( $mp, $layer ) {
    if ( ! is_array( $mp ) ) { return array(); }
    // Legacy flat value (has a top-level 'position' key, no device keys) → it IS the base layer.
    if ( isset( $mp['position'] ) ) {
        return ( $layer === 'base' ) ? $mp : array();
    }
    return ( isset( $mp[ $layer ] ) && is_array( $mp[ $layer ] ) ) ? $mp[ $layer ] : array();
}
endif;

if ( ! function_exists( 'sc_position_decls' ) ) :
/**
 * Build the position + offsets + z-index CSS declarations for ONE multi-picker value (one device
 * layer). Returns e.g. "position:sticky;top:20px;z-index:5;" or '' for Default / an unpositioned
 * value. Offsets + z-index are omitted for `static` (they do nothing there).
 *
 * @param array $mp A single-layer multi-picker value { position, <pos>:{ pos_offsets, element_zindex } }.
 * @return string
 */
function sc_position_decls( $mp ) {
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

if ( ! function_exists( 'sc_overflow_layer' ) ) :
/** One device layer's overflow value from the (responsive) element_overflow att; '' if unset/invalid. */
function sc_overflow_layer( $atts, $layer ) {
    $ov = isset( $atts['element_overflow'] ) ? $atts['element_overflow'] : '';
    if ( is_array( $ov ) ) {
        // Legacy flat shape would be a scalar, so an array here is the responsive { base, md, lg, xl }.
        $v = isset( $ov[ $layer ] ) ? (string) $ov[ $layer ] : '';
    } else {
        $v = ( $layer === 'base' ) ? (string) $ov : '';
    }
    return in_array( $v, array( 'hidden', 'auto', 'scroll', 'clip' ), true ) ? $v : '';
}
endif;

if ( ! function_exists( 'sc_position_style' ) ) :
/**
 * Inline style for the BASE layer of position/offsets/z-index + overflow — applies at all widths,
 * exactly as before this control went responsive (so existing output is byte-identical). The md/lg/xl
 * overrides are emitted as scoped @media CSS by sc_position_responsive_css() (per-page dynamic CSS).
 */
function sc_position_style( $atts ) {
    $mp    = isset( $atts['element_position'] ) ? $atts['element_position'] : null;
    $style = sc_position_decls( sc_position_mp_layer( $mp, 'base' ) );
    $ov    = sc_overflow_layer( $atts, 'base' );
    if ( $ov !== '' ) { $style .= 'overflow:' . $ov . ';'; }
    return $style;
}
endif;

if ( ! function_exists( 'sc_position_responsive_css' ) ) :
/**
 * Scoped per-device CSS for the Position / Overflow controls' md/lg/xl layers, keyed to the element's
 * `.u{hash}` scope class so it rides the per-page dynamic CSS file (dynamic-css.php) — the base layer
 * stays inline (sc_position_style). Returns '' when no md/lg/xl override is set (the ~everything case),
 * so it is a no-op for elements that aren't positioned responsively. Mirrors sc_section_dynamic_css()'s
 * "called by BOTH the view and the aggregator" contract.
 *
 * @param array  $atts  element atts (expects element_position / element_overflow / unique_id)
 * @param string $scope element scope class (sc_element_scope_class), no leading dot
 * @return string CSS (no <style> tag)
 */
function sc_position_responsive_css( $atts, $scope ) {
    $scope = trim( (string) $scope );
    if ( $scope === '' || ! is_array( $atts ) ) { return ''; }
    $mp  = isset( $atts['element_position'] ) ? $atts['element_position'] : null;
    $bp  = array( 'md' => 768, 'lg' => 992, 'xl' => 1200 );
    $css = '';
    foreach ( $bp as $layer => $minw ) {
        $decls = sc_position_decls( sc_position_mp_layer( $mp, $layer ) );
        $ov    = sc_overflow_layer( $atts, $layer );
        if ( $ov !== '' ) { $decls .= 'overflow:' . $ov . ';'; }
        if ( $decls !== '' ) {
            $css .= '@media (min-width:' . $minw . 'px){.' . $scope . '{' . $decls . '}}';
        }
    }
    return $css;
}
endif;

if ( ! function_exists( 'sc_has_responsive_position' ) ) :
/** True when element_position / element_overflow set any md/lg/xl override (so the wrapper needs the
 *  scope class for sc_position_responsive_css()'s @media rules to target). */
function sc_has_responsive_position( $atts ) {
    if ( ! is_array( $atts ) ) { return false; }
    $mp = isset( $atts['element_position'] ) ? $atts['element_position'] : null;
    foreach ( array( 'md', 'lg', 'xl' ) as $layer ) {
        if ( sc_position_decls( sc_position_mp_layer( $mp, $layer ) ) !== '' ) { return true; }
        if ( sc_overflow_layer( $atts, $layer ) !== '' ) { return true; }
    }
    return false;
}
endif;

/** Builds the outer wrapper attributes (base class, unique id, extra attrs) for a shortcode. */
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

    // Add the prefix-independent scope class when this element carries per-element Custom CSS OR a
    // responsive Position/Overflow override, so the CSS aggregator's `.u{hash}` rules have a matching
    // target. Markup stays clean for every other element.
    if ( ! empty( $atts['custom_css'] )
         || ( function_exists( 'sc_has_responsive_position' ) && sc_has_responsive_position( $atts ) ) ) {
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

    // Position + Z-Index + Overflow (shared Advanced tab) → BASE layer merged onto the wrapper style
    // (applies at all widths, byte-identical to the pre-responsive behaviour). The md/lg/xl overrides
    // ride the per-page scoped CSS via sc_position_responsive_css() (collected in dynamic-css.php).
    $pos_style = function_exists( 'sc_position_style' ) ? sc_position_style( $atts ) : '';
    if ( $pos_style !== '' ) {
        $attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], '; ' ) . ';' : '' ) . $pos_style;
    }

    // Add class attribute if classes exist (de-duplicated — e.g. a base_class of
    // "heading" plus a user/converter css_class of "heading" collapse to one token).
    if ( ! empty( $classes ) ) {
        $classes = array_values( array_unique( array_filter( $classes ) ) );
        $attr['class'] = esc_attr( implode( ' ', $classes ) );
    }

    /** Filters the assembled HTML attribute array for a shortcode wrapper element before it is rendered. */
    return apply_filters( 'sc_build_wrapper_attr', $attr, $atts );
}

if ( ! function_exists( 'sc_editor_notice' ) ) :
	/**
	 * Returns an italic editor-only placeholder notice div wrapping the escaped text.
	 *
	 * An editor-only "nothing to render yet" note.
	 *
	 * Most elements say what is missing when they have nothing to show. That
	 * matters more in a Gutenberg block than it ever did in the page builder: a
	 * dynamic block that renders an empty string produces "Block rendered as
	 * empty", which is exactly what a BROKEN block looks like. The user cannot
	 * tell "you have not chosen an image yet" from "this is not working".
	 *
	 * The styling is INLINE rather than a class, because several of the elements
	 * that need this ship no stylesheet of their own — a `.fw-sc__empty` rule
	 * would have nowhere to live for them, and a message that looks unstyled in
	 * some blocks and styled in others is its own small confusion.
	 *
	 * Callers are responsible for the editor check; this helper only builds the
	 * markup, so a caller can decide to show it in other contexts too.
	 *
	 * @param string $text The message. Escaped here — pass plain text.
	 * @return string HTML.
	 */
	function sc_editor_notice( $text ) {
		return '<div class="fw-sc__empty" style="color:#646970;font-style:italic;padding:12px 0;">'
			. esc_html( $text )
			. '</div>';
	}
endif;
