<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 *
 * A hamburger button. Emits the exact markup the theme's navigation.js binds to
 * (`.menu-toggle` + `aria-controls` + `.menu-toggle__bar`), so a builder-authored
 * off-canvas header reuses the theme drawer (open/close/focus-trap) with no extra JS.
 */

$target     = ! empty( $atts['target'] ) ? sanitize_html_class( $atts['target'] ) : 'primary-navigation-drawer';
$label      = ! empty( $atts['label'] ) ? $atts['label'] : __( 'Menu', 'fw' );
$icon_style = ! empty( $atts['icon_style'] ) ? $atts['icon_style'] : 'bars';

$atts['base_class']       = 'menu-toggle';
$atts['unique_id_prefix'] = 'mt-';
$atts['extra_attrs']      = array();
$attr = sc_build_wrapper_attr( $atts );

$classes = ! empty( $attr['class'] ) ? explode( ' ', $attr['class'] ) : array();
unset( $attr['class'] );
$classes[] = 'menu-toggle--' . sanitize_html_class( $icon_style );

$bar_class = ( $icon_style === 'dots' ) ? 'menu-toggle__dot' : 'menu-toggle__bar';
?>
<button type="button"
        class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>"
        aria-controls="<?php echo esc_attr( $target ); ?>"
        aria-expanded="false"
        aria-label="<?php echo esc_attr( $label ); ?>"
        <?php echo fw_attr_to_html( $attr ); ?>>
	<span class="<?php echo esc_attr( $bar_class ); ?>"></span>
	<span class="<?php echo esc_attr( $bar_class ); ?>"></span>
	<span class="<?php echo esc_attr( $bar_class ); ?>"></span>
</button>
