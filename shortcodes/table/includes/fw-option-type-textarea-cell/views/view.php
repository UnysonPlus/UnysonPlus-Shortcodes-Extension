<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * @var  string $id
 * @var  array $option
 * @var  array $data
 * @var  array $internal_options
 */
?>

<?php $div_attr = $option['attr'];
unset($div_attr['name'], $div_attr['value'], $div_attr['rows']);
?>

<div <?php echo fw_attr_to_html($div_attr) ?>>
	<div class="fw-textarea-tab content">
		<?php echo esc_html( $option['value'] ) ?>
	</div>
	<div class="fw-textarea-tab control closed">
		<textarea <?php echo fw_attr_to_html($option['attr']) ?> ><?php echo esc_textarea( $option['value'] ) ?></textarea>
	</div>
</div>