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

if ( ! function_exists( 'sc_bq_render' ) ) {
	function sc_bq_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$design   = sc_get( 'design', $atts, 'classic' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'classic'; }

		$quote = trim( (string) sc_get( 'quote', $atts, '' ) );
		if ( $quote === '' ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-bq__empty">' . esc_html__( 'Add a quote.', 'fw' ) . '</div>';
			}
			return '';
		}

		$author = trim( (string) sc_get( 'author', $atts, '' ) );
		$role   = trim( (string) sc_get( 'role', $atts, '' ) );
		$src     = trim( (string) sc_get( 'source_url', $atts, '' ) );
		$mark   = sc_get( 'show_mark', $atts, 'yes' ) === 'yes';
		$align  = sc_get( 'align', $atts, 'left' );
		$align_cls = function_exists( 'sc_alignment_class' ) ? sc_alignment_class( $align ) : '';
		$max_w  = preg_replace( '/[^0-9a-zA-Z.%]/', '', (string) sc_get( 'max_width', $atts, '' ) );

		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( ! is_array( $raw ) ) { return ''; }
			// Custom hex wins (matches the compact picker's mutual-exclusion UI).
			if ( ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			// Palette swatch: resolve the preset slug to its hex so it drives the CSS
			// variable (border / mark / background). The bg-*/text-* utility class the
			// picker stores can't recolor a border, so the swatch otherwise did nothing.
			if ( ! empty( $raw['predefined'] ) && function_exists( 'unysonplus_color_preset_slug_map' ) ) {
				$slug = preg_replace( '/^(?:bg|text)-/', '', (string) $raw['predefined'] );
				$map  = unysonplus_color_preset_slug_map();
				if ( isset( $map[ $slug ] ) && $map[ $slug ] !== '' ) {
					return $name . ':' . $map[ $slug ] . ';';
				}
			}
			return '';
		};
		$style_var  = $var( 'quote_color', '--bq-quote' );
		$style_var .= $var( 'accent_color', '--bq-accent' );
		$style_var .= $var( 'author_color', '--bq-author' );
		$style_var .= $var( 'bg_color', '--bq-bg' );
		if ( $max_w !== '' ) { $style_var .= 'max-width:' . $max_w . ';'; }

		$classes = array( 'fw-bq', 'fw-bq--design-' . sanitize_html_class( $design ) );
		if ( $align_cls ) { $classes[] = $align_cls; }
		if ( $mark )      { $classes[] = 'fw-bq--mark'; }

		$atts['base_class']       = 'blockquote';
		$atts['unique_id_prefix'] = 'bq-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $style_var !== '' ) {
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;
		}

		$allowed = array( 'strong' => array(), 'b' => array(), 'em' => array(), 'i' => array(), 'br' => array(), 'a' => array( 'href' => true, 'title' => true, 'target' => true, 'rel' => true ) );

		ob_start();
		echo '<figure ' . fw_attr_to_html( $attr ) . '>';
		echo '<blockquote class="fw-bq__quote">';
		if ( $mark ) { echo '<span class="fw-bq__mark" aria-hidden="true">&ldquo;</span>'; }
		echo '<p class="fw-bq__text">' . nl2br( wp_kses( $quote, $allowed ) ) . '</p>';
		echo '</blockquote>';

		if ( $author !== '' || $role !== '' ) {
			echo '<figcaption class="fw-bq__cite">';
			$name_html = esc_html( $author );
			if ( $author !== '' && $src !== '' ) {
				$name_html = '<a href="' . esc_url( $src ) . '" rel="noopener" target="_blank">' . esc_html( $author ) . '</a>';
			}
			if ( $author !== '' ) { echo '<span class="fw-bq__author">' . $name_html . '</span>'; } // phpcs:ignore
			if ( $role !== '' ) { echo '<span class="fw-bq__role">' . esc_html( $role ) . '</span>'; }
			echo '</figcaption>';
		}

		echo '</figure>';
		return ob_get_clean();
	}
}

echo sc_bq_render( $atts );
