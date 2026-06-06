<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */

// Skip if no image
if ( empty( $atts['image'] ) ) {
	return;
}

$attachment_id = ! empty( $atts['image']['attachment_id'] ) ? $atts['image']['attachment_id'] : 0;

// Width & height are unit-inputs. For each, compile a CSS length string and —
// when the unit is px — an integer used for server-side cropping and the HTML
// width/height attributes (those accept unitless pixels only). Non-px units
// (%, vw, …) only affect display via inline CSS. Legacy bare numbers = px.
$dim = function ( $raw ) {
	$css = '';
	$px  = 0;
	if ( is_array( $raw ) ) {
		$val  = isset( $raw['value'] ) ? trim( (string) $raw['value'] ) : '';
		$unit = isset( $raw['unit'] ) ? $raw['unit'] : 'px';
		if ( $val !== '' && is_numeric( $val ) ) {
			$css = $val . $unit;
			if ( $unit === 'px' ) {
				$px = (int) $val;
			}
		}
	} else {
		$raw = trim( (string) $raw );
		if ( $raw !== '' && is_numeric( $raw ) ) {
			$css = $raw . 'px';
			$px  = (int) $raw;
		}
	}
	return array( $css, max( 0, $px ) );
};

list( $width_css, $width_px )   = $dim( isset( $atts['width'] ) ? $atts['width'] : '' );
list( $height_css, $height_px ) = $dim( isset( $atts['height'] ) ? $atts['height'] : '' );

// Crop to an exact size only when BOTH dimensions are concrete pixels.
if ( $width_px && $height_px && $attachment_id ) {
	$image = fw_resize( $attachment_id, $width_px, $height_px, true );
} else {
	$image = $atts['image']['url'];
}

if ( empty( $image ) ) {
	return;
}

// Alt text from the media library. Never fall back to the URL — a URL as alt
// text is meaningless to screen readers and hurts SEO; an empty alt is correct
// for a decorative image.
$alt = $attachment_id ? (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) : '';

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
if ( $should_wrap ) {
	$img_classes = array( 'img-fluid' );
} else {
	$img_classes = array_values( array_filter( preg_split( '/\s+/', trim( isset( $attr['class'] ) ? $attr['class'] : '' ) ) ) );
	if ( ! in_array( 'img-fluid', $img_classes, true ) ) {
		$img_classes[] = 'img-fluid';
	}
}

// Build the <img>. fw_html_tag escapes every attribute value, so raw strings
// are safe here; esc_url additionally strips unsafe protocols from the src.
$img_attributes = array(
	'src'      => esc_url( $image ),
	'alt'      => $alt,
	'class'    => implode( ' ', $img_classes ),
	'loading'  => 'lazy',
	'decoding' => 'async',
);

// HTML width/height attributes accept unitless pixels only (and help reserve
// layout space → less CLS). Emit them only for px values.
if ( $width_px ) {
	$img_attributes['width'] = $width_px;
}
if ( $height_px ) {
	$img_attributes['height'] = $height_px;
}

// Apply the chosen dimensions as inline CSS so non-pixel units (%, vw, …) work.
// Inline width/height win over the img-fluid helper (which has no !important).
$img_style = '';
if ( $width_css !== '' ) {
	$img_style .= 'width:' . $width_css . ';';
}
if ( $height_css !== '' ) {
	$img_style .= 'height:' . $height_css . ';';
}
if ( $img_style !== '' ) {
	$img_attributes['style'] = $img_style;
}

$link     = ! empty( $atts['link'] ) ? $atts['link'] : '';
$has_link = ( '' !== $link );

// Where the CSS ID lands when there is no wrapper to hold it: on the link if the
// image is linked, otherwise on the image itself (so it is never lost).
if ( ! $should_wrap && ! $has_link && $css_id ) {
	$img_attributes['id'] = $css_id;
}

$img_html = fw_html_tag( 'img', $img_attributes );

if ( $has_link ) {
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

// Render the wrapper only when it carries something (styling / animation / id /
// class / custom attrs). Padding on this wrapper is what makes a background
// color show as a frame around the image.
if ( $should_wrap ) {
	echo '<div ' . fw_attr_to_html( $attr ) . '>' . $inner_html . '</div>';
} else {
	echo $inner_html;
}
