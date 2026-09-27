<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Pricing table — GRID layout (the default, and what every pricing table rendered before layouts existed).
 *
 * Plans sit side by side in `--pt-cols` columns, each a stacked card: name, subtitle, price, features, CTA.
 * Included by views/view.php, which has already prepared every variable used here ($plans, $columns, the
 * billing state, $emit_price, the colour vars, the wrapper classes …) — same contract as the gallery's
 * design partials.
 *
 * This markup is byte-for-byte what view.php used to emit inline, so a pricing table saved before the
 * layout option existed renders identically.
 */

		echo '<div class="fw-pt__grid">';
		$__boxp = function_exists( 'sc_card_box_style_class' ) ? sc_card_box_style_class( $atts ) : ''; // Box Style per plan card
		// Shortcode-level Icon Badge Preset — one `iconb-{slug}` styling EVERY plan icon.
		$icon_badge_pre = function_exists( 'sc_icon_badge_preset_class' ) ? sc_icon_badge_preset_class( $atts ) : '';

		foreach ( $plans as $p ) {
			$featured = isset( $p['featured'] ) && $p['featured'] === 'yes';
			$ribbon   = isset( $p['ribbon'] ) ? trim( (string) $p['ribbon'] ) : '';
			$pname    = isset( $p['plan_title'] ) ? trim( (string) $p['plan_title'] ) : '';
			$subtitle = isset( $p['subtitle'] ) ? trim( (string) $p['subtitle'] ) : '';
			$currency = isset( $p['currency'] ) ? trim( (string) $p['currency'] ) : '';
			// Price / Period / Original Price are `multi-inline` fields → array( monthly, yearly ).
			// Back-compat: a plan saved before the merge stores a plain string = the monthly value.
			$mi_val = function ( $key, $which ) use ( $p ) {
				$v = isset( $p[ $key ] ) ? $p[ $key ] : '';
				if ( is_array( $v ) ) { return isset( $v[ $which ] ) ? trim( (string) $v[ $which ] ) : ''; }
				return 'monthly' === $which ? trim( (string) $v ) : '';
			};
			$price    = $mi_val( 'price', 'monthly' );
			$period   = $mi_val( 'period', 'monthly' );
			$original = $mi_val( 'original_price', 'monthly' );
			$icon     = sc_pt_icon( isset( $p['icon'] ) ? $p['icon'] : null );
			$btn_lbl  = isset( $p['button_label'] ) ? trim( (string) $p['button_label'] ) : '';
			$btn_url  = isset( $p['button_url'] ) ? trim( (string) $p['button_url'] ) : '';
			$btn_tgt  = ( isset( $p['button_target'] ) && $p['button_target'] === '_blank' ) ? '_blank' : '_self';

			echo '<div class="fw-pt__plan' . ( $__boxp !== '' ? ' ' . $__boxp : '' ) . ( $featured ? ' is-featured' : '' ) . esc_attr( $hov['class'] ) . '"' . $hov['attr'] . '>';
			// Top-center badge (the 'badge' emphasis) on the featured plan — uses the
			// plan's Ribbon text, or "Most Popular" if none. Falls back to the classic
			// corner ribbon otherwise.
			if ( $featured && $has_badge ) {
				$badge_txt = $ribbon !== '' ? $ribbon : __( 'Most Popular', 'fw' );
				echo '<span class="fw-pt__badge">' . esc_html( $badge_txt ) . '</span>';
			} elseif ( $ribbon !== '' ) {
				echo '<span class="fw-pt__ribbon">' . esc_html( $ribbon ) . '</span>';
			}

			echo '<div class="fw-pt__head">';
			if ( $icon !== '' ) {
				echo '<span class="fw-pt__icon' . ( $icon_badge_pre !== '' ? ' ' . $icon_badge_pre : '' ) . '" aria-hidden="true">' . $icon . '</span>'; // phpcs:ignore
			}
			if ( $pname !== '' ) {
				echo '<h4 class="fw-pt__name">' . esc_html( $pname ) . '</h4>';
			}
			if ( $subtitle !== '' ) {
				echo '<div class="fw-pt__subtitle">' . esc_html( $subtitle ) . '</div>';
			}
			echo '</div>';

			if ( $billing_active ) {
				// Each yearly field is honoured INDEPENDENTLY and only falls back to its monthly
				// counterpart when the user left it blank. This lets a yearly-only "was" price swap
				// even when the live price is the same in both states (e.g. free plans), while a plan
				// with NO yearly data still mirrors monthly so nothing ever blanks on toggle.
				$price_yr  = $mi_val( 'price', 'yearly' );
				$period_yr = $mi_val( 'period', 'yearly' );
				$orig_yr   = $mi_val( 'original_price', 'yearly' );
				$has_year  = ( $price_yr !== '' );
				$price_y   = $has_year ? $price_yr : $price;
				$period_y  = ( $period_yr !== '' ) ? $period_yr : $period;
				// Yearly "was": the yearly value if set; else the monthly "was" ONLY when the plan has
				// no distinct yearly price (fully mirrors monthly) — otherwise show no yearly "was".
				$orig_y    = ( $orig_yr !== '' ) ? $orig_yr : ( $has_year ? '' : $original );
				echo $emit_price( $currency, $price, $period, $original, 'monthly' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $emit_price( $currency, $price_y, $period_y, $orig_y, 'yearly' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo $emit_price( $currency, $price, $period, $original, '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
				// With a preset: a full-width themed .btn (the preset owns colours/shape). Otherwise the
				// pricing-table's own accent button.
				$btn_cls = ( $btn_preset !== '' )
					? 'fw-pt__btn-preset btn ' . sanitize_html_class( $btn_preset )
					: 'fw-pt__btn';
				echo '<div class="fw-pt__cta"><a class="' . esc_attr( $btn_cls ) . '" href="' . $href . '"'
					. ( $btn_tgt === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>'
					. esc_html( $btn_lbl ) . '</a></div>';
			}

			echo '</div>'; // plan
		}

		echo '</div>'; // .fw-pt__grid
