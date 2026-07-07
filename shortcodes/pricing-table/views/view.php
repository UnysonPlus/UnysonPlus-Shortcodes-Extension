<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Pricing Table — frontend render.
 *
 * @var array $atts
 */

if ( ! function_exists( 'sc_get' ) ) {
	function sc_get( $path, $atts, $default = '' ) {
		if ( function_exists( 'fw_akg' ) ) {
			$v = fw_akg( $path, $atts, null );
			if ( $v !== null ) { return $v; }
		}
		return $default;
	}
}

if ( ! function_exists( 'sc_pt_icon' ) ) {
	function sc_pt_icon( $picked ) {
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

if ( ! function_exists( 'sc_pt_render' ) ) {
	function sc_pt_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$design   = sc_get( 'design', $atts, 'classic' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'classic'; }

		$title = trim( (string) sc_get( 'title', $atts, '' ) );
		$plans = sc_get( 'plans', $atts, array() );
		if ( ! is_array( $plans ) || empty( $plans ) ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-pt__empty">' . esc_html__( 'Add at least one plan.', 'fw' ) . '</div>';
			}
			return '';
		}

		$columns = (int) sc_get( 'columns', $atts, 3 );
		$columns = max( 1, min( 5, $columns ) );
		$raise   = sc_get( 'featured_raise', $atts, 'yes' ) === 'yes';
		$align   = sc_get( 'align', $atts, 'center' );
		$align_cls = function_exists( 'sc_alignment_class' ) ? sc_alignment_class( $align ) : '';

		/* Gap from the Gap Scale (var(--gap-<slug>)). */
		$gap_slug = preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) sc_get( 'gap', $atts, '4' ) ) );
		$gap_css  = $gap_slug === '' ? '0px' : 'var(--gap-' . $gap_slug . ', 1.5rem)';

		/* Per-element colors as CSS vars (custom hex honored). */
		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$style_var  = '--pt-cols:' . $columns . ';--pt-gap:' . $gap_css . ';';
		$style_var .= $var( 'accent_color', '--pt-accent' );
		$style_var .= $var( 'card_bg', '--pt-card-bg' );
		$style_var .= $var( 'title_color', '--pt-title' );
		$style_var .= $var( 'price_color', '--pt-price' );
		$style_var .= $var( 'text_color', '--pt-text' );

		$classes = array(
			'fw-pt',
			'fw-pt--design-' . sanitize_html_class( $design ),
			'fw-pt--cols-' . $columns,
		);
		if ( $raise )     { $classes[] = 'fw-pt--raise'; }
		if ( $align_cls ) { $classes[] = 'fw-pt--' . $align_cls; }

		$atts['base_class']       = 'pricing-table';
		$atts['unique_id_prefix'] = 'pt-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;

		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . '>';
		if ( $title !== '' ) {
			echo '<h3 class="fw-pt__title">' . esc_html( $title ) . '</h3>';
		}
		echo '<div class="fw-pt__grid">';

		foreach ( $plans as $p ) {
			$featured = isset( $p['featured'] ) && $p['featured'] === 'yes';
			$ribbon   = isset( $p['ribbon'] ) ? trim( (string) $p['ribbon'] ) : '';
			$pname    = isset( $p['plan_title'] ) ? trim( (string) $p['plan_title'] ) : '';
			$subtitle = isset( $p['subtitle'] ) ? trim( (string) $p['subtitle'] ) : '';
			$currency = isset( $p['currency'] ) ? trim( (string) $p['currency'] ) : '';
			$price    = isset( $p['price'] ) ? trim( (string) $p['price'] ) : '';
			$period   = isset( $p['period'] ) ? trim( (string) $p['period'] ) : '';
			$icon     = sc_pt_icon( isset( $p['icon'] ) ? $p['icon'] : null );
			$btn_lbl  = isset( $p['button_label'] ) ? trim( (string) $p['button_label'] ) : '';
			$btn_url  = isset( $p['button_url'] ) ? trim( (string) $p['button_url'] ) : '';
			$btn_tgt  = ( isset( $p['button_target'] ) && $p['button_target'] === '_blank' ) ? '_blank' : '_self';

			echo '<div class="fw-pt__plan' . ( $featured ? ' is-featured' : '' ) . '">';
			if ( $ribbon !== '' ) {
				echo '<span class="fw-pt__ribbon">' . esc_html( $ribbon ) . '</span>';
			}

			echo '<div class="fw-pt__head">';
			if ( $icon !== '' ) {
				echo '<span class="fw-pt__icon" aria-hidden="true">' . $icon . '</span>'; // phpcs:ignore
			}
			if ( $pname !== '' ) {
				echo '<h4 class="fw-pt__name">' . esc_html( $pname ) . '</h4>';
			}
			if ( $subtitle !== '' ) {
				echo '<div class="fw-pt__subtitle">' . esc_html( $subtitle ) . '</div>';
			}
			echo '</div>';

			if ( $price !== '' || $currency !== '' ) {
				echo '<div class="fw-pt__price">';
				if ( $currency !== '' ) { echo '<span class="fw-pt__currency">' . esc_html( $currency ) . '</span>'; }
				echo '<span class="fw-pt__amount">' . esc_html( $price ) . '</span>';
				if ( $period !== '' ) { echo '<span class="fw-pt__period">' . esc_html( $period ) . '</span>'; }
				echo '</div>';
			}

			$features = isset( $p['features'] ) ? (string) $p['features'] : '';
			$lines    = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $features ) ), 'strlen' );
			if ( ! empty( $lines ) ) {
				echo '<ul class="fw-pt__features">';
				foreach ( $lines as $line ) {
					$off = ( $line !== '' && ( $line[0] === '-' || $line[0] === '!' ) );
					$txt = $off ? trim( ltrim( $line, '-! ' ) ) : $line;
					echo '<li class="fw-pt__feature' . ( $off ? ' is-off' : '' ) . '">'
						. '<span class="fw-pt__tick" aria-hidden="true">' . ( $off ? '&#10005;' : '&#10003;' ) . '</span>'
						. '<span>' . esc_html( $txt ) . '</span></li>';
				}
				echo '</ul>';
			}

			if ( $btn_lbl !== '' ) {
				$href = $btn_url !== '' ? esc_url( $btn_url ) : '#';
				echo '<div class="fw-pt__cta"><a class="fw-pt__btn" href="' . $href . '"'
					. ( $btn_tgt === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>'
					. esc_html( $btn_lbl ) . '</a></div>';
			}

			echo '</div>'; // plan
		}

		echo '</div></div>';
		return ob_get_clean();
	}
}

echo sc_pt_render( $atts );
