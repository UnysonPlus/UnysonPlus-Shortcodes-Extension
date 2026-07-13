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

if ( ! function_exists( 'sc_fl_icon' ) ) {
	function sc_fl_icon( $picked ) {
		// Central icon renderer (single source of truth). aria_hidden => false
		// preserves this element's original decorative-icon markup.
		if ( function_exists( 'sc_icon_render' ) ) {
			return sc_icon_render( $picked, array( 'aria_hidden' => false ) );
		}
		// Fallback (helper not loaded): original inline behaviour.
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

if ( ! function_exists( 'sc_fl_render' ) ) {
	function sc_fl_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$design   = sc_get( 'design', $atts, 'check' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'check'; }

		$items = sc_get( 'items', $atts, array() );
		if ( ! is_array( $items ) || empty( $items ) ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-fl__empty">' . esc_html__( 'Add at least one item.', 'fw' ) . '</div>';
			}
			return '';
		}

		$columns  = (int) sc_get( 'columns', $atts, 1 );
		$columns  = max( 1, min( 3, $columns ) );
		$dividers = sc_get( 'dividers', $atts, 'no' ) === 'yes';
		$gap      = sc_get( 'spacing_size', $atts, 'md' );

		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$style_var  = '--fl-cols:' . $columns . ';';
		$style_var .= $var( 'marker_color', '--fl-marker' );
		$style_var .= $var( 'text_color', '--fl-text' );
		$style_var .= $var( 'sub_color', '--fl-sub' );
		// Icon Size — a unit-input compiled to a CSS length driving --fl-marker-size (the
		// marker svg/img read it for width+height; unset falls back to the 1.25em default).
		$msz = sc_get( 'marker_size', $atts, '' );
		if ( is_array( $msz ) && isset( $msz['value'] ) && trim( (string) $msz['value'] ) !== '' ) {
			$mlen = class_exists( 'FW_Option_Type_Unit_Input' )
				? FW_Option_Type_Unit_Input::to_string( $msz )
				: ( trim( (string) $msz['value'] ) . ( isset( $msz['unit'] ) ? preg_replace( '/[^a-z%]/', '', (string) $msz['unit'] ) : 'px' ) );
			if ( $mlen !== '' ) { $style_var .= '--fl-marker-size:' . $mlen . ';'; }
		}

		$classes = array(
			'fw-fl',
			'fw-fl--design-' . sanitize_html_class( $design ),
			'fw-fl--gap-' . sanitize_html_class( $gap ),
		);
		if ( $dividers ) { $classes[] = 'fw-fl--dividers'; }

		$atts['base_class']       = 'feature-list';
		$atts['unique_id_prefix'] = 'fl-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $style_var !== '' ) {
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;
		}

		$check = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>';
		$cross = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>';

		ob_start();
		echo '<ul ' . fw_attr_to_html( $attr ) . '>';
		$i = 0;
		foreach ( $items as $it ) {
			$i++;
			$text = isset( $it['text'] ) ? trim( (string) $it['text'] ) : '';
			$sub  = isset( $it['subtext'] ) ? trim( (string) $it['subtext'] ) : '';
			$icon = sc_fl_icon( isset( $it['icon'] ) ? $it['icon'] : null );
			$state= ( isset( $it['state'] ) && $it['state'] === 'off' ) ? 'off' : 'on';
			$lu   = isset( $it['link_url'] ) ? trim( (string) $it['link_url'] ) : '';
			$lt   = ( isset( $it['link_target'] ) && $it['link_target'] === '_blank' ) ? '_blank' : '_self';

			$marker = '';
			if ( $design === 'check' ) {
				$marker = '<span class="fw-fl__marker fw-fl__marker--' . $state . '">' . ( $state === 'off' ? $cross : $check ) . '</span>';
			} elseif ( $design === 'numbered' ) {
				$marker = '<span class="fw-fl__marker fw-fl__marker--num">' . (int) $i . '</span>';
			} elseif ( $design === 'bullet' ) {
				$marker = '<span class="fw-fl__marker fw-fl__marker--bullet"></span>';
			} else { // icon / badge
				$marker = '<span class="fw-fl__marker fw-fl__marker--icon">' . ( $icon !== '' ? $icon : $check ) . '</span>';
			}

			$body  = '<span class="fw-fl__text">' . esc_html( $text ) . '</span>';
			if ( $sub !== '' ) { $body .= '<span class="fw-fl__sub">' . esc_html( $sub ) . '</span>'; }

			echo '<li class="fw-fl__item' . ( $state === 'off' ? ' is-off' : '' ) . '">';
			echo $marker; // phpcs:ignore
			if ( $lu !== '' ) {
				echo '<a class="fw-fl__body" href="' . esc_url( $lu ) . '"' . ( $lt === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . $body . '</a>';
			} else {
				echo '<span class="fw-fl__body">' . $body . '</span>';
			}
			echo '</li>';
		}
		echo '</ul>';
		return ob_get_clean();
	}
}

echo sc_fl_render( $atts );
