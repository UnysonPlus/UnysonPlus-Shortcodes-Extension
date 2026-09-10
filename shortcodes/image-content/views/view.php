<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */

if ( empty( $atts['image'] ) && empty( $atts['content'] ) ) {
	return;
}

// Route content color + background to the inner content column (kept off the wrapper).
$content_styling = sc_extract_styling_atts( $atts, array( 'content_color', 'content_bg' ) );
$content_extras  = $content_styling['classes'];

$atts['base_class']       = 'image-content';
$atts['unique_id_prefix'] = 'ic-';
$atts['extra_attrs']      = [];

// sc_build_wrapper_attr already applies the Styling-tab classes (incl. the new
// Margin & Padding composite) via its `sc_build_wrapper_attr` filter, so the
// wrapper carries them without an extra call here.
$attr = sc_build_wrapper_attr( $atts );

// Wrapper collapse. When the wrapper carries nothing — no Margin & Padding, no
// entrance Animation, no Advanced-tab option (CSS ID/Class, Custom CSS/Attrs,
// Responsive Hide, Overflow, Position) — its identity (image-content + unique
// class) is merged onto the single CSS-grid/stack div, so a bare element renders
// as ONE div instead of two. Any of those options → keep the standard wrapper.
$ic_is_bare = function_exists( 'sc_wrapper_is_bare' ) ? sc_wrapper_is_bare( $atts ) : false;

/**
 * Emit the element shell. $grid_data is an assoc array of data-* attributes for the
 * layout div. Bare → identity + layout class/attrs/style live on one div; otherwise
 * the wrapper div wraps the layout div (today's structure).
 */
$ic_shell = function ( $grid_class, array $grid_data, $grid_style, $cells ) use ( $attr, $ic_is_bare ) {
	if ( $ic_is_bare ) {
		$a          = $attr;
		$a['class'] = trim( ( isset( $a['class'] ) ? $a['class'] : '' ) . ' ' . $grid_class );
		if ( $grid_style !== '' ) {
			$a['style'] = ( isset( $a['style'] ) && $a['style'] !== '' ? rtrim( $a['style'], '; ' ) . ';' : '' ) . $grid_style;
		}
		foreach ( $grid_data as $k => $v ) { $a[ $k ] = $v; }
		return '<div ' . fw_attr_to_html( $a ) . '>' . $cells . '</div>';
	}
	$data_html = '';
	foreach ( $grid_data as $k => $v ) { $data_html .= ' ' . $k . '="' . esc_attr( $v ) . '"'; }
	$style_html = $grid_style !== '' ? ' style="' . esc_attr( $grid_style ) . '"' : '';
	return '<div ' . fw_attr_to_html( $attr ) . '>'
		. '<div class="' . esc_attr( $grid_class ) . '"' . $data_html . $style_html . '>'
		. $cells . '</div></div>';
};

$layout         = ! empty( $atts['layout'] ) ? $atts['layout'] : 'image-left';
$vertical_align = ! empty( $atts['vertical_align'] ) ? $atts['vertical_align'] : 'align-items-center';
$image_fit      = ! empty( $atts['image_fit'] ) ? $atts['image_fit'] : 'contain';
$image_radius   = ! empty( $atts['image_radius'] ) ? $atts['image_radius'] : 'rounded-0';
$image_shadow   = ! empty( $atts['image_shadow'] ) ? $atts['image_shadow'] : '';
$bp             = ! empty( $atts['breakpoint'] ) && in_array( $atts['breakpoint'], [ 'sm', 'md', 'lg' ], true ) ? $atts['breakpoint'] : 'md';
$content_align  = ! empty( $atts['content_align'] ) ? $atts['content_align'] : ''; // left / center / right (sc_alignment_field)

// Gap → a CSS length. The picker stores a gap-scale slug ("0".."5"); legacy saves
// hold the full class ("g-4"). Map to the same rem scale the framework grid used.
$gap_raw   = ! empty( $atts['gap'] ) ? (string) $atts['gap'] : '4';
$gap_slug  = preg_replace( '/^g[xy]?-/', '', $gap_raw );
$gap_scale = array( '0' => '0', '1' => '.25rem', '2' => '.5rem', '3' => '1rem', '4' => '1.5rem', '5' => '3rem' );
$gap_css   = isset( $gap_scale[ $gap_slug ] ) ? $gap_scale[ $gap_slug ] : '1.5rem';

// Vertical alignment → a bare align-items value. Stored as "align-items-{start|center|end}".
$valign_css = str_replace( 'align-items-', '', $vertical_align );
if ( ! in_array( $valign_css, array( 'start', 'center', 'end', 'stretch' ), true ) ) {
	$valign_css = 'center';
}

// Image / content split. Preferred shape = "n/d" (the image fraction; divider snaps
// to twelfths AND fifths). Legacy shapes: a bare int span (out of 12) or the very old
// image-picker "4-8" string.
// $img_fr / $content_fr are the CSS grid column tracks (integer fr units). Fifths
// stay on a /5 scale (1fr 4fr …); everything else resolves onto the /12 scale.
$ratio_raw   = isset( $atts['column_ratio'] ) ? $atts['column_ratio'] : '1/3';
$img_fr      = 4;
$content_fr  = 8;
if ( is_string( $ratio_raw ) && strpos( $ratio_raw, '/' ) !== false ) {
	$pp = explode( '/', $ratio_raw );
	$rn = (int) $pp[0];
	$rd = isset( $pp[1] ) ? (int) $pp[1] : 12;
	if ( $rd === 5 ) {
		$rn         = max( 1, min( 4, $rn ) );
		$img_fr     = $rn;
		$content_fr = 5 - $rn;
	} else {
		$rd         = $rd > 0 ? $rd : 12;
		$span       = max( 1, min( 11, (int) round( $rn * 12 / $rd ) ) );
		$img_fr     = $span;
		$content_fr = 12 - $span;
	}
} elseif ( is_string( $ratio_raw ) && strpos( $ratio_raw, '-' ) !== false ) {
	$parts      = explode( '-', $ratio_raw );
	$span       = max( 1, min( 11, (int) $parts[0] ) );
	$img_fr     = $span;
	$content_fr = 12 - $span;
} else {
	$span       = max( 1, min( 11, (int) $ratio_raw ) );
	$img_fr     = $span;
	$content_fr = 12 - $span;
}

// Image element ----------------------------------------------------------------
// Self-contained: no .img-fluid / .rounded-* / .shadow-* / .w-100 utility classes.
// Base sizing (max-width/height) is in the component stylesheet; radius, shadow and
// width:100% (for cover / fixed-ratio) are emitted inline.
$img_classes     = [ 'image-content__image' ];
$img_style_parts = [];

$radius_map = [ 'rounded-0' => '0', 'rounded-2' => '.375rem', 'rounded-3' => '.5rem', 'rounded-4' => '1rem', 'rounded-circle' => '50%' ];
if ( isset( $radius_map[ $image_radius ] ) && $radius_map[ $image_radius ] !== '0' ) {
	$img_style_parts[] = 'border-radius:' . $radius_map[ $image_radius ];
}
$shadow_map = [
	'shadow-sm' => '0 .125rem .25rem rgba(0,0,0,.075)',
	'shadow'    => '0 .5rem 1rem rgba(0,0,0,.15)',
	'shadow-lg' => '0 1rem 3rem rgba(0,0,0,.175)',
];
if ( ! empty( $image_shadow ) && isset( $shadow_map[ $image_shadow ] ) ) {
	$img_style_parts[] = 'box-shadow:' . $shadow_map[ $image_shadow ];
}

$ratio_map   = [ '1x1' => '1 / 1', '4x3' => '4 / 3', '3x2' => '3 / 2', '16x9' => '16 / 9', '3x4' => '3 / 4' ];
$image_ratio = ! empty( $atts['image_ratio'] ) && isset( $ratio_map[ $atts['image_ratio'] ] ) ? $atts['image_ratio'] : '';

if ( $image_ratio !== '' ) {
	$img_style_parts[] = 'width:100%';
	$img_style_parts[] = 'aspect-ratio:' . $ratio_map[ $image_ratio ];
	$img_style_parts[] = 'object-fit:' . ( $image_fit === 'contain' ? 'contain' : 'cover' );
} elseif ( $image_fit === 'cover' ) {
	$img_style_parts[] = 'width:100%';
	$img_style_parts[] = 'object-fit:cover';
	$img_style_parts[] = 'height:100%';
}
$img_style = implode( ';', $img_style_parts );

// Alt comes from the attachment's media-library alt (single source of truth).
$alt = '';
if ( ! empty( $atts['image']['attachment_id'] ) ) {
	$alt = get_post_meta( $atts['image']['attachment_id'], '_wp_attachment_image_alt', true );
}

$img_attr = [
	'src'      => ! empty( $atts['image']['url'] ) ? esc_url( $atts['image']['url'] ) : '',
	'alt'      => esc_attr( $alt ),
	'class'    => esc_attr( implode( ' ', $img_classes ) ),
	'loading'  => 'lazy',
	'decoding' => 'async',
];
if ( $img_style !== '' ) {
	$img_attr['style'] = esc_attr( $img_style );
}

$image_html = '';
if ( ! empty( $atts['image'] ) && ! empty( $atts['image']['url'] ) ) {
	$image_html = '<img ' . fw_attr_to_html( $img_attr ) . '/>';
	if ( ! empty( $atts['image_link'] ) ) {
		$target     = ! empty( $atts['image_link_target'] ) && in_array( $atts['image_link_target'], [ '_self', '_blank' ], true ) ? $atts['image_link_target'] : '_self';
		$rel        = $target === '_blank' ? ' rel="noopener noreferrer"' : '';
		$image_html = '<a href="' . esc_url( $atts['image_link'] ) . '" target="' . esc_attr( $target ) . '"' . $rel . '>' . $image_html . '</a>';
	}
	// Image Style preset (Theme Settings → Components → Image Styles).
	$imgs_cls = function_exists( 'sc_image_style_class' ) ? sc_image_style_class( $atts ) : '';
	if ( $imgs_cls !== '' ) {
		$image_html = '<span class="imgs-wrap ' . esc_attr( $imgs_cls ) . '">' . $image_html . '</span>';
	}
}

// Content column: text + alignment class + optional readability max-width.
$content_html = ! empty( $atts['content'] ) ? do_shortcode( $atts['content'] ) : '';

$align_class     = sc_alignment_class( $content_align ); // text-start / text-center / text-end
$content_classes = [ 'the-content' ];
if ( $align_class !== '' ) {
	$content_classes[] = $align_class;
}
if ( $content_extras ) {
	$content_classes = array_merge( $content_classes, $content_extras );
}
// Per-side content padding (spacing composite, padding mode) → utility classes.
if ( ! empty( $atts['content_padding'] ) ) {
	$content_classes = array_merge( $content_classes, sc_flatten_spacing_value( $atts['content_padding'] ) );
}

$content_styles = ! empty( $content_styling['styles'] ) ? $content_styling['styles'] : [];
$cmw            = isset( $atts['content_max_width'] ) && is_array( $atts['content_max_width'] ) ? $atts['content_max_width'] : [];
if ( ! empty( $cmw['value'] ) ) {
	$unit             = ! empty( $cmw['unit'] ) ? preg_replace( '/[^a-z%]/i', '', $cmw['unit'] ) : 'ch';
	$content_styles[] = 'max-width:' . (float) $cmw['value'] . $unit;
	if ( $content_align === 'center' ) {
		$content_styles[] = 'margin-left:auto';
		$content_styles[] = 'margin-right:auto';
	} elseif ( $content_align === 'right' ) {
		$content_styles[] = 'margin-left:auto';
	}
}
$content_cls  = implode( ' ', $content_classes );
$content_attr = $content_styles ? ' style="' . esc_attr( implode( '; ', $content_styles ) ) . '"' : '';

// ============================ STACKED (image on top) ============================
if ( $layout === 'image-top' ) {
	// Stacked image max-width + alignment (Image Top only).
	$stack_img_styles = [];
	$siw              = isset( $atts['stack_image_width'] ) && is_array( $atts['stack_image_width'] ) ? $atts['stack_image_width'] : [];
	if ( ! empty( $siw['value'] ) ) {
		$su                 = ! empty( $siw['unit'] ) ? preg_replace( '/[^a-z%]/i', '', $siw['unit'] ) : 'px';
		$stack_img_styles[] = 'max-width:' . (float) $siw['value'] . $su;
		$sia                = ! empty( $atts['stack_image_align'] ) ? $atts['stack_image_align'] : 'center';
		if ( $sia === 'center' ) {
			$stack_img_styles[] = 'margin-left:auto';
			$stack_img_styles[] = 'margin-right:auto';
		} elseif ( $sia === 'right' ) {
			$stack_img_styles[] = 'margin-left:auto';
		}
	}
	$stack_img_attr = $stack_img_styles ? ' style="' . esc_attr( implode( ';', $stack_img_styles ) ) . '"' : '';

	$cells = '';
	if ( $image_html ) {
		$cells .= '<div class="the-image image-content__media"' . $stack_img_attr . '>' . $image_html . '</div>';
	}
	$cells .= '<div class="' . esc_attr( $content_cls ) . ' image-content__body"' . $content_attr . '>' . $content_html . '</div>';

	echo $ic_shell( 'image-content__stack', array(), '--ic-gap:' . $gap_css . ';', $cells ); // phpcs:ignore
	return;
}

// ============================ SIDE BY SIDE (left / right) ========================
// CSS grid: the ratio, gap, vertical-align, breakpoint and source-order are carried
// by data-attributes + custom properties (see static/css/styles.css). DOM order is
// always image → content; the stylesheet reverses the columns for image-right and
// applies the mobile Content-First swap.
$mobile_order = ! empty( $atts['mobile_order'] ) ? $atts['mobile_order'] : 'image-first';
$data_layout  = ( $layout === 'image-right' ) ? 'right' : 'left';
// Emit the fr tracks WHOLE (fr is invalid inside calc()); --ic-cols-rev is the
// reversed pair used for image-right.
$grid_style   = sprintf(
	'--ic-cols:%1$dfr %2$dfr;--ic-cols-rev:%2$dfr %1$dfr;--ic-gap:%3$s;--ic-valign:%4$s;',
	$img_fr,
	$content_fr,
	$gap_css,
	$valign_css
);

$cells = '<div class="the-image image-content__media">' . $image_html . '</div>'
	. '<div class="' . esc_attr( $content_cls ) . ' image-content__body"' . $content_attr . '>' . $content_html . '</div>';

echo $ic_shell( 'image-content__grid', array(
	'data-bp'     => $bp,
	'data-layout' => $data_layout,
	'data-mobile' => $mobile_order,
), $grid_style, $cells ); // phpcs:ignore
