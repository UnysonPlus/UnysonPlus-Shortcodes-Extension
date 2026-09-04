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
	/** Renders a picked icon (via the central icon renderer, or icon-font/upload fallback) for a step. */
	function sc_steps_icon( $picked ) {
		// Central icon renderer (single source of truth). aria_hidden => false
		// preserves this element's original decorative-icon markup.
		if ( function_exists( 'sc_icon_render' ) ) {
			return sc_icon_render( $picked, array( 'aria_hidden' => false ) );
		}
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
	/** Renders the Steps shortcode from its atts, resolving the design and the list of steps. */
	function sc_steps_render( $atts ) {
		if ( function_exists( 'fw_sc_design_resolve' ) ) {
			$design = fw_sc_design_resolve( 'steps', $atts, 'horizontal' );
		} else {
			$registry = require __DIR__ . '/parts/registry.php';
			$design   = sc_get( 'design', $atts, 'horizontal' );
			if ( ! isset( $registry[ $design ] ) ) { $design = 'horizontal'; }
		}

		// Render-time skin-CSS enqueue (robust). static.php enqueues design/<key>.css from the
		// [steps] static-scan action, but that regex is defeated by the huge HTML-entity-encoded
		// `steps="…"` atts blob on builder / Site-Converter pages, so the skin CSS silently never
		// loads and `cards` / `horizontal` collapse to an unstyled block stack. Enqueue it here now
		// that $design is resolved. Deduped, so the head action (when it does fire) can't double it.
		if ( function_exists( 'fw_sc_design_enqueue_now' ) ) { fw_sc_design_enqueue_now( 'steps', $design ); }

		$steps = sc_get( 'steps', $atts, array() );
		if ( ! is_array( $steps ) || empty( $steps ) ) {
			if ( fw_is_editor_context() ) {
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
			'design-' . sanitize_html_class( $design ), // generic scope for skin packs
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

		// Shortcode-level Icon Badge Preset — one `iconb-{slug}` styling EVERY step icon.
		$icon_badge_pre = function_exists( 'sc_icon_badge_preset_class' ) ? sc_icon_badge_preset_class( $atts ) : '';

		// Box Style preset (.boxp-{slug}) applied to EVERY step card — the card fill / border / corners /
		// shadow + hover. Most visible on the Cards design; harmless on the line designs.
		$box_style = function_exists( 'sc_card_box_style_class' ) ? sc_card_box_style_class( $atts ) : '';

		// Card Rows — the step BODY layout (icon / number / title / description order + inline/stacked +
		// alignment). Read once; when set, each step's body renders through the shared row renderer. The
		// marker chip + connector spine stay outside the rows (owned by Design + Marker).
		$card_rows = function_exists( 'sc_card_rows_value' ) ? sc_card_rows_value( $atts, 'card_rows' ) : array();

		ob_start();
		echo '<ol ' . fw_attr_to_html( $attr ) . '>';
		$i = 0;
		foreach ( $steps as $s ) {
			$i++;
			$title = isset( $s['title'] ) ? trim( (string) $s['title'] ) : '';
			$desc  = isset( $s['content'] ) ? trim( (string) $s['content'] ) : '';
			$num   = isset( $s['number'] ) && trim( (string) $s['number'] ) !== '' ? trim( (string) $s['number'] ) : (string) $i;
			$icon  = sc_steps_icon( isset( $s['icon'] ) ? $s['icon'] : null );

			echo '<li class="fw-steps__item' . ( $box_style !== '' ? ' ' . esc_attr( $box_style ) : '' ) . '">';
			echo '<div class="fw-steps__connector" aria-hidden="true"></div>';
			if ( $marker !== 'none' ) {
				echo '<div class="fw-steps__marker">';
				if ( $marker === 'icon' && $icon !== '' ) {
					echo '<span class="fw-steps__icon' . ( $icon_badge_pre !== '' ? ' ' . $icon_badge_pre : '' ) . '">' . $icon . '</span>';
				} else {
					echo '<span class="fw-steps__num">' . esc_html( $num ) . '</span>';
				}
				echo '</div>';
			}
			// Body slot markup (single source of truth for both the Card-Rows and the default renderer).
			$title_html   = $title !== '' ? '<' . $title_tag . ' class="fw-steps__title">' . esc_html( $title ) . '</' . $title_tag . '>' : '';
			$content_html = $desc !== '' ? '<div class="fw-steps__text">' . do_shortcode( wpautop( $desc ) ) . '</div>' : '';

			echo '<div class="fw-steps__body">';
			if ( ! empty( $card_rows ) && function_exists( 'sc_card_rows_render' ) ) {
				// Card Rows govern the body: icon / number can also appear inline here (independent of the
				// spine marker), letting a step read as e.g. [number · title] then [description].
				$slot_map = array();
				if ( $title_html !== '' )   { $slot_map['title']   = $title_html; }
				if ( $content_html !== '' ) { $slot_map['content'] = $content_html; }
				$slot_map['number'] = '<span class="fw-steps__num-inline">' . esc_html( $num ) . '</span>';
				// The icon renders as a BADGE (accent fill + marker shape + size) so a step's icon reads the same
				// whether it's on the marker spine or placed in a Card Row — the row is the single source of truth.
				if ( $icon !== '' )         { $slot_map['icon'] = '<span class="fw-steps__icon-badge' . ( $icon_badge_pre !== '' ? ' ' . $icon_badge_pre : '' ) . '"><span class="fw-steps__icon">' . $icon . '</span></span>'; }
				$body_inner = sc_card_rows_render( $card_rows, $slot_map, 'steps-card' );
				echo $body_inner !== '' ? $body_inner : ( $title_html . $content_html );
			} else {
				echo $title_html . $content_html;
			}
			echo '</div>'; // body
			echo '</li>';
		}
		echo '</ol>';
		return ob_get_clean();
	}
}

echo sc_steps_render( $atts );
