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

if ( ! function_exists( 'sc_steps_icon' ) ) {
	function sc_steps_icon( $picked ) {
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

if ( ! function_exists( 'sc_steps_render' ) ) {
	function sc_steps_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$design   = sc_get( 'design', $atts, 'horizontal' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'horizontal'; }

		$steps = sc_get( 'steps', $atts, array() );
		if ( ! is_array( $steps ) || empty( $steps ) ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-steps__empty">' . esc_html__( 'Add at least one step.', 'fw' ) . '</div>';
			}
			return '';
		}

		$marker    = sc_get( 'marker', $atts, 'number' );
		$shape     = sc_get( 'marker_shape', $atts, 'circle' );
		$connector = sc_get( 'connector', $atts, 'solid' );
		$title_tag = sc_get( 'title_tag', $atts, 'h3' );
		$allowed_tags = array( 'h2', 'h3', 'h4', 'h5', 'div' );
		if ( ! in_array( $title_tag, $allowed_tags, true ) ) { $title_tag = 'h3'; }

		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$style_var  = '--st-count:' . count( $steps ) . ';';
		$style_var .= $var( 'accent_color', '--st-accent' );
		$style_var .= $var( 'marker_text_color', '--st-marker-text' );
		$style_var .= $var( 'title_color', '--st-title' );
		$style_var .= $var( 'text_color', '--st-text' );

		$classes = array(
			'fw-steps',
			'fw-steps--design-' . sanitize_html_class( $design ),
			'fw-steps--marker-' . sanitize_html_class( $marker ),
			'fw-steps--shape-' . sanitize_html_class( $shape ),
			'fw-steps--connector-' . sanitize_html_class( $connector ),
		);

		$atts['base_class']       = 'steps';
		$atts['unique_id_prefix'] = 'steps-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $style_var !== '' ) {
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;
		}

		ob_start();
		echo '<ol ' . fw_attr_to_html( $attr ) . '>';
		$i = 0;
		foreach ( $steps as $s ) {
			$i++;
			$title = isset( $s['title'] ) ? trim( (string) $s['title'] ) : '';
			$desc  = isset( $s['content'] ) ? trim( (string) $s['content'] ) : '';
			$num   = isset( $s['number'] ) && trim( (string) $s['number'] ) !== '' ? trim( (string) $s['number'] ) : (string) $i;
			$icon  = sc_steps_icon( isset( $s['icon'] ) ? $s['icon'] : null );

			echo '<li class="fw-steps__item">';
			echo '<div class="fw-steps__connector" aria-hidden="true"></div>';
			if ( $marker !== 'none' ) {
				echo '<div class="fw-steps__marker">';
				if ( $marker === 'icon' && $icon !== '' ) {
					echo '<span class="fw-steps__icon">' . $icon . '</span>';
				} else {
					echo '<span class="fw-steps__num">' . esc_html( $num ) . '</span>';
				}
				echo '</div>';
			}
			echo '<div class="fw-steps__body">';
			if ( $title !== '' ) {
				echo '<' . $title_tag . ' class="fw-steps__title">' . esc_html( $title ) . '</' . $title_tag . '>';
			}
			if ( $desc !== '' ) {
				echo '<div class="fw-steps__text">' . do_shortcode( wpautop( $desc ) ) . '</div>';
			}
			echo '</div>'; // body
			echo '</li>';
		}
		echo '</ol>';
		return ob_get_clean();
	}
}

echo sc_steps_render( $atts );
