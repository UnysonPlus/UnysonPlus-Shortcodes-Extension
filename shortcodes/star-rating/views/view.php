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

if ( ! function_exists( 'sc_sr_symbol' ) ) {
	function sc_sr_symbol( $design ) {
		switch ( $design ) {
			case 'heart':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7.5-4.6-10-8.7C.6 9.7 2.3 6 5.7 6 8 6 9.4 7.4 12 9c2.6-1.6 4-3 6.3-3 3.4 0 5.1 3.7 3.7 6.3C19.5 16.4 12 21 12 21z"/></svg>';
			case 'circle':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/></svg>';
			case 'star':
			default:
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 .9l3.4 6.9 7.6 1.1-5.5 5.4 1.3 7.6L12 18.9l-6.8 3.6 1.3-7.6L1 9.9l7.6-1.1z"/></svg>';
		}
	}
}

if ( ! function_exists( 'sc_sr_render' ) ) {
	function sc_sr_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$design   = sc_get( 'design', $atts, 'star' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'star'; }

		$max    = (int) sc_get( 'max', $atts, 5 );
		$max    = $max === 10 ? 10 : 5;
		$rating = (float) sc_get( 'rating', $atts, 0 );
		$rating = max( 0, min( $max, $rating ) );

		$label   = trim( (string) sc_get( 'label', $atts, '' ) );
		$show_v  = sc_get( 'show_value', $atts, 'yes' ) === 'yes';
		$count   = trim( (string) sc_get( 'count_text', $atts, '' ) );
		$size    = sc_get( 'size', $atts, 'md' );
		$align   = sc_get( 'align', $atts, 'left' );
		$align_cls = function_exists( 'sc_alignment_class' ) ? sc_alignment_class( $align ) : '';

		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$style_var  = $var( 'fill_color', '--sr-fill' );
		$style_var .= $var( 'empty_color', '--sr-empty' );
		$style_var .= $var( 'text_color', '--sr-text' );

		$classes = array(
			'fw-sr',
			'fw-sr--design-' . sanitize_html_class( $design ),
			'fw-sr--size-' . sanitize_html_class( $size ),
		);
		if ( $align_cls ) { $classes[] = $align_cls; }

		$atts['base_class']       = 'star-rating';
		$atts['unique_id_prefix'] = 'sr-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $style_var !== '' ) {
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;
		}

		$val_str = rtrim( rtrim( number_format( $rating, 1, '.', '' ), '0' ), '.' );
		$aria    = sprintf( __( 'Rated %1$s out of %2$d', 'fw' ), $val_str, $max );

		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . ' role="img" aria-label="' . esc_attr( $aria ) . '">';
		if ( $label !== '' ) { echo '<span class="fw-sr__label">' . esc_html( $label ) . '</span>'; }

		if ( $design === 'bar' ) {
			$pct = $max > 0 ? ( $rating / $max ) * 100 : 0;
			echo '<span class="fw-sr__symbols"><span class="fw-sr__bar" style="width:' . esc_attr( round( $pct, 2 ) ) . '%;"></span></span>';
		} else {
			$svg = sc_sr_symbol( $design );
			echo '<span class="fw-sr__symbols">';
			for ( $i = 1; $i <= $max; $i++ ) {
				$fill = max( 0, min( 1, $rating - ( $i - 1 ) ) ) * 100;
				echo '<span class="fw-sr__sym">'
					. '<span class="fw-sr__sym-empty">' . $svg . '</span>'
					. '<span class="fw-sr__sym-fill" style="width:' . esc_attr( round( $fill, 2 ) ) . '%;">' . $svg . '</span>'
					. '</span>';
			}
			echo '</span>';
		}

		if ( $show_v ) { echo '<span class="fw-sr__value">' . esc_html( $val_str . '/' . $max ) . '</span>'; }
		if ( $count !== '' ) { echo '<span class="fw-sr__count">' . esc_html( $count ) . '</span>'; }

		echo '</div>';

		// Optional AggregateRating JSON-LD (machine-readable rating). A number found in
		// the Count Text (e.g. "based on 220 reviews") becomes the ratingCount.
		if ( sc_get( 'rating_schema', $atts, 'no' ) === 'yes' && $rating > 0 ) {
			$ld = array(
				'@context'    => 'https://schema.org',
				'@type'       => 'AggregateRating',
				'ratingValue' => (float) $val_str,
				'bestRating'  => $max,
				'worstRating' => 0,
			);
			if ( $count !== '' && preg_match( '/\d[\d,]*/', $count, $m ) ) {
				$ld['ratingCount'] = (int) str_replace( ',', '', $m[0] );
			}
			echo '<script type="application/ld+json">' . wp_json_encode( $ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
		}

		return ob_get_clean();
	}
}

echo sc_sr_render( $atts );
