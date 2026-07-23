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

if ( ! function_exists( 'sc_ah_render' ) ) {
	function sc_ah_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$anim     = sc_get( 'anim', $atts, 'typewriter' );
		if ( ! isset( $registry[ $anim ] ) ) { $anim = 'typewriter'; }

		$before = trim( (string) sc_get( 'before_text', $atts, '' ) );
		$after  = trim( (string) sc_get( 'after_text', $atts, '' ) );
		$raw    = (string) sc_get( 'words', $atts, '' );
		$words  = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ), 'strlen' ) );

		if ( empty( $words ) && $before === '' && $after === '' ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-ah__empty">' . esc_html__( 'Add some rotating words.', 'fw' ) . '</div>';
			}
			return '';
		}

		$tag = sc_get( 'tag', $atts, 'h2' );
		if ( ! in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div' ), true ) ) { $tag = 'h2'; }
		$speed = sc_get( 'speed', $atts, 'normal' );
		$hl    = sc_get( 'highlight', $atts, 'color' );
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
		$style_var  = $var( 'text_color', '--ah-text' );
		$style_var .= $var( 'accent_color', '--ah-accent' );

		$classes = array(
			'fw-ah',
			'fw-ah--anim-' . sanitize_html_class( $anim ),
			'fw-ah--speed-' . sanitize_html_class( $speed ),
			'fw-ah--hl-' . sanitize_html_class( $hl ),
		);
		if ( $align_cls ) { $classes[] = $align_cls; }

		$atts['base_class']       = 'animated-heading';
		$atts['unique_id_prefix'] = 'ah-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $style_var !== '' ) {
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;
		}
		// The wrapper IS the heading tag.
		$attr_html = fw_attr_to_html( $attr );

		$first = isset( $words[0] ) ? $words[0] : '';

		ob_start();
		echo '<' . $tag . ' ' . $attr_html . ' data-ah-words="' . esc_attr( wp_json_encode( $words ) ) . '">';
		if ( $before !== '' ) { echo '<span class="fw-ah__static">' . esc_html( $before ) . ' </span>'; }
		echo '<span class="fw-ah__rotate"><span class="fw-ah__word">' . esc_html( $first ) . '</span><span class="fw-ah__caret" aria-hidden="true"></span></span>';
		// Server-render the remaining words (visually hidden) so raw-HTML readers / AI
		// agents see the full rotating set, not just the first word. JS rotates only
		// .fw-ah__word (from data-ah-words), so this span is left untouched.
		$rest = array_slice( $words, 1 );
		if ( ! empty( $rest ) ) {
			echo '<span class="screen-reader-text">' . esc_html( implode( ', ', $rest ) ) . '</span>';
		}
		if ( $after !== '' ) { echo '<span class="fw-ah__static"> ' . esc_html( $after ) . '</span>'; }
		echo '</' . $tag . '>';
		return ob_get_clean();
	}
}

echo sc_ah_render( $atts );
