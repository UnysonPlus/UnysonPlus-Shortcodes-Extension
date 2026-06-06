<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Orchestrator view for the `table` option type.
 *
 * Renders the shared "Table Styling" (purpose) selector once, then two editors
 * toggled by that selector:
 *   - tabular  -> the new JSON-backed spreadsheet editor (views/tabular-editor.php)
 *   - pricing  -> the legacy grid editor (views/pricing-editor.php) which embeds
 *                 button / switch / popup option types and serializes through Unyson.
 *
 * @var  string $id
 * @var  array  $option
 * @var  array  $data
 * @var  array  $editor_model  normalized model the tabular JS boots from
 */

$wrapper_attr = $option['attr'];
unset( $wrapper_attr['name'], $wrapper_attr['value'] );

$purpose = isset( $data['value']['header_options']['table_purpose'] )
	? $data['value']['header_options']['table_purpose']
	: 'tabular';
$purpose = ( 'pricing' === $purpose ) ? 'pricing' : 'tabular';

$data_header = array(
	'name_prefix' => $option['attr']['name'] . '[header_options]',
	'id_prefix'   => $option['attr']['id'] . '-header-options',
);
$values_header = isset( $data['value']['header_options'] ) ? $data['value']['header_options'] : array();

$declared_path = fw_ext( 'shortcodes' )->get_shortcode( 'table' )->get_declared_path()
                 . '/includes/fw-option-type-table/views/';
?>

<div <?php echo fw_attr_to_html( $wrapper_attr ) ?> data-table-purpose="<?php echo esc_attr( $purpose ) ?>">

	<div class="fw-table-purpose-bar">
		<?php echo fw()->backend->render_options(
			array( 'table_purpose' => $option['header_options']['table_purpose'] ),
			$values_header,
			$data_header
		); ?>
	</div>

	<?php /* New tabular editor */ ?>
	<div class="fw-table-editor fw-table-editor-tabular<?php echo 'tabular' === $purpose ? ' fw-table-editor-active' : '' ?>">
		<?php echo fw_render_view( $declared_path . 'tabular-editor.php', array(
			'option'       => $option,
			'data'         => $data,
			'editor_model' => $editor_model,
		) ); ?>
	</div>

	<?php /* Legacy pricing editor */ ?>
	<div class="fw-table-editor fw-table-editor-pricing<?php echo 'pricing' === $purpose ? ' fw-table-editor-active' : '' ?>">
		<?php echo fw_render_view( $declared_path . 'pricing-editor.php', array(
			'option' => $option,
			'data'   => $data,
		) ); ?>
	</div>

</div>
