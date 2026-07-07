<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** @var array $atts */

if ( ! function_exists( 'sc_announce_color' ) ) {
	/** Resolve a compact color-field value to a CSS color string: a Color Preset slug → var(--color-slug),
	 *  or a custom hex / rgb(a). Returns '' when unset. */
	function sc_announce_color( $val ) {
		if ( ! is_array( $val ) || ! function_exists( 'sc_normalize_color_value' ) ) { return ''; }
		$norm = sc_normalize_color_value( $val, 'text' );
		if ( ! empty( $norm['style'] ) && preg_match( '/(#[0-9a-fA-F]{3,8}|(?:rgb|hsl)a?\([^)]*\))/', $norm['style'], $m ) ) {
			return $m[1];
		}
		if ( ! empty( $norm['class'] ) ) {
			$slug = sanitize_html_class( preg_replace( '/^(?:text|bg|color)-/', '', $norm['class'] ) );
			if ( $slug !== '' ) { return 'var(--color-' . $slug . ')'; }
		}
		return '';
	}
}

if ( ! function_exists( 'sc_announce_render' ) ) {
	function sc_announce_render( $atts ) {
		$get = function ( $k, $d = '' ) use ( $atts ) {
			if ( function_exists( 'fw_akg' ) ) { $v = fw_akg( $k, $atts, null ); if ( $v !== null ) { return $v; } }
			return isset( $atts[ $k ] ) ? $atts[ $k ] : $d;
		};

		$tag_text = trim( (string) $get( 'tag_text', '' ) );
		$message  = trim( (string) $get( 'message', '' ) );
		$link     = trim( (string) $get( 'link', '' ) );

		if ( $tag_text === '' && $message === '' ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-announce__empty">' . esc_html__( 'Add a message (or a sub-tag) for the pill.', 'fw' ) . '</div>';
			}
			return '';
		}

		// --- whitelisted design modifiers ---
		$style   = in_array( $get( 'style', 'soft' ), array( 'soft', 'outline', 'solid', 'subtle', 'ghost', 'gradient', 'glass' ), true ) ? $get( 'style', 'soft' ) : 'soft';
		$shape   = in_array( $get( 'shape', 'pill' ), array( 'pill', 'rounded', 'square' ), true ) ? $get( 'shape', 'pill' ) : 'pill';
		$size    = in_array( $get( 'size', 'md' ), array( 'sm', 'md', 'lg' ), true ) ? $get( 'size', 'md' ) : 'md';
		$align   = in_array( $get( 'align', 'start' ), array( 'start', 'center', 'end' ), true ) ? $get( 'align', 'start' ) : 'start';
		$tstyle  = in_array( $get( 'tag_style', 'filled' ), array( 'filled', 'soft', 'outline', 'none' ), true ) ? $get( 'tag_style', 'filled' ) : 'filled';
		$hover   = in_array( $get( 'hover', 'lift' ), array( 'none', 'lift', 'glow', 'slide' ), true ) ? $get( 'hover', 'lift' ) : 'lift';
		$leading = in_array( $get( 'leading', 'none' ), array( 'none', 'dot', 'pulse', 'icon' ), true ) ? $get( 'leading', 'none' ) : 'none';

		// --- colors → CSS vars on the pill ---
		$vars = array();
		$pill_c = sc_announce_color( $get( 'pill_color', null ) );  if ( $pill_c !== '' ) { $vars['--ap-color']     = $pill_c; }
		$text_c = sc_announce_color( $get( 'text_color', null ) );  if ( $text_c !== '' ) { $vars['--ap-text']      = $text_c; }
		$tag_c  = sc_announce_color( $get( 'tag_color', null ) );   if ( $tag_c  !== '' ) { $vars['--ap-tag-color'] = $tag_c; }
		if ( $style === 'gradient' ) {
			$gf = sc_announce_color( $get( 'gradient_from', null ) ); if ( $gf !== '' ) { $vars['--ap-grad-from'] = $gf; }
			$gt = sc_announce_color( $get( 'gradient_to', null ) );   if ( $gt !== '' ) { $vars['--ap-grad-to']   = $gt; }
		}
		foreach ( array( 'pill_color', 'text_color', 'tag_color', 'gradient_from', 'gradient_to' ) as $ck ) { unset( $atts[ $ck ] ); }

		// --- icons (icon-v2 → central renderer; slot class stays on the <i>) ---
		$render_icon = function ( $v, $slot ) {
			if ( function_exists( 'sc_icon_render' ) ) {
				return sc_icon_render( $v, array( 'font_class' => $slot ) );
			}
			$c = ( is_array( $v ) && ! empty( $v['icon-class'] ) && is_string( $v['icon-class'] ) ) ? $v['icon-class'] : '';
			return $c !== '' ? '<i class="' . esc_attr( $slot ) . ' ' . esc_attr( $c ) . '" aria-hidden="true"></i>' : '';
		};
		$lead_icon  = $leading === 'icon' ? $render_icon( $get( 'leading_icon', null ), 'ap-pill__lead' ) : '';
		$trail_icon = $render_icon( $get( 'trailing_icon', null ), 'ap-pill__trail' );

		// --- inner content (clean DOM: classes only on structural spans) ---
		$inner = '';
		if ( $leading === 'dot' )        { $inner .= '<span class="ap-pill__dot" aria-hidden="true"></span>'; }
		elseif ( $leading === 'pulse' )  { $inner .= '<span class="ap-pill__dot ap-pill__dot--pulse" aria-hidden="true"></span>'; }
		elseif ( $lead_icon !== '' )     { $inner .= $lead_icon; }
		if ( $tag_text !== '' && $tstyle !== 'none' ) { $inner .= '<span class="ap-pill__tag">' . esc_html( $tag_text ) . '</span>'; }
		elseif ( $tag_text !== '' )                   { $inner .= '<span class="ap-pill__tag ap-pill__tag--plain">' . esc_html( $tag_text ) . '</span>'; }
		if ( $message !== '' )    { $inner .= '<span class="ap-pill__msg">' . esc_html( $message ) . '</span>'; }
		if ( $trail_icon !== '' ) { $inner .= $trail_icon; }

		// --- SEO link attributes ---
		$aria  = trim( (string) $get( 'aria_label', '' ) );
		$title = trim( (string) $get( 'title_attr', '' ) );
		$aria_attr  = $aria  !== '' ? ' aria-label="' . esc_attr( $aria ) . '"' : '';
		$title_attr = $title !== '' ? ' title="' . esc_attr( $title ) . '"' : '';

		$link_attrs = '';
		if ( $link !== '' ) {
			$home_host = function_exists( 'home_url' ) ? parse_url( home_url(), PHP_URL_HOST ) : '';
			$is_ext = false;
			if ( preg_match( '#^https?://#i', $link ) ) {
				$h = parse_url( $link, PHP_URL_HOST );
				$is_ext = ( $h && $home_host && strcasecmp( $h, $home_host ) !== 0 );
			}
			$lt  = $get( 'link_target', 'auto' );
			$tgt = '_blank' === $lt ? '_blank' : ( '_self' === $lt ? '_self' : ( $is_ext ? '_blank' : '' ) );
			$rel = array();
			if ( $is_ext || $tgt === '_blank' ) { $rel[] = 'noopener'; $rel[] = 'noreferrer'; }
			if ( $get( 'rel_nofollow', 'no' )  === 'yes' ) { $rel[] = 'nofollow'; }
			if ( $get( 'rel_sponsored', 'no' ) === 'yes' ) { $rel[] = 'sponsored'; }
			if ( $get( 'rel_ugc', 'no' )       === 'yes' ) { $rel[] = 'ugc'; }
			$link_attrs = ' href="' . esc_url( $link ) . '"'
				. ( $tgt !== '' ? ' target="' . esc_attr( $tgt ) . '"' : '' )
				. ( $rel ? ' rel="' . esc_attr( implode( ' ', array_unique( $rel ) ) ) . '"' : '' );
		}

		// --- dismissible ---
		$dismissible = $get( 'dismissible', 'no' ) === 'yes';
		$dismiss_id  = sanitize_html_class( (string) $get( 'dismiss_id', '' ) );
		$dismissible = $dismissible && $dismiss_id !== '';
		$close = $dismissible ? '<button type="button" class="ap-pill__close" aria-label="' . esc_attr__( 'Dismiss', 'fw' ) . '">&times;</button>' : '';

		// --- assemble the pill: whole pill is the <a> when linked & not dismissible; otherwise a <span>
		//     wrapper (the content links, the × stays a real sibling button — valid HTML). ---
		if ( $link !== '' && ! $dismissible ) {
			$pill_open  = '<a class="ap-pill"' . $link_attrs . $aria_attr . $title_attr;
			$pill_body  = '>' . $inner;
			$pill_close = '</a>';
		} elseif ( $link !== '' ) {
			$pill_open  = '<span class="ap-pill"';
			$pill_body  = '><a class="ap-pill__link"' . $link_attrs . $aria_attr . $title_attr . '>' . $inner . '</a>' . $close;
			$pill_close = '</span>';
		} else {
			$pill_open  = '<span class="ap-pill"' . $aria_attr . $title_attr;
			$pill_body  = '>' . $inner . $close;
			$pill_close = '</span>';
		}

		// --- outer wrapper: alignment + margin/padding + the unique class + Advanced CSS class ---
		$classes = array( 'fw-announce', 'ap-' . $style, 'ap-shape-' . $shape, 'ap-' . $size, 'ap-align-' . $align, 'ap-tag-' . $tstyle, 'ap-hover-' . $hover );
		$atts['base_class']       = 'fw-announce';
		$atts['unique_id_prefix'] = 'ap-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $vars ) {
			$style_str = '';
			foreach ( $vars as $k => $v ) { $style_str .= $k . ':' . $v . ';'; }
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], '; ' ) . ';' : '' ) . $style_str;
		}
		if ( $dismissible ) { $attr['data-ap-dismiss'] = $dismiss_id; }

		// --- optional SpecialAnnouncement JSON-LD (genuine announcements only) ---
		$schema = '';
		if ( $get( 'schema_enable', 'no' ) === 'yes' ) {
			$sname = trim( (string) $get( 'schema_name', '' ) );
			if ( $sname === '' ) { $sname = $message !== '' ? $message : $tag_text; }
			$data = array( '@context' => 'https://schema.org', '@type' => 'SpecialAnnouncement', 'name' => $sname, 'text' => trim( $tag_text . ' ' . $message ) );
			$sdate = trim( (string) $get( 'schema_date', '' ) );
			if ( $sdate !== '' ) { $data['datePosted'] = $sdate; }
			if ( $link !== '' )  { $data['url'] = $link; }
			$json = wp_json_encode( $data );
			if ( $json ) { $schema = '<script type="application/ld+json">' . $json . '</script>'; }
		}

		return '<div ' . fw_attr_to_html( $attr ) . '>' . $pill_open . $pill_body . $pill_close . $schema . '</div>';
	}
}

// Enqueue ONLY the icon-v2 pack(s) the chosen leading / trailing icons need (not every pack).
if ( function_exists( 'fw' ) && isset( fw()->backend ) ) {
	$ot = fw()->backend->option_type( 'icon-v2' );
	if ( $ot && isset( $ot->packs_loader ) && $ot->packs_loader ) {
		foreach ( array( 'leading_icon', 'trailing_icon' ) as $ik ) {
			if ( ! empty( $atts[ $ik ] ) && is_array( $atts[ $ik ] ) && ! empty( $atts[ $ik ]['icon-class'] ) ) {
				$ot->packs_loader->enqueue_pack_for_icon( $atts[ $ik ] );
			}
		}
	}
}

echo sc_announce_render( $atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
