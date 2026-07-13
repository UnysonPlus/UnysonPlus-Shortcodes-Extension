<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * @var array $atts
 */

$text      = isset( $atts['text'] ) ? trim( (string) $atts['text'] ) : '';
$target    = isset( $atts['target'] ) ? trim( (string) $atts['target'] ) : '';
$layout    = isset( $atts['layout'] ) && in_array( $atts['layout'], array( 'stacked', 'stacked-reverse', 'inline', 'icon-only' ), true ) ? $atts['layout'] : 'stacked';
$animation = isset( $atts['animation'] ) && in_array( $atts['animation'], array( 'bounce', 'pulse', 'nudge', 'none' ), true ) ? $atts['animation'] : 'bounce';

// Per-element colour picks routed to the label / icon (compact color pickers → class + inline hex).
$text_styling = function_exists( 'sc_extract_styling_atts' ) ? sc_extract_styling_atts( $atts, array( 'text_color' ) ) : array( 'classes' => array(), 'styles' => array() );
$icon_styling = function_exists( 'sc_extract_styling_atts' ) ? sc_extract_styling_atts( $atts, array( 'icon_color' ) ) : array( 'classes' => array(), 'styles' => array() );
$text_class   = implode( ' ', (array) $text_styling['classes'] );
$icon_class   = implode( ' ', (array) $icon_styling['classes'] );
$text_style   = ! empty( $text_styling['styles'] ) ? implode( '; ', $text_styling['styles'] ) : '';
$icon_style   = ! empty( $icon_styling['styles'] ) ? implode( '; ', $icon_styling['styles'] ) : '';

// Icon Size — a unit-input compiled to a CSS length, applied as font-size on the icon (the
// .sc-scroll-cue__icon svg is 1em, so this scales font icons AND SVGs).
$raw_size = isset( $atts['icon_size'] ) ? $atts['icon_size'] : '';
$icon_size = '';
if ( is_array( $raw_size ) && isset( $raw_size['value'] ) && trim( (string) $raw_size['value'] ) !== '' ) {
	$icon_size = class_exists( 'FW_Option_Type_Unit_Input' )
		? FW_Option_Type_Unit_Input::to_string( $raw_size )
		: ( trim( (string) $raw_size['value'] ) . ( isset( $raw_size['unit'] ) ? preg_replace( '/[^a-z%]/', '', (string) $raw_size['unit'] ) : 'px' ) );
} elseif ( is_string( $raw_size ) && trim( $raw_size ) !== '' ) {
	$icon_size = trim( $raw_size );
}
if ( $icon_size !== '' ) {
	$icon_style = trim( $icon_style . ( $icon_style !== '' ? '; ' : '' ) . 'font-size:' . $icon_size );
}

// Icon — the picked glyph, or a default chevron-down when left as None.
$icon_val = ( isset( $atts['icon'] ) && is_array( $atts['icon'] ) && ( $atts['icon']['type'] ?? 'none' ) !== 'none' )
	? $atts['icon']
	: array( 'type' => 'svg', 'svg-source' => 'library', 'svg-id' => 'lucide/chevron-down' );
$icon_html = function_exists( 'sc_icon_render' )
	? sc_icon_render( $icon_val, array( 'aria_hidden' => true ) )
	: '';

// Wrapper (Styling-tab bg/spacing + animations + id/class/custom attrs).
$atts['base_class']       = 'scroll-indicator';
$atts['unique_id_prefix'] = 'scr-';
$attr = sc_build_wrapper_attr( $atts );

// The clickable cue. No target → href="#" + data-scroll-down (JS scrolls one screen).
$has_target = ( $target !== '' );
$href       = $has_target ? $target : '#';
$aria       = $text !== '' ? $text : __( 'Scroll down', 'fw' );

$cue_classes = array(
	'sc-scroll-cue',
	'sc-scroll-cue--' . $layout,
	'sc-scroll-cue--anim-' . $animation,
);

$label_html = '';
if ( $text !== '' && $layout !== 'icon-only' ) {
	$label_html = '<span class="sc-scroll-cue__label' . ( $text_class ? ' ' . esc_attr( $text_class ) : '' ) . '"'
		. ( $text_style !== '' ? ' style="' . esc_attr( $text_style ) . '"' : '' ) . '>' . esc_html( $text ) . '</span>';
}
$icon_span = '<span class="sc-scroll-cue__icon' . ( $icon_class ? ' ' . esc_attr( $icon_class ) : '' ) . '"'
	. ( $icon_style !== '' ? ' style="' . esc_attr( $icon_style ) . '"' : '' ) . ' aria-hidden="true">' . $icon_html . '</span>';
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<a class="<?php echo esc_attr( implode( ' ', $cue_classes ) ); ?>" href="<?php echo esc_attr( $href ); ?>"<?php echo $has_target ? '' : ' data-scroll-down="1"'; ?> aria-label="<?php echo esc_attr( $aria ); ?>">
		<?php echo $label_html . $icon_span; // label first in DOM; CSS controls visual order ?>
	</a>
</div>
