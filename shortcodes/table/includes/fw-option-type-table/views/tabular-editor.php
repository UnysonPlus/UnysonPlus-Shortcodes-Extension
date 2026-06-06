<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * New tabular spreadsheet editor.
 *
 * The whole UI (toolbar + grid) is rendered client-side by tabular-editor.js from
 * the JSON seed below. On every edit the JS writes the current model back into the
 * hidden <textarea>, which is what Unyson serializes and what
 * FW_Option_Type_Table::get_value_from_json() decodes + sanitizes server-side.
 *
 * @var array $option
 * @var array $data
 * @var array $editor_model
 */

$json_name = $option['attr']['name'] . '[__json]';
$seed      = wp_json_encode( $editor_model );
?>

<div class="fw-tabular">
	<textarea class="fw-tabular-json" name="<?php echo esc_attr( $json_name ) ?>" style="display:none" aria-hidden="true"><?php
		echo fw_htmlspecialchars( $seed );
	?></textarea>

	<div class="fw-tabular-editor" data-seeded="0">
		<div class="fw-tabular-loading"><?php echo esc_html__( 'Loading editor…', 'fw' ) ?></div>
	</div>
</div>
