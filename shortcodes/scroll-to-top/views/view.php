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

if ( ! function_exists( 'sc_stt_render' ) ) {
	function sc_stt_render( $atts ) {
		$show_btn  = sc_get( 'show_button', $atts, 'yes' ) === 'yes';
		$show_prog = sc_get( 'show_progress', $atts, 'no' ) === 'yes';
		if ( ! $show_btn && ! $show_prog ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-stt__empty">' . esc_html__( 'Enable the button and/or the progress bar.', 'fw' ) . '</div>';
			}
			return '';
		}

		$position  = sc_get( 'position', $atts, 'bottom-right' );
		$shape     = sc_get( 'shape', $atts, 'circle' );
		$size      = sc_get( 'button_size', $atts, 'md' );
		$after     = (int) sc_get( 'show_after', $atts, '300' );
		$prog_pos  = sc_get( 'progress_position', $atts, 'top' );
		$prog_h    = max( 1, (int) sc_get( 'progress_height', $atts, '4' ) );

		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$style_var  = $var( 'accent_color', '--stt-accent' );
		$style_var .= $var( 'icon_color', '--stt-icon' );
		$style_var .= '--stt-prog-h:' . $prog_h . 'px;';

		$classes = array(
			'fw-stt',
			'fw-stt--pos-' . sanitize_html_class( $position ),
			'fw-stt--shape-' . sanitize_html_class( $shape ),
			'fw-stt--size-' . sanitize_html_class( $size ),
			'fw-stt--prog-' . sanitize_html_class( $prog_pos ),
		);

		$atts['base_class']       = 'scroll-to-top';
		$atts['unique_id_prefix'] = 'stt-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;
		$attr['data-stt'] = '1';
		$attr['data-after'] = $after;

		// Icon (icon-v2) with arrow fallback.
		$icon  = sc_get( 'icon', $atts, null );
		$icon_html = '';
		if ( is_array( $icon ) && isset( $icon['type'] ) ) {
			if ( $icon['type'] === 'icon-font' && ! empty( $icon['icon-class'] ) ) {
				$icon_html = '<i class="' . esc_attr( $icon['icon-class'] ) . '"></i>';
			} elseif ( $icon['type'] === 'custom-upload' && ! empty( $icon['url'] ) ) {
				$icon_html = '<img src="' . esc_url( $icon['url'] ) . '" alt="" />';
			}
		}
		if ( $icon_html === '' ) {
			$icon_html = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5M6 11l6-6 6 6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		}

		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . '>';
		if ( $show_prog ) {
			echo '<div class="fw-stt__progress" data-stt-progress aria-hidden="true"><span class="fw-stt__progress-fill"></span></div>';
		}
		if ( $show_btn ) {
			echo '<button type="button" class="fw-stt__btn" data-stt-top aria-label="' . esc_attr__( 'Scroll to top', 'fw' ) . '">' . $icon_html . '</button>';
		}
		echo '</div>';
		return ob_get_clean();
	}
}

echo sc_stt_render( $atts );
