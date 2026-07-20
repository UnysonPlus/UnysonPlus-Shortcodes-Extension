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
?>
<div <?php echo function_exists( 'fw_attr_to_html' ) ? fw_attr_to_html( $attr ) : 'class="' . esc_attr( $atts['css_class'] ) . '"'; ?>>
	<?php if ( $image !== '' ) : ?>
		<div class="fw-team-image"><img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" /></div>
	<?php endif; ?>
	<div class="fw-team-inner">
		<div class="fw-team-name">
			<?php if ( $name !== '' ) : ?><h3><?php echo esc_html( $name ); ?></h3><?php endif; ?>
			<?php if ( $job !== '' ) : ?><span><?php echo esc_html( $job ); ?></span><?php endif; ?>
		</div>
		<?php if ( $desc !== '' ) : ?>
			<div class="fw-team-text"><p><?php echo wp_kses_post( $desc ); ?></p></div>
		<?php endif; ?>
	</div>
</div>
