<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Components → Icon Badges (reusable icon badge tiles).
 *
 * @var array $options       Filled with the option schema (loaded via upw_ts_get_options()).
 * @var array $color_choices slug => array( label, color ) from the Color Presets.
 */

$cc = isset( $color_choices ) && is_array( $color_choices )
	? $color_choices
	: ( function_exists( 'unysonplus_components_color_choices' ) ? unysonplus_components_color_choices() : array() );

$options = array(
	'icon_badge_presets' => array(
		'label'         => __( 'Icon Badges', 'fw' ),
		'type'          => 'icon-badge-presets',
		'color-choices' => $cc,
		'value'         => function_exists( 'unysonplus_default_icon_badge_presets' ) ? unysonplus_default_icon_badge_presets() : array(),
		'desc'          => __( 'A reusable badge for icons — a shaped tile (circle / rounded / square / hexagon) at a set size, with a tile fill, a centered glyph (its own color + size), border and box-shadow. Each preset produces an <code>.iconb-{name}</code> class with a live preview — pick it on an Icon Box (Styling → Icon Badge Preset). Set per Default / Hover state.', 'fw' ),
	),
);
