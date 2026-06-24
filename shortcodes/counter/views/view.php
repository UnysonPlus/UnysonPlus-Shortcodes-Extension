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
			$css .= 'font-size:' . (int) $t['size'] . 'px;';
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
$number_font  = isset( $atts['number_font'] ) && is_array( $atts['number_font'] ) ? $atts['number_font'] : array();
$prefix_font  = isset( $atts['prefix_font'] ) && is_array( $atts['prefix_font'] ) ? $atts['prefix_font'] : array();
$suffix_font  = isset( $atts['suffix_font'] ) && is_array( $atts['suffix_font'] ) ? $atts['suffix_font'] : array();
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
<div<?php echo $css_id !== '' ? ' id="' . esc_attr( $css_id ) . '"' : ''; ?> class="<?php echo esc_attr( implode( ' ', array_unique( $classes ) ) ); ?>">
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
