<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 *
 * A self-contained site search form. The "icon-toggle" style reveals the form
 * on click (see static/js/site-search.js).
 */

$style       = ! empty( $atts['style'] ) ? $atts['style'] : 'inline-form';
$placeholder = ! empty( $atts['placeholder'] ) ? $atts['placeholder'] : __( 'Search …', 'fw' );

$atts['base_class']       = 'sc-site-search';
$atts['unique_id_prefix'] = 'search-';
$atts['extra_attrs']      = array();
$attr = sc_build_wrapper_attr( $atts );

$classes = ! empty( $attr['class'] ) ? explode( ' ', $attr['class'] ) : array();
unset( $attr['class'] );
$classes[] = 'sc-site-search--' . sanitize_html_class( $style );

$icon = '<svg class="sc-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="7" cy="7" r="5"></circle><line x1="11" y1="11" x2="14.5" y2="14.5"></line></svg>';

$form  = '<form role="search" method="get" class="sc-search-form" action="' . esc_url( home_url( '/' ) ) . '">';
$form .= '<label class="screen-reader-text" for="' . esc_attr( $atts['unique_id_prefix'] . 'field' ) . '">' . esc_html__( 'Search for:', 'fw' ) . '</label>';
$form .= '<input type="search" id="' . esc_attr( $atts['unique_id_prefix'] . 'field' ) . '" class="sc-search-field" placeholder="' . esc_attr( $placeholder ) . '" value="' . esc_attr( get_search_query() ) . '" name="s">';
$form .= '<button type="submit" class="sc-search-submit" aria-label="' . esc_attr__( 'Search', 'fw' ) . '">' . $icon . '</button>';
$form .= '</form>';
?>
<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>" <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $style === 'icon-toggle' ) : ?>
		<button type="button" class="sc-search-toggle" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open search', 'fw' ); ?>"><?php echo $icon; // phpcs:ignore ?></button>
		<div class="sc-search-panel" hidden><?php echo $form; // phpcs:ignore ?></div>
	<?php else : ?>
		<?php echo $form; // phpcs:ignore ?>
	<?php endif; ?>
</div>
