<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */

if ( ! function_exists( 'fw_counter_typography_css' ) ) {
	/** A typography-v2 value → inline CSS declarations (only the keys that are set). */
	function fw_counter_typography_css( $t ) {
		if ( ! is_array( $t ) ) {
			return '';
		}
		$css = '';
		if ( ! empty( $t['family'] ) ) {
			$css .= 'font-family:' . $t['family'] . ';';
		}
		if ( ! empty( $t['weight'] ) ) {
			$css .= 'font-weight:' . $t['weight'] . ';';
		}
		if ( isset( $t['size'] ) && $t['size'] !== '' ) {
			// Size is a unit-input ({value,unit}); resolve it (tolerating a legacy bare
			// number → px). Only emit when there's a real length (skip an empty value).
			$size_css = fw_typography_size_css( $t['size'] );
			if ( $size_css !== '' ) { $css .= 'font-size:' . $size_css . ';'; }
		}
		if ( isset( $t['line-height'] ) && $t['line-height'] !== '' && (int) $t['line-height'] > 0 ) {
			$css .= 'line-height:' . (int) $t['line-height'] . 'px;';
		}
		if ( isset( $t['letter-spacing'] ) && $t['letter-spacing'] !== '' && (int) $t['letter-spacing'] !== 0 ) {
			$css .= 'letter-spacing:' . (int) $t['letter-spacing'] . 'px;';
		}
		if ( ! empty( $t['style'] ) && $t['style'] !== 'normal' ) {
			$css .= 'font-style:' . $t['style'] . ';';
		}
		return $css;
	}
}

if ( ! function_exists( 'fw_counter_default_font' ) ) {
	/**
	 * Repair an "unconfigured" per-part typography value.
	 *
	 * When a counter is imported / generated with only a STUB font (e.g. `number_font`
	 * saved as just `{ family: '' }`), the page-builder's typography encoder fills the
	 * missing keys from the option TYPE's generic default — a tiny 12px / weight-400 —
	 * NOT from the counter's own intended size (the option's field default of 42/24 is
	 * never consulted at encode time). That exact signature (family '', weight 400,
	 * size 12px, line-height 15) is not something anyone picks for an animated stat
	 * number, so treat it as "unset" and substitute the counter's real default size,
	 * weight and line-height. A value the user actually customised (any other size, or a
	 * bold weight) is left untouched.
	 */
	function fw_counter_default_font( $font, $size, $line_height ) {
		$font = is_array( $font ) ? $font : array();
		$sz   = isset( $font['size'] ) ? $font['size'] : '';
		if ( is_array( $sz ) ) {
			$sz_val  = isset( $sz['value'] ) ? (string) $sz['value'] : '';
			$sz_unit = isset( $sz['unit'] ) ? $sz['unit'] : 'px';
		} else {
			$sz_val  = (string) $sz;
			$sz_unit = 'px';
		}
		$weight = isset( $font['weight'] ) ? (string) $font['weight'] : '';
		// "Unset" = no size, or the option type's untouched 12px default, paired with the
		// default (or missing) 400 weight. Anything else is a real user choice — keep it.
		$size_is_default = ( $sz_val === '' || ( $sz_val === '12' && $sz_unit === 'px' ) );
		if ( $size_is_default && ( $weight === '' || $weight === '400' ) ) {
			$font['size']        = array( 'value' => (string) $size, 'unit' => 'px' );
			$font['line-height'] = $line_height;
			$font['weight']      = '700';
		}
		return $font;
	}
}

if ( ! function_exists( 'fw_counter_enqueue_font' ) ) {
	/** Enqueue a typography-v2 value's Google font, if one was chosen. */
	function fw_counter_enqueue_font( $t ) {
		if ( ! is_array( $t ) || empty( $t['google_font'] ) || empty( $t['family'] ) ) {
			return;
		}
		$fam = str_replace( ' ', '+', $t['family'] );
		$wt  = ! empty( $t['weight'] ) ? ':' . $t['weight'] : '';
		wp_enqueue_style(
			'fw-counter-font-' . sanitize_title( $t['family'] . $t['weight'] ),
			'https://fonts.googleapis.com/css?family=' . $fam . $wt . '&display=swap',
			array(),
			null
		);
	}
}

if ( ! function_exists( 'fw_counter_part' ) ) {
	/**
	 * Render one counter part (prefix / num / suffix) as a <span> carrying its
	 * typography (inline style) + colour (preset class or custom inline style).
	 */
	function fw_counter_part( $tag, $inner, $font, $color ) {
		fw_counter_enqueue_font( $font );

		$style   = fw_counter_typography_css( $font );
		$classes = array( 'fw-counter__' . $tag );

		// Back-compat: pre-redesign saves (e.g. an old section template) stored the
		// colour as a flat hex/rgb string from a plain color-picker, not the compact
		// { predefined, custom } shape. Treat such a string as a custom colour so it
		// renders inline instead of becoming a bogus CSS class.
		if ( is_string( $color ) && $color !== '' && preg_match( '/^(#|rgb)/i', trim( $color ) ) ) {
			$color = array( 'predefined' => '', 'custom' => trim( $color ) );
		}

		if ( function_exists( 'sc_normalize_color_value' ) ) {
			$c = sc_normalize_color_value( $color, 'text' );
			if ( ! empty( $c['class'] ) ) {
				$classes[] = $c['class'];
			}
			if ( ! empty( $c['style'] ) ) {
				$style .= $c['style'] . ';';
			}
		}

		$attr = ' class="' . esc_attr( implode( ' ', $classes ) ) . '"';
		if ( $style !== '' ) {
			$attr .= ' style="' . esc_attr( $style ) . '"';
		}
		return '<span' . $attr . '>' . esc_html( $inner ) . '</span>';
	}
}

$number    = isset( $atts['number'] ) ? trim( (string) $atts['number'] ) : '';
$start     = isset( $atts['start'] ) && is_numeric( $atts['start'] ) ? (float) $atts['start'] : 0;
$prefix    = isset( $atts['prefix'] ) ? (string) $atts['prefix'] : '';
$suffix    = isset( $atts['suffix'] ) ? (string) $atts['suffix'] : '';
$decimals  = isset( $atts['decimals'] ) ? max( 0, min( 4, (int) $atts['decimals'] ) ) : 0;
$separator = ( isset( $atts['separator'] ) && $atts['separator'] === 'no' ) ? '' : ',';
$duration  = ( isset( $atts['duration'] ) && is_numeric( $atts['duration'] ) ) ? (int) $atts['duration'] : 2000;
$easing    = isset( $atts['easing'] ) && in_array( $atts['easing'], array( 'ease-out', 'linear', 'ease-in-out' ), true ) ? $atts['easing'] : 'ease-out';
// Alignment: '' (Inherit) forces nothing — the counter follows the theme / parent
// alignment. Only `center` / `right` emit a modifier class (there is no `--left`
// rule: left is the natural default), so an empty / left value adds no class.
$alignment = isset( $atts['alignment'] ) && in_array( $atts['alignment'], array( 'left', 'center', 'right' ), true ) ? $atts['alignment'] : '';

// Numeric target (strip anything that isn't a digit / dot / minus).
$target = preg_replace( '/[^0-9.\-]/', '', $number );
if ( $target === '' || ! is_numeric( $target ) ) {
	$target = '0';
}
$formatted = number_format( (float) $target, $decimals, '.', $separator );

// Per-part typography + colour.
$number_font  = fw_counter_default_font( isset( $atts['number_font'] ) && is_array( $atts['number_font'] ) ? $atts['number_font'] : array(), 42, 46 );
$prefix_font  = fw_counter_default_font( isset( $atts['prefix_font'] ) && is_array( $atts['prefix_font'] ) ? $atts['prefix_font'] : array(), 24, 28 );
$suffix_font  = fw_counter_default_font( isset( $atts['suffix_font'] ) && is_array( $atts['suffix_font'] ) ? $atts['suffix_font'] : array(), 24, 28 );
$number_color = isset( $atts['number_color'] ) ? $atts['number_color'] : '';
$prefix_color = isset( $atts['prefix_color'] ) ? $atts['prefix_color'] : '';
$suffix_color = isset( $atts['suffix_color'] ) ? $atts['suffix_color'] : '';

// Advanced (the 'advanced_settings' group flattens to top-level $atts keys).
$css_id    = ! empty( $atts['css_id'] ) ? $atts['css_id'] : '';
$css_class = ! empty( $atts['css_class'] ) ? $atts['css_class'] : '';
$hide_keys = array_keys( array_filter( (array) ( $atts['responsive_hide'] ?? array() ) ) );

$classes = array( 'fw-counter' );
if ( $alignment === 'center' || $alignment === 'right' ) {
	$classes[] = 'fw-counter--' . $alignment;
}
if ( $css_class !== '' ) {
	$classes[] = $css_class;
}
$classes = array_merge( $classes, $hide_keys );
?>
<div<?php echo $css_id !== '' ? ' id="' . esc_attr( $css_id ) . '"' : ''; ?> class="<?php echo esc_attr( implode( ' ', array_unique( $classes ) ) ); ?>"<?php echo ( function_exists( 'sc_position_style' ) && ( $sc_pos = sc_position_style( $atts ) ) !== '' ) ? ' style="' . esc_attr( $sc_pos ) . '"' : ''; ?>>
	<div class="fw-counter__main">
		<span class="fw-counter__value" data-target="<?php echo esc_attr( $target ); ?>" data-start="<?php echo esc_attr( $start ); ?>" data-duration="<?php echo esc_attr( $duration ); ?>" data-decimals="<?php echo esc_attr( $decimals ); ?>" data-sep="<?php echo $separator === ',' ? '1' : '0'; ?>" data-easing="<?php echo esc_attr( $easing ); ?>">
			<?php
			if ( $prefix !== '' ) {
				echo fw_counter_part( 'prefix', $prefix, $prefix_font, $prefix_color ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			echo fw_counter_part( 'num', $formatted, $number_font, $number_color ); // phpcs:ignore WordPress.Security.EscapeOutput
			if ( $suffix !== '' ) {
				echo fw_counter_part( 'suffix', $suffix, $suffix_font, $suffix_color ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>
		</span>
	</div>
</div>
