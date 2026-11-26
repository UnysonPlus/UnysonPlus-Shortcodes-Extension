<?php
/**
 * PHP Version: 7.4 or higher
 *
 * One Effects group on every element (Animation Engine roadmap 8a).
 *
 * Webflow's Style panel gives every element the same "Effects" group — opacity,
 * blend mode, outline, stacked box shadows, 2D/3D transforms, filters, backdrop
 * filters, a transition for hover/state changes, cursor, pointer events and
 * custom properties (CSS variables). This adds the same group, uniformly, to
 * EVERY UnysonPlus element.
 *
 * Provides:
 *   1. sc_get_effects_fields() — the inner fields of the shared "Effects" tab.
 *   2. A filter on `fw_shortcode_get_options` that injects that tab onto every
 *      tab-structured shortcode (so no per-shortcode options.php edits).
 *   3. A filter on `sc_build_wrapper_attr` that turns the saved values into
 *      scoped INLINE CSS on the element's own wrapper — per-instance by nature,
 *      so a preset that owns the look still wins where the author didn't set a
 *      value here.
 *
 * Additive and non-destructive: it does not touch or remove the per-element
 * effects a few elements already carry (the Div's Blend Mode / Backdrop Blur, a
 * column's Box Shadow). Those keep working; the Effects tab is the uniform home
 * going forward, and a value set there simply layers on as inline style.
 */
if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/* -------------------------------------------------------------------------- *
 * Choice lists
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'sc_effects_blend_choices' ) ) :
function sc_effects_blend_choices() {
	return array(
		''            => __( 'Normal (no blending)', 'fw' ),
		'multiply'    => __( 'Multiply', 'fw' ),
		'screen'      => __( 'Screen', 'fw' ),
		'overlay'     => __( 'Overlay', 'fw' ),
		'darken'      => __( 'Darken', 'fw' ),
		'lighten'     => __( 'Lighten', 'fw' ),
		'color-dodge' => __( 'Color Dodge', 'fw' ),
		'color-burn'  => __( 'Color Burn', 'fw' ),
		'hard-light'  => __( 'Hard Light', 'fw' ),
		'soft-light'  => __( 'Soft Light', 'fw' ),
		'difference'  => __( 'Difference', 'fw' ),
		'exclusion'   => __( 'Exclusion', 'fw' ),
		'hue'         => __( 'Hue', 'fw' ),
		'saturation'  => __( 'Saturation', 'fw' ),
		'color'       => __( 'Color', 'fw' ),
		'luminosity'  => __( 'Luminosity', 'fw' ),
	);
}
endif;

if ( ! function_exists( 'sc_effects_cursor_choices' ) ) :
function sc_effects_cursor_choices() {
	return array(
		''            => __( 'Default', 'fw' ),
		'pointer'     => __( 'Pointer (hand)', 'fw' ),
		'grab'        => __( 'Grab', 'fw' ),
		'grabbing'    => __( 'Grabbing', 'fw' ),
		'zoom-in'     => __( 'Zoom in', 'fw' ),
		'zoom-out'    => __( 'Zoom out', 'fw' ),
		'crosshair'   => __( 'Crosshair', 'fw' ),
		'move'        => __( 'Move', 'fw' ),
		'help'        => __( 'Help', 'fw' ),
		'wait'        => __( 'Wait', 'fw' ),
		'text'        => __( 'Text', 'fw' ),
		'not-allowed' => __( 'Not allowed', 'fw' ),
		'none'        => __( 'None (hidden)', 'fw' ),
	);
}
endif;

if ( ! function_exists( 'sc_effects_origin_choices' ) ) :
function sc_effects_origin_choices() {
	return array(
		''             => __( 'Center (default)', 'fw' ),
		'top left'     => __( 'Top left', 'fw' ),
		'top'          => __( 'Top', 'fw' ),
		'top right'    => __( 'Top right', 'fw' ),
		'left'         => __( 'Left', 'fw' ),
		'right'        => __( 'Right', 'fw' ),
		'bottom left'  => __( 'Bottom left', 'fw' ),
		'bottom'       => __( 'Bottom', 'fw' ),
		'bottom right' => __( 'Bottom right', 'fw' ),
	);
}
endif;

/* -------------------------------------------------------------------------- *
 * Fields
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'sc_get_effects_fields' ) ) :
/**
 * The inner fields of the shared "Effects" tab. Flat (no value-nesting groups),
 * except the two addable-box repeaters (stacked shadows + CSS variables), so the
 * wrapper filter can read each value straight off $atts.
 */
function sc_get_effects_fields() {
	$slider = function ( $label, $val, $min, $max, $step, $desc = '' ) {
		return array( 'type' => 'slider', 'label' => $label, 'desc' => $desc, 'value' => $val, 'properties' => array( 'min' => $min, 'max' => $max, 'step' => $step ) );
	};
	$head = function ( $text ) {
		return array( 'type' => 'html', 'label' => false, 'desc' => false,
			'html' => '<p style="margin:.4rem 0 0;font-weight:600;color:#1c1e22;font-size:12px;text-transform:uppercase;letter-spacing:.04em">' . esc_html( $text ) . '</p>' );
	};

	return array(
		'effect_note' => array(
			'type'  => 'html',
			'label' => false,
			'desc'  => false,
			'html'  => '<p style="margin:0;color:#646970;font-size:12px;line-height:1.5;">'
				. esc_html__( 'The same Effects on every element, applied to this one as inline CSS. Leave a control at its default to skip it. These are per-element values; where a preset owns the look, set it on the preset instead.', 'fw' ) . '</p>',
		),

		// Opacity + blend.
		'effect_opacity' => $slider( __( 'Opacity (%)', 'fw' ), 100, 0, 100, 1, __( 'Below 100 fades the whole element.', 'fw' ) ),
		'effect_blend'   => array(
			'type'    => 'select',
			'label'   => __( 'Blend Mode', 'fw' ),
			'desc'    => __( 'CSS mix-blend-mode — blends the element with whatever sits behind it. Needs something behind to blend with.', 'fw' ),
			'value'   => '',
			'choices' => sc_effects_blend_choices(),
		),

		// Outline.
		'effect_outline_head'   => $head( __( 'Outline', 'fw' ) ),
		'effect_outline_width'  => $slider( __( 'Outline width (px)', 'fw' ), 0, 0, 20, 1, __( 'Draws OUTSIDE the box without affecting layout (unlike a border). 0 = off.', 'fw' ) ),
		'effect_outline_style'  => array( 'type' => 'select', 'label' => __( 'Outline style', 'fw' ), 'value' => 'solid', 'choices' => array( 'solid' => __( 'Solid', 'fw' ), 'dashed' => __( 'Dashed', 'fw' ), 'dotted' => __( 'Dotted', 'fw' ), 'double' => __( 'Double', 'fw' ) ) ),
		'effect_outline_color'  => array( 'type' => 'rgba-color-picker', 'label' => __( 'Outline color', 'fw' ), 'desc' => __( 'Empty = the text colour.', 'fw' ), 'value' => '' ),
		'effect_outline_offset' => $slider( __( 'Outline offset (px)', 'fw' ), 0, -10, 20, 1 ),

		// Box shadows (stacked).
		'effect_shadows' => array(
			'type'        => 'addable-box',
			'label'       => __( 'Box Shadows', 'fw' ),
			'desc'        => __( 'Stack one or more shadows; later layers paint over earlier ones. Use a soft dark shadow for depth, or several for a layered glow.', 'fw' ),
			'box-options' => array(
				'shadow' => array( 'type' => 'box-shadow', 'label' => false ),
			),
			'value'       => array(),
		),

		// Transforms.
		'effect_transform_head' => $head( __( 'Transform (2D / 3D)', 'fw' ) ),
		'effect_tx'      => $slider( __( 'Move X (px)', 'fw' ), 0, -500, 500, 1 ),
		'effect_ty'      => $slider( __( 'Move Y (px)', 'fw' ), 0, -500, 500, 1 ),
		'effect_scale'   => $slider( __( 'Scale (%)', 'fw' ), 100, 0, 300, 1 ),
		'effect_rotate'  => $slider( __( 'Rotate (°)', 'fw' ), 0, -360, 360, 1 ),
		'effect_skx'     => $slider( __( 'Skew X (°)', 'fw' ), 0, -80, 80, 1 ),
		'effect_sky'     => $slider( __( 'Skew Y (°)', 'fw' ), 0, -80, 80, 1 ),
		'effect_rx'      => $slider( __( 'Tilt X · rotateX (°)', 'fw' ), 0, -180, 180, 1, __( 'Needs a Perspective below to show as 3D.', 'fw' ) ),
		'effect_ry'      => $slider( __( 'Tilt Y · rotateY (°)', 'fw' ), 0, -180, 180, 1 ),
		'effect_persp'   => $slider( __( 'Perspective (px)', 'fw' ), 0, 0, 2000, 10, __( 'Depth for the 3D tilts. 0 = off (flat).', 'fw' ) ),
		'effect_origin'  => array( 'type' => 'select', 'label' => __( 'Transform origin', 'fw' ), 'value' => '', 'choices' => sc_effects_origin_choices() ),

		// Filters.
		'effect_filter_head' => $head( __( 'Filters', 'fw' ) ),
		'effect_f_blur'     => $slider( __( 'Blur (px)', 'fw' ), 0, 0, 40, 0.5 ),
		'effect_f_bright'   => $slider( __( 'Brightness (%)', 'fw' ), 100, 0, 300, 1 ),
		'effect_f_contrast' => $slider( __( 'Contrast (%)', 'fw' ), 100, 0, 300, 1 ),
		'effect_f_sat'      => $slider( __( 'Saturation (%)', 'fw' ), 100, 0, 300, 1 ),
		'effect_f_gray'     => $slider( __( 'Grayscale (%)', 'fw' ), 0, 0, 100, 1 ),
		'effect_f_hue'      => $slider( __( 'Hue rotate (°)', 'fw' ), 0, 0, 360, 1 ),
		'effect_f_sepia'    => $slider( __( 'Sepia (%)', 'fw' ), 0, 0, 100, 1 ),
		'effect_f_invert'   => $slider( __( 'Invert (%)', 'fw' ), 0, 0, 100, 1 ),

		// Backdrop filters.
		'effect_backdrop_head' => $head( __( 'Backdrop filters (glass)', 'fw' ) ),
		'effect_b_blur'     => $slider( __( 'Backdrop blur (px)', 'fw' ), 0, 0, 40, 0.5, __( 'Frosted glass — pair with a translucent background so what is behind shows through.', 'fw' ) ),
		'effect_b_bright'   => $slider( __( 'Backdrop brightness (%)', 'fw' ), 100, 0, 300, 1 ),
		'effect_b_sat'      => $slider( __( 'Backdrop saturation (%)', 'fw' ), 100, 0, 300, 1 ),
		'effect_b_contrast' => $slider( __( 'Backdrop contrast (%)', 'fw' ), 100, 0, 300, 1 ),

		// Transition.
		'effect_transition_head' => $head( __( 'Transition', 'fw' ) ),
		'effect_tr_dur'  => $slider( __( 'Transition (ms)', 'fw' ), 0, 0, 2000, 10, __( 'Animates changes to these properties on hover or state change. 0 = off (instant).', 'fw' ) ),
		'effect_tr_ease' => array( 'type' => 'select', 'label' => __( 'Transition easing', 'fw' ), 'value' => 'ease', 'choices' => array(
			'ease'                            => __( 'Ease', 'fw' ),
			'linear'                          => __( 'Linear', 'fw' ),
			'ease-in'                         => __( 'Ease in', 'fw' ),
			'ease-out'                        => __( 'Ease out', 'fw' ),
			'ease-in-out'                     => __( 'Ease in-out', 'fw' ),
			'cubic-bezier(.34,1.56,.64,1)'    => __( 'Back (overshoot)', 'fw' ),
			'cubic-bezier(.22,1,.36,1)'       => __( 'Smooth out', 'fw' ),
		) ),
		'effect_tr_prop' => array( 'type' => 'select', 'label' => __( 'Transition applies to', 'fw' ), 'value' => 'all', 'choices' => array(
			'all'        => __( 'Everything', 'fw' ),
			'transform'  => __( 'Transform', 'fw' ),
			'opacity'    => __( 'Opacity', 'fw' ),
			'filter'     => __( 'Filter', 'fw' ),
			'box-shadow' => __( 'Box shadow', 'fw' ),
			'background-color, color, border-color' => __( 'Colours', 'fw' ),
		) ),

		// Interaction.
		'effect_interaction_head' => $head( __( 'Interaction', 'fw' ) ),
		'effect_cursor'  => array( 'type' => 'select', 'label' => __( 'Cursor', 'fw' ), 'value' => '', 'choices' => sc_effects_cursor_choices() ),
		'effect_pointer' => array( 'type' => 'select', 'label' => __( 'Pointer events', 'fw' ), 'value' => '', 'desc' => __( 'Ignore the mouse lets clicks pass through to what is behind.', 'fw' ), 'choices' => array(
			''     => __( 'Default', 'fw' ),
			'none' => __( 'Ignore the mouse (none)', 'fw' ),
			'auto' => __( 'React to the mouse (auto)', 'fw' ),
		) ),

		// Custom properties.
		'effect_vars' => array(
			'type'        => 'addable-box',
			'label'       => __( 'Custom Properties (CSS variables)', 'fw' ),
			'desc'        => __( 'Set CSS variables on this element — e.g. --gap: 20px — usable by its own Custom CSS or children.', 'fw' ),
			'box-options' => array(
				'name' => array( 'type' => 'text', 'label' => __( 'Name', 'fw' ), 'value' => '', 'attr' => array( 'placeholder' => '--gap' ) ),
				'val'  => array( 'type' => 'text', 'label' => __( 'Value', 'fw' ), 'value' => '', 'attr' => array( 'placeholder' => '20px' ) ),
			),
			'value'       => array(),
		),
	);
}
endif;

/* -------------------------------------------------------------------------- *
 * Inject the Effects tab onto every element
 * -------------------------------------------------------------------------- */

add_filter( 'fw_shortcode_get_options', 'sc_effects_inject_tab', 50, 2 );
if ( ! function_exists( 'sc_effects_inject_tab' ) ) :
/**
 * Append the shared Effects tab to every tab-structured shortcode option set.
 * Guarded: skips an option set that is not laid out as tabs (so a flat / utility
 * shortcode is left alone), and never double-adds. `sc_effects_tab_shortcodes`
 * filters the tag denylist.
 */
function sc_effects_inject_tab( $options, $tag ) {
	if ( ! is_array( $options ) || isset( $options['tab_effects'] ) ) {
		return $options;
	}
	/** Tags that should NOT receive the Effects tab. */
	$skip = apply_filters( 'sc_effects_tab_skip', array(), $tag );
	if ( is_array( $skip ) && in_array( $tag, $skip, true ) ) {
		return $options;
	}
	// Only elements whose options are laid out as tabs (every builder element).
	$has_tab = false;
	foreach ( $options as $o ) {
		if ( is_array( $o ) && isset( $o['type'] ) && $o['type'] === 'tab' ) {
			$has_tab = true;
			break;
		}
	}
	if ( ! $has_tab ) {
		return $options;
	}
	$options['tab_effects'] = array(
		'title'   => __( 'Effects', 'fw' ),
		'type'    => 'tab',
		'options' => sc_get_effects_fields(),
	);
	return $options;
}
endif;

/* -------------------------------------------------------------------------- *
 * Emit the effects as scoped inline CSS on the wrapper
 * -------------------------------------------------------------------------- */

add_filter( 'sc_build_wrapper_attr', 'sc_effects_apply_wrapper', 40, 2 );
if ( ! function_exists( 'sc_effects_apply_wrapper' ) ) :
function sc_effects_apply_wrapper( $attr, $atts ) {
	$css = sc_effects_inline_css( $atts );
	if ( $css === '' ) {
		return $attr;
	}
	$existing = ( isset( $attr['style'] ) && $attr['style'] !== '' ) ? rtrim( $attr['style'], '; ' ) . ';' : '';
	$attr['style'] = $existing . $css;
	return $attr;
}
endif;

/**
 * An element whose ONLY styling is an effect still needs its wrapper, so the
 * inline CSS has somewhere to live. A plain heading / button normally skips the
 * wrapper; this forces it when any effect is set (mirrors how Interactions and
 * States opt an element into a wrapper).
 */
add_filter( 'sc_needs_wrapper', 'sc_effects_needs_wrapper', 10, 2 );
if ( ! function_exists( 'sc_effects_needs_wrapper' ) ) :
function sc_effects_needs_wrapper( $needs, $atts ) {
	if ( $needs ) {
		return $needs;
	}
	return sc_effects_inline_css( $atts ) !== '';
}
endif;

/* -------------------------------------------------------------------------- *
 * Value → CSS helpers
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'sc_effects_num' ) ) :
function sc_effects_num( $atts, $key, $def = 0 ) {
	return ( is_array( $atts ) && isset( $atts[ $key ] ) && is_numeric( $atts[ $key ] ) ) ? (float) $atts[ $key ] : (float) $def;
}
endif;

if ( ! function_exists( 'sc_effects_f' ) ) :
/** Trim a float for CSS: 1.000 → 1, 1.250 → 1.25. */
function sc_effects_f( $n ) {
	$s = rtrim( rtrim( number_format( (float) $n, 3, '.', '' ), '0' ), '.' );
	return $s === '' || $s === '-0' ? '0' : $s;
}
endif;

if ( ! function_exists( 'sc_effects_color' ) ) :
/** Normalise an rgba-color-picker value (string, or {color,opacity}) to a CSS colour, or '' . */
function sc_effects_color( $value, $fallback = '' ) {
	if ( is_array( $value ) ) {
		$value = isset( $value['color'] ) ? $value['color'] : '';
	}
	$value = trim( (string) $value );
	if ( $value === '' ) {
		return $fallback;
	}
	// Only hex / rgb(a) / hsl(a) / a plain keyword — no other characters.
	if ( preg_match( '/^#[0-9a-fA-F]{3,8}$/', $value ) || preg_match( '/^(rgb|rgba|hsl|hsla)\([0-9.,%\s\/]+\)$/i', $value ) || preg_match( '/^[a-zA-Z]+$/', $value ) ) {
		return $value;
	}
	return $fallback;
}
endif;

if ( ! function_exists( 'sc_effects_shadows_css' ) ) :
/** addable-box of box-shadow → a `box-shadow` value (layers joined), or '' . */
function sc_effects_shadows_css( $value ) {
	if ( ! is_array( $value ) ) {
		return '';
	}
	$layers = array();
	foreach ( $value as $box ) {
		$s = ( is_array( $box ) && isset( $box['shadow'] ) && is_array( $box['shadow'] ) ) ? $box['shadow'] : ( is_array( $box ) ? $box : null );
		if ( ! is_array( $s ) ) {
			continue;
		}
		$x = isset( $s['x'] ) && is_numeric( $s['x'] ) ? (int) $s['x'] : 0;
		$y = isset( $s['y'] ) && is_numeric( $s['y'] ) ? (int) $s['y'] : 0;
		$b = isset( $s['blur'] ) && is_numeric( $s['blur'] ) ? max( 0, (int) $s['blur'] ) : 0;
		$sp = isset( $s['spread'] ) && is_numeric( $s['spread'] ) ? (int) $s['spread'] : 0;
		$color = sc_effects_color( isset( $s['color'] ) ? $s['color'] : '', 'rgba(0,0,0,.2)' );
		$inset = ! empty( $s['inset'] ) && $s['inset'] !== 'false' ? 'inset ' : '';
		if ( $x === 0 && $y === 0 && $b === 0 && $sp === 0 && $inset === '' ) {
			continue; // an empty layer
		}
		$layers[] = $inset . $x . 'px ' . $y . 'px ' . $b . 'px ' . $sp . 'px ' . $color;
	}
	return implode( ', ', $layers );
}
endif;

if ( ! function_exists( 'sc_effects_transform_css' ) ) :
/** The transform + transform-origin, as [ 'transform' => …, 'origin' => … ]. */
function sc_effects_transform_css( $atts ) {
	$parts = array();
	$persp = sc_effects_num( $atts, 'effect_persp' );
	if ( $persp > 0 ) {
		$parts[] = 'perspective(' . sc_effects_f( $persp ) . 'px)';
	}
	$tx = sc_effects_num( $atts, 'effect_tx' );
	$ty = sc_effects_num( $atts, 'effect_ty' );
	if ( $tx || $ty ) {
		$parts[] = 'translate(' . sc_effects_f( $tx ) . 'px,' . sc_effects_f( $ty ) . 'px)';
	}
	$sc = sc_effects_num( $atts, 'effect_scale', 100 );
	if ( $sc != 100 ) {
		$parts[] = 'scale(' . sc_effects_f( $sc / 100 ) . ')';
	}
	$rot = sc_effects_num( $atts, 'effect_rotate' );
	if ( $rot ) {
		$parts[] = 'rotate(' . sc_effects_f( $rot ) . 'deg)';
	}
	$rx = sc_effects_num( $atts, 'effect_rx' );
	if ( $rx ) {
		$parts[] = 'rotateX(' . sc_effects_f( $rx ) . 'deg)';
	}
	$ry = sc_effects_num( $atts, 'effect_ry' );
	if ( $ry ) {
		$parts[] = 'rotateY(' . sc_effects_f( $ry ) . 'deg)';
	}
	$skx = sc_effects_num( $atts, 'effect_skx' );
	$sky = sc_effects_num( $atts, 'effect_sky' );
	if ( $skx || $sky ) {
		$parts[] = 'skew(' . sc_effects_f( $skx ) . 'deg,' . sc_effects_f( $sky ) . 'deg)';
	}
	$origin = ( is_array( $atts ) && isset( $atts['effect_origin'] ) ) ? preg_replace( '/[^a-z0-9%\- ]/', '', (string) $atts['effect_origin'] ) : '';
	return array( 'transform' => implode( ' ', $parts ), 'origin' => trim( $origin ) );
}
endif;

if ( ! function_exists( 'sc_effects_filter_css' ) ) :
function sc_effects_filter_css( $atts ) {
	$f = array();
	$blur = sc_effects_num( $atts, 'effect_f_blur' );
	if ( $blur > 0 ) { $f[] = 'blur(' . sc_effects_f( $blur ) . 'px)'; }
	foreach ( array( 'effect_f_bright' => 'brightness', 'effect_f_contrast' => 'contrast', 'effect_f_sat' => 'saturate' ) as $k => $fn ) {
		$v = sc_effects_num( $atts, $k, 100 );
		if ( $v != 100 ) { $f[] = $fn . '(' . sc_effects_f( $v / 100 ) . ')'; }
	}
	$gray = sc_effects_num( $atts, 'effect_f_gray' );
	if ( $gray > 0 ) { $f[] = 'grayscale(' . sc_effects_f( $gray / 100 ) . ')'; }
	$hue = sc_effects_num( $atts, 'effect_f_hue' );
	if ( $hue ) { $f[] = 'hue-rotate(' . sc_effects_f( $hue ) . 'deg)'; }
	$sepia = sc_effects_num( $atts, 'effect_f_sepia' );
	if ( $sepia > 0 ) { $f[] = 'sepia(' . sc_effects_f( $sepia / 100 ) . ')'; }
	$invert = sc_effects_num( $atts, 'effect_f_invert' );
	if ( $invert > 0 ) { $f[] = 'invert(' . sc_effects_f( $invert / 100 ) . ')'; }
	return implode( ' ', $f );
}
endif;

if ( ! function_exists( 'sc_effects_backdrop_css' ) ) :
function sc_effects_backdrop_css( $atts ) {
	$f = array();
	$blur = sc_effects_num( $atts, 'effect_b_blur' );
	if ( $blur > 0 ) { $f[] = 'blur(' . sc_effects_f( $blur ) . 'px)'; }
	foreach ( array( 'effect_b_bright' => 'brightness', 'effect_b_sat' => 'saturate', 'effect_b_contrast' => 'contrast' ) as $k => $fn ) {
		$v = sc_effects_num( $atts, $k, 100 );
		if ( $v != 100 ) { $f[] = $fn . '(' . sc_effects_f( $v / 100 ) . ')'; }
	}
	return implode( ' ', $f );
}
endif;

if ( ! function_exists( 'sc_effects_transition_css' ) ) :
function sc_effects_transition_css( $atts ) {
	$dur = sc_effects_num( $atts, 'effect_tr_dur' );
	if ( $dur <= 0 ) {
		return '';
	}
	$prop = ( is_array( $atts ) && isset( $atts['effect_tr_prop'] ) ) ? (string) $atts['effect_tr_prop'] : 'all';
	if ( ! preg_match( '/^[a-z, \-]+$/', $prop ) ) {
		$prop = 'all';
	}
	$ease = ( is_array( $atts ) && isset( $atts['effect_tr_ease'] ) ) ? (string) $atts['effect_tr_ease'] : 'ease';
	if ( ! preg_match( '/^[a-z\-]+$/', $ease ) && ! preg_match( '/^cubic-bezier\([0-9.,\s\-]+\)$/', $ease ) ) {
		$ease = 'ease';
	}
	return $prop . ' ' . sc_effects_f( $dur ) . 'ms ' . $ease;
}
endif;

if ( ! function_exists( 'sc_effects_vars_css' ) ) :
/** addable-box of {name,val} → `--name:value;…`, or '' . Names are kept to CSS-var-safe characters. */
function sc_effects_vars_css( $value ) {
	if ( ! is_array( $value ) ) {
		return '';
	}
	$out = array();
	foreach ( $value as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$name = isset( $row['name'] ) ? trim( (string) $row['name'] ) : '';
		$val  = isset( $row['val'] ) ? trim( (string) $row['val'] ) : '';
		if ( $name === '' || $val === '' ) {
			continue;
		}
		if ( strpos( $name, '--' ) !== 0 ) {
			$name = '--' . ltrim( $name, '-' );
		}
		$name = preg_replace( '/[^a-zA-Z0-9_\-]/', '', $name );
		// Strip every inline-style breakout vector from the value: declaration
		// separators, rule braces, markup, the attribute's own quotes, CSS
		// escapes and newlines. What is left cannot start a new declaration or
		// escape the style attribute.
		$val = str_replace( array( ';', '}', '{', '<', '>', '"', "'", '\\', "\r", "\n" ), '', $val );
		if ( $name === '--' || $name === '' || $val === '' ) {
			continue;
		}
		$out[] = $name . ':' . $val;
	}
	return implode( ';', $out );
}
endif;

if ( ! function_exists( 'sc_effects_inline_css' ) ) :
/**
 * Build the inline CSS declaration string for an element's effects, or '' when
 * nothing is set. Only non-default values emit, so an element with an untouched
 * Effects tab gets no extra style.
 *
 * @param array $atts The element's attributes.
 * @return string e.g. "opacity:.5;mix-blend-mode:multiply;transform:rotate(4deg);"
 */
function sc_effects_inline_css( $atts ) {
	if ( ! is_array( $atts ) ) {
		return '';
	}
	$d = array();

	$op = sc_effects_num( $atts, 'effect_opacity', 100 );
	if ( $op < 100 ) {
		$d[] = 'opacity:' . sc_effects_f( max( 0, $op ) / 100 );
	}

	$blend = isset( $atts['effect_blend'] ) ? (string) $atts['effect_blend'] : '';
	if ( $blend !== '' && preg_match( '/^[a-z\-]+$/', $blend ) ) {
		$d[] = 'mix-blend-mode:' . $blend;
	}

	$ow = sc_effects_num( $atts, 'effect_outline_width' );
	if ( $ow > 0 ) {
		$ostyle = isset( $atts['effect_outline_style'] ) ? preg_replace( '/[^a-z]/', '', (string) $atts['effect_outline_style'] ) : 'solid';
		$ocolor = sc_effects_color( isset( $atts['effect_outline_color'] ) ? $atts['effect_outline_color'] : '', 'currentColor' );
		$d[]    = 'outline:' . sc_effects_f( $ow ) . 'px ' . ( $ostyle !== '' ? $ostyle : 'solid' ) . ' ' . $ocolor;
		$ooff   = sc_effects_num( $atts, 'effect_outline_offset' );
		if ( $ooff != 0 ) {
			$d[] = 'outline-offset:' . sc_effects_f( $ooff ) . 'px';
		}
	}

	$shadows = sc_effects_shadows_css( isset( $atts['effect_shadows'] ) ? $atts['effect_shadows'] : null );
	if ( $shadows !== '' ) {
		$d[] = 'box-shadow:' . $shadows;
	}

	$tf = sc_effects_transform_css( $atts );
	if ( $tf['transform'] !== '' ) {
		$d[] = 'transform:' . $tf['transform'];
	}
	if ( $tf['origin'] !== '' ) {
		$d[] = 'transform-origin:' . $tf['origin'];
	}

	$filter = sc_effects_filter_css( $atts );
	if ( $filter !== '' ) {
		$d[] = 'filter:' . $filter;
	}

	$backdrop = sc_effects_backdrop_css( $atts );
	if ( $backdrop !== '' ) {
		$d[] = '-webkit-backdrop-filter:' . $backdrop;
		$d[] = 'backdrop-filter:' . $backdrop;
	}

	$transition = sc_effects_transition_css( $atts );
	if ( $transition !== '' ) {
		$d[] = 'transition:' . $transition;
	}

	$cursor = isset( $atts['effect_cursor'] ) ? (string) $atts['effect_cursor'] : '';
	if ( $cursor !== '' && preg_match( '/^[a-z\-]+$/', $cursor ) ) {
		$d[] = 'cursor:' . $cursor;
	}

	$pe = isset( $atts['effect_pointer'] ) ? (string) $atts['effect_pointer'] : '';
	if ( in_array( $pe, array( 'none', 'auto' ), true ) ) {
		$d[] = 'pointer-events:' . $pe;
	}

	$vars = sc_effects_vars_css( isset( $atts['effect_vars'] ) ? $atts['effect_vars'] : null );
	if ( $vars !== '' ) {
		$d[] = $vars;
	}

	if ( ! $d ) {
		return '';
	}
	return implode( ';', $d ) . ';';
}
endif;
