<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** @var array $atts */

if ( ! function_exists( 'sc_get' ) ) {
	function sc_get( $path, $atts, $default = '' ) {
		if ( function_exists( 'fw_akg' ) ) {
			$v = fw_akg( $path, $atts, null );
			if ( $v !== null ) { return $v; }
		}
		return $default;
	}
}

if ( ! function_exists( 'sc_hl_render' ) ) {
	function sc_hl_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$fx       = sc_get( 'fx', $atts, 'marker' );
		if ( ! isset( $registry[ $fx ] ) ) { $fx = 'marker'; }

		$text = trim( (string) sc_get( 'text', $atts, '' ) );
		if ( $text === '' ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-hl__empty">' . esc_html__( 'Add some text.', 'fw' ) . '</div>';
			}
			return '';
		}

		$tag = sc_get( 'tag', $atts, 'h2' );
		if ( ! in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'div' ), true ) ) { $tag = 'h2'; }
		$align = sc_get( 'align', $atts, 'left' );
		$align_cls = function_exists( 'sc_alignment_class' ) ? sc_alignment_class( $align ) : '';

		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$style_var  = $var( 'text_color', '--hl-text' );
		$style_var .= $var( 'accent_color', '--hl-accent' );
		$style_var .= $var( 'accent2_color', '--hl-accent2' );

		$classes = array( 'fw-hl', 'fw-hl--fx-' . sanitize_html_class( $fx ) );
		if ( $align_cls ) { $classes[] = $align_cls; }

		$atts['base_class']       = 'highlight-text';
		$atts['unique_id_prefix'] = 'hl-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $style_var !== '' ) {
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;
		}

		$allowed = array( 'strong' => array(), 'b' => array(), 'em' => array(), 'i' => array(), 'br' => array(), 'a' => array( 'href' => true, 'title' => true, 'target' => true, 'rel' => true ) );
		$inner = nl2br( wp_kses( $text, $allowed ) );

		// Optional plain prefix / suffix on the same line — lets the effect scope to a
		// single phrase inside a heading. Only the middle .fw-hl__text gets the effect.
		$prefix = trim( wp_strip_all_tags( (string) sc_get( 'prefix', $atts, '' ) ) );
		$suffix = trim( wp_strip_all_tags( (string) sc_get( 'suffix', $atts, '' ) ) );

		ob_start();
		echo '<' . $tag . ' ' . fw_attr_to_html( $attr ) . '>';
		if ( $prefix !== '' ) { echo '<span class="fw-hl__prefix">' . esc_html( $prefix ) . '</span> '; }
		echo '<span class="fw-hl__text">' . $inner . '</span>'; // phpcs:ignore
		if ( $suffix !== '' ) { echo ' <span class="fw-hl__suffix">' . esc_html( $suffix ) . '</span>'; }
		echo '</' . $tag . '>';
		return ob_get_clean();
	}
}

echo sc_hl_render( $atts );
