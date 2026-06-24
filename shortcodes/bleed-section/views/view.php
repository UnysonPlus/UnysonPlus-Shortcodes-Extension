<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * @var array  $atts
 * @var string $content
 */

// --- Resolve the bleed image URL (upload value shapes: {url}/{data.icon}/{attachment_id}/numeric). ---
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

$bleed_alt = '';
if ( $bleed_att_id && function_exists( 'get_post_meta' ) ) {
	$bleed_alt = get_post_meta( $bleed_att_id, '_wp_attachment_image_alt', true );
}

// --- Settings ---
$bleed_side     = ( isset( $atts['bleed_image_side'] ) && $atts['bleed_image_side'] === 'left' ) ? 'left' : 'right';
$bleed_ratio    = ! empty( $atts['bleed_image_ratio'] ) ? (string) $atts['bleed_image_ratio'] : '5-7';
$bleed_position = ! empty( $atts['bleed_image_position'] ) ? (string) $atts['bleed_image_position'] : 'center';
$bleed_mobile   = ! empty( $atts['bleed_mobile_stacking'] ) ? (string) $atts['bleed_mobile_stacking'] : 'content-first';
$bleed_padding  = isset( $atts['bleed_content_padding'] ) ? (string) $atts['bleed_content_padding'] : '3rem';

$valign_map    = array( 'align-items-start' => 'flex-start', 'align-items-center' => 'center', 'align-items-end' => 'flex-end' );
$valign_raw    = ! empty( $atts['bleed_vertical_align'] ) ? (string) $atts['bleed_vertical_align'] : 'align-items-center';
$bleed_justify = isset( $valign_map[ $valign_raw ] ) ? $valign_map[ $valign_raw ] : 'center';

// Content-side background (color / gradient / image) via the shared bg-pro emitter.
$bgv      = ( ! empty( $atts['background'] ) && is_array( $atts['background'] ) ) ? $atts['background'] : null;
$bg_style = function_exists( 'sc_bg_pro_style' ) ? sc_bg_pro_style( $bgv ) : '';

$container_class = ( isset( $atts['is_fullwidth'] ) && $atts['is_fullwidth'] )
	? 'fw-container-fluid'
	: 'fw-container';

$ratio_parts = explode( '-', $bleed_ratio );
$image_col   = isset( $ratio_parts[0] ) ? (int) $ratio_parts[0] : 5;
$content_col = isset( $ratio_parts[1] ) ? (int) $ratio_parts[1] : 7;

$padding_style = ( $bleed_padding !== '0' && $bleed_padding !== '' )
	? 'padding-top:' . esc_attr( $bleed_padding ) . ';padding-bottom:' . esc_attr( $bleed_padding ) . ';'
	: '';

$content_style = 'display:flex;flex-direction:column;justify-content:' . esc_attr( $bleed_justify ) . ';' . $padding_style;

$content_order = '';
$image_order   = '';
if ( $bleed_side === 'right' ) {
	if ( $bleed_mobile === 'image-first' ) {
		$content_order = ' fw-order-2 fw-order-md-1';
		$image_order   = ' fw-order-1 fw-order-md-2';
	}
} else {
	if ( $bleed_mobile === 'content-first' ) {
		$image_order   = ' fw-order-2';
		$content_order = ' fw-order-1';
	} else {
		$image_order   = ' fw-order-md-2';
		$content_order = ' fw-order-md-1';
	}
}

$image_pct   = round( ( $image_col / 12 ) * 100, 6 );
$content_pct = round( ( $content_col / 12 ) * 100, 6 );

if ( $bleed_side === 'right' ) {
	$bleed_img_style = 'right:0;width:' . $image_pct . '%;';
	$bleed_bg_inline = 'left:0;width:' . $content_pct . '%;' . $bg_style;
} else {
	$bleed_img_style = 'left:0;width:' . $image_pct . '%;';
	$bleed_bg_inline = 'right:0;width:' . $content_pct . '%;' . $bg_style;
}

$attr = function_exists( 'sc_build_wrapper_attr' ) ? sc_build_wrapper_attr( $atts ) : array();
$existing_class = ! empty( $attr['class'] ) ? $attr['class'] . ' ' : '';
$attr['class']  = $existing_class . 'bleed-section';
?>
<section <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $bg_style !== '' ) : ?>
		<div class="bleed-section__bg" style="<?php echo $bleed_bg_inline; ?>"></div>
	<?php endif; ?>
	<?php if ( $bleed_url ) : ?>
		<div class="bleed-section__img" style="<?php echo $bleed_img_style; ?>">
			<img src="<?php echo esc_url( $bleed_url ); ?>" alt="<?php echo esc_attr( $bleed_alt ); ?>" style="object-position:<?php echo esc_attr( $bleed_position ); ?>;" />
		</div>
	<?php endif; ?>
	<div class="<?php echo esc_attr( $container_class ); ?>" style="position:relative;z-index:2;">
		<div class="fw-row">
			<?php if ( $bleed_side === 'left' ) : ?>
				<div class="fw-col-md-<?php echo esc_attr( $image_col . $image_order ); ?>"></div>
				<div class="fw-col-md-<?php echo esc_attr( $content_col . $content_order ); ?>" style="<?php echo $content_style; ?>">
					<?php echo do_shortcode( $content ); ?>
				</div>
			<?php else : ?>
				<div class="fw-col-md-<?php echo esc_attr( $content_col . $content_order ); ?>" style="<?php echo $content_style; ?>">
					<?php echo do_shortcode( $content ); ?>
				</div>
				<div class="fw-col-md-<?php echo esc_attr( $image_col . $image_order ); ?>"></div>
			<?php endif; ?>
		</div>
	</div>
</section>
