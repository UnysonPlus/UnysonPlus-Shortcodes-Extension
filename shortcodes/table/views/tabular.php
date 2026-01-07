<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
?>
<?php
/*
 * you may add the following classes to the `.table` div:
 * `table-bordered`, `table-hover`, `table-striped`
 */
// Build wrapper attributes
$atts['base_class']       = 'table';
$atts['unique_id_prefix'] = 'tb-';
$attr = sc_build_wrapper_attr( $atts );
?>

<div <?php echo fw_attr_to_html( $attr ); ?>>
	<table>
		<?php foreach ( $atts['table']['rows'] as $row_key => $row ) : ?>
			<?php if ( $row['name'] == 'heading-row' ) : ?>
				<thead>
					<tr class="<?php echo esc_attr($row['name']); ?>">
						<?php foreach ( $atts['table']['cols'] as $col_key => $col ) : ?>
							<th class="<?php echo esc_attr($col['name']); ?>">
								<?php echo $atts['table']['content'][ $row_key ][ $col_key ]['textarea']; ?>
							</th>
						<?php endforeach; ?>
					</tr>
				</thead>
			<?php elseif ( $row['name'] == 'default-row' ) : ?>
				<tr class="<?php echo esc_attr($row['name']); ?>">
					<?php foreach ( $atts['table']['cols'] as $col_key => $col ) : ?>
						<td class="<?php echo esc_attr($col['name']); ?>">
							<?php echo $atts['table']['content'][ $row_key ][ $col_key ]['textarea']; ?>
						</td>
					<?php endforeach; ?>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
	</table>
</div>