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

if ( ! function_exists( 'sc_hs_icon' ) ) {
	function sc_hs_icon( $picked ) {
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

if ( ! function_exists( 'sc_hs_render' ) ) {
	function sc_hs_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$design   = sc_get( 'design', $atts, 'pulse' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'pulse'; }

		$image = sc_get( 'image', $atts, array() );
		$img_url = ( is_array( $image ) && ! empty( $image['url'] ) ) ? $image['url'] : '';
		$img_id  = ( is_array( $image ) && ! empty( $image['attachment_id'] ) ) ? (int) $image['attachment_id'] : 0;
		if ( $img_id ) {
			$full = wp_get_attachment_image_url( $img_id, 'large' );
			if ( $full ) { $img_url = $full; }
		}
		$hotspots = sc_get( 'hotspots', $atts, array() );
		if ( ! is_array( $hotspots ) ) { $hotspots = array(); }

		if ( $img_url === '' ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-hs__empty">' . esc_html__( 'Add an image.', 'fw' ) . '</div>';
			}
			return '';
		}

		$trigger = sc_get( 'trigger', $atts, 'hover' ) === 'click' ? 'click' : 'hover';
		$psize   = sc_get( 'pin_size', $atts, 'md' );
		$rounded = sc_get( 'rounded', $atts, 'rounded' );

		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$style_var  = $var( 'pin_color', '--hs-pin' );
		$style_var .= $var( 'pop_bg', '--hs-pop-bg' );
		$style_var .= $var( 'pop_color', '--hs-pop-color' );
		$style_var .= $var( 'accent_color', '--hs-accent' );

		$classes = array(
			'fw-hs',
			'fw-hs--design-' . sanitize_html_class( $design ),
			'fw-hs--' . $trigger,
			'fw-hs--pin-' . sanitize_html_class( $psize ),
		);

		$atts['base_class']       = 'image-hotspots';
		$atts['unique_id_prefix'] = 'hs-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $style_var !== '' ) {
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;
		}

		$alt = $img_id ? (string) get_post_meta( $img_id, '_wp_attachment_image_alt', true ) : '';

		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . '>';
		echo '<div class="fw-hs__stage ' . esc_attr( sanitize_html_class( $rounded ) ) . '">';
		echo '<img class="fw-hs__img" src="' . esc_url( $img_url ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy" decoding="async" />';

		$i = 0;
		foreach ( $hotspots as $h ) {
			$i++;
			$x = isset( $h['x'] ) ? max( 0, min( 100, (float) $h['x'] ) ) : 50;
			$y = isset( $h['y'] ) ? max( 0, min( 100, (float) $h['y'] ) ) : 50;
			$ht = isset( $h['title'] ) ? trim( (string) $h['title'] ) : '';
			$hx = isset( $h['text'] ) ? trim( (string) $h['text'] ) : '';
			$ic = sc_hs_icon( isset( $h['icon'] ) ? $h['icon'] : null );
			$ll = isset( $h['link_label'] ) ? trim( (string) $h['link_label'] ) : '';
			$lu = isset( $h['link_url'] ) ? trim( (string) $h['link_url'] ) : '';
			$lt = ( isset( $h['link_target'] ) && $h['link_target'] === '_blank' ) ? '_blank' : '_self';

			$pin_inner = '';
			if ( $design === 'numbered' ) {
				$pin_inner = '<span class="fw-hs__num">' . (int) $i . '</span>';
			} elseif ( $design === 'icon' ) {
				$pin_inner = '<span class="fw-hs__ic">' . ( $ic !== '' ? $ic : '+' ) . '</span>';
			}

			$aria = $ht !== '' ? $ht : sprintf( __( 'Hotspot %d', 'fw' ), $i );

			echo '<div class="fw-hs__point" style="left:' . esc_attr( $x ) . '%;top:' . esc_attr( $y ) . '%;">';
			echo '<button type="button" class="fw-hs__pin" aria-label="' . esc_attr( $aria ) . '">' . $pin_inner . '<span class="fw-hs__ring" aria-hidden="true"></span></button>'; // phpcs:ignore

			if ( $ht !== '' || $hx !== '' || $ll !== '' ) {
				echo '<div class="fw-hs__pop" role="tooltip">';
				if ( $ht !== '' ) { echo '<span class="fw-hs__pop-title">' . esc_html( $ht ) . '</span>'; }
				if ( $hx !== '' ) { echo '<span class="fw-hs__pop-text">' . wp_kses_post( $hx ) . '</span>'; }
				if ( $ll !== '' ) {
					$href = $lu !== '' ? esc_url( $lu ) : '#';
					echo '<a class="fw-hs__pop-link" href="' . $href . '"' . ( $lt === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . esc_html( $ll ) . ' <span aria-hidden="true">&rarr;</span></a>';
				}
				echo '<span class="fw-hs__pop-arrow" aria-hidden="true"></span>';
				echo '</div>';
			}
			echo '</div>';
		}

		echo '</div></div>';
		return ob_get_clean();
	}
}

echo sc_hs_render( $atts );
