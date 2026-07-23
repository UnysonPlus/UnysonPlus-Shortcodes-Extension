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

if ( ! function_exists( 'sc_tl_icon' ) ) {
	function sc_tl_icon( $picked ) {
		// Central icon renderer (single source of truth). aria_hidden => false
		// preserves this element's original decorative-icon markup.
		if ( function_exists( 'sc_icon_render' ) ) {
			return sc_icon_render( $picked, array( 'aria_hidden' => false ) );
		}
		if ( is_array( $picked ) && isset( $picked['type'] ) ) {
			if ( $picked['type'] === 'icon-font' && ! empty( $picked['icon-class'] ) ) {
				return '<i class="' . esc_attr( $picked['icon-class'] ) . '"></i>';
			}
			if ( $picked['type'] === 'custom-upload' && ! empty( $picked['url'] ) ) {
				return '<img src="' . esc_url( $picked['url'] ) . '" alt="" loading="lazy" />';
			}
		}
		return '';

	}
}

if ( ! function_exists( 'sc_tl_render' ) ) {
	function sc_tl_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$design   = sc_get( 'design', $atts, 'alternating' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'alternating'; }

		$items = sc_get( 'items', $atts, array() );
		if ( ! is_array( $items ) || empty( $items ) ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-tl__empty">' . esc_html__( 'Add at least one milestone.', 'fw' ) . '</div>';
			}
			return '';
		}

		$marker = sc_get( 'marker', $atts, 'dot' );
		$card   = sc_get( 'card_style', $atts, 'card' );

		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$style_var  = $var( 'accent_color', '--tl-accent' );
		$style_var .= $var( 'line_color', '--tl-line' );
		$style_var .= $var( 'card_bg', '--tl-card-bg' );
		$style_var .= $var( 'date_color', '--tl-date' );
		$style_var .= $var( 'title_color', '--tl-title' );
		$style_var .= $var( 'text_color', '--tl-text' );

		$classes = array(
			'fw-tl',
			'fw-tl--design-' . sanitize_html_class( $design ),
			'fw-tl--marker-' . sanitize_html_class( $marker ),
			'fw-tl--card-' . sanitize_html_class( $card ),
		);

		$atts['base_class']       = 'timeline';
		$atts['unique_id_prefix'] = 'tl-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;

		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . '>';
		echo '<div class="fw-tl__track">';

		$i = 0;
		foreach ( $items as $it ) {
			$i++;
			$date  = isset( $it['date'] ) ? trim( (string) $it['date'] ) : '';
			$itl   = isset( $it['title'] ) ? trim( (string) $it['title'] ) : '';
			$text  = isset( $it['text'] ) ? trim( (string) $it['text'] ) : '';
			$icon  = sc_tl_icon( isset( $it['icon'] ) ? $it['icon'] : null );
			$img   = ( isset( $it['image'] ) && is_array( $it['image'] ) && ! empty( $it['image']['url'] ) ) ? $it['image']['url'] : '';
			$llbl  = isset( $it['link_label'] ) ? trim( (string) $it['link_label'] ) : '';
			$lurl  = isset( $it['link_url'] ) ? trim( (string) $it['link_url'] ) : '';
			$ltgt  = ( isset( $it['link_target'] ) && $it['link_target'] === '_blank' ) ? '_blank' : '_self';

			echo '<div class="fw-tl__item">';

			echo '<div class="fw-tl__marker" aria-hidden="true">';
			if ( $marker === 'icon' && $icon !== '' ) {
				echo '<span class="fw-tl__marker-icon">' . $icon . '</span>'; // phpcs:ignore
			} elseif ( $marker === 'number' ) {
				echo '<span class="fw-tl__marker-num">' . (int) $i . '</span>';
			} else {
				echo '<span class="fw-tl__marker-dot"></span>';
			}
			echo '</div>';

			echo '<div class="fw-tl__content"><div class="fw-tl__card">';
			if ( $img !== '' ) {
				echo '<div class="fw-tl__image"><img src="' . esc_url( $img ) . '" alt="' . esc_attr( $itl ) . '" loading="lazy" decoding="async" /></div>';
			}
			if ( $date !== '' ) { echo '<div class="fw-tl__date">' . esc_html( $date ) . '</div>'; }
			if ( $itl !== '' ) { echo '<h4 class="fw-tl__title">' . esc_html( $itl ) . '</h4>'; }
			if ( $text !== '' ) { echo '<div class="fw-tl__text">' . wp_kses_post( wpautop( $text ) ) . '</div>'; }
			if ( $llbl !== '' ) {
				$href = $lurl !== '' ? esc_url( $lurl ) : '#';
				echo '<a class="fw-tl__link" href="' . $href . '"' . ( $ltgt === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . esc_html( $llbl ) . ' <span aria-hidden="true">&rarr;</span></a>';
			}
			echo '</div></div>';

			echo '</div>'; // item
		}

		echo '</div></div>';

		// Optional HowTo JSON-LD — each milestone becomes a step.
		if ( sc_get( 'howto_schema', $atts, 'no' ) === 'yes' ) {
			$steps = array();
			$n     = 0;
			foreach ( $items as $it ) {
				$t = isset( $it['title'] ) ? trim( wp_strip_all_tags( (string) $it['title'] ) ) : '';
				$x = isset( $it['text'] ) ? trim( wp_strip_all_tags( strip_shortcodes( (string) $it['text'] ) ) ) : '';
				if ( $t === '' && $x === '' ) { continue; }
				$n++;
				$step = array( '@type' => 'HowToStep', 'position' => $n );
				if ( $t !== '' ) { $step['name'] = $t; }
				$step['text'] = $x !== '' ? preg_replace( '/\s+/u', ' ', $x ) : $t;
				$steps[] = $step;
			}
			if ( ! empty( $steps ) ) {
				$name = function_exists( 'get_the_title' ) ? wp_strip_all_tags( get_the_title() ) : '';
				if ( $name === '' ) { $name = __( 'Steps', 'fw' ); }
				$ld = array( '@context' => 'https://schema.org', '@type' => 'HowTo', 'name' => $name, 'step' => $steps );
				echo '<script type="application/ld+json">' . wp_json_encode( $ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
			}
		}

		return ob_get_clean();
	}
}

echo sc_tl_render( $atts );
