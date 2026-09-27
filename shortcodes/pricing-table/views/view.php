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
	/** Renders a picked icon (via the central icon renderer, or icon-font/upload fallback) for the pricing table. */
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
	/** Renders the Pricing Table shortcode from its atts, resolving the design skin, plans, columns, and featured emphasis. */
	function sc_pt_render( $atts ) {
		// Resolve against the merged registry (built-in skins + installed skin packs)
		// so a pack key is accepted; fall back to the local registry whitelist.
		if ( function_exists( 'fw_sc_design_resolve' ) ) {
			$design = fw_sc_design_resolve( 'pricing_table', $atts, 'classic' );
		} else {
			$registry = require __DIR__ . '/parts/registry.php';
			$design   = sc_get( 'design', $atts, 'classic' );
			if ( ! isset( $registry[ $design ] ) ) { $design = 'classic'; }
		}
		// Render-time enqueue of the resolved design's CSS (static/css/design[s]/<key>.css). The
		// per-instance `fw_ext_shortcodes_enqueue_static:pricing_table` action is fired from a scan whose
		// shortcode regex is NOT recursive, so on a deeply nested page-builder / Site-Converter tree it
		// only reaches the outer elements — the action never fires for this one and the design CSS
		// silently never loads, collapsing the design to an unstyled block stack. Deduped by handle.
		if ( function_exists( 'fw_sc_design_enqueue_now' ) ) { fw_sc_design_enqueue_now( 'pricing_table', $design ); }


		$plans = sc_get( 'plans', $atts, array() );
		if ( ! is_array( $plans ) || empty( $plans ) ) {
			if ( fw_is_editor_context() ) {
				return '<div class="fw-pt__empty">' . esc_html__( 'Add at least one plan.', 'fw' ) . '</div>';
			}
			return '';
		}

		$columns = (int) sc_get( 'columns', $atts, 3 );
		$columns = max( 1, min( 5, $columns ) );
		/* Featured emphasis — a multi-select of composable treatments. Legacy fallback:
		   the old `featured_raise` switch (which merely SCALED the plan) maps to 'enlarge'. */
		$fstyle = sc_get( 'featured_style', $atts, null );
		if ( ! is_array( $fstyle ) ) {
			$fstyle = ( sc_get( 'featured_raise', $atts, 'yes' ) === 'yes' ) ? array( 'enlarge' ) : array();
		}
		$fstyle  = array_values( array_intersect(
			array( 'raise', 'enlarge', 'highlight', 'glow', 'fill', 'badge', 'accent_button', 'emphasize' ),
			array_map( 'strval', $fstyle )
		) );
		$has_badge = in_array( 'badge', $fstyle, true );
		// Button Preset (Theme Settings → Buttons) — when set, plan buttons wear it and it owns the
		// look. Empty = the accent-coloured button (legacy `button_style` solid/outline still honoured
		// for instances saved before presets, since dropping the option shouldn't restyle old tables).
		$btn_preset = trim( (string) sc_get( 'button_preset', $atts, '' ) );
		$btn_style  = sc_get( 'button_style', $atts, 'solid' ) === 'outline' ? 'outline' : 'solid';
		$align   = sc_get( 'align', $atts, 'center' );
		$align_cls = function_exists( 'sc_alignment_class' ) ? sc_alignment_class( $align ) : '';

		/* Monthly / Yearly billing toggle. Active only when enabled AND at least one plan actually
		   carries a yearly price (otherwise the toggle would do nothing). Each price is rendered
		   twice — a --monthly and a --yearly variant — and a tiny script flips `is-yearly` on .fw-pt
		   to swap which shows (CSS-driven; monthly is the no-JS default). */
		$billing_on      = sc_get( 'billing_toggle', $atts, 'no' ) === 'yes';
		$billing_default = sc_get( 'billing_default', $atts, 'monthly' ) === 'yearly' ? 'yearly' : 'monthly';
		$bl_month        = trim( (string) sc_get( 'billing_monthly_label', $atts, __( 'Bill Monthly', 'fw' ) ) );
		$bl_year         = trim( (string) sc_get( 'billing_yearly_label', $atts, __( 'Bill Yearly', 'fw' ) ) );
		$bl_note         = trim( (string) sc_get( 'billing_note', $atts, '' ) );
		// Show the toggle whenever it's enabled — enabling it must visibly DO something even before
		// yearly prices are filled (a plan with a blank Yearly Price just falls back to its monthly
		// figure, below). Gating on "at least one yearly price exists" made an enabled toggle silently
		// vanish, which reads as broken.
		$billing_active = $billing_on;

		/* Emit one price block (optional struck-out "was" + amount). $variant '' = single (no toggle),
		   else 'monthly'/'yearly' tags the block so CSS can show/hide it per toggle state. */
		$emit_price = function ( $currency, $amount, $period, $orig, $variant ) {
			$sfx = $variant !== '' ? ' fw-pt__price--' . $variant : '';
			$wfx = $variant !== '' ? ' fw-pt__was--' . $variant : '';
			$out = '';
			if ( $orig !== '' ) { $out .= '<div class="fw-pt__was' . $wfx . '"><s>' . esc_html( $orig ) . '</s></div>'; }
			if ( $amount !== '' || $currency !== '' ) {
				$out .= '<div class="fw-pt__price' . $sfx . '">';
				if ( $currency !== '' ) { $out .= '<span class="fw-pt__currency">' . esc_html( $currency ) . '</span>'; }
				$out .= '<span class="fw-pt__amount">' . esc_html( $amount ) . '</span>';
				if ( $period !== '' ) { $out .= '<span class="fw-pt__period">' . esc_html( $period ) . '</span>'; }
				$out .= '</div>';
			}
			return $out;
		};

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
			'design-' . sanitize_html_class( $design ), // generic scope for skin packs
			'fw-pt--cols-' . $columns,
		);
		foreach ( $fstyle as $fs ) { $classes[] = 'fw-pt--feat-' . $fs; }
		$classes[] = 'fw-pt--btn-' . $btn_style;
		if ( $align_cls ) { $classes[] = 'fw-pt--' . $align_cls; }
		if ( sc_get( 'feature_dividers', $atts, 'yes' ) === 'no' ) { $classes[] = 'fw-pt--no-dividers'; }
		if ( $billing_active ) {
			$classes[] = 'fw-pt--has-billing';
			if ( $billing_default === 'yearly' ) { $classes[] = 'is-yearly'; }
		}

		$atts['base_class']       = 'pricing-table';
		// Per-item Hover (Hover Target = Each item): stamp each plan so only the hovered one reacts.
		$hov = function_exists( 'sc_hover_item_markup' ) ? sc_hover_item_markup( $atts ) : array( 'class' => '', 'attr' => '' );
		$atts['unique_id_prefix'] = 'pt-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;

		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . '>';

		// Billing-period toggle (Monthly / Yearly). Clicking the switch OR a label flips the table.
		if ( $billing_active ) {
			$is_year = ( $billing_default === 'yearly' );
			echo '<div class="fw-pt__billing" role="group" aria-label="' . esc_attr__( 'Billing period', 'fw' ) . '">';
			echo '<span class="fw-pt__billing-label fw-pt__billing-label--monthly' . ( $is_year ? '' : ' is-active' ) . '" data-pt-billing="monthly">' . esc_html( $bl_month ) . '</span>';
			echo '<button type="button" class="fw-pt__billing-switch" role="switch" aria-checked="' . ( $is_year ? 'true' : 'false' ) . '" aria-label="' . esc_attr__( 'Toggle yearly billing', 'fw' ) . '"><span class="fw-pt__billing-knob"></span></button>';
			echo '<span class="fw-pt__billing-label fw-pt__billing-label--yearly' . ( $is_year ? ' is-active' : '' ) . '" data-pt-billing="yearly">' . esc_html( $bl_year ) . '</span>';
			if ( $bl_note !== '' ) { echo '<span class="fw-pt__billing-note">' . esc_html( $bl_note ) . '</span>'; }
			echo '</div>';
		}

		/* ------------------------------------------------------------------ *
		 * LAYOUT — the plans' structure, not their paint.
		 *
		 * `design` (classic / modern / minimal / …) is a SKIN: a class on the shared card markup. It can
		 * never produce a price LIST, which is how a service menu is written — name and description left,
		 * price right, a rule between rows. Measured across the capture corpus, list-shaped pricing is 43%
		 * of the priced groups a conversion meets, and none of it was expressible.
		 *
		 * So the STRUCTURE is its own axis, each layout a self-contained partial under views/layouts/ —
		 * the same shape the gallery and testimonials shortcodes use for their designs.
		 *
		 * Deliberately NOT views/designs/: fw_sc_designs() treats that filename as the shortcode's ONE
		 * design registry and would prefer it over views/parts/registry.php, silently replacing the
		 * existing Design (skin) picker — and every installed skin pack — with this list. Layout and skin
		 * are separate axes, so layout keeps its own registry and the pluggable design layer is untouched.
		 *
		 * BACK-COMPAT: the id read here is the NEW `design_settings/layout`; anything saved before this
		 * existed has none, falls back to 'grid', and renders exactly as it always did.
		 * ------------------------------------------------------------------ */
		$pt_layout = sc_get( 'design_settings/layout', $atts, 'grid' );
		if ( ! is_string( $pt_layout ) || $pt_layout === '' ) { $pt_layout = 'grid'; }
		$pt_partial = __DIR__ . '/layouts/' . preg_replace( '/[^a-z0-9_-]/', '', strtolower( $pt_layout ) ) . '.php';
		if ( ! file_exists( $pt_partial ) ) { $pt_layout = 'grid'; $pt_partial = __DIR__ . '/layouts/grid.php'; }
		include $pt_partial;

		echo '</div>'; // .fw-pt wrapper (the layout partial closed its own container)

		// Optional Product + Offer JSON-LD, one per plan (machine-readable pricing).
		if ( sc_get( 'product_schema', $atts, 'no' ) === 'yes' && ! empty( $plans ) ) {
			$cmap = array( '$' => 'USD', '€' => 'EUR', '£' => 'GBP', '¥' => 'JPY', '₹' => 'INR', 'R$' => 'BRL', 'A$' => 'AUD', 'C$' => 'CAD' );
			foreach ( $plans as $p ) {
				$pname = isset( $p['plan_title'] ) ? trim( wp_strip_all_tags( (string) $p['plan_title'] ) ) : '';
				if ( $pname === '' ) { continue; }
				$prod = array( '@context' => 'https://schema.org', '@type' => 'Product', 'name' => $pname );
				$sub  = isset( $p['subtitle'] ) ? trim( wp_strip_all_tags( (string) $p['subtitle'] ) ) : '';
				if ( $sub !== '' ) { $prod['description'] = $sub; }
				// Price is a multi-inline array( monthly, yearly ) — schema uses the monthly amount.
				// Back-compat: a plain string from a pre-merge plan is the monthly value.
				$pr        = isset( $p['price'] ) ? $p['price'] : '';
				$price_raw = is_array( $pr ) ? (string) ( isset( $pr['monthly'] ) ? $pr['monthly'] : '' ) : (string) $pr;
				if ( preg_match( '/\d[\d.,]*/', $price_raw, $m ) ) {
					$amount = str_replace( ',', '', $m[0] );
					$cur    = isset( $p['currency'] ) ? trim( (string) $p['currency'] ) : '';
					$iso    = preg_match( '/^[A-Za-z]{3}$/', $cur ) ? strtoupper( $cur ) : ( isset( $cmap[ $cur ] ) ? $cmap[ $cur ] : 'USD' );
					$offer  = array( '@type' => 'Offer', 'price' => $amount, 'priceCurrency' => $iso, 'availability' => 'https://schema.org/InStock' );
					$url    = isset( $p['button_url'] ) ? trim( (string) $p['button_url'] ) : '';
					if ( $url !== '' ) { $offer['url'] = $url; }
					$prod['offers'] = $offer;
				}
				echo '<script type="application/ld+json">' . wp_json_encode( $prod, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "
";
			}
		}

		return ob_get_clean();
	}
}

echo sc_pt_render( $atts );
