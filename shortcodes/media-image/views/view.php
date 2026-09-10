<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */

// Skip if no image. The editor gets a note instead of silence — see the identical
// branch below for why; this is simply the earlier of the two ways to have none.
if ( empty( $atts['image'] ) ) {
	if ( fw_is_editor_context() ) {
		echo sc_editor_notice( __( 'Choose an image.', 'fw' ) );
	}
	return;
}

$attachment_id = ! empty( $atts['image']['attachment_id'] ) ? $atts['image']['attachment_id'] : 0;

// Image source: attachment ID (enables responsive srcset + exact-crop) or URL.
$image_url = ! empty( $atts['image']['url'] ) ? $atts['image']['url'] : '';
if ( empty( $image_url ) && empty( $attachment_id ) ) {
	// Say so in an editor; render nothing at all to a visitor. Without the message
	// a Gutenberg preview shows "Block rendered as empty", which is what a BROKEN
	// block looks like — the user cannot tell "you have not picked an image yet"
	// from "this is not working".
	if ( fw_is_editor_context() ) {
		echo sc_editor_notice( __( 'Choose an image.', 'fw' ) );
	}
	return;
}

// Wrapper attributes. The sc_build_wrapper_attr filter chain folds the Styling
// tab (background color, margin & padding) and Animations into $attr as classes
// + inline style, so a wrapper is only worth rendering when something actually
// needs it (styling, animation, CSS id/class, custom attrs).
$atts['base_class']       = 'image';
$atts['unique_id_prefix'] = 'img-';
$attr = sc_build_wrapper_attr( $atts );

$should_wrap = function_exists( 'sc_needs_wrapper' )
	? sc_needs_wrapper( $atts )
	: ( ! empty( $atts['css_id'] ) || ! empty( $atts['css_class'] ) );

$css_id = isset( $attr['id'] ) ? $attr['id'] : '';

// Class placement: with a wrapper, the <div> owns base/unique/styling classes
// and the id; the <img> only needs the responsive helper. Without a wrapper the
// <img> (or its <a>) keeps carrying them, exactly as before.
// `.media-image__img` is the shortcode's own self-contained responsive-image rule
// (max-width:100%; height:auto) — no dependency on the builder's global .img-fluid.
if ( $should_wrap ) {
	$img_classes = array( 'media-image__img' );
} else {
	$img_classes = array_values( array_filter( preg_split( '/\s+/', trim( isset( $attr['class'] ) ? $attr['class'] : '' ) ) ) );
	if ( ! in_array( 'media-image__img', $img_classes, true ) ) {
		$img_classes[] = 'media-image__img';
	}
}

$link        = ! empty( $atts['link'] ) ? $atts['link'] : '';
$lightbox_on = ( isset( $atts['lightbox'] ) && 'yes' === $atts['lightbox'] );
$has_link    = ( ! $lightbox_on && '' !== $link ); // lightbox takes precedence over the link

// CSS id lands on the <img> only when no wrapper / link / lightbox anchor will carry it.
$extra_attr = array();
if ( ! $should_wrap && ! $has_link && ! $lightbox_on && $css_id ) {
	$extra_attr['id'] = $css_id;
}

// Modern <img>: responsive srcset (or exact-crop + 2x), width/height attrs for
// CLS, fetchpriority/eager for above-the-fold, lazy otherwise — via fw_image_tag.
$fetchpriority = ( ! empty( $atts['fetchpriority'] ) && 'high' === $atts['fetchpriority'] ) ? 'high' : '';

// Focal crop (optional): an Aspect Ratio drops the image into a ratio box and chooses
// which part shows via object-fit + object-position — "like a background-image position".
$image_ratio    = isset( $atts['image_ratio'] ) ? trim( (string) $atts['image_ratio'] ) : '';
$image_fit      = ( isset( $atts['image_fit'] ) && 'contain' === $atts['image_fit'] ) ? 'contain' : 'cover';
$focal_position = ( '' !== $image_ratio && isset( $atts['focal_position'] ) && '' !== trim( (string) $atts['focal_position'] ) )
	? trim( (string) $atts['focal_position'] ) : 'center center';

$img_html = fw_image_tag(
	$attachment_id ? $attachment_id : $image_url,
	array(
		'width'           => isset( $atts['width'] ) ? $atts['width'] : '',
		'height'          => isset( $atts['height'] ) ? $atts['height'] : '',
		'class'           => implode( ' ', $img_classes ),
		'fetchpriority'   => $fetchpriority,
		'fallback_size'   => 'large',
		'extra_attr'      => $extra_attr,
		'aspect_ratio'    => $image_ratio,
		'object_fit'      => $image_fit,
		'object_position' => $focal_position,
	)
);

if ( '' === $img_html ) {
	return;
}

if ( $lightbox_on ) {
	// Lightbox: click opens the FULL-size image in the shared overlay. A unique group id
	// scopes prev/next to this instance; the shared lightbox JS neutralises the href on
	// load (keeps it a real link for no-JS visitors). Enqueued per-instance in static.php.
	$full = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'full' ) : $image_url;
	if ( ! $full ) { $full = $image_url; }
	$group      = function_exists( 'wp_unique_id' ) ? wp_unique_id( 'mi-lb-' ) : uniqid( 'mi-lb-' );
	$lb_caption = isset( $atts['caption'] ) ? trim( (string) $atts['caption'] ) : '';

	$anchor = '<a class="media-image__lightbox" href="' . esc_url( $full ) . '"'
		. ' data-fw-lightbox="' . esc_attr( $group ) . '"';
	if ( '' !== $lb_caption ) { $anchor .= ' data-fw-caption="' . esc_attr( $lb_caption ) . '"'; }
	if ( ! $should_wrap && $css_id ) { $anchor .= ' id="' . esc_attr( $css_id ) . '"'; }
	$anchor .= '>';

	$inner_html = $anchor . $img_html . '</a>';
} elseif ( $has_link ) {
	$target = ( ! empty( $atts['target'] ) && in_array( $atts['target'], array( '_self', '_blank' ), true ) )
		? $atts['target']
		: '_self';

	// Build the anchor by hand: esc_url (not fw_html_tag) so query-string
	// ampersands in the link aren't double-encoded, and unsafe protocols
	// (e.g. javascript:) are stripped.
	$anchor = '<a href="' . esc_url( $link ) . '" target="' . esc_attr( $target ) . '"';

	// Harden links that open a new tab against window.opener hijacking / referrer leakage.
	if ( '_blank' === $target ) {
		$anchor .= ' rel="noopener noreferrer"';
	}
	if ( ! $should_wrap && $css_id ) {
		$anchor .= ' id="' . esc_attr( $css_id ) . '"';
	}
	$anchor .= '>';

	$inner_html = $anchor . $img_html . '</a>';
} else {
	$inner_html = $img_html;
}

// Image Style preset (Theme Settings → Components → Image Styles): wrap the image
// (or its link) in a dedicated `.imgs-wrap imgs-{slug}` element — the base rule needs
// a positioned wrapper for the scrim/duotone layers, and the `<img>` inside consumes
// the preset's inherited CSS custom properties.
$imgs_cls = function_exists( 'sc_image_style_class' ) ? sc_image_style_class( $atts ) : '';
if ( '' !== $imgs_cls ) {
	$inner_html = '<span class="imgs-wrap ' . esc_attr( $imgs_cls ) . '">' . $inner_html . '</span>';
}

// Caption: wrap the image (and any link/lightbox/style layer) in a semantic <figure>
// with a <figcaption> beneath it. Kept outside the styled image so the caption sits
// below the frame, not on top of it.
$caption = isset( $atts['caption'] ) ? trim( (string) $atts['caption'] ) : '';
if ( '' !== $caption ) {
	$inner_html = '<figure class="media-image__figure">' . $inner_html
		. '<figcaption class="media-image__caption">' . esc_html( $caption ) . '</figcaption></figure>';
}

// Render the wrapper only when it carries something (styling / animation / id /
// class / custom attrs). Padding on this wrapper is what makes a background
// color show as a frame around the image.
if ( $should_wrap ) {
	echo '<div ' . fw_attr_to_html( $attr ) . '>' . $inner_html . '</div>';
} else {
	echo $inner_html;
}
