<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 *
 * Renders a WordPress menu. Reuses the theme's `.primary-menu` +
 * `.menu-item-has-children` class contract so the theme's navigation.js
 * (dropdowns, accordion toggles, drawer) works on builder-authored menus too.
 */

$source = isset( $atts['menu_source']['type'] ) ? $atts['menu_source']['type'] : 'location';

$menu_args = array();
if ( $source === 'menu' ) {
	$menu_id = isset( $atts['menu_source']['menu']['menu_id'] ) ? $atts['menu_source']['menu']['menu_id'] : '';
	if ( $menu_id === '' || ! wp_get_nav_menu_object( $menu_id ) ) {
		return;
	}
	$menu_args['menu'] = $menu_id;
} else {
	$loc = isset( $atts['menu_source']['location']['menu_location'] ) ? $atts['menu_source']['location']['menu_location'] : '';
	if ( $loc === '' || ! has_nav_menu( $loc ) ) {
		return;
	}
	$menu_args['theme_location'] = $loc;
}

$orientation = ! empty( $atts['orientation'] ) ? $atts['orientation'] : 'horizontal';
$submenu     = ! empty( $atts['submenu_style'] ) ? $atts['submenu_style'] : 'dropdown';
$depth       = isset( $atts['depth'] ) ? (int) $atts['depth'] : 0;
$align       = ! empty( $atts['alignment'] ) ? $atts['alignment'] : '';

$atts['base_class']       = 'sc-nav-menu';
$atts['unique_id_prefix'] = 'nav-';
$atts['extra_attrs']      = array();
$attr = sc_build_wrapper_attr( $atts );

$wrap_classes = ! empty( $attr['class'] ) ? explode( ' ', $attr['class'] ) : array();
unset( $attr['class'] );

$menu_classes = array(
	'primary-menu',
	'primary-menu--' . sanitize_html_class( $orientation ),
	'submenu-' . sanitize_html_class( $submenu ),
);
if ( $submenu === 'mega' ) {
	$menu_classes[] = 'has-mega';
}
if ( $align !== '' ) {
	$menu_classes[] = 'nav-align-' . sanitize_html_class( $align );
}

$menu_html = wp_nav_menu( array_merge( $menu_args, array(
	'container'   => false,
	'menu_class'  => implode( ' ', $menu_classes ),
	'depth'       => $depth,
	'fallback_cb' => false,
	'echo'        => false,
) ) );

if ( ! $menu_html ) {
	return;
}

$wrap_classes[] = 'sc-nav';
?>
<nav class="<?php echo esc_attr( implode( ' ', array_filter( $wrap_classes ) ) ); ?>" aria-label="<?php esc_attr_e( 'Menu', 'fw' ); ?>" <?php echo fw_attr_to_html( $attr ); ?>>
	<?php echo $menu_html; // phpcs:ignore — wp_nav_menu output is already escaped. ?>
</nav>
