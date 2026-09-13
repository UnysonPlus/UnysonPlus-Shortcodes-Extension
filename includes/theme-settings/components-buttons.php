<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Components → Buttons (color presets + sizes + hover animations).
 *
 * @var array $options       Filled with the option schema (loaded via upw_ts_get_options()).
 * @var array $color_choices slug => array( label, color ) from the Color Presets.
 */

$cc = isset( $color_choices ) && is_array( $color_choices )
	? $color_choices
	: ( function_exists( 'unysonplus_components_color_choices' ) ? unysonplus_components_color_choices() : array() );

$options = array(
	'button_colors' => array(
		'label'         => __( 'Button Presets', 'fw' ),
		'type'          => 'button-presets',
		'color-choices' => $cc,
		'value'         => function_exists( 'unysonplus_default_button_color_presets' ) ? unysonplus_default_button_color_presets() : array(),
		'desc'          => __( 'Each preset produces a <code>.btn-{id}</code> class with a live preview. Colors reference your Color Presets. Default / Hover / Active / Focus / Disabled states, typography, box, shadow and custom CSS are all supported.', 'fw' ),
	),
	'button_sizes' => array(
		'inline'          => true,
		'label'           => __( 'Sizes', 'fw' ),
		'type'            => 'addable-box',
		'value'           => function_exists( 'unysonplus_default_button_size_presets' ) ? unysonplus_default_button_size_presets() : array(),
		'sortable'        => true,
		'add-button-text' => __( 'Add More Sizes', 'fw' ),
		'box-options'     => array(
			// Grouped ( show_borders => false ) so the fields read as one panel with no
			// divider between rows. The group flattens on save, so every leaf key stays
			// top-level (no migration) and the template still resolves.
			'grp_size' => array(
				'type'         => 'group',
				'show_borders' => false,
				'options'      => array(
					'id'            => array( 'type' => 'unique' ),
					'size_name'     => array( 'label' => __( 'Size Name', 'fw' ), 'type' => 'text', 'value' => '', 'dynamic_content' => false, 'help' => __( 'The display name for this size (shown in the size picker).', 'fw' ) ),
					'slug'          => array( 'label' => __( 'Slug', 'fw' ), 'type' => 'text', 'value' => '', 'dynamic_content' => false, 'help' => __( 'Becomes the CSS class suffix (e.g. <code>sm</code> → <code>.btn-sm</code>).', 'fw' ) ),
					'font_size'     => array( 'label' => __( 'Font Size', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', 'em', 'rem' ), 'min' => 0, 'dynamic_content' => false, 'help' => __( 'Button text size.', 'fw' ) ),
					'line_height'   => array( 'label' => __( 'Line Height', 'fw' ), 'type' => 'short-text', 'value' => '', 'dynamic_content' => false, 'help' => __( 'Unitless is fine (e.g. 1.5), or use a unit.', 'fw' ) ),
					'padding_y'     => array( 'label' => __( 'Padding Y', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', 'em', 'rem' ), 'min' => 0, 'dynamic_content' => false, 'help' => __( 'Top and bottom padding.', 'fw' ) ),
					'padding_x'     => array( 'label' => __( 'Padding X', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', 'em', 'rem' ), 'min' => 0, 'dynamic_content' => false, 'help' => __( 'Left and right padding.', 'fw' ) ),
					'border_radius' => array( 'label' => __( 'Border Radius', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', '%', 'em', 'rem' ), 'min' => 0, 'dynamic_content' => false, 'help' => __( 'Corner rounding of the button.', 'fw' ) ),
					'min_height'    => array( 'label' => __( 'Min Height', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', 'em', 'rem' ), 'min' => 0, 'dynamic_content' => false, 'help' => __( 'Optional. A FIXED button height (e.g. a source <code>h-11</code> = 44px) — the button centres its content (inline-flex) to this height. Use instead of Padding Y when the source sizes by height, not padding.', 'fw' ) ),
					'min_width'     => array( 'label' => __( 'Min Width', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', '%', 'rem', 'em' ), 'min' => 0, 'dynamic_content' => false, 'help' => __( 'Optional. Minimum button width.', 'fw' ) ),
					'max_width'     => array( 'label' => __( 'Max Width', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', '%', 'rem', 'em' ), 'min' => 0, 'dynamic_content' => false, 'help' => __( 'Optional. Maximum button width.', 'fw' ) ),
				),
			),
		),
		'template'        => '<span class="btn btn-size-preview-{{- id }}">{{- size_name }}</span>',
	),
);
