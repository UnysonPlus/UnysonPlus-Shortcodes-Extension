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
if ( function_exists( 'sc_easing_normalize' ) ) {
	$val = sc_easing_normalize( $val ); // older short keys (out, back…) show as their Classic entry
}
if ( $val === '' ) {
	$val = 'default';
}

$defs   = function_exists( 'sc_easing_defs' ) ? sc_easing_defs() : array();
$custom = stripos( $val, 'cubic-bezier(' ) === 0;
$label  = isset( $defs[ $val ]['label'] ) ? $defs[ $val ]['label'] : ( $val === 'default' ? __( 'Default', 'fw' ) : ( $custom ? __( 'Custom curve', 'fw' ) : $val ) );

$ext   = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' ) : null;
$base  = $ext ? $ext->get_declared_URI( '/static/img/easings' ) : '';
$file  = ( isset( $defs[ $val ] ) || $val === 'default' ) ? $val : 'default';
$thumb = $base . '/' . $file . '.svg';
// A custom cubic-bezier: draw its own thumbnail (same 120×96 box as the generated ones).
if ( $custom && preg_match_all( '/-?[0-9]*\.?[0-9]+/', substr( $val, 12 ), $mm ) && count( $mm[0] ) === 4 ) {
	list( $x1, $y1, $x2, $y2 ) = array_map( 'floatval', $mm[0] );
	$pts = array();
	for ( $i = 0; $i <= 40; $i++ ) {
		$t     = $i / 40;
		$pts[] = array( 3 * $x1 * $t * ( 1 - $t ) * ( 1 - $t ) + 3 * $x2 * $t * $t * ( 1 - $t ) + $t * $t * $t, 3 * $y1 * $t * ( 1 - $t ) * ( 1 - $t ) + 3 * $y2 * $t * $t * ( 1 - $t ) + $t * $t * $t );
	}
	$ys = array_merge( array( 0, 1 ), array_column( $pts, 1 ) );
	$mn = min( $ys );
	$mx = max( $ys );
	$d  = '';
	foreach ( $pts as $i => $p ) {
		$d .= ( $i ? ' L ' : 'M ' ) . round( 13 + 94 * $p[0], 1 ) . ' ' . round( 84 - ( $p[1] - $mn ) / ( $mx - $mn ) * 72, 1 );
	}
	$thumb = 'data:image/svg+xml,' . rawurlencode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 96"><path d="' . $d . '" fill="none" stroke="#3858e9" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>' );
}
?>
<div class="upw-easing" data-value="<?php echo esc_attr( $val ); ?>">
	<input type="hidden" <?php echo fw_attr_to_html( $attr ); ?> value="<?php echo esc_attr( $val ); ?>" />
	<button type="button" class="upw-easing-trigger" aria-haspopup="listbox">
		<img class="upw-easing-thumb" src="<?php echo $custom ? esc_attr( $thumb ) : esc_url( $thumb ); ?>" alt="" aria-hidden="true" />
		<span class="upw-easing-name"><?php echo esc_html( $label ); ?></span>
		<svg class="upw-easing-caret" viewBox="0 0 12 12" width="12" height="12" aria-hidden="true"><path d="M3 4.5 6 8l3-3.5" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
	</button>
</div>
