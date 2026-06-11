<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * Canonical schema for the shortcode "preset" settings (Color Presets,
 * Typography, Spacing/Gap, Buttons, Borders, Tables).
 *
 * SINGLE SOURCE OF TRUTH — owned by the plugin, theme-independent. Rendered by
 * Unyson's extension settings form (see settings-options.php) and saved to the
 * theme-independent store `fw_ext_settings_options:shortcodes`. The getters in
 * framework/includes/presets.php read the same keys back via
 * unysonplus_preset_store_get().
 *
 * Replaces both the old theme option fragments (general-colors / -typography /
 * -spacing / -buttons / -borders / -tables) and the stale Theme-Settings
 * injection schema, so there is exactly one definition.
 */

if ( ! function_exists( 'unysonplus_components_color_choices' ) ) :
	/**
	 * Compact-color-picker choices from the current Color Presets:
	 *   slug => array( 'label' => Name, 'color' => #hex )
	 * Wired into every preset's color fields; css-tokens.php resolves the saved
	 * slug back to a hex when emitting CSS.
	 */
	function unysonplus_components_color_choices() {
		$choices = array();
		if ( function_exists( 'unysonplus_get_color_presets' ) ) {
			foreach ( unysonplus_get_color_presets() as $cp ) {
				if ( empty( $cp['name'] ) || empty( $cp['color'] ) ) { continue; }
				$slug = trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( $cp['name'] ) ), '-' );
				if ( $slug === '' ) { continue; }
				$choices[ $slug ] = array( 'label' => $cp['name'], 'color' => $cp['color'] );
			}
		}
		return $choices;
	}
endif;

if ( ! function_exists( 'unysonplus_components_settings_options' ) ) :
	function unysonplus_components_settings_options() {
		$color_choices = unysonplus_components_color_choices();

		$gap_choices = function ( $empty_label ) {
			return function_exists( 'sc_get_gap_select_choices' )
				? sc_get_gap_select_choices( $empty_label )
				: array( '' => $empty_label );
		};

		/* ---- Color Presets ---- */
		$colors = array(
			'theme_colors' => array(
				'label'       => __( 'Color Presets', 'fw' ),
				'type'        => 'addable-box',
				'value'       => function_exists( 'unysonplus_default_color_presets' ) ? unysonplus_default_color_presets() : array(),
				'desc'        => __( 'Swatches used by the Text Color / Background Color dropdowns in every shortcode\'s Styling tab, and by the Button / Border / Table preset color fields. Each becomes <code>.text-{slug}</code> / <code>.bg-{slug}</code> and a <code>--color-{slug}</code> CSS variable.', 'fw' ),
				'sortable'    => true,
				'box-duplicate' => true,
				'attr'        => array( 'class' => 'fw-preset-2col' ),
				'width'       => 'full',
				'add-button-text' => __( 'Add another colour', 'fw' ),
				'box-options' => array(
					'name'  => array( 'label' => __( 'Color', 'fw' ), 'type' => 'text', 'value' => '', 'dynamic_content' => false ),
					'color' => array( 'label' => '', 'type' => 'color-picker', 'value' => '' ),
				),
				'template'    => '<span style="background-color:{{- color}}; width:50px; height:10px; display:inline-block"></span> {{- name }}',
			),
		);

		/* ---- Typography (font-size presets) ---- */
		$typography = array(
			'font_sizes' => array(
				'label'           => __( 'Font Size Presets', 'fw' ),
				'type'            => 'addable-box',
				'value'           => function_exists( 'unysonplus_default_font_size_presets' ) ? unysonplus_default_font_size_presets() : array(),
				'desc'            => __( 'Font sizes offered by the Font Size Preset dropdown in shortcode Styling tabs. Each becomes a <code>.font-{slug}</code> (or your literal Class) utility.', 'fw' ),
				'sortable'        => true,
				'box-duplicate'   => true,
				'attr'            => array( 'class' => 'fw-preset-2col' ),
				'width'           => 'full',
				'size'            => 'medium',
				'add-button-text' => __( 'Add another preset', 'fw' ),
				'box-options'     => array(
					'name'  => array( 'label' => __( 'Name', 'fw' ), 'type' => 'text', 'value' => '' ),
					'size'  => array( 'label' => __( 'Size', 'fw' ), 'type' => 'text', 'value' => '', 'desc' => __( 'Enter value in pixels. Don\'t include the \'px\' unit.', 'fw' ) ),
					'class' => array( 'label' => __( 'Class', 'fw' ), 'type' => 'text', 'value' => '', 'desc' => __( 'Optional. If filled, becomes a literal CSS class (e.g. type "display-1" to override Bootstrap\'s .display-1). If blank, auto-derived as a safe .font-NAME class.', 'fw' ) ),
				),
				'template'        => '<strong>{{- size }}px</strong> - {{- name }}{{ if (obj["class"]) { }} <code>.{{- obj["class"] }}</code>{{ } }}',
			),
		);

		/* ---- Spacing + Gap ---- */
		$spacing = array(
			'spacing_scale' => array(
				'label'           => __( 'Spacing Scale', 'fw' ),
				'type'            => 'addable-box',
				'value'           => function_exists( 'unysonplus_default_spacing_scale' ) ? unysonplus_default_spacing_scale() : array(),
				'desc'            => __( 'Values behind Bootstrap-style margin/padding classes. Each entry produces a complete set of utilities (<code>.m-NAME</code>, <code>.p-NAME</code>, <code>.mt-NAME</code>, <code>.mx-NAME</code>, etc.).', 'fw' ),
				'sortable'        => true,
				'box-duplicate'   => true,
				'attr'            => array( 'class' => 'fw-preset-2col' ),
				'width'           => 'full',
				'add-button-text' => __( 'Add spacer', 'fw' ),
				'box-options'     => array(
					'name' => array( 'label' => __( 'Name', 'fw' ), 'type' => 'text', 'value' => '', 'dynamic_content' => false, 'desc' => __( 'Becomes the slot suffix (e.g. "3" → <code>.m-3</code> / <code>.p-3</code>). Avoid Bootstrap-reserved names: <code>sm md lg xl xxl n1–n5 auto</code>.', 'fw' ) ),
					'size' => array( 'label' => __( 'Value', 'fw' ), 'type' => 'text', 'value' => '', 'dynamic_content' => false, 'desc' => __( 'Any CSS length: <code>0.5rem</code>, <code>8px</code>, <code>calc(1rem + 2px)</code>…', 'fw' ) ),
				),
				'template'        => '<strong>{{- name }}</strong> ({{- size }})',
			),
			'group_gaps' => array(
				'title'   => __( 'Gaps', 'fw' ),
				'type'    => 'group',
				'options' => array(
					'gap_scale' => array(
						'label'           => __( 'Gap Scale', 'fw' ),
						'type'            => 'addable-box',
						'value'           => function_exists( 'unysonplus_default_gap_scale' ) ? unysonplus_default_gap_scale() : array(),
						'desc'            => __( 'Values available in every column-gap dropdown (Default Gap below and the per-section Gap field on the Section shortcode).', 'fw' ),
						'sortable'        => true,
						'box-duplicate'   => true,
						'attr'            => array( 'class' => 'fw-preset-2col' ),
						'width'           => 'full',
						'add-button-text' => __( 'Add gap', 'fw' ),
						'box-options'     => array(
							'name' => array( 'label' => __( 'Name', 'fw' ), 'type' => 'text', 'value' => '', 'dynamic_content' => false ),
							'size' => array( 'label' => __( 'Value', 'fw' ), 'type' => 'text', 'value' => '', 'dynamic_content' => false, 'desc' => __( 'Any CSS length: <code>0.5rem</code>, <code>8px</code>, <code>1.25rem</code>…', 'fw' ) ),
						),
						'template'        => '<strong>{{- name }}</strong> ({{- size }})',
					),
					'default_gap' => array(
						'label'   => __( 'Default Gap', 'fw' ),
						'type'    => 'short-select',
						'value'   => '',
						'choices' => $gap_choices( __( 'None (use Bootstrap default — 1.5rem horizontal, 0 vertical)', 'fw' ) ),
						'desc'    => __( 'Sets both horizontal and vertical gap on every Bootstrap row site-wide.', 'fw' ),
					),
					'default_gap_x' => array(
						'label'   => __( 'Default Gap X', 'fw' ),
						'type'    => 'short-select',
						'value'   => '',
						'choices' => $gap_choices( __( 'Use Default Gap', 'fw' ) ),
						'desc'    => __( 'Overrides Default Gap on the horizontal axis only.', 'fw' ),
					),
					'default_gap_y' => array(
						'label'   => __( 'Default Gap Y', 'fw' ),
						'type'    => 'short-select',
						'value'   => '',
						'choices' => $gap_choices( __( 'Use Default Gap', 'fw' ) ),
						'desc'    => __( 'Overrides Default Gap on the vertical axis only.', 'fw' ),
					),
				),
			),
		);

		/* ---- Buttons (color presets + sizes + hover animations) ---- */
		$buttons = array(
			'button_colors' => array(
				'label'         => __( 'Button Presets', 'fw' ),
				'type'          => 'button-presets',
				'color-choices' => $color_choices,
				'value'         => function_exists( 'unysonplus_default_button_color_presets' ) ? unysonplus_default_button_color_presets() : array(),
				'desc'          => __( 'Each preset produces a <code>.btn-{id}</code> class with a live preview. Colors reference your Color Presets. Default / Hover / Active / Focus / Disabled states, typography, box, shadow and custom CSS are all supported.', 'fw' ),
			),
			'button_sizes' => array(
				'label'        => __( 'Sizes', 'fw' ),
				'type'         => 'addable-box',
				'value'        => function_exists( 'unysonplus_default_button_size_presets' ) ? unysonplus_default_button_size_presets() : array(),
				'desc'         => __( 'Each entry produces a <code>.btn-{slug}</code> class controlling only the dimensions. Pair a size with a Button Preset: <code>class="btn btn-primary btn-lg"</code>.', 'fw' ),
				'sortable'     => true,
				'add-button-text' => __( 'Add More Sizes', 'fw' ),
				'box-options'  => array(
					'id'           => array( 'type' => 'unique' ),
					'size_name'    => array( 'label' => __( 'Size Name', 'fw' ), 'type' => 'text', 'value' => '' ),
					'slug'         => array( 'label' => __( 'Slug', 'fw' ), 'type' => 'text', 'value' => '', 'desc' => __( 'Becomes the CSS class suffix (e.g. <code>sm</code> → <code>.btn-sm</code>).', 'fw' ) ),
					'font_size'    => array( 'label' => __( 'Font Size', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', 'em', 'rem' ), 'min' => 0 ),
					'line_height'  => array( 'label' => __( 'Line Height', 'fw' ), 'type' => 'short-text', 'value' => '', 'desc' => __( 'Unitless is fine (e.g. 1.5), or use a unit.', 'fw' ) ),
					'padding_y'    => array( 'label' => __( 'Padding Y (top / bottom)', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', 'em', 'rem' ), 'min' => 0 ),
					'padding_x'    => array( 'label' => __( 'Padding X (left / right)', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', 'em', 'rem' ), 'min' => 0 ),
					'border_radius'=> array( 'label' => __( 'Border Radius', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', '%', 'em', 'rem' ), 'min' => 0 ),
					'min_width'    => array( 'label' => __( 'Min Width', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', '%', 'rem', 'em' ), 'min' => 0, 'desc' => __( 'Optional.', 'fw' ) ),
					'max_width'    => array( 'label' => __( 'Max Width', 'fw' ), 'type' => 'unit-input', 'units' => array( 'px', '%', 'rem', 'em' ), 'min' => 0, 'desc' => __( 'Optional.', 'fw' ) ),
				),
				'template'     => '<span class="btn btn-size-preview-{{- id }}">{{- size_name }}</span>',
			),
			'button_animations' => array(
				'label'        => __( 'Hover Animations', 'fw' ),
				'type'         => 'addable-box',
				'value'        => function_exists( 'unysonplus_default_custom_hover_animations' ) ? unysonplus_default_custom_hover_animations() : array(),
				'desc'         => __( 'Add your own button hover effects with CSS. Use <code>{{BTN}}</code> for this button and <code>{{ANIM}}</code> for a unique keyframes name. Each entry appears in the Button shortcode\'s Hover Animation dropdown (as <code>.btnfx-c-{slug}</code>).', 'fw' ),
				'sortable'     => true,
				'add-button-text' => __( 'Add Animation', 'fw' ),
				'box-options'  => array(
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
				'template'     => '<span class="btn btn-primary btnfx-preview-{{- id }}">{{- name }}</span>',
			),
		);

		/* ---- Borders ---- */
		$borders = array(
			'border_presets' => array(
				'label'         => __( 'Border Presets', 'fw' ),
				'type'          => 'border-presets',
				'color-choices' => $color_choices,
				'value'         => function_exists( 'unysonplus_default_border_presets' ) ? unysonplus_default_border_presets() : array(),
				'desc'          => __( 'Each preset produces a <code>.colb-{name}</code> class with a live preview — pick it on a Column (Styling → Border Preset) or a Table (Table Options → Frame). Border, corner radius and box-shadow are set per Default / Hover state.', 'fw' ),
			),
		);

		/* ---- Tables ---- */
		$tables = array(
			'table_presets' => array(
				'label'         => __( 'Table Presets', 'fw' ),
				'type'          => 'table-presets',
				'color-choices' => $color_choices,
				'value'         => function_exists( 'unysonplus_default_table_presets' ) ? unysonplus_default_table_presets() : array(),
				'desc'          => __( 'Each preset produces a <code>.tbl-{name}</code> class with a live preview — pick it on a Table (Table Options → Table Preset). Header / Body / Striped / Hover / Footer / Caption skins plus grid, frame, radius and padding.', 'fw' ),
			),
		);

		$tab = function ( $title, $options ) {
			return array( 'title' => $title, 'type' => 'tab', 'options' => $options );
		};

		return apply_filters( 'unysonplus_components_settings_options', array(
			'tab_colors'     => $tab( __( 'Color Presets', 'fw' ), $colors ),
			'tab_typography' => $tab( __( 'Typography', 'fw' ), $typography ),
			'tab_spacing'    => $tab( __( 'Spacing', 'fw' ), $spacing ),
			'tab_buttons'    => $tab( __( 'Buttons', 'fw' ), $buttons ),
			'tab_borders'    => $tab( __( 'Borders', 'fw' ), $borders ),
			'tab_tables'     => $tab( __( 'Tables', 'fw' ), $tables ),
		) );
	}
endif;
