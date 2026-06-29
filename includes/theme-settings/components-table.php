<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Components → Table Presets.
 *
 * @var array $options       Filled with the option schema (loaded via upw_ts_get_options()).
 * @var array $color_choices slug => array( label, color ) from the Color Presets.
 */

$cc = isset( $color_choices ) && is_array( $color_choices )
	? $color_choices
	: ( function_exists( 'unysonplus_components_color_choices' ) ? unysonplus_components_color_choices() : array() );

$options = array(
	'table_presets' => array(
		'label'         => __( 'Table Presets', 'fw' ),
		'type'          => 'table-presets',
		'color-choices' => $cc,
		'value'         => function_exists( 'unysonplus_default_table_presets' ) ? unysonplus_default_table_presets() : array(),
		'desc'          => __( 'Each preset produces a <code>.tbl-{name}</code> class with a live preview — pick it on a Table (Table Options → Table Preset). Header / Body / Striped / Hover / Footer / Caption skins plus grid, frame, radius and padding.', 'fw' ),
	),
);
