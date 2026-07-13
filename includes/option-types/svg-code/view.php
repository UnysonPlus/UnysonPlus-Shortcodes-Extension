<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var string $id
 * @var array  $option
 * @var array  $data
 */

$attr = isset( $option['attr'] ) ? $option['attr'] : array();
$attr['class'] = trim( ( isset( $attr['class'] ) ? $attr['class'] : '' ) . ' fw-svg-code-input' );
unset( $attr['value'] );

$val    = (string) $option['value'];
$is_svg = ( stripos( $val, '<svg' ) !== false );
$ph     = ! empty( $option['placeholder'] ) ? $option['placeholder'] : '<svg viewBox="0 0 24 24">…</svg>';
?>
<div class="fw-svg-code">
	<div class="fw-svg-code-head">
		<button type="button" class="button button-secondary fw-svg-code-upload"><?php echo esc_html( __( 'Upload SVG file', 'fw' ) ); ?></button>
		<input type="file" accept=".svg,image/svg+xml" class="fw-svg-code-file" tabindex="-1" />
	</div>
	<textarea <?php echo fw_attr_to_html( $attr ); ?> rows="6" placeholder="<?php echo esc_attr( $ph ); ?>"><?php echo htmlspecialchars( $val, ENT_COMPAT, 'UTF-8' ); ?></textarea>
	<div class="fw-svg-code-preview<?php echo $is_svg ? ' has-svg' : ''; ?>" aria-hidden="true"><?php echo $is_svg ? $val : ''; // sanitised on save ?></div>
</div>
