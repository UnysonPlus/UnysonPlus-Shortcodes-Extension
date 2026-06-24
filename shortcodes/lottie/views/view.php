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

if ( ! function_exists( 'sc_lottie_render' ) ) {
	function sc_lottie_render( $atts ) {
		$source = sc_get( 'source', $atts, 'url' );
		$src    = '';
		if ( $source === 'upload' ) {
			$file = sc_get( 'lottie_file', $atts, array() );
			if ( is_array( $file ) && ! empty( $file['url'] ) ) { $src = $file['url']; }
		} else {
			$src = trim( (string) sc_get( 'lottie_url', $atts, '' ) );
		}

		if ( $src === '' ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-lottie__empty">' . esc_html__( 'Add a Lottie .json URL or upload a file.', 'fw' ) . '</div>';
			}
			return '';
		}

		$trigger   = sc_get( 'trigger', $atts, 'autoplay' );
		$loop      = sc_get( 'loop', $atts, 'yes' ) === 'yes';
		$rev_hover = sc_get( 'reverse_hover', $atts, 'no' ) === 'yes';
		$speed     = (float) sc_get( 'speed', $atts, 1 );
		if ( $speed <= 0 ) { $speed = 1; }
		$direction = sc_get( 'direction', $atts, 'forward' ) === 'reverse' ? -1 : 1;
		$max_width = trim( (string) sc_get( 'max_width', $atts, '240' ) );
		$alignment = sc_get( 'alignment', $atts, 'center' );

		$style_var = '';
		if ( $max_width !== '' && is_numeric( $max_width ) ) {
			$style_var = '--lt-max:' . (int) $max_width . 'px;';
		}

		$classes = array(
			'fw-lottie',
			'fw-lottie--align-' . sanitize_html_class( $alignment ),
		);

		$atts['base_class']       = 'lottie';
		$atts['unique_id_prefix'] = 'lt-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $style_var !== '' ) {
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;
		}
		$attr['data-lottie']    = '1';
		$attr['data-src']       = esc_url( $src );
		$attr['data-trigger']   = sanitize_html_class( $trigger );
		$attr['data-loop']      = $loop ? '1' : '0';
		$attr['data-speed']     = $speed;
		$attr['data-direction'] = $direction;
		$attr['data-reverse-hover'] = $rev_hover ? '1' : '0';

		return '<div ' . fw_attr_to_html( $attr ) . '><div class="fw-lottie__canvas"></div></div>';
	}
}

echo sc_lottie_render( $atts );
