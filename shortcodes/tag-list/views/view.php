<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** @var array $atts */

if ( ! function_exists( 'sc_tl_render' ) ) {
	function sc_tl_render( $atts ) {
		$get = function ( $k, $d = '' ) use ( $atts ) {
			if ( function_exists( 'fw_akg' ) ) { $v = fw_akg( $k, $atts, null ); if ( $v !== null ) { return $v; } }
			return isset( $atts[ $k ] ) ? $atts[ $k ] : $d;
		};

		// --- parse items: one per line; "Label | URL" makes the tag a link ---
		// Normalize <br> → newline first: wpautop / nl2br on the page-builder textarea can turn the
		// real line breaks into "<br>\n", which would otherwise get glued onto every label.
		$raw   = preg_replace( '#<br\s*/?\s*>#i', "\n", (string) $get( 'items', '' ) );
		$items = array();
		foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
			$line = trim( $line );
			if ( $line === '' ) { continue; }
			$url = '';
			if ( strpos( $line, '|' ) !== false ) {
				$parts = explode( '|', $line, 2 );
				$label = trim( $parts[0] );
				$url   = trim( $parts[1] );
			} else {
				$label = $line;
			}
			if ( $label === '' ) { continue; }
			$items[] = array( 'label' => $label, 'url' => $url );
		}
		if ( empty( $items ) ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-taglist__empty">' . esc_html__( 'Add at least one item (one per line).', 'fw' ) . '</div>';
			}
			return '';
		}

		// --- design + modifiers (whitelisted) ---
		$design = in_array( $get( 'design', 'soft' ), array( 'soft', 'outline', 'solid', 'subtle', 'line' ), true ) ? $get( 'design', 'soft' ) : 'soft';
		$shape  = in_array( $get( 'shape', 'pill' ), array( 'pill', 'rounded', 'square' ), true ) ? $get( 'shape', 'pill' ) : 'pill';
		$size   = in_array( $get( 'size', 'md' ), array( 'sm', 'md', 'lg' ), true ) ? $get( 'size', 'md' ) : 'md';
		$align  = in_array( $get( 'align', 'start' ), array( 'start', 'center', 'end' ), true ) ? $get( 'align', 'start' ) : 'start';
		$gap    = in_array( $get( 'gap', 'sm' ), array( 'sm', 'md', 'lg' ), true ) ? $get( 'gap', 'sm' ) : 'sm';
		$marker = $get( 'marker', 'none' ) === 'dot';
		$hover  = $get( 'hover', 'no' ) === 'yes';
		$is_line = ( $design === 'line' );

		// --- colour → one --tl-color variable: a Color Preset slug → var(--color-slug), or custom hex ---
		$tl_color = '';
		$tc = $get( 'tag_color', null );
		if ( is_array( $tc ) && function_exists( 'sc_normalize_color_value' ) ) {
			$norm = sc_normalize_color_value( $tc, 'text' );
			if ( ! empty( $norm['style'] ) && preg_match( '/(#[0-9a-fA-F]{3,8}|(?:rgb|hsl)a?\([^)]*\))/', $norm['style'], $m ) ) {
				$tl_color = $m[1];
			} elseif ( ! empty( $norm['class'] ) ) {
				$slug = sanitize_html_class( preg_replace( '/^(?:text|bg|color)-/', '', $norm['class'] ) );
				if ( $slug !== '' ) { $tl_color = 'var(--color-' . $slug . ')'; }
			}
		}
		unset( $atts['tag_color'] ); // resolved here — keep it off the wrapper class filter

		// --- wrapper ---
		$classes = array( 'fw-taglist', 'tl-' . $design, 'tl-shape-' . $shape, 'tl-' . $size, 'tl-align-' . $align, 'tl-gap-' . $gap );
		if ( $marker && ! $is_line ) { $classes[] = 'tl-marker'; }
		if ( $hover )                { $classes[] = 'tl-hover'; }

		$atts['base_class']       = 'tag-list';
		$atts['unique_id_prefix'] = 'tl-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $tl_color !== '' ) {
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], '; ' ) . ';' : '' ) . '--tl-color:' . $tl_color . ';';
		}

		// --- markup. External links (a different host than this site) open in a new tab. ---
		$home_host = function_exists( 'home_url' ) ? parse_url( home_url(), PHP_URL_HOST ) : '';
		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . '>';
		foreach ( $items as $it ) {
			$inner = ( $marker && ! $is_line ? '<span class="fw-tag__dot" aria-hidden="true"></span>' : '' )
				. '<span class="fw-tag__label">' . esc_html( $it['label'] ) . '</span>';
			if ( $it['url'] !== '' ) {
				$is_ext = false;
				if ( preg_match( '#^https?://#i', $it['url'] ) ) {
					$h = parse_url( $it['url'], PHP_URL_HOST );
					$is_ext = ( $h && $home_host && strcasecmp( $h, $home_host ) !== 0 );
				}
				$target = $is_ext ? ' target="_blank" rel="noopener noreferrer"' : '';
				echo '<a class="fw-tag" href="' . esc_url( $it['url'] ) . '"' . $target . '>' . $inner . '</a>';
			} else {
				echo '<span class="fw-tag">' . $inner . '</span>';
			}
		}
		echo '</div>';
		return ob_get_clean();
	}
}

echo sc_tl_render( $atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
