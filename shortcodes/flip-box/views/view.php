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

if ( ! function_exists( 'sc_fb_icon' ) ) {
	function sc_fb_icon( $picked ) {
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

if ( ! function_exists( 'sc_fb_render' ) ) {
	function sc_fb_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$design   = sc_get( 'design', $atts, 'solid' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'solid'; }

		$front_title = trim( (string) sc_get( 'front_title', $atts, '' ) );
		$front_text  = trim( (string) sc_get( 'front_text', $atts, '' ) );
		$back_title  = trim( (string) sc_get( 'back_title', $atts, '' ) );
		$back_text   = trim( (string) sc_get( 'back_text', $atts, '' ) );
		$icon        = sc_fb_icon( sc_get( 'front_icon', $atts, null ) );

		if ( $front_title === '' && $front_text === '' && $back_title === '' && $back_text === '' && $icon === '' ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-fb__empty">' . esc_html__( 'Add front / back content to the Flip Box.', 'fw' ) . '</div>';
			}
			return '';
		}

		$dir     = sc_get( 'flip_direction', $atts, 'left' );
		if ( ! in_array( $dir, array( 'left', 'right', 'up', 'down' ), true ) ) { $dir = 'left'; }
		$trigger = sc_get( 'trigger', $atts, 'hover' ) === 'click' ? 'click' : 'hover';
		$height  = (int) sc_get( 'height', $atts, 300 );
		$height  = max( 120, min( 900, $height ) );
		$rounded = sc_get( 'rounded', $atts, 'rounded' );

		$front_img = sc_get( 'front_image', $atts, array() );
		$front_img_url = ( is_array( $front_img ) && ! empty( $front_img['url'] ) ) ? $front_img['url'] : '';

		/* Button. */
		$btn_lbl = trim( (string) sc_get( 'button_label', $atts, '' ) );
		$btn_url = trim( (string) sc_get( 'button_url', $atts, '' ) );
		$btn_tgt = sc_get( 'button_target', $atts, '_self' ) === '_blank' ? '_blank' : '_self';

		/* Color CSS vars (custom hex). */
		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$style_var  = '--fb-h:' . $height . 'px;';
		$style_var .= $var( 'front_bg', '--fb-front-bg' );
		$style_var .= $var( 'front_color', '--fb-front-color' );
		$style_var .= $var( 'back_bg', '--fb-back-bg' );
		$style_var .= $var( 'back_color', '--fb-back-color' );
		$style_var .= $var( 'accent_color', '--fb-accent' );
		if ( $design === 'image' && $front_img_url !== '' ) {
			$style_var .= "--fb-front-image:url('" . esc_url( $front_img_url ) . "');";
		}

		$classes = array(
			'fw-fb',
			'fw-fb--design-' . sanitize_html_class( $design ),
			'fw-fb--dir-' . sanitize_html_class( $dir ),
			'fw-fb--' . $trigger,
			sanitize_html_class( $rounded ),
		);

		$atts['base_class']       = 'flip-box';
		$atts['unique_id_prefix'] = 'fb-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;

		// Click trigger needs to be keyboard-operable.
		$kb = $trigger === 'click' ? ' tabindex="0" role="button" aria-pressed="false"' : '';

		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . $kb . '>';
		echo '<div class="fw-fb__inner">';

		/* Front. */
		echo '<div class="fw-fb__face fw-fb__front">';
		if ( $icon !== '' ) { echo '<span class="fw-fb__icon" aria-hidden="true">' . $icon . '</span>'; } // phpcs:ignore
		if ( $front_title !== '' ) { echo '<h3 class="fw-fb__title">' . esc_html( $front_title ) . '</h3>'; }
		if ( $front_text !== '' ) { echo '<div class="fw-fb__text">' . wp_kses_post( wpautop( $front_text ) ) . '</div>'; }
		echo '</div>';

		/* Back. */
		echo '<div class="fw-fb__face fw-fb__back">';
		if ( $back_title !== '' ) { echo '<h3 class="fw-fb__title">' . esc_html( $back_title ) . '</h3>'; }
		if ( $back_text !== '' ) { echo '<div class="fw-fb__text">' . wp_kses_post( wpautop( $back_text ) ) . '</div>'; }
		if ( $btn_lbl !== '' ) {
			$href = $btn_url !== '' ? esc_url( $btn_url ) : '#';
			echo '<a class="fw-fb__btn" href="' . $href . '"' . ( $btn_tgt === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . esc_html( $btn_lbl ) . '</a>';
		}
		echo '</div>';

		echo '</div></div>';
		return ob_get_clean();
	}
}

echo sc_fb_render( $atts );
