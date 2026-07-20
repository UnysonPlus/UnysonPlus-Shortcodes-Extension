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

if ( ! function_exists( 'sc_ss_render' ) ) {
	function sc_ss_render( $atts ) {
		$catalog  = require __DIR__ . '/parts/networks.php';
		$registry = require __DIR__ . '/parts/registry.php';

		$design = sc_get( 'design', $atts, 'brand' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'brand'; }

		$selected = sc_get( 'networks', $atts, array() );
		if ( ! is_array( $selected ) ) { $selected = array(); }
		// Keep only known networks, preserve the saved order.
		$selected = array_values( array_filter( $selected, function ( $k ) use ( $catalog ) { return isset( $catalog[ $k ] ); } ) );
		if ( empty( $selected ) ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-ss__empty">' . esc_html__( 'Pick at least one network.', 'fw' ) . '</div>';
			}
			return '';
		}

		/* Resolve the share URL + title. */
		$source = sc_get( 'share_source', $atts, 'current' );
		if ( $source === 'custom' ) {
			$share_url = trim( (string) sc_get( 'custom_url', $atts, '' ) );
		} else {
			$share_url = get_permalink();
			if ( ! $share_url ) {
				$share_url = home_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/' );
			}
		}
		if ( $share_url === '' ) { $share_url = home_url( '/' ); }

		$share_text = trim( (string) sc_get( 'share_text', $atts, '' ) );
		if ( $share_text === '' ) {
			$share_text = get_the_title();
			if ( $share_text === '' ) { $share_text = get_bloginfo( 'name' ); }
		}

		$u = rawurlencode( $share_url );
		$t = rawurlencode( wp_strip_all_tags( $share_text ) );

		/* Appearance. */
		$shape   = sc_get( 'shape', $atts, 'circle' );
		$size    = sc_get( 'size', $atts, 'md' );
		$layout  = sc_get( 'layout', $atts, 'inline' );
		$show_lbl = sc_get( 'show_label', $atts, 'no' ) === 'yes' || $design === 'text';
		$align   = sc_get( 'align', $atts, 'left' );
		$align_cls = function_exists( 'sc_alignment_class' ) ? sc_alignment_class( $align ) : '';

		/* Color overrides. */
		$hex = function ( $key ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				return preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
			}
			return '';
		};
		$override = $hex( 'custom_color' );
		$icon_col = $hex( 'icon_color' );
		$style_var = '';
		if ( $icon_col !== '' ) { $style_var .= '--ss-icon:' . $icon_col . ';'; }

		$classes = array(
			'fw-ss',
			'fw-ss--design-' . sanitize_html_class( $design ),
			'fw-ss--shape-' . sanitize_html_class( $shape ),
			'fw-ss--size-' . sanitize_html_class( $size ),
			'fw-ss--layout-' . sanitize_html_class( $layout ),
		);
		if ( $show_lbl )  { $classes[] = 'fw-ss--labeled'; }
		if ( $align_cls ) { $classes[] = 'fw-ss--' . $align_cls; }

		$atts['base_class']       = 'social-share';
		$atts['unique_id_prefix'] = 'ss-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $style_var !== '' ) {
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;
		}

		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . '>';
		echo '<div class="fw-ss__list">';

		foreach ( $selected as $key ) {
			$n     = $catalog[ $key ];
			$color = $override !== '' ? $override : $n['color'];
			$label = $n['label'];

			$is_copy = ( $key === 'copy' || $n['url'] === '' );
			if ( $is_copy ) {
				$aria = __( 'Copy link', 'fw' );
				$href = '#';
				$extra = ' data-ss-copy="1" data-ss-url="' . esc_attr( $share_url ) . '"';
			} else {
				$aria = sprintf( __( 'Share on %s', 'fw' ), $label );
				$href = esc_url( sprintf( $n['url'], $u, $t ) );
				$extra = ! empty( $n['window'] ) ? ' data-ss-window="1"' : '';
				if ( $key !== 'email' ) { $extra .= ' rel="noopener noreferrer"'; }
			}

			echo '<a class="fw-ss__btn fw-ss__btn--' . sanitize_html_class( $key ) . '" href="' . $href . '"'
				. ' style="--ss-color:' . esc_attr( $color ) . '"'
				. ' aria-label="' . esc_attr( $aria ) . '"' . $extra . '>'
				. '<span class="fw-ss__icon">' . $n['icon'] . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			if ( $show_lbl ) {
				echo '<span class="fw-ss__label">' . esc_html( $label ) . '</span>';
			}
			echo '<span class="fw-ss__copied" aria-hidden="true">' . esc_html__( 'Copied!', 'fw' ) . '</span>';
			echo '</a>';
		}

		echo '</div></div>';
		return ob_get_clean();
	}
}

echo sc_ss_render( $atts );
