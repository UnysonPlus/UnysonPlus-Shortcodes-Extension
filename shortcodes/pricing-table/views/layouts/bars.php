<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Pricing table — BARS layout (a full-width band per plan).
 *
 * Each plan is a stacked full-width CARD: name and description left, price and call-to-action right. It is
 * the shape a comparison page reaches for when there are only two or three plans and the feature lists are
 * short — side-by-side cards leave a lot of empty column, and a flush menu row is too plain for something
 * carrying a button.
 *
 * WHERE IT SITS BETWEEN THE OTHER TWO, because that is the only thing that justifies a third layout:
 *   - grid  — cards SIDE BY SIDE; comparison is horizontal.
 *   - list  — flush rows, hairline between, no card; a price list / service menu.
 *   - bars  — stacked rows that ARE cards: the list's reading order with the grid's card treatment.
 *
 * The plan card keeps its skin here (the `design` option paints it, exactly as in the grid), which is
 * precisely what a list row does not have. Gap between bands comes from the shared Gap option.
 *
 * NOT auto-selected by the Site Converter, deliberately. The conversion plan had guessed a `display:flex`
 * row would mean bars, but a flex row IS cards side by side and is already mapped to `grid`; and measured
 * across the capture corpus, priced groups whose plans are stacked SKINNED boxes came to **0 of 43
 * classifiable groups**. There is no source population to detect, so wiring a detector rule would only put
 * the 31 correctly-gridded groups at risk. This layout exists for someone choosing it by hand.
 *
 * Included by views/view.php, which has prepared every variable used here ($plans, the billing state,
 * $emit_price, $btn_preset, the wrapper classes) — the same contract the grid and list partials use.
 */

$__boxp = function_exists( 'sc_card_box_style_class' ) ? sc_card_box_style_class( $atts ) : '';

echo '<div class="fw-pt__bars">';

foreach ( $plans as $p ) {
	$featured = isset( $p['featured'] ) && $p['featured'] === 'yes';
	$ribbon   = isset( $p['ribbon'] ) ? trim( (string) $p['ribbon'] ) : '';
	$pname    = isset( $p['plan_title'] ) ? trim( (string) $p['plan_title'] ) : '';
	$subtitle = isset( $p['subtitle'] ) ? trim( (string) $p['subtitle'] ) : '';
	$currency = isset( $p['currency'] ) ? trim( (string) $p['currency'] ) : '';

	// Price / Period / Original Price are `multi-inline` fields -> array( monthly, yearly ). A plan saved
	// before that merge stores a plain string = the monthly value. Same reader the other layouts use.
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

	$band_cls = 'fw-pt__band fw-pt__plan';
	if ( $featured ) { $band_cls .= ' is-featured'; }
	if ( $__boxp !== '' ) { $band_cls .= ' ' . $__boxp; }

	echo '<div class="' . esc_attr( $band_cls ) . '">';

		echo '<div class="fw-pt__band-main">';
			echo '<div class="fw-pt__band-head">';
				if ( $pname !== '' ) { echo '<h4 class="fw-pt__name">' . esc_html( $pname ) . '</h4>'; }
				if ( $ribbon !== '' ) { echo '<span class="fw-pt__row-ribbon">' . esc_html( $ribbon ) . '</span>'; }
			echo '</div>';
			if ( $subtitle !== '' ) { echo '<div class="fw-pt__subtitle">' . esc_html( $subtitle ) . '</div>'; }

			// Features run INLINE here rather than as a stacked checklist: a band is wide and short, and a
			// vertical list inside one turns the band back into a card with a lot of dead width beside it.
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

		echo '<div class="fw-pt__band-price">';
			// $emit_price is view.php's shared renderer, so currency/period/was markup and the billing
			// monthly<->yearly swap behave identically in every layout.
			echo $emit_price( $currency, $price, $period, $original, $billing_active ? 'monthly' : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			if ( $billing_active && ( $price_y !== '' || $orig_y !== '' ) ) {
				echo $emit_price( $currency, $price_y, $period_y, $orig_y, 'yearly' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		echo '</div>';

		$btn_lbl = isset( $p['button_label'] ) ? trim( (string) $p['button_label'] ) : '';
		if ( $btn_lbl !== '' ) {
			$href   = esc_url( isset( $p['button_url'] ) ? (string) $p['button_url'] : '' );
			$target = ( isset( $p['button_target'] ) && $p['button_target'] === '_blank' ) ? ' target="_blank" rel="noopener"' : '';
			// Its own cell, not inside the price cell: the button is the band's end-stop, and every band's
			// button must line up down the right edge however tall or short its price happens to be.
			echo '<div class="fw-pt__band-cta">';
				$btn_cls = 'fw-pt__btn fw-pt__btn--band' . ( $btn_preset !== '' ? ' ' . $btn_preset : '' );
				echo '<a class="' . esc_attr( $btn_cls ) . '" href="' . $href . '"' . $target . '>' . esc_html( $btn_lbl ) . '</a>';
			echo '</div>';
		}

	echo '</div>';
}

echo '</div>'; // .fw-pt__bars
