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

if ( ! function_exists( 'sc_mp_icon' ) ) {
	function sc_mp_icon( $picked ) {
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

if ( ! function_exists( 'sc_mp_render' ) ) {
	function sc_mp_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$design   = sc_get( 'design', $atts, 'center' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'center'; }

		$ttype = sc_get( 'trigger_type', $atts, 'button' );
		$label = trim( (string) sc_get( 'trigger_label', $atts, '' ) );
		$ticon = sc_mp_icon( sc_get( 'trigger_icon', $atts, null ) );
		$timg  = sc_get( 'trigger_image', $atts, array() );
		$timg_url = ( is_array( $timg ) && ! empty( $timg['url'] ) ) ? $timg['url'] : '';

		$mtitle = trim( (string) sc_get( 'modal_title', $atts, '' ) );
		$mbody  = trim( (string) sc_get( 'modal_content', $atts, '' ) );

		if ( $mbody === '' && $mtitle === '' ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-mp__empty">' . esc_html__( 'Add modal content.', 'fw' ) . '</div>';
			}
			return '';
		}

		$size       = sc_get( 'size', $atts, 'md' );
		// 'open_animation' (renamed). 'animation' is reserved by the builder's
		// Animations tab (an object) — guard so a non-string can never reach the
		// class build below (the "Array to string conversion" bug).
		$animation  = sc_get( 'open_animation', $atts, '' );
		if ( $animation === '' || ! is_string( $animation ) ) {
			$legacy   = sc_get( 'animation', $atts, '' );
			$animation = ( is_string( $legacy ) && $legacy !== '' ) ? $legacy : 'zoom';
		}
		$on_load    = sc_get( 'open_on_load', $atts, 'no' ) === 'yes';
		$delay      = (int) sc_get( 'open_delay', $atts, 0 );
		$close_ov   = sc_get( 'close_overlay', $atts, 'yes' ) === 'yes';

		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$trig_var  = $var( 'accent_color', '--mp-accent' );
		$modal_var = $var( 'overlay_color', '--mp-overlay' ) . $var( 'modal_bg', '--mp-bg' ) . $var( 'modal_color', '--mp-color' ) . $var( 'accent_color', '--mp-accent' );

		$id = function_exists( 'wp_unique_id' ) ? wp_unique_id( 'fw-mp-' ) : uniqid( 'fw-mp-' );

		/* Trigger. */
		$wrap_classes = array( 'fw-mp', 'fw-mp--trig-' . sanitize_html_class( $ttype ) );
		$atts['base_class']       = 'modal-popup';
		$atts['unique_id_prefix'] = 'mp-';
		$atts['css_class']        = trim( implode( ' ', $wrap_classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $trig_var !== '' ) {
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $trig_var;
		}

		if ( $ttype === 'image' && $timg_url !== '' ) {
			$trigger_inner = '<img src="' . esc_url( $timg_url ) . '" alt="' . esc_attr( $label ) . '" loading="lazy" />';
		} elseif ( $ttype === 'icon' ) {
			$trigger_inner = $ticon !== '' ? $ticon : '<span aria-hidden="true">&#9776;</span>';
		} else {
			$trigger_inner = esc_html( $label !== '' ? $label : __( 'Open', 'fw' ) );
		}

		$overlay_classes = array(
			'fw-mp__overlay',
			'fw-mp--design-' . sanitize_html_class( $design ),
			'fw-mp--size-' . sanitize_html_class( $size ),
			'fw-mp--anim-' . sanitize_html_class( $animation ),
		);

		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . '>';

		echo '<button type="button" class="fw-mp__trigger" aria-haspopup="dialog" aria-controls="' . esc_attr( $id ) . '">' . $trigger_inner . '</button>'; // phpcs:ignore

		echo '<div class="' . esc_attr( implode( ' ', $overlay_classes ) ) . '" id="' . esc_attr( $id ) . '" role="dialog" aria-modal="true" aria-hidden="true"'
			. ' data-mp-close-overlay="' . ( $close_ov ? '1' : '0' ) . '"'
			. ( $on_load ? ' data-mp-onload="1" data-mp-delay="' . esc_attr( max( 0, $delay ) ) . '"' : '' )
			. ( $mtitle !== '' ? ' aria-label="' . esc_attr( $mtitle ) . '"' : '' )
			. ( $modal_var !== '' ? ' style="' . esc_attr( $modal_var ) . '"' : '' ) . '>';
		echo '<div class="fw-mp__dialog" role="document">';
		echo '<button type="button" class="fw-mp__close" aria-label="' . esc_attr__( 'Close', 'fw' ) . '"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button>';
		if ( $mtitle !== '' ) { echo '<h3 class="fw-mp__title">' . esc_html( $mtitle ) . '</h3>'; }
		echo '<div class="fw-mp__body">' . wp_kses_post( wpautop( $mbody ) ) . '</div>';
		echo '</div></div>';

		echo '</div>';
		return ob_get_clean();
	}
}

echo sc_mp_render( $atts );
