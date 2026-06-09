<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 *
 * Renders the site logo. Self-contained (no theme functions) so it works in any
 * theme: custom image, else the Customizer custom logo, else the site title.
 */

$source    = ! empty( $atts['source'] ) ? $atts['source'] : 'site_identity';
$link_home = ! isset( $atts['link_home'] ) || $atts['link_home'] !== 'no';
$align     = ! empty( $atts['alignment'] ) ? $atts['alignment'] : '';

$max_height = '';
if ( ! empty( $atts['max_height'] ) && class_exists( 'FW_Option_Type_Unit_Input' ) ) {
	$max_height = FW_Option_Type_Unit_Input::to_string( $atts['max_height'] );
}
$img_style = ( $max_height !== '' ) ? ' style="max-height:' . esc_attr( $max_height ) . ';width:auto;"' : '';

$site_name = get_bloginfo( 'name' );
$home_url  = esc_url( home_url( '/' ) );

$img_url = '';
if ( $source === 'custom' && ! empty( $atts['custom_image']['url'] ) ) {
	$img_url = $atts['custom_image']['url'];
} else {
	$custom_logo_id = get_theme_mod( 'custom_logo' );
	if ( $custom_logo_id ) {
		$src = wp_get_attachment_image_src( $custom_logo_id, 'full' );
		if ( $src ) {
			$img_url = $src[0];
		}
	}
}

if ( $img_url !== '' ) {
	$inner = '<img src="' . esc_url( $img_url ) . '" alt="' . esc_attr( $site_name ) . '" class="sc-site-logo__img"' . $img_style . '>';
	$inner = $link_home
		? '<a href="' . $home_url . '" class="sc-site-logo__link" rel="home">' . $inner . '</a>'
		: $inner;
} else {
	$inner = $link_home
		? '<a href="' . $home_url . '" class="sc-site-logo__title" rel="home">' . esc_html( $site_name ) . '</a>'
		: '<span class="sc-site-logo__title">' . esc_html( $site_name ) . '</span>';
}

$atts['base_class']       = 'sc-site-logo';
$atts['unique_id_prefix'] = 'logo-';
$atts['extra_attrs']      = array();
$attr = sc_build_wrapper_attr( $atts );

$classes = ! empty( $attr['class'] ) ? explode( ' ', $attr['class'] ) : array();
unset( $attr['class'] );
if ( $align !== '' ) {
	$classes[] = 'text-' . sanitize_html_class( $align );
}
?>
<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>" <?php echo fw_attr_to_html( $attr ); ?>>
	<?php echo $inner; // phpcs:ignore — built from escaped parts above. ?>
</div>
