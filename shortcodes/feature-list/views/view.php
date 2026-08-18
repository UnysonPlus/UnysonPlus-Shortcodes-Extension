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
		// Back-compat: the pre-1.13 designs fold into the new icon-overrides-marker
		// model. `icon` (marker = item icon, else check) is exactly the new `check`
		// behavior; `badge` = the same, boxed → `check` + a square Icon Style.
		$legacy_square = false;
		if ( $design === 'icon' )  { $design = 'check'; }
		if ( $design === 'badge' ) { $design = 'check'; $legacy_square = true; }
		if ( function_exists( 'fw_sc_designs' ) ) {
			$fl_all = fw_sc_designs( 'feature_list' );
			if ( ! isset( $fl_all[ $design ] ) ) { $design = 'check'; }
		} elseif ( ! isset( $registry[ $design ] ) ) {
			$design = 'check';
		}

		$items = sc_get( 'items', $atts, array() );
		if ( ! is_array( $items ) || empty( $items ) ) {
			if ( fw_is_editor_context() ) {
				return '<div class="fw-fl__empty">' . esc_html__( 'Add at least one item.', 'fw' ) . '</div>';
			}
			return '';
		}

		// Shortcode-level Icon Badge Preset — one `iconb-{slug}` styling EVERY item's icon.
		$icon_badge_pre = function_exists( 'sc_icon_badge_preset_class' ) ? sc_icon_badge_preset_class( $atts ) : '';

		$columns  = (int) sc_get( 'columns', $atts, 1 );
		$columns  = max( 1, min( 3, $columns ) );
		$dividers = sc_get( 'dividers', $atts, 'no' ) === 'yes';
		$zebra    = sc_get( 'zebra', $atts, 'no' ) === 'yes';
		$gap      = sc_get( 'spacing_size', $atts, 'md' );

		$orient = sc_get( 'orientation', $atts, 'vertical' ) === 'horizontal' ? 'horizontal' : 'vertical';
		$ipos   = sc_get( 'icon_position', $atts, 'left' ) === 'top' ? 'top' : 'left';
		$istyle = sc_get( 'icon_style', $atts, 'plain' );
		if ( ! in_array( $istyle, array( 'plain', 'tint', 'circle', 'outline', 'square' ), true ) ) { $istyle = 'plain'; }
		// A legacy `badge` design with no explicit Icon Style keeps its boxed look.
		if ( $legacy_square && $istyle === 'plain' ) { $istyle = 'square'; }

		// Colour emission — preset-aware (compact {predefined,custom} or legacy
		// string) via sc_color_to_css, so palette presets resolve to var(--color-*).
		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			$css = function_exists( 'sc_color_to_css' ) ? sc_color_to_css( $raw, '' ) : '';
			return ( $css !== '' ) ? $name . ':' . $css . ';' : '';
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
			'design-' . sanitize_html_class( $design ), // generic scope for skin packs
			'fw-fl--gap-' . sanitize_html_class( $gap ),
			'fw-fl--orient-' . $orient,
			'fw-fl--ipos-' . $ipos,
			'fw-fl--istyle-' . $istyle,
		);
		if ( $dividers ) { $classes[] = 'fw-fl--dividers'; }
		if ( $zebra )    { $classes[] = 'fw-fl--zebra'; }

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
		$__boxp = function_exists( 'sc_card_box_style_class' ) ? sc_card_box_style_class( $atts ) : ''; // Box Style per feature card
		$i = 0;
		foreach ( $items as $it ) {
			$i++;
			$text = isset( $it['text'] ) ? trim( (string) $it['text'] ) : '';
			$sub  = isset( $it['subtext'] ) ? trim( (string) $it['subtext'] ) : '';
			$val  = isset( $it['value_text'] ) ? trim( (string) $it['value_text'] ) : '';
			$icon = sc_fl_icon( isset( $it['icon'] ) ? $it['icon'] : null );
			$state= ( isset( $it['state'] ) && $it['state'] === 'off' ) ? 'off' : 'on';
			$lu   = isset( $it['link_url'] ) ? trim( (string) $it['link_url'] ) : '';
			$lt   = ( isset( $it['link_target'] ) && $it['link_target'] === '_blank' ) ? '_blank' : '_self';

			// Per-item marker colour override → set --fl-marker on the marker only.
			$mc = isset( $it['marker_color'] ) && function_exists( 'sc_color_to_css' ) ? sc_color_to_css( $it['marker_color'], '' ) : '';
			$mstyle = ( $mc !== '' ) ? ' style="--fl-marker:' . esc_attr( $mc ) . '"' : '';
			$has_icon = ( $icon !== '' );

			// Marker resolution — a per-item ICON always wins when set, so adding an
			// icon on the Content tab shows it regardless of the Default Marker
			// (matches Elementor / Divi / Bricks). The Default Marker is the fallback
			// for icon-less items. Exceptions: an "unavailable" item always shows the
			// cross (checklist semantics); Numbered is positional (keeps its number);
			// "Checkmark + icon" deliberately shows both.
			// `--chip` marks a marker as chip-able so the Icon Style presets
			// (tint/circle/outline/square) can wrap it.
			if ( $state === 'off' ) {
				$marker = '<span class="fw-fl__marker fw-fl__marker--off fw-fl__marker--chip"' . $mstyle . '>' . $cross . '</span>';
			} elseif ( $design === 'numbered' ) {
				$marker = '<span class="fw-fl__marker fw-fl__marker--num"' . $mstyle . '>' . (int) $i . '</span>';
			} elseif ( $design === 'check_icon' ) {
				$inner  = '<span class="fw-fl__ci-check">' . $check . '</span>';
				if ( $has_icon ) { $inner .= '<span class="fw-fl__ci-icon' . ( $icon_badge_pre !== '' ? ' ' . $icon_badge_pre : '' ) . '">' . $icon . '</span>'; }
				$marker = '<span class="fw-fl__marker fw-fl__marker--dual"' . $mstyle . '>' . $inner . '</span>';
			} elseif ( $has_icon ) {
				$marker = '<span class="fw-fl__marker fw-fl__marker--icon fw-fl__marker--chip' . ( $icon_badge_pre !== '' ? ' ' . $icon_badge_pre : '' ) . '"' . $mstyle . '>' . $icon . '</span>';
			} elseif ( $design === 'bullet' ) {
				$marker = '<span class="fw-fl__marker fw-fl__marker--bullet"' . $mstyle . '></span>';
			} elseif ( $design === 'none' ) {
				$marker = ''; // Icons-only: no fallback decoration when an item has no icon.
			} else { // check
				$marker = '<span class="fw-fl__marker fw-fl__marker--on fw-fl__marker--chip"' . $mstyle . '>' . $check . '</span>';
			}

			$body  = '<span class="fw-fl__text">' . esc_html( $text ) . '</span>';
			if ( $sub !== '' ) { $body .= '<span class="fw-fl__sub">' . esc_html( $sub ) . '</span>'; }

			echo '<li class="fw-fl__item' . ( $__boxp !== '' ? ' ' . $__boxp : '' ) . ( $sub !== '' ? ' fw-fl__item--has-sub' : '' ) . ( $state === 'off' ? ' is-off' : '' ) . '">';
			echo $marker; // phpcs:ignore
			if ( $lu !== '' ) {
				echo '<a class="fw-fl__body" href="' . esc_url( $lu ) . '"' . ( $lt === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . $body . '</a>';
			} else {
				echo '<span class="fw-fl__body">' . $body . '</span>';
			}
			if ( $val !== '' ) {
				echo '<span class="fw-fl__value">' . esc_html( $val ) . '</span>';
			}
			echo '</li>';
		}
		echo '</ul>';
		return ob_get_clean();
	}
}

echo sc_fl_render( $atts );
