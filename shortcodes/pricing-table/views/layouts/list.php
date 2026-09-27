<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Pricing table — LIST layout (a price list / service menu).
 *
 * Each plan is a full-width ROW: the name and its description on the left, the price on the right, sharing a
 * baseline, with a hairline between rows. This is how a salon, barbershop, restaurant, clinic or trade writes
 * its prices, and measured across the capture corpus it is 43% of the priced groups a conversion meets —
 * none of which the card grid could express.
 *
 * Included by views/view.php, which has already prepared every variable used here ($plans, the billing state,
 * $emit_price, the colour vars, the wrapper classes). Same contract as the gallery's design partials.
 *
 * Deliberately NOT a one-column grid: a grid cell stacks name -> description -> price down the page, which is
 * a different design. The price belongs on the row.
 */

// The row divider is the layout's own option (design_settings/list/row_rule), default on: a hairline
// between rows is the usual price-list rhythm, but a spaced menu without rules is a real design too.
$pt_rule = sc_get( 'design_settings/list/row_rule', $atts, 'yes' ) !== 'no';

echo '<div class="fw-pt__list' . ( $pt_rule ? '' : ' fw-pt__list--no-rule' ) . '">';

$__boxp        = function_exists( 'sc_card_box_style_class' ) ? sc_card_box_style_class( $atts ) : '';
$icon_badge_pre = function_exists( 'sc_icon_badge_preset_class' ) ? sc_icon_badge_preset_class( $atts ) : '';

foreach ( $plans as $p ) {
	$featured = isset( $p['featured'] ) && $p['featured'] === 'yes';
	$ribbon   = isset( $p['ribbon'] ) ? trim( (string) $p['ribbon'] ) : '';
	$pname    = isset( $p['plan_title'] ) ? trim( (string) $p['plan_title'] ) : '';
	$subtitle = isset( $p['subtitle'] ) ? trim( (string) $p['subtitle'] ) : '';
	$currency = isset( $p['currency'] ) ? trim( (string) $p['currency'] ) : '';

	// Price / Period / Original Price are `multi-inline` fields -> array( monthly, yearly ). A plan saved
	// before that merge stores a plain string = the monthly value. Same reader the grid layout uses.
	$mi_val = function ( $key, $which ) use ( $p ) {
		$v = isset( $p[ $key ] ) ? $p[ $key ] : '';
		if ( is_array( $v ) ) { return isset( $v[ $which ] ) ? trim( (string) $v[ $which ] ) : ''; }
		return 'monthly' === $which ? trim( (string) $v ) : '';
	};
	$price    = $mi_val( 'price', 'monthly' );
	$period   = $mi_val( 'period', 'monthly' );
	$original = $mi_val( 'original_price', 'monthly' );
	$price_y  = $mi_val( 'price', 'yearly' );
	$period_y = $mi_val( 'period', 'yearly' );
	$orig_y   = $mi_val( 'original_price', 'yearly' );

	$row_cls = 'fw-pt__row';
	// A row whose price column also holds a CTA is TALLER than its text. Baseline alignment lines the price
	// up with the name and then lets the button hang out of the row, crossing the divider below it — so a
	// row with a button aligns on its centre instead. Marked here rather than with :has() so the rule is
	// deterministic and does not depend on selector support.
	if ( '' !== trim( (string) ( $p['button_label'] ?? '' ) ) ) { $row_cls .= ' has-cta'; }
	if ( $featured ) { $row_cls .= ' is-featured'; }
	if ( $__boxp !== '' ) { $row_cls .= ' ' . $__boxp; }

	echo '<div class="' . esc_attr( $row_cls ) . '">';

		echo '<div class="fw-pt__row-main">';
			echo '<div class="fw-pt__row-head">';
				if ( $pname !== '' ) { echo '<h4 class="fw-pt__name">' . esc_html( $pname ) . '</h4>'; }
				if ( $ribbon !== '' ) { echo '<span class="fw-pt__row-ribbon">' . esc_html( $ribbon ) . '</span>'; }
			echo '</div>';
			if ( $subtitle !== '' ) { echo '<div class="fw-pt__subtitle">' . esc_html( $subtitle ) . '</div>'; }

			// A plan's feature list still renders, under the description — a menu rarely has one, but a
			// package ("Cut + Shave + Facial") sometimes does, and dropping it would lose content.
			$features = isset( $p['features'] ) ? (string) $p['features'] : '';
			$flines   = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $features ) ) ) );
			if ( $flines ) {
				echo '<ul class="fw-pt__features fw-pt__features--inline">';
				foreach ( $flines as $line ) {
					$off = ( isset( $line[0] ) && $line[0] === '-' );
					$txt = $off ? ltrim( substr( $line, 1 ) ) : $line;
					echo '<li class="fw-pt__feature' . ( $off ? ' is-off' : '' ) . '">' . esc_html( $txt ) . '</li>';
				}
				echo '</ul>';
			}
		echo '</div>';

		echo '<div class="fw-pt__row-price">';
			// $emit_price is view.php's shared renderer, so currency/period/was markup and the billing
			// monthly<->yearly swap behave identically in every layout.
			echo $emit_price( $currency, $price, $period, $original, $billing_active ? 'monthly' : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			if ( $billing_active && ( $price_y !== '' || $orig_y !== '' ) ) {
				echo $emit_price( $currency, $price_y, $period_y, $orig_y, 'yearly' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			$btn_lbl = isset( $p['button_label'] ) ? trim( (string) $p['button_label'] ) : '';
			if ( $btn_lbl !== '' ) {
				$href    = esc_url( isset( $p['button_url'] ) ? (string) $p['button_url'] : '' );
				$target  = ( isset( $p['button_target'] ) && $p['button_target'] === '_blank' ) ? ' target="_blank" rel="noopener"' : '';
				$btn_cls = 'fw-pt__btn fw-pt__btn--row' . ( $btn_preset !== '' ? ' ' . $btn_preset : '' );
				echo '<a class="' . esc_attr( $btn_cls ) . '" href="' . $href . '"' . $target . '>' . esc_html( $btn_lbl ) . '</a>';
			}
		echo '</div>';

	echo '</div>';
}

echo '</div>'; // .fw-pt__list
