<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */

// icon-v3 stores the same value shape as icon-v2:
// [ 'type' => 'icon-font'|'svg'|'emoji'|'custom-upload'|'none', ... ]
// sc_icon_render() is the shared renderer + auto-enqueues any needed pack CSS.
$icon = is_array( $atts['icon'] ?? null ) ? $atts['icon'] : array();

$base_class = 'sc-icon-v3-test';
if ( ! empty( $atts['class'] ) ) {
	$base_class .= ' ' . $atts['class'];
}

$icon_html = sc_icon_render( $icon, array(
	'style'     => 'font-size:48px;line-height:1;',
	'img_class' => 'icon-image',
) );
?>
<span class="<?php echo esc_attr( $base_class ); ?>" style="display:inline-flex;align-items:center;gap:12px;"<?php echo ( ! empty( $atts['id'] ) ? ' id="' . esc_attr( $atts['id'] ) . '"' : '' ); ?>>
	<?php echo $icon_html; // already escaped by sc_icon_render() ?>

	<?php if ( ! empty( $atts['title'] ) ) : ?>
		<span class="sc-icon-v3-test__label"><?php echo esc_html( $atts['title'] ); ?></span>
	<?php endif; ?>
</span>
