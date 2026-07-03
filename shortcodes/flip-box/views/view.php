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
		// Design comes from the multi-picker (design_settings/skin); fall back to the legacy
		// scalar `design` att so boxes saved before the popover conversion still render.
		$ds     = sc_get( 'design_settings', $atts, array() );
		$design = ( is_array( $ds ) && ! empty( $ds['skin'] ) ) ? (string) $ds['skin'] : (string) sc_get( 'design', $atts, 'solid' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'solid'; }

		$front_title = trim( (string) sc_get( 'front_title', $atts, '' ) );
		$front_text  = trim( (string) sc_get( 'front_text', $atts, '' ) );
		$back_title  = trim( (string) sc_get( 'back_title', $atts, '' ) );
		$back_text   = trim( (string) sc_get( 'back_text', $atts, '' ) );
		$icon        = sc_fb_icon( sc_get( 'front_icon', $atts, null ) );
		$back_icon   = sc_fb_icon( sc_get( 'back_icon', $atts, null ) );

		$title_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p' );
		$front_tag  = (string) sc_get( 'front_title_tag', $atts, 'h3' );
		$back_tag   = (string) sc_get( 'back_title_tag', $atts, 'h3' );
		if ( ! in_array( $front_tag, $title_tags, true ) ) { $front_tag = 'h3'; }
		if ( ! in_array( $back_tag, $title_tags, true ) ) { $back_tag = 'h3'; }

		if ( $front_title === '' && $front_text === '' && $back_title === '' && $back_text === '' && $icon === '' && $back_icon === '' ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-fb__empty">' . esc_html__( 'Add front / back content to the Flip Box.', 'fw' ) . '</div>';
			}
			return '';
		}

		$dir = sc_get( 'flip_direction', $atts, 'left' );
		if ( is_array( $dir ) ) { $dir = isset( $dir['fx'] ) ? (string) $dir['fx'] : 'left'; } // popover passthrough safety
		$flip_fx   = array( 'left', 'right', 'up', 'down', 'diagonal' );
		$reveal_fx = array( 'fade', 'zoom', 'slide-up', 'slide-down', 'slide-left', 'slide-right' );
		if ( ! in_array( $dir, array_merge( $flip_fx, $reveal_fx ), true ) ) { $dir = 'left'; }
		$fx_mode = in_array( $dir, $flip_fx, true ) ? 'flip' : 'reveal';
		$trigger = sc_get( 'trigger', $atts, 'hover' );
		if ( ! in_array( $trigger, array( 'hover', 'click', 'both' ), true ) ) { $trigger = 'hover'; }
		$parallax = sc_get( 'parallax', $atts, 'no' ) === 'yes';
		$height  = (int) sc_get( 'height', $atts, 300 );
		$height  = max( 120, min( 900, $height ) );
		$rounded = sc_get( 'rounded', $atts, 'rounded' );
		if ( is_array( $rounded ) ) { $rounded = isset( $rounded['r'] ) ? (string) $rounded['r'] : 'rounded'; } // popover passthrough
		$radius_map = array( 'rounded-0' => '0', 'rounded-sm' => '.375rem', 'rounded' => '.625rem', 'rounded-lg' => '1rem', 'rounded-xl' => '1.75rem' );
		$radius = isset( $radius_map[ $rounded ] ) ? $radius_map[ $rounded ] : '.625rem';

		// Flip speed + easing.
		$speed = (int) sc_get( 'flip_speed', $atts, 600 );
		$speed = max( 100, min( 2000, $speed ) );
		$ease_map = array(
			'smooth'      => 'cubic-bezier(.2,.7,.2,1)',
			'ease'        => 'ease',
			'ease-in-out' => 'ease-in-out',
			'spring'      => 'cubic-bezier(.68,-0.55,.27,1.55)',
			'linear'      => 'linear',
		);
		$ease_key = (string) sc_get( 'flip_easing', $atts, 'smooth' );
		$ease     = isset( $ease_map[ $ease_key ] ) ? $ease_map[ $ease_key ] : $ease_map['smooth'];

		// Button presets (shared by the front flip button + the back link button).
		$bstyle = (string) sc_get( 'button_style', $atts, '' );
		$bsize  = (string) sc_get( 'button_size', $atts, '' );
		$fb_btn_class = function ( $extra ) use ( $bstyle, $bsize ) {
			$c = array_merge( array( 'btn', 'fw-fb__btn' ), $extra );
			if ( $bstyle !== '' ) { $c[] = sanitize_html_class( $bstyle ); }
			if ( $bsize !== '' ) { $c[] = sanitize_html_class( $bsize ); }
			return esc_attr( implode( ' ', $c ) );
		};

		$front_img = sc_get( 'front_image', $atts, array() );
		$front_img_url = ( is_array( $front_img ) && ! empty( $front_img['url'] ) ) ? $front_img['url'] : '';
		$back_img = sc_get( 'back_image', $atts, array() );
		$back_img_url = ( is_array( $back_img ) && ! empty( $back_img['url'] ) ) ? $back_img['url'] : '';

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
		$style_var  = '--fb-h:' . $height . 'px;--fb-speed:' . $speed . 'ms;--fb-ease:' . $ease . ';--fb-radius:' . $radius . ';';
		$style_var .= $var( 'front_bg', '--fb-front-bg' );
		$style_var .= $var( 'front_color', '--fb-front-color' );
		$style_var .= $var( 'back_bg', '--fb-back-bg' );
		$style_var .= $var( 'back_color', '--fb-back-color' );
		// A Front/Back Background Image now shows on ANY design (not just "Image front").
		if ( $front_img_url !== '' ) {
			$style_var .= "--fb-front-image:url('" . esc_url( $front_img_url ) . "');";
		}
		if ( $back_img_url !== '' ) {
			$style_var .= "--fb-back-image:url('" . esc_url( $back_img_url ) . "');";
		}

		$classes = array(
			'fw-fb',
			'fw-fb--design-' . sanitize_html_class( $design ),
			'fw-fb--dir-' . sanitize_html_class( $dir ),
			'fw-fb--mode-' . $fx_mode,
			'fw-fb--' . $trigger,
		);
		if ( $front_img_url !== '' ) {
			$classes[] = 'fw-fb--has-front-image';
		}
		if ( $back_img_url !== '' ) {
			$classes[] = 'fw-fb--has-back-image';
		}
		if ( $parallax ) {
			$classes[] = 'fw-fb--parallax';
		}

		$atts['base_class']       = 'flip-box';
		$atts['unique_id_prefix'] = 'fb-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;

		// Click / hover+click triggers need to be keyboard-operable.
		$kb = ( $trigger === 'click' || $trigger === 'both' ) ? ' tabindex="0" role="button" aria-pressed="false"' : '';

		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . $kb . '>';
		echo '<div class="fw-fb__inner">';

		/* Front (content wrapped so Parallax can float it forward in 3D). */
		echo '<div class="fw-fb__face fw-fb__front"><div class="fw-fb__content">';
		if ( $icon !== '' ) { echo '<span class="fw-fb__icon" aria-hidden="true">' . $icon . '</span>'; } // phpcs:ignore
		if ( $front_title !== '' ) { echo '<' . $front_tag . ' class="fw-fb__title">' . esc_html( $front_title ) . '</' . $front_tag . '>'; }
		if ( $front_text !== '' ) { echo '<div class="fw-fb__text">' . wp_kses_post( wpautop( $front_text ) ) . '</div>'; }
		$front_btn = trim( (string) sc_get( 'front_button_label', $atts, '' ) );
		if ( $front_btn !== '' ) {
			// Flips the card to the back (JS-driven); works on touch. Not a link.
			echo '<button type="button" class="' . $fb_btn_class( array( 'fw-fb__flip-btn' ) ) . '">' . esc_html( $front_btn ) . '</button>';
		}
		echo '</div></div>';

		/* Back. */
		echo '<div class="fw-fb__face fw-fb__back"><div class="fw-fb__content">';
		if ( $back_icon !== '' ) { echo '<span class="fw-fb__icon" aria-hidden="true">' . $back_icon . '</span>'; } // phpcs:ignore
		if ( $back_title !== '' ) { echo '<' . $back_tag . ' class="fw-fb__title">' . esc_html( $back_title ) . '</' . $back_tag . '>'; }
		if ( $back_text !== '' ) { echo '<div class="fw-fb__text">' . wp_kses_post( wpautop( $back_text ) ) . '</div>'; }
		if ( $btn_lbl !== '' ) {
			$href = $btn_url !== '' ? esc_url( $btn_url ) : '#';
			// Back link button reuses the [button] preset classes (Theme Settings → Buttons).
			echo '<a class="' . $fb_btn_class( array() ) . '" href="' . $href . '"' . ( $btn_tgt === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . esc_html( $btn_lbl ) . '</a>';
		}
		echo '</div></div>';

		echo '</div>'; // .fw-fb__inner
		echo '</div>'; // wrapper
		return ob_get_clean();
	}
}

echo sc_fb_render( $atts );
