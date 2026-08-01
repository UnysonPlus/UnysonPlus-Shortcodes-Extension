<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** @var array $atts */

// Guard every field — a default / empty team member must never throw
// "undefined array key" / "array offset on string" warnings.
$name = isset( $atts['name'] ) ? (string) $atts['name'] : '';
$job  = isset( $atts['job'] )  ? (string) $atts['job']  : '';
$desc = isset( $atts['desc'] ) ? (string) $atts['desc'] : '';

$image = '';
if ( ! empty( $atts['image'] ) && is_array( $atts['image'] ) && ! empty( $atts['image']['url'] ) ) {
	$image = $atts['image']['url'];
} elseif ( function_exists( 'fw_get_framework_directory_uri' ) ) {
	$image = fw_get_framework_directory_uri( '/static/img/no-image.png' );
}

// Per-element styling (Text / Background colours) — preset classes + custom-hex.
$styling = function_exists( 'sc_extract_styling_atts' )
	? sc_extract_styling_atts( $atts, array( 'text_color', 'bg_color' ) )
	: array( 'classes' => array(), 'styles' => array() );
$extra_classes = isset( $styling['classes'] ) ? $styling['classes'] : array();
$inline_style  = ( ! empty( $styling['styles'] ) ) ? implode( '; ', $styling['styles'] ) : '';

// Font-size preset (a class slug) when set.
if ( ! empty( $atts['font_size_preset'] ) && is_string( $atts['font_size_preset'] ) ) {
	$extra_classes[] = $atts['font_size_preset'];
}

// Wrapper attributes (css_class / css_id / animation / spacing) via the shared
// builder, keeping `.fw-team` as the base class so the existing CSS applies.
$classes = array_merge( array( 'fw-team' ), $extra_classes );
$atts['base_class']       = 'fw-team';
$atts['unique_id_prefix'] = 'team-';
$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
$attr = function_exists( 'sc_build_wrapper_attr' ) ? sc_build_wrapper_attr( $atts ) : array( 'class' => $atts['css_class'] );
// Box Style preset (.boxp-{slug}) on the card wrapper.
$__boxp = function_exists( 'sc_card_box_style_class' ) ? sc_card_box_style_class( $atts ) : '';
if ( $__boxp !== '' ) { $attr['class'] = trim( ( isset( $attr['class'] ) ? $attr['class'] : '' ) . ' ' . $__boxp ); }
if ( $inline_style !== '' ) {
	$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $inline_style;
}

// --- Build the slot HTML (a slot renders only when it has content). ---
$slots = array();
// Image Style preset (Theme Settings → Components → Image Styles): wrap the photo in
// `.imgs-wrap imgs-{slug}` so crop / corners / mask / filter / scrim apply. Only when set.
$imgs_cls = function_exists( 'sc_image_style_class' ) ? sc_image_style_class( $atts ) : '';
$img_tag  = '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $name ) . '" loading="lazy" />';
if ( $imgs_cls !== '' ) {
	$img_tag = '<span class="imgs-wrap ' . esc_attr( $imgs_cls ) . '">' . $img_tag . '</span>';
}
$slots['media'] = ( $image !== '' ) ? '<div class="fw-team-image">' . $img_tag . '</div>' : '';
$slots['name'] = ( $name !== '' ) ? '<div class="fw-team-name"><h3>' . esc_html( $name ) . '</h3></div>' : '';
$slots['job']  = ( $job !== '' )  ? '<div class="fw-team-job">' . esc_html( $job ) . '</div>' : '';
$slots['desc'] = ( $desc !== '' ) ? '<div class="fw-team-text"><p>' . wp_kses_post( $desc ) . '</p></div>' : '';

// Assemble the card from the shared Card Rows designer. sc_card_rows_value() reads the
// saved rows (an instance saved before the designer inherits the seeded default rows,
// which reproduce the classic image · name+job · description stack). sc_card_rows_render()
// turns them into flex rows with the `fw-team__row …` classes.
$card_html = '';
if ( function_exists( 'sc_card_rows_value' ) && function_exists( 'sc_card_rows_render' ) ) {
	$rows      = sc_card_rows_value( $atts, 'card_rows' );
	$card_html = sc_card_rows_render( $rows, $slots, 'fw-team' );
}

// Fallback: the shared designer/renderer is unavailable, or the row list is empty —
// emit the classic stacked markup so the element always renders its content.
if ( $card_html === '' ) {
	$card_html = $slots['media']
		. '<div class="fw-team-inner">'
		. $slots['name'] . $slots['job'] . $slots['desc']
		. '</div>';
}
?>
<div <?php echo function_exists( 'fw_attr_to_html' ) ? fw_attr_to_html( $attr ) : 'class="' . esc_attr( $atts['css_class'] ) . '"'; ?>>
	<?php echo $card_html; // phpcs:ignore WordPress.Security.EscapeOutput — each slot is escaped above ?>
</div>
