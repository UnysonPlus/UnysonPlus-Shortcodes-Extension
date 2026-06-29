<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Components → Box Presets (card / border presets).
 *
 * @var array $options       Filled with the option schema (loaded via upw_ts_get_options()).
 * @var array $color_choices slug => array( label, color ) from the Color Presets.
 */

$cc = isset( $color_choices ) && is_array( $color_choices )
	? $color_choices
	: ( function_exists( 'unysonplus_components_color_choices' ) ? unysonplus_components_color_choices() : array() );

$options = array(
	'border_presets' => array(
		'label'         => __( 'Box Presets', 'fw' ),
		'type'          => 'border-presets',
		'color-choices' => $cc,
		'value'         => function_exists( 'unysonplus_default_border_presets' ) ? unysonplus_default_border_presets() : array(),
		'desc'          => __( 'A reusable card style — border, corner radius, padding, box-shadow and an optional background fill (color / gradient / image). Each preset produces a <code>.boxp-{name}</code> class with a live preview — pick it on a Column (Styling → Box Preset), a Table (Table Options → Frame), or a Countdown. Set per Default / Hover state.', 'fw' ),
	),
);
