<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * @var array  $atts
 * @var string $content
 */

// --- Resolve the bleed image (url + attachment id). Upload value shapes:
//     {url}/{data.icon}/{attachment_id}/numeric. ---
$bleed_url    = '';
$bleed_att_id = 0;
$bi = isset( $atts['bleed_image'] ) ? $atts['bleed_image'] : '';
if ( is_array( $bi ) ) {
	if ( ! empty( $bi['url'] ) ) {
		$bleed_url = $bi['url'];
	} elseif ( ! empty( $bi['data']['icon'] ) ) {
		$bleed_url = $bi['data']['icon'];
	} elseif ( ! empty( $bi['attachment_id'] ) && function_exists( 'wp_get_attachment_url' ) ) {
		$bleed_url = wp_get_attachment_url( $bi['attachment_id'] );
	}
	if ( ! empty( $bi['attachment_id'] ) ) {
		$bleed_att_id = (int) $bi['attachment_id'];
	}
} elseif ( is_numeric( $bi ) && function_exists( 'wp_get_attachment_url' ) ) {
	$bleed_url    = wp_get_attachment_url( $bi );
	$bleed_att_id = (int) $bi;
}

// Alt text: an explicit override wins; else the alt saved on the media item.
$bleed_alt = isset( $atts['bleed_image_alt'] ) ? trim( (string) $atts['bleed_image_alt'] ) : '';
if ( $bleed_alt === '' && $bleed_att_id && function_exists( 'get_post_meta' ) ) {
	$bleed_alt = (string) get_post_meta( $bleed_att_id, '_wp_attachment_image_alt', true );
}

// --- Settings ---
$bleed_side     = ( isset( $atts['bleed_image_side'] ) && $atts['bleed_image_side'] === 'left' ) ? 'left' : 'right';
$bleed_ratio    = ! empty( $atts['bleed_image_ratio'] ) ? (string) $atts['bleed_image_ratio'] : '5-7';
$bleed_position = ! empty( $atts['bleed_image_position'] ) ? (string) $atts['bleed_image_position'] : 'center';
$bleed_mobile   = ( isset( $atts['bleed_mobile_stacking'] ) && $atts['bleed_mobile_stacking'] === 'image-first' ) ? 'image-first' : 'content-first';
$bleed_padding  = isset( $atts['bleed_content_padding'] ) ? (string) $atts['bleed_content_padding'] : '3rem';
$bleed_lazy     = ( isset( $atts['bleed_image_lazy'] ) && $atts['bleed_image_lazy'] === 'yes' );

$valign_map    = array( 'align-items-start' => 'flex-start', 'align-items-center' => 'center', 'align-items-end' => 'flex-end' );
$valign_raw    = ! empty( $atts['bleed_vertical_align'] ) ? (string) $atts['bleed_vertical_align'] : 'align-items-center';
$bleed_justify = isset( $valign_map[ $valign_raw ] ) ? $valign_map[ $valign_raw ] : 'center';

// Minimum height — lets the split read as a hero even when content is short.
$minh_map = array( 'sm' => '40vh', 'md' => '60vh', 'lg' => '80vh', 'full' => '100vh' );
$minh_key = isset( $atts['bleed_min_height'] ) ? (string) $atts['bleed_min_height'] : 'none';
$minh     = isset( $minh_map[ $minh_key ] ) ? $minh_map[ $minh_key ] : '';

// Image overlay (scrim) — a tint laid over the bleed image.
$ov_color = ( isset( $atts['bleed_overlay_color'] ) && function_exists( 'sc_color_to_css' ) ) ? sc_color_to_css( $atts['bleed_overlay_color'], '' ) : '';
$ov_op    = isset( $atts['bleed_overlay_opacity'] ) ? max( 0, min( 100, (int) $atts['bleed_overlay_opacity'] ) ) : 0;

// Content-side background (color / gradient / image) via the shared bg-pro emitter.
$bgv      = ( ! empty( $atts['background'] ) && is_array( $atts['background'] ) ) ? $atts['background'] : null;
$bg_style = function_exists( 'sc_bg_pro_style' ) ? sc_bg_pro_style( $bgv ) : '';

$container_class = ( isset( $atts['is_fullwidth'] ) && $atts['is_fullwidth'] )
	? 'fw-container-fluid'
	: 'fw-container';

// Ratio → columns (each pair sums to 12; clamp defensively).
$ratio_parts = explode( '-', $bleed_ratio );
$image_col   = isset( $ratio_parts[0] ) ? (int) $ratio_parts[0] : 5;
$image_col   = max( 1, min( 11, $image_col ) );
$content_col = 12 - $image_col;

$padding_style = ( $bleed_padding !== '0' && $bleed_padding !== '' )
	? 'padding-top:' . esc_attr( $bleed_padding ) . ';padding-bottom:' . esc_attr( $bleed_padding ) . ';'
	: '';

$content_style = 'display:flex;flex-direction:column;justify-content:' . esc_attr( $bleed_justify ) . ';' . $padding_style;

$image_pct   = round( ( $image_col / 12 ) * 100, 4 );
$content_pct = round( ( $content_col / 12 ) * 100, 4 );

// Absolute halves bleed to the viewport edge. The grid below only RESERVES the
// matching space with a spacer column (correct DOM order per side — no order
// classes, which previously leaked to desktop and overlapped the image).
if ( $bleed_side === 'right' ) {
	$bleed_img_style = 'right:0;width:' . $image_pct . '%;';
	$bleed_bg_inline = 'left:0;width:' . $content_pct . '%;' . $bg_style;
} else {
	$bleed_img_style = 'left:0;width:' . $image_pct . '%;';
	$bleed_bg_inline = 'right:0;width:' . $content_pct . '%;' . $bg_style;
}

// Wrapper attributes + classes.
$attr    = function_exists( 'sc_build_wrapper_attr' ) ? sc_build_wrapper_attr( $atts ) : array();
$classes = 'bleed-section bleed-section--m-' . $bleed_mobile;
if ( $minh !== '' ) { $classes .= ' bleed-section--minh'; }
$attr['class'] = ( ! empty( $attr['class'] ) ? $attr['class'] . ' ' : '' ) . $classes;
if ( $minh !== '' ) {
	$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . 'min-height:' . $minh . ';';
}

// Responsive <img> — srcset via wp_get_attachment_image when we have the
// attachment (better LCP than a single full-size src); graceful <img> fallback.
$img_html   = '';
$img_istyle = 'object-position:' . esc_attr( $bleed_position ) . ';';
if ( $bleed_url ) {
	if ( $bleed_att_id && function_exists( 'wp_get_attachment_image' ) ) {
		$img_html = wp_get_attachment_image( $bleed_att_id, 'full', false, array(
			'alt'      => $bleed_alt,
			'class'    => 'bleed-section__image',
			'style'    => $img_istyle,
			'sizes'    => '(max-width: 767.98px) 100vw, ' . $image_pct . 'vw',
			'decoding' => 'async',
			'loading'  => $bleed_lazy ? 'lazy' : 'eager',
		) );
	} else {
		$img_html = '<img class="bleed-section__image" src="' . esc_url( $bleed_url ) . '" alt="' . esc_attr( $bleed_alt ) . '" style="' . $img_istyle . '" decoding="async" loading="' . ( $bleed_lazy ? 'lazy' : 'eager' ) . '" />';
	}
}
?>
<section <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $bg_style !== '' ) : ?>
		<div class="bleed-section__bg" style="<?php echo $bleed_bg_inline; ?>"></div>
	<?php endif; ?>
	<?php if ( $img_html !== '' ) : ?>
		<div class="bleed-section__img" style="<?php echo $bleed_img_style; ?>">
			<?php echo $img_html; // phpcs:ignore ?>
			<?php if ( $ov_color !== '' && $ov_op > 0 ) : ?>
				<span class="bleed-section__overlay" style="background-color:<?php echo esc_attr( $ov_color ); ?>;opacity:<?php echo esc_attr( $ov_op / 100 ); ?>;"></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<div class="<?php echo esc_attr( $container_class ); ?> bleed-section__container">
		<div class="fw-row">
			<?php if ( $bleed_side === 'left' ) : ?>
				<div class="fw-col-md-<?php echo (int) $image_col; ?> bleed-section__spacer" aria-hidden="true"></div>
				<div class="fw-col-md-<?php echo (int) $content_col; ?> bleed-section__content" style="<?php echo $content_style; ?>">
					<?php echo do_shortcode( $content ); ?>
				</div>
			<?php else : ?>
				<div class="fw-col-md-<?php echo (int) $content_col; ?> bleed-section__content" style="<?php echo $content_style; ?>">
					<?php echo do_shortcode( $content ); ?>
				</div>
				<div class="fw-col-md-<?php echo (int) $image_col; ?> bleed-section__spacer" aria-hidden="true"></div>
			<?php endif; ?>
		</div>
	</div>
</section>
