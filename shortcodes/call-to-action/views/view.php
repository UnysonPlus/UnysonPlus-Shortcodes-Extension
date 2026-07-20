<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
} ?>

<?php
// Route per-element color picks to specific inner elements (kept out of wrapper).
// sc_extract_styling_atts gives us both preset classes AND custom-hex inline
// styles from the compact picker.
$title_styling   = sc_extract_styling_atts( $atts, array( 'title_color' ) );
$message_styling = sc_extract_styling_atts( $atts, array( 'message_color' ) );
$title_extras    = $title_styling['classes'];
$message_extras  = $message_styling['classes'];
$title_style     = $title_styling['styles']   ? implode( '; ', $title_styling['styles'] )   : '';
$message_style   = $message_styling['styles'] ? implode( '; ', $message_styling['styles'] ) : '';

$atts['base_class']         = 'call-to-action';
$atts['unique_id_prefix']   = 'cta-';

// Build attributes for wrapper
$attr = sc_build_wrapper_attr( $atts );
// Box Style preset (.boxp-{slug}) on the card wrapper.
$__boxp = function_exists( 'sc_card_box_style_class' ) ? sc_card_box_style_class( $atts ) : '';
if ( $__boxp !== '' ) { $attr['class'] = trim( ( isset( $attr['class'] ) ? $attr['class'] : '' ) . ' ' . $__boxp ); }

// Append our custom classes
$attr['class'] = trim(
    ($attr['class'] ?? '') . ' fw-call-to-action'
);

$title_class       = trim( implode( ' ', $title_extras ) );
$message_class     = trim( 'fw-action-message ' . implode( ' ', $message_extras ) );
$title_style_attr  = $title_style   !== '' ? ' style="' . esc_attr( $title_style )   . '"' : '';
$message_style_attr = $message_style !== '' ? ' style="' . esc_attr( $message_style ) . '"' : '';
$needs_title_attrs = $title_class !== '' || $title_style !== '';

// Content / Button split. Preferred shape = "n/d" (the content fraction; the divider
// snaps to twelfths AND fifths). Legacy shape = a bare int span out of 12. This drives
// the flex-grow ratio of each side, so any denominator (twelfths or fifths) works.
$split_raw = isset( $atts['column_split'] ) ? $atts['column_split'] : '3/4';
if ( is_string( $split_raw ) && strpos( $split_raw, '/' ) !== false ) {
	$pp           = explode( '/', $split_raw );
	$cn           = (int) $pp[0];
	$cd           = isset( $pp[1] ) ? (int) $pp[1] : 12;
	$cd           = $cd > 1 ? $cd : 12;
	$cn           = max( 1, min( $cd - 1, $cn ) );
	$content_grow = $cn;
	$btn_grow     = $cd - $cn;
} else {
	$v            = max( 1, min( 11, (int) $split_raw ) );
	$content_grow = $v;
	$btn_grow     = 12 - $v;
}
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<div class="fw-action-content" style="flex-grow:<?php echo esc_attr( $content_grow ); ?>">
		<?php if (!empty($atts['title'])): ?>
		<h2<?php echo $title_class !== '' ? ' class="' . esc_attr( $title_class ) . '"' : ''; ?><?php echo $title_style_attr; ?>><?php echo esc_html( $atts['title'] ); ?></h2>
		<?php endif; ?>
		<?php if ( ! empty( $atts['message'] ) ) : ?>
		<p class="<?php echo esc_attr( $message_class ); ?>"<?php echo $message_style_attr; ?>><?php echo wp_kses_post( $atts['message'] ); ?></p>
		<?php endif; ?>
	</div>
	<?php
	$cta_label  = isset( $atts['button_label'] )  ? trim( (string) $atts['button_label'] ) : '';
	$cta_link   = isset( $atts['button_link'] )   ? (string) $atts['button_link']   : '#';
	$cta_target = isset( $atts['button_target'] ) ? (string) $atts['button_target'] : '_self';
	if ( $cta_label !== '' ) :
	?>
	<div class="fw-action-btn" style="flex-grow:<?php echo esc_attr( $btn_grow ); ?>">
		<a href="<?php echo esc_url( $cta_link ); ?>" class="btn btn-1" target="<?php echo esc_attr( $cta_target ); ?>">
			<span><?php echo esc_html( $cta_label ); ?></span>
		</a>
	</div>
	<?php endif; ?>
</div>