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

if ( ! function_exists( 'sc_tt_icon' ) ) {
	function sc_tt_icon( $picked ) {
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

if ( ! function_exists( 'sc_tt_render' ) ) {
	function sc_tt_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$design   = sc_get( 'design', $atts, 'dark' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'dark'; }

		$ttype = sc_get( 'trigger_type', $atts, 'text' );
		$ttext = trim( (string) sc_get( 'trigger_text', $atts, '' ) );
		$ticon = sc_tt_icon( sc_get( 'trigger_icon', $atts, null ) );
		$title = trim( (string) sc_get( 'tip_title', $atts, '' ) );
		$body  = trim( (string) sc_get( 'tip_content', $atts, '' ) );

		if ( $body === '' && $title === '' ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-tt__empty">' . esc_html__( 'Add tooltip content.', 'fw' ) . '</div>';
			}
			return '';
		}

		$position = sc_get( 'position', $atts, 'top' );
		if ( ! in_array( $position, array( 'top', 'right', 'bottom', 'left' ), true ) ) { $position = 'top'; }
		$event = sc_get( 'event', $atts, 'hover' ) === 'click' ? 'click' : 'hover';
		$arrow = sc_get( 'arrow', $atts, 'yes' ) === 'yes';
		$max_w = preg_replace( '/[^0-9a-zA-Z.%]/', '', (string) sc_get( 'max_width', $atts, '240px' ) );

		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$style_var  = $var( 'tip_bg', '--tt-bg' );
		$style_var .= $var( 'tip_color', '--tt-color' );
		$style_var .= $var( 'accent_color', '--tt-accent' );
		if ( $max_w !== '' ) { $style_var .= '--tt-maxw:' . $max_w . ';'; }

		$classes = array(
			'fw-tt',
			'fw-tt--design-' . sanitize_html_class( $design ),
			'fw-tt--pos-' . sanitize_html_class( $position ),
			'fw-tt--' . $event,
		);
		if ( $arrow ) { $classes[] = 'fw-tt--arrow'; }
		$classes[] = 'fw-tt--trig-' . sanitize_html_class( $ttype );

		$atts['base_class']       = 'tooltip';
		$atts['unique_id_prefix'] = 'tt-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $style_var !== '' ) {
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;
		}

		$tip_id = function_exists( 'wp_unique_id' ) ? wp_unique_id( 'fw-tt-' ) : uniqid( 'fw-tt-' );

		/* Trigger markup. */
		if ( $ttype === 'icon' ) {
			$inner = $ticon !== '' ? $ticon : '<span class="fw-tt__q">?</span>';
			$trigger = '<button type="button" class="fw-tt__trigger fw-tt__trigger--icon" aria-describedby="' . esc_attr( $tip_id ) . '">' . $inner . '</button>'; // phpcs:ignore
		} elseif ( $ttype === 'button' ) {
			$trigger = '<button type="button" class="fw-tt__trigger fw-tt__trigger--button" aria-describedby="' . esc_attr( $tip_id ) . '">' . esc_html( $ttext ) . '</button>';
		} else {
			$trigger = '<button type="button" class="fw-tt__trigger fw-tt__trigger--text" aria-describedby="' . esc_attr( $tip_id ) . '">' . esc_html( $ttext ) . '</button>';
		}

		ob_start();
		echo '<span ' . fw_attr_to_html( $attr ) . '>';
		echo $trigger; // phpcs:ignore
		echo '<span class="fw-tt__bubble" id="' . esc_attr( $tip_id ) . '" role="tooltip">';
		if ( $title !== '' ) { echo '<span class="fw-tt__title">' . esc_html( $title ) . '</span>'; }
		echo '<span class="fw-tt__body">' . wp_kses_post( $body ) . '</span>';
		echo '<span class="fw-tt__arrow" aria-hidden="true"></span>';
		echo '</span>';
		echo '</span>';
		return ob_get_clean();
	}
}

echo sc_tt_render( $atts );
