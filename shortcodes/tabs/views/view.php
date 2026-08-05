<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Tabs — frontend render.
 *
 * One shared markup (nav list + panes) built by two closures and composed per
 * layout (horizontal / vertical / media). The visual style is the DESIGN registry
 * (views/parts/registry.php) resolved to a `tabs--design-<key>` + `design-<key>`
 * class on the wrapper. Accessibility: WAI-ARIA tabs — role=tablist/tab/tabpanel,
 * aria-selected/controls/labelledby, aria-orientation, and a SERVER-rendered roving
 * tabindex (active tab tabindex=0, others -1) so the tablist is correct before JS.
 *
 * @var array $atts
 */

/* Per-element colour picks (kept off the wrapper; applied across all tabs). */
$tab_title_styling   = sc_extract_styling_atts( $atts, array( 'tab_title_color' ) );
$tab_content_styling = sc_extract_styling_atts( $atts, array( 'tab_content_color' ) );
$tab_title_class     = ! empty( $tab_title_styling['classes'] )   ? ' ' . implode( ' ', $tab_title_styling['classes'] )   : '';
$tab_content_class   = ! empty( $tab_content_styling['classes'] ) ? ' ' . implode( ' ', $tab_content_styling['classes'] ) : '';
$tab_title_style_attr   = ! empty( $tab_title_styling['styles'] )   ? ' style="' . esc_attr( implode( '; ', $tab_title_styling['styles'] ) ) . '"'   : '';
$tab_content_style_attr = ! empty( $tab_content_styling['styles'] ) ? ' style="' . esc_attr( implode( '; ', $tab_content_styling['styles'] ) ) . '"' : '';

$atts['base_class']       = 'tabs';
$atts['unique_id_prefix'] = 'tb-';
// Base id: the user's CSS ID when set (→ STABLE tab/pane ids, so #hash deep-links
// survive reloads), else a per-render unique id (session-only deep-links).
$tabs_id = ! empty( $atts['css_id'] ) ? sanitize_html_class( (string) $atts['css_id'] ) : wp_unique_id( 'tabs-' );
$atts['css_id'] = $tabs_id;

/* --- Resolve the DESIGN (registry). Legacy `tab_style` values (tabs/pills/
   underline/segmented) are kept as keys so old instances still render. --- */
$valid = function_exists( 'fw_sc_designs' )
	? array_keys( fw_sc_designs( 'tabs' ) )
	: array( 'underline', 'tabs', 'pills', 'segmented', 'boxed', 'minimal', 'buttons', 'popover' );
// Read the EXPLICIT design att directly. (fw_sc_design_resolve auto-defaults an ABSENT
// value to the first registry key, which would mask the legacy `tab_style` on old instances.)
$design = (string) fw_akg( 'design', $atts, fw_akg( 'design_settings/design', $atts, '' ) );
if ( $design === '' || ! in_array( $design, $valid, true ) ) { $design = 'underline'; }

/* Settings */
$layout       = ( ! empty( $atts['layout'] ) && $atts['layout'] === 'media' ) ? 'media' : 'content';
$is_vertical  = ! empty( $atts['orientation'] ) && $atts['orientation'] === 'vertical';
$alignment    = ! empty( $atts['alignment'] ) ? $atts['alignment'] : 'start';
$justified    = ! empty( $atts['justified'] ) && $atts['justified'] === 'yes';
$tab_width    = ! empty( $atts['tab_width'] ) ? $atts['tab_width'] : ( $justified ? 'equal' : 'auto' ); // auto | fill | equal (legacy justified → equal)
$fade_enabled = ! empty( $atts['fade'] ) && $atts['fade'] === 'yes';
$media_side   = ( ! empty( $atts['media_side'] ) && $atts['media_side'] === 'left' ) ? 'left' : 'right';
$activate_on  = ( ! empty( $atts['activate_on'] ) && $atts['activate_on'] === 'hover' ) ? 'hover' : 'click';
$activation   = ( ! empty( $atts['activation'] ) && $atts['activation'] === 'manual' ) ? 'manual' : 'automatic';
$mobile       = ! empty( $atts['mobile'] ) ? $atts['mobile'] : 'none';
$autoplay     = ! empty( $atts['autoplay'] ) && $atts['autoplay'] === 'yes';
$interval_ms  = max( 2, min( 12, (int) ( $atts['autoplay_interval'] ?? 5 ) ) ) * 1000;

/* Wrapper class + behaviour data-attrs. */
$atts['css_class'] = trim(
	( ! empty( $atts['css_class'] ) ? $atts['css_class'] . ' ' : '' )
	. 'tabs-container tabs--design-' . sanitize_html_class( $design ) . ' design-' . sanitize_html_class( $design )
);
$attr = sc_build_wrapper_attr( $atts );
if ( $activate_on === 'hover' ) { $attr['data-fw-activate'] = 'hover'; }
$attr['data-fw-activation'] = $activation;
if ( $autoplay ) { $attr['data-fw-autoplay'] = (string) $interval_ms; }
if ( $mobile !== 'none' ) { $attr['data-fw-mobile'] = sanitize_html_class( $mobile ); }
if ( ! empty( $atts['deep_link'] ) && $atts['deep_link'] === 'yes' ) { $attr['data-fw-deeplink'] = '1'; }
if ( ! empty( $atts['remember'] ) && $atts['remember'] === 'yes' ) { $attr['data-fw-remember'] = '1'; }
if ( $layout === 'media' ) {
	$attr['class'] = ( isset( $attr['class'] ) ? $attr['class'] . ' ' : '' ) . 'tabs-container--media tabs-container--media-' . $media_side;
}

/* Nav class: design → nav-<key> skin, plus alignment / justified (horizontal only). */
$nav_skin = array(
	'underline' => 'nav-underline', 'pills' => 'nav-pills',
	'segmented' => 'nav-segmented', 'boxed' => 'nav-boxed', 'minimal' => 'nav-minimal',
	'buttons'   => 'nav-buttons', 'popover' => 'nav-popover',
);
// Built-in designs add their nav-<key> skin class; an installed skin PACK adds none here
// (its CSS targets the wrapper's design-<key> class instead).
$nav_class = 'nav' . ( isset( $nav_skin[ $design ] ) ? ' ' . $nav_skin[ $design ] : '' );
if ( ! $is_vertical && $layout !== 'media' ) {
	$nav_class .= ' justify-content-' . $alignment;
	if ( $tab_width === 'equal' ) { $nav_class .= ' nav-justified'; }    // equal-width (flex-basis:0)
	elseif ( $tab_width === 'fill' ) { $nav_class .= ' nav-fill'; }      // proportional grow
}

$tabs = fw_akg( 'tabs', $atts, array() );
if ( ! is_array( $tabs ) ) { $tabs = array(); }
$has_active = array_filter( $tabs, fn( $t ) => ! empty( $t['is_active'] ) && $t['is_active'] === 'yes' );

/* Resolve a tab's Image (upload value) → responsive <img> for the media layout. */
$sc_tabs_img_html = function ( $val, $alt_fallback = '' ) {
	$url = ''; $id = 0;
	if ( is_array( $val ) ) {
		if ( ! empty( $val['url'] ) ) { $url = $val['url']; }
		elseif ( ! empty( $val['data']['icon'] ) ) { $url = $val['data']['icon']; }
		if ( ! empty( $val['attachment_id'] ) ) { $id = (int) $val['attachment_id']; }
		if ( $url === '' && $id && function_exists( 'wp_get_attachment_url' ) ) { $url = wp_get_attachment_url( $id ); }
	} elseif ( is_numeric( $val ) && function_exists( 'wp_get_attachment_url' ) ) {
		$id = (int) $val; $url = wp_get_attachment_url( $id );
	}
	if ( $url === '' ) { return ''; }
	$alt = $alt_fallback;
	if ( $id && function_exists( 'get_post_meta' ) ) {
		$a = get_post_meta( $id, '_wp_attachment_image_alt', true );
		if ( $a !== '' ) { $alt = $a; }
	}
	if ( $id && function_exists( 'wp_get_attachment_image' ) ) {
		return wp_get_attachment_image( $id, 'large', false, array( 'class' => 'tabs-media__img', 'alt' => $alt, 'decoding' => 'async', 'loading' => 'lazy' ) );
	}
	return '<img class="tabs-media__img" src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" decoding="async" loading="lazy" />';
};

/* ---- Shared markup builders (de-duplicated across all layouts) ------------ */
$tab_active = function ( $tab, $i ) use ( $has_active ) {
	$a = ! empty( $tab['is_active'] ) && $tab['is_active'] === 'yes';
	if ( $i === 0 && ! $has_active ) { $a = true; }
	return $a;
};

$render_nav = function ( $extra_ul_class = '' ) use ( $tabs, $tabs_id, $nav_class, $is_vertical, $tab_active, $tab_title_class, $tab_title_style_attr, $sc_tabs_img_html ) {
	$orient = $is_vertical ? ' aria-orientation="vertical"' : '';
	$out  = '<ul class="' . esc_attr( trim( $nav_class . ' ' . $extra_ul_class ) ) . '" id="' . esc_attr( $tabs_id . '-tablist' ) . '" role="tablist"' . $orient . '>';
	foreach ( $tabs as $key => $tab ) {
		$active = $tab_active( $tab, $key );
		$tab_id = $tabs_id . '-' . ( $key + 1 );
		$disabled = ! empty( $tab['disabled'] ) && $tab['disabled'] === 'yes';
		$icon_html = '';
		if ( ! empty( $tab['icon'] ) && function_exists( 'sc_icon_render' ) ) {
			$icon_html = '<span class="tab-icon">' . sc_icon_render( $tab['icon'], array( 'aria_hidden' => true ) ) . '</span>';
		}
		$out .= '<li class="nav-item" role="presentation">';
		$out .= '<button class="nav-link' . ( $active ? ' active' : '' ) . ( $disabled ? ' disabled' : '' ) . esc_attr( $tab_title_class ) . '"' . $tab_title_style_attr
			. ' id="' . esc_attr( $tab_id . '-tab' ) . '" data-fw-toggle="tab" data-fw-target="#' . esc_attr( $tab_id ) . '"'
			. ' type="button" role="tab" aria-controls="' . esc_attr( $tab_id ) . '" aria-selected="' . ( $active ? 'true' : 'false' ) . '"'
			. ' tabindex="' . ( $active ? '0' : '-1' ) . '"' . ( $disabled ? ' aria-disabled="true"' : '' ) . '>'
			. $icon_html . esc_html( isset( $tab['tab_title'] ) ? $tab['tab_title'] : '' )
			. ( ! empty( $tab['badge'] ) ? '<span class="tab-badge">' . esc_html( $tab['badge'] ) . '</span>' : '' )
			. '</button></li>';
	}
	$out .= '</ul>';
	return $out;
};

$render_panes = function ( $media = false, $extra_wrap_class = '' ) use ( $tabs, $tabs_id, $tab_active, $fade_enabled, $tab_content_class, $tab_content_style_attr, $sc_tabs_img_html ) {
	$out = '<div class="tab-content ' . esc_attr( $extra_wrap_class ) . '" id="' . esc_attr( $tabs_id ) . '-content">';
	foreach ( $tabs as $key => $tab ) {
		$active = $tab_active( $tab, $key );
		$tab_id = $tabs_id . '-' . ( $key + 1 );
		$cls    = 'tab-pane' . ( $fade_enabled ? ' fade' : '' ) . ( $active ? ' show active' : '' ) . esc_attr( $tab_content_class );
		$out   .= '<div class="' . $cls . '"' . $tab_content_style_attr . ' id="' . esc_attr( $tab_id ) . '" role="tabpanel" tabindex="0" aria-labelledby="' . esc_attr( $tab_id . '-tab' ) . '">';
		if ( $media ) {
			$img     = $sc_tabs_img_html( isset( $tab['tab_image'] ) ? $tab['tab_image'] : '', isset( $tab['tab_title'] ) ? $tab['tab_title'] : '' );
			$caption = isset( $tab['tab_content'] ) ? trim( (string) $tab['tab_content'] ) : '';
			$out    .= '<figure class="tabs-media__figure">' . $img
				. ( $caption !== '' ? '<figcaption class="tabs-media__caption">' . do_shortcode( $tab['tab_content'] ) . '</figcaption>' : '' )
				. '</figure>';
		} else {
			$out .= do_shortcode( isset( $tab['tab_content'] ) ? $tab['tab_content'] : '' );
		}
		$out .= '</div>';
	}
	$out .= '</div>';
	return $out;
};

echo '<div ' . fw_attr_to_html( $attr ) . '>';

if ( $layout === 'media' ) {
	$list = '<div class="fw-col-md-4 tabs-media__list">' . $render_nav( 'flex-column' ) . '</div>';
	$mcol = '<div class="fw-col-md-8 tabs-media__media">' . $render_panes( true, 'tabs-media__panel' ) . '</div>';
	echo '<div class="fw-row tabs-media__row">' . ( $media_side === 'left' ? $mcol . $list : $list . $mcol ) . '</div>'; // phpcs:ignore
} elseif ( $is_vertical ) {
	echo '<div class="fw-row"><div class="fw-col-3">' . $render_nav( 'flex-column' ) . '</div>'
		. '<div class="fw-col-9">' . $render_panes() . '</div></div>'; // phpcs:ignore
} else {
	echo $render_nav() . $render_panes(); // phpcs:ignore
}

echo '</div>';
