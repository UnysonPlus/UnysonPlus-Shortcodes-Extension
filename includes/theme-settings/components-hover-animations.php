<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }
/**
 * Theme Settings → Components → Hover Animations.
 *
 * ONE shared library of user-authored hover effects, consumed by every element that
 * can animate on hover: the Button shortcode's Hover Animation picker AND a Box
 * Preset's Hover Animation field (Theme Settings → Components → Box Presets) both
 * list the built-in `.btnfx-*` effects plus every entry saved here. Add or edit an
 * animation once and both pickers update — the same one-library / many-consumers
 * model as Color Presets. Each entry emits a `.btnfx-c-{slug}` class (buttons) and
 * is re-emitted onto `.boxp-{slug}` for any Box Preset that selects it.
 *
 * The tab sits right before Box Presets — hover starts at the box, then the button;
 * a section never hovers.
 *
 * Storage key `hover_animations` (legacy `button_animations`, the old Buttons-tab
 * list, is read as a fallback by unysonplus_get_custom_hover_animations()).
 */

$options = array(
	'hover_animations' => array(
		'label'           => __( 'Hover Animations', 'fw' ),
		'type'            => 'addable-box',
		'value'           => function_exists( 'unysonplus_default_custom_hover_animations' ) ? unysonplus_default_custom_hover_animations() : array(),
		'desc'            => __( 'Add your own hover effects with CSS. Use <code>{{SELECTOR}}</code> for the element (a button or a box) and <code>{{ANIM}}</code> for a unique keyframes name. Each entry appears in the Button shortcode\'s Hover Animation dropdown and in a Box Preset\'s Hover Animation field (as <code>.btnfx-c-{slug}</code>). Motion only — leave colours to the preset.', 'fw' ),
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
				'placeholder' => "{{SELECTOR}}:hover { animation: {{ANIM}} .6s ease; }\n@keyframes {{ANIM}} {\n  0%   { transform: scale(1); }\n  50%  { transform: scale(1.1); }\n  100% { transform: scale(1); }\n}",
				'desc'        => __( '<code>{{SELECTOR}}</code> = the element this animation is applied to, <code>{{ANIM}}</code> = a unique keyframes name. (<code>{{BTN}}</code> still works as an alias of <code>{{SELECTOR}}</code>.)', 'fw' ),
			),
		),
		'template'        => '<span class="btn btn-primary btnfx-preview-{{- id }}">{{- name }}</span>',
	),
);
