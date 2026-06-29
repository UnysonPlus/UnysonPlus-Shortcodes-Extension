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
		'label'           => __( 'Sizes', 'fw' ),
		'type'            => 'addable-box',
		'value'           => function_exists( 'unysonplus_default_button_size_presets' ) ? unysonplus_default_button_size_presets() : array(),
		'desc'            => __( 'Each entry produces a <code>.btn-{slug}</code> class controlling only the dimensions. Pair a size with a Button Preset: <code>class="btn btn-primary btn-lg"</code>.', 'fw' ),
		'sortable'        => true,
		'add-button-text' => __( 'Add More Sizes', 'fw' ),
		'box-options'     => array(
			'id'            => array( 'type' => 'unique' ),
			'size_name'     => array( 'label' => __( 'Size Name', 'fw' ), 'type' => 'text', 'value' => '' ),
			'slug'          => array( 'label' => __( 'Slug', 'fw' ), 'type' => 'text', 'value' => '', 'desc' => __( 'Becomes the CSS class suffix (e.g. <code>sm</code> → <code>.btn-sm</code>).', 'fw' ) ),
			'font_size'     => array( 'label' => __( 'Font Size', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', 'em', 'rem' ), 'min' => 0 ),
			'line_height'   => array( 'label' => __( 'Line Height', 'fw' ), 'type' => 'short-text', 'value' => '', 'desc' => __( 'Unitless is fine (e.g. 1.5), or use a unit.', 'fw' ) ),
			'padding_y'     => array( 'label' => __( 'Padding Y (top / bottom)', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', 'em', 'rem' ), 'min' => 0 ),
			'padding_x'     => array( 'label' => __( 'Padding X (left / right)', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', 'em', 'rem' ), 'min' => 0 ),
			'border_radius' => array( 'label' => __( 'Border Radius', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', '%', 'em', 'rem' ), 'min' => 0 ),
			'min_width'     => array( 'label' => __( 'Min Width', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', '%', 'rem', 'em' ), 'min' => 0, 'desc' => __( 'Optional.', 'fw' ) ),
			'max_width'     => array( 'label' => __( 'Max Width', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', '%', 'rem', 'em' ), 'min' => 0, 'desc' => __( 'Optional.', 'fw' ) ),
		),
		'template'        => '<span class="btn btn-size-preview-{{- id }}">{{- size_name }}</span>',
	),
	'button_animations' => array(
		'label'           => __( 'Hover Animations', 'fw' ),
		'type'            => 'addable-box',
		'value'           => function_exists( 'unysonplus_default_custom_hover_animations' ) ? unysonplus_default_custom_hover_animations() : array(),
		'desc'            => __( 'Add your own button hover effects with CSS. Use <code>{{BTN}}</code> for this button and <code>{{ANIM}}</code> for a unique keyframes name. Each entry appears in the Button shortcode\'s Hover Animation dropdown (as <code>.btnfx-c-{slug}</code>).', 'fw' ),
		'sortable'        => true,
		'add-button-text' => __( 'Add Animation', 'fw' ),
		'box-options'     => array(
			'id'   => array( 'type' => 'unique' ),
			'name' => array( 'label' => __( 'Name', 'fw' ), 'type' => 'text', 'value' => '' ),
			'css'  => array(
				'label'       => __( 'CSS', 'fw' ),
				'type'        => 'code-editor',
				'mode'        => 'css',
				'height'      => 160,
				'placeholder' => "{{BTN}}:hover { animation: {{ANIM}} .6s ease; }\n@keyframes {{ANIM}} {\n  0%   { transform: scale(1); }\n  50%  { transform: scale(1.1); }\n  100% { transform: scale(1); }\n}",
				'desc'        => __( '<code>{{BTN}}</code> = this button, <code>{{ANIM}}</code> = a unique keyframes name.', 'fw' ),
			),
		),
		'template'        => '<span class="btn btn-primary btnfx-preview-{{- id }}">{{- name }}</span>',
	),
);
