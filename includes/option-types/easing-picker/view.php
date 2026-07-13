<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var string $id
 * @var array  $option
 * @var array  $data
 */

$attr = isset( $option['attr'] ) ? $option['attr'] : array();
unset( $attr['value'] );
$attr['class'] = trim( ( isset( $attr['class'] ) ? $attr['class'] : '' ) . ' upw-easing-value' );

$val = (string) $option['value'];
if ( $val === '' ) {
	$val = 'default';
}

$defs  = function_exists( 'sc_easing_defs' ) ? sc_easing_defs() : array();
$label = isset( $defs[ $val ]['label'] ) ? $defs[ $val ]['label'] : ( $val === 'default' ? __( 'Default', 'fw' ) : $val );

$ext   = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' ) : null;
$base  = $ext ? $ext->get_declared_URI( '/static/img/easings' ) : '';
$file  = ( isset( $defs[ $val ] ) || $val === 'default' ) ? $val : 'default';
$thumb = $base . '/' . $file . '.svg';
?>
<div class="upw-easing" data-value="<?php echo esc_attr( $val ); ?>">
	<input type="hidden" <?php echo fw_attr_to_html( $attr ); ?> value="<?php echo esc_attr( $val ); ?>" />
	<button type="button" class="upw-easing-trigger" aria-haspopup="listbox">
		<img class="upw-easing-thumb" src="<?php echo esc_url( $thumb ); ?>" alt="" aria-hidden="true" />
		<span class="upw-easing-name"><?php echo esc_html( $label ); ?></span>
		<svg class="upw-easing-caret" viewBox="0 0 12 12" width="12" height="12" aria-hidden="true"><path d="M3 4.5 6 8l3-3.5" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
	</button>
</div>
