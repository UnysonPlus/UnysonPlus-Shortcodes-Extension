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

$attr = sc_build_wrapper_attr( $atts );

$layout         = ! empty( $atts['layout'] ) ? $atts['layout'] : 'image-left';
$vertical_align = ! empty( $atts['vertical_align'] ) ? $atts['vertical_align'] : 'align-items-center';
$image_fit      = ! empty( $atts['image_fit'] ) ? $atts['image_fit'] : 'contain';
$image_radius   = ! empty( $atts['image_radius'] ) ? $atts['image_radius'] : 'rounded-0';
$image_shadow   = ! empty( $atts['image_shadow'] ) ? $atts['image_shadow'] : '';
$bp             = ! empty( $atts['breakpoint'] ) && in_array( $atts['breakpoint'], [ 'sm', 'md', 'lg' ], true ) ? $atts['breakpoint'] : 'md';
$content_align  = ! empty( $atts['content_align'] ) ? $atts['content_align'] : ''; // left / center / right (sc_alignment_field)

// Gap = a gap-scale slug (e.g. "4"); legacy saves hold the full class ("g-4").
$gap_raw = ! empty( $atts['gap'] ) ? $atts['gap'] : '4';
if ( preg_match( '/^g[xy]?-/', $gap_raw ) ) {          // legacy full class
	$gap_class   = $gap_raw;
	$gap_y_class = 'gy-' . preg_replace( '/^g[xy]?-/', '', $gap_raw );
} elseif ( $gap_raw !== '' ) {
	$gap_class   = 'g-' . $gap_raw;
	$gap_y_class = 'gy-' . $gap_raw;
} else {                                                // inherit the site Default Gap
	$gap_class = $gap_y_class = '';
}

// Image / content split. New shape = a slider int (image column span 1–11, on a
// 1–12 scale). Legacy shape = the old image-picker string "4-8".
// Preferred shape = "n/d" (the image fraction; divider snaps to twelfths AND fifths).
// Legacy shapes: a bare int span (out of 12) or the very old image-picker "4-8" string.
// $image_col / $content_col are fw-col class SUFFIXES — a twelfth ("1".."11") or a fifth
// ("15"/"25"/"35"/"45" = 1/5..4/5, matching fw-col-*-{15,25,35,45}).
$ratio_raw   = isset( $atts['column_ratio'] ) ? $atts['column_ratio'] : '1/3';
$image_col   = '4';
$content_col = '8';
if ( is_string( $ratio_raw ) && strpos( $ratio_raw, '/' ) !== false ) {
	$pp = explode( '/', $ratio_raw );
	$rn = (int) $pp[0];
	$rd = isset( $pp[1] ) ? (int) $pp[1] : 12;
	if ( $rd === 5 ) {
		$rn          = max( 1, min( 4, $rn ) );
		$image_col   = $rn . '5';
		$content_col = ( 5 - $rn ) . '5';
	} else {
		$rd          = $rd > 0 ? $rd : 12;
		$span        = max( 1, min( 11, (int) round( $rn * 12 / $rd ) ) );
		$image_col   = (string) $span;
		$content_col = (string) ( 12 - $span );
	}
} elseif ( is_string( $ratio_raw ) && strpos( $ratio_raw, '-' ) !== false ) {
	$parts       = explode( '-', $ratio_raw );
	$span        = max( 1, min( 11, (int) $parts[0] ) );
	$image_col   = (string) $span;
	$content_col = (string) ( 12 - $span );
} else {
	$span        = max( 1, min( 11, (int) $ratio_raw ) );
	$image_col   = (string) $span;
	$content_col = (string) ( 12 - $span );
}

// Image element ----------------------------------------------------------------
$img_classes = [ 'img-fluid' ];
if ( $image_radius !== 'rounded-0' ) {
	$img_classes[] = $image_radius;
}
if ( ! empty( $image_shadow ) ) {
	$img_classes[] = $image_shadow;
}

$ratio_map   = [ '1x1' => '1 / 1', '4x3' => '4 / 3', '3x2' => '3 / 2', '16x9' => '16 / 9', '3x4' => '3 / 4' ];
$image_ratio = ! empty( $atts['image_ratio'] ) && isset( $ratio_map[ $atts['image_ratio'] ] ) ? $atts['image_ratio'] : '';

$img_style_parts = [];
if ( $image_ratio !== '' ) {
	$img_classes[]     = 'w-100';
	$img_style_parts[] = 'aspect-ratio:' . $ratio_map[ $image_ratio ];
	$img_style_parts[] = 'object-fit:' . ( $image_fit === 'contain' ? 'contain' : 'cover' );
} elseif ( $image_fit === 'cover' ) {
	$img_classes[]     = 'w-100';
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
	?>
	<div <?php echo fw_attr_to_html( $attr ); ?>>
		<div class="image-content__stack fw-row<?php echo $gap_y_class ? ' ' . esc_attr( $gap_y_class ) : ''; ?>">
			<?php if ( $image_html ) : ?>
				<div class="the-image fw-col-12"<?php echo $stack_img_attr; ?>><?php echo $image_html; ?></div>
			<?php endif; ?>
			<div class="<?php echo esc_attr( $content_cls ); ?> fw-col-12"<?php echo $content_attr; ?>><?php echo $content_html; ?></div>
		</div>
	</div>
	<?php
	return;
}

// ============================ SIDE BY SIDE (left / right) ========================
$mobile_order          = ! empty( $atts['mobile_order'] ) ? $atts['mobile_order'] : 'image-first';
$image_order_classes   = '';
$content_order_classes = '';

if ( $layout === 'image-left' ) {
	if ( $mobile_order === 'content-first' ) {
		$image_order_classes   = ' fw-order-2 fw-order-' . $bp . '-1';
		$content_order_classes = ' fw-order-1 fw-order-' . $bp . '-2';
	}
} else { // image-right
	if ( $mobile_order === 'content-first' ) {
		$image_order_classes   = ' fw-order-2';
		$content_order_classes = ' fw-order-1';
	} else {
		$image_order_classes   = ' fw-order-' . $bp . '-2';
		$content_order_classes = ' fw-order-' . $bp . '-1';
	}
}

$row_class         = trim( 'fw-row ' . $vertical_align . ( $gap_class ? ' ' . $gap_class : '' ) );
$image_col_class   = 'the-image fw-col-12 fw-col-' . $bp . '-' . $image_col . $image_order_classes;
$content_col_class = $content_cls . ' fw-col-12 fw-col-' . $bp . '-' . $content_col . $content_order_classes;
?>

<div <?php echo fw_attr_to_html( $attr ); ?>>
	<div class="<?php echo esc_attr( $row_class ); ?>">
		<div class="<?php echo esc_attr( $image_col_class ); ?>">
			<?php echo $image_html; ?>
		</div>
		<div class="<?php echo esc_attr( $content_col_class ); ?>"<?php echo $content_attr; ?>>
			<?php echo $content_html; ?>
		</div>
	</div>
</div>
