<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * Styling tab for every Unysonplus shortcode.
 *
 * Public API for shortcode authors:
 *
 *   Field builders (compose Styling-tab options — each shortcode writes its
 *   own Styling-tab `options` array inline and picks the builders it needs):
 *     sc_color_field( $args )           — one color picker <select> (kind: 'text' or 'bg')
 *     sc_color_field_compact( $args )   — one compact preset-dropdown + custom-color picker
 *     sc_font_size_field( $args )       — one custom font-size-preset picker
 *     // Spacing is the composite option type: array( 'type' => 'spacing', ... )
 *
 *   View-side helpers:
 *     sc_extract_styling_classes( &$atts, $keys )  — pulls atts out for inner-element styling
 *     sc_sanitize_class( $value )                  — CSS-class sanitization
 *     sc_needs_wrapper( $atts )                    — should the wrapper <div> render?
 *
 * Frontend wiring:
 *   sc_apply_styling_classes  — filters sc_build_wrapper_attr, appends Styling-tab classes
 *                               to the wrapper (skipped automatically for atts that the
 *                               shortcode has extracted via sc_extract_styling_classes).
 *
 * Admin UX:
 *   sc_emit_color_select_admin_css      — colors <option> entries in any select.sc-color-text
 *                                         or select.sc-color-bg dropdown.
 *   sc_emit_font_size_select_admin_css  — sizes <option> entries in select.sc-font-size
 *                                         proportionally to their preset value.
 *
 * Presets are sourced from the plugin getters (unysonplus_get_color_presets,
 * unysonplus_get_font_size_presets) — see framework/includes/presets.php.
 * On the official unysonplus-theme, those getters return user overrides
 * saved via Theme Settings. On other themes they return plugin defaults.
 */

/* -----------------------------------------------------------------------------
 * Utilities
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'sc_sanitize_class' ) ) :
	/**
	 * Sanitize a string for safe use as a CSS class name.
	 * Allowed: a-z A-Z 0-9 _ -. Everything else is stripped.
	 */
	function sc_sanitize_class( $value ) {
		return preg_replace( '/[^a-zA-Z0-9_-]/', '', trim( (string) $value ) );
	}
endif;

if ( ! function_exists( 'sc_theme_settings_url' ) ) :
	/**
	 * Returns a URL to the Theme Settings page, optionally scrolled to a tab.
	 * Used in field help-text links so users can jump from a shortcode picker
	 * directly to where they can ADD MORE presets.
	 *
	 * Contexts and corresponding tab anchors on unysonplus-theme:
	 *   'colors'     → tab_colors      (Color Presets)
	 *   'typography' → tab_typography  (Font sizes live here)
	 *   'spacing'    → tab_spacing     (Spacing Scale)
	 *   'buttons'    → tab_button      (Button Color Presets + Sizes)
	 *
	 * Filterable via `sc_theme_settings_url` so non-unysonplus themes can
	 * point at their own settings page / different tab IDs.
	 */
	function sc_theme_settings_url( $context = '' ) {
		$map = array(
			'colors'     => 'tab_colors',
			'typography' => 'tab_typography',
			'spacing'    => 'tab_spacing',
			'buttons'    => 'tab_buttons',
			'borders'    => 'tab_borders',
			'tables'     => 'tab_tables',
		);
		// Presets now live in Appearance → Theme Settings → Components (theme-scoped),
		// surfaced under any active theme by includes/theme-settings-presets.php.
		$slug = ( function_exists( 'fw' ) && method_exists( fw()->backend, '_get_settings_page_slug' ) )
			? fw()->backend->_get_settings_page_slug()
			: 'fw-settings';
		$url = admin_url( 'admin.php?page=' . $slug );
		if ( isset( $map[ $context ] ) ) {
			$url .= '#fw-options-tab-' . $map[ $context ];
		}
		return apply_filters( 'sc_theme_settings_url', $url, $context );
	}
endif;

if ( ! function_exists( 'sc_theme_provides_settings_ui' ) ) :
	/**
	 * True if the active theme (parent or directly active) ships the
	 * Unyson+-style Theme Settings UI (Color Presets / Typography / Spacing /
	 * Buttons tabs). Default: only `unysonplus-theme` matches. Third-party
	 * themes that re-implement those tabs should hook the
	 * `sc_theme_provides_settings_ui` filter and return true — they should
	 * also hook `sc_theme_settings_url` to point at their own URL.
	 *
	 * Cached per-request: theme can't change mid-request.
	 */
	function sc_theme_provides_settings_ui() {
		static $result = null;
		if ( $result === null ) {
			$result = (bool) apply_filters(
				'sc_theme_provides_settings_ui',
				'unysonplus-theme' === get_template()
			);
		}
		return $result;
	}
endif;

if ( ! function_exists( 'sc_plugin_provides_settings_ui' ) ) :
	/**
	 * The plugin always provides the preset editor now — the Shortcodes
	 * extension Settings form (settings-options.php), stored theme-independently.
	 * So a Settings UI is always reachable regardless of the active theme.
	 * (Formerly defined in the now-removed shortcode-options/loader.php.)
	 */
	function sc_plugin_provides_settings_ui() {
		return true;
	}
endif;

if ( ! function_exists( 'sc_styling_help_text' ) ) :
	/**
	 * Returns the localised `help` tooltip string for a Styling-tab preset
	 * picker. Switches between two wordings:
	 *  - A (theme provides Settings UI) → "Add more in Shortcode Settings → …"
	 *  - B (theme does not)             → "Install the Unyson+ Theme to manage … visually."
	 *
	 * @param string $context One of: 'color', 'font_size', 'spacing',
	 *                        'button_style', 'button_outline', 'button_size'.
	 * @return string         HTML-safe help string with a single anchor.
	 */
	function sc_styling_help_text( $context ) {
		$theme_url = 'https://github.com/UnysonPlus/UnysonPlus-Theme';
		// "UI available" if EITHER the active theme provides Theme Settings
		// tabs for these presets (unysonplus-theme) OR the plugin injects its
		// own tabs into the Theme Settings page (loader.php's
		// `fw_settings_options` filter — kicks in on any non-unysonplus theme).
		$has_ui = sc_theme_provides_settings_ui()
			|| ( function_exists( 'sc_plugin_provides_settings_ui' ) && sc_plugin_provides_settings_ui() );

		switch ( $context ) {
			case 'color':
				return $has_ui
					? sprintf(
						/* translators: %s: URL to Theme Settings → Color Presets */
						__( 'Need a colour not in this list? <a href="%s" target="_blank" rel="noopener">Add more in Shortcode Settings → Color Presets</a>.', 'fw' ),
						esc_url( sc_theme_settings_url( 'colors' ) )
					)
					: sprintf(
						/* translators: %s: URL to the Unyson+ Theme on GitHub */
						__( 'Need a colour not in this list? <a href="%s" target="_blank" rel="noopener">Install the Unyson+ Theme</a> to manage Color Presets visually.', 'fw' ),
						esc_url( $theme_url )
					);

			case 'font_size':
				return $has_ui
					? sprintf(
						/* translators: %s: URL to Theme Settings → Typography */
						__( 'Need a font size not in this list? <a href="%s" target="_blank" rel="noopener">Add more in Shortcode Settings → Typography</a>.', 'fw' ),
						esc_url( sc_theme_settings_url( 'typography' ) )
					)
					: sprintf(
						/* translators: %s: URL to the Unyson+ Theme on GitHub */
						__( 'Need a font size not in this list? <a href="%s" target="_blank" rel="noopener">Install the Unyson+ Theme</a> to manage Font Sizes visually.', 'fw' ),
						esc_url( $theme_url )
					);

			case 'spacing':
				return $has_ui
					? sprintf(
						/* translators: %s: URL to Theme Settings → Spacing */
						__( 'Need a spacing value not in this list? <a href="%s" target="_blank" rel="noopener">Add more in Shortcode Settings → Spacing</a>.', 'fw' ),
						esc_url( sc_theme_settings_url( 'spacing' ) )
					)
					: sprintf(
						/* translators: %s: URL to the Unyson+ Theme on GitHub */
						__( 'Need a spacing value not in this list? <a href="%s" target="_blank" rel="noopener">Install the Unyson+ Theme</a> to manage the Spacing Scale visually.', 'fw' ),
						esc_url( $theme_url )
					);

			case 'button_style':
				return $has_ui
					? sprintf(
						/* translators: %s: URL to Theme Settings → Buttons */
						__( 'Need a button style not in this list? <a href="%s" target="_blank" rel="noopener">Add more in Shortcode Settings → Buttons → Button Color Presets</a>.', 'fw' ),
						esc_url( sc_theme_settings_url( 'buttons' ) )
					)
					: sprintf(
						/* translators: %s: URL to the Unyson+ Theme on GitHub */
						__( 'Need a button style not in this list? <a href="%s" target="_blank" rel="noopener">Install the Unyson+ Theme</a> to manage Button Color Presets visually.', 'fw' ),
						esc_url( $theme_url )
					);

			case 'button_outline':
				return $has_ui
					? sprintf(
						/* translators: %s: URL to Theme Settings → Buttons */
						__( 'Outline variants are derived from the same Button Color Presets. <a href="%s" target="_blank" rel="noopener">Manage them in Shortcode Settings → Buttons</a>.', 'fw' ),
						esc_url( sc_theme_settings_url( 'buttons' ) )
					)
					: sprintf(
						/* translators: %s: URL to the Unyson+ Theme on GitHub */
						__( 'Outline variants are derived from the same Button Color Presets. <a href="%s" target="_blank" rel="noopener">Install the Unyson+ Theme</a> to manage them visually.', 'fw' ),
						esc_url( $theme_url )
					);

			case 'button_size':
				return $has_ui
					? sprintf(
						/* translators: %s: URL to Theme Settings → Buttons */
						__( 'Need a button size not in this list? <a href="%s" target="_blank" rel="noopener">Add more in Shortcode Settings → Buttons → Sizes</a>.', 'fw' ),
						esc_url( sc_theme_settings_url( 'buttons' ) )
					)
					: sprintf(
						/* translators: %s: URL to the Unyson+ Theme on GitHub */
						__( 'Need a button size not in this list? <a href="%s" target="_blank" rel="noopener">Install the Unyson+ Theme</a> to manage Sizes visually.', 'fw' ),
						esc_url( $theme_url )
					);
		}
		return '';
	}
endif;

if ( ! function_exists( 'sc_color_is_light' ) ) :
	/**
	 * Returns true if a hex color is essentially white — luminance so high
	 * its text would be invisible against the admin dropdown's white surface.
	 * Used by the admin <option> stylers to pick a contrasting backdrop only
	 * for `#fff` and near-whites (e.g. Bootstrap's `Light` #f8f9fa). Yellow
	 * (#ffeb3b ≈ 0.87), Lime, Light Gray etc. stay bare so their actual hue
	 * is visible.
	 *
	 * Threshold 0.95 chosen so White (1.0) and Light (0.976) trigger the
	 * backdrop, but Yellow (0.87) does not.
	 */
	function sc_color_is_light( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0].$hex[0] . $hex[1].$hex[1] . $hex[2].$hex[2];
		}
		if ( strlen( $hex ) !== 6 || ! ctype_xdigit( $hex ) ) { return false; }
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
		$lum = ( 0.299 * $r + 0.587 * $g + 0.114 * $b ) / 255;
		return $lum > 0.95;
	}
endif;

if ( ! function_exists( 'sc_extract_styling_classes' ) ) :
	/**
	 * Pull styling atts out of $atts, sanitize their values, return them as a class array,
	 * and unset them from $atts so the wrapper-class filter won't apply them to the wrapper.
	 *
	 * Use in view.php when a shortcode wants to apply styling-tab picks to an inner element
	 * (title, subtitle, icon, body, etc.) instead of the wrapper.
	 *
	 *   $title_extras = sc_extract_styling_classes( $atts, array( 'text_color', 'font_size_preset' ) );
	 *   // $atts no longer has text_color or font_size_preset → wrapper won't get them
	 *   // $title_extras = array( 'text-red', 'display-1' ) → append to your inner element's class list
	 *
	 * @param array $atts Reference. The shortcode's atts array. Modified in place.
	 * @param array $keys Att keys to extract.
	 * @return array CSS-safe class names (one per non-empty extracted att).
	 */
	function sc_extract_styling_classes( &$atts, array $keys ) {
		$classes = array();
		if ( ! is_array( $atts ) ) { return $classes; }
		foreach ( $keys as $key ) {
			if ( ! empty( $atts[ $key ] ) ) {
				// Color values may arrive as the new {predefined, custom}
				// array shape from sc_color_field_compact(). Normalise so
				// we always end up with a flat class string (silently
				// dropping a `custom` hex — callers that need the inline
				// style for inner-element custom-color picks should switch
				// to sc_extract_styling_atts() instead).
				$raw = $atts[ $key ];
				if ( is_array( $raw ) ) {
					$kind = ( strpos( $key, 'bg' ) !== false ) ? 'bg' : 'text';
					$norm = sc_normalize_color_value( $raw, $kind );
					$raw  = $norm['class'];
				}
				$cls = sc_sanitize_class( $raw );
				if ( $cls !== '' ) {
					$classes[] = $cls;
				}
			}
			unset( $atts[ $key ] );
		}
		return $classes;
	}
endif;

if ( ! function_exists( 'sc_extract_styling_atts' ) ) :
	/**
	 * Like {@see sc_extract_styling_classes()} but returns BOTH a class
	 * list AND an inline-style list, so inner-element views can honour
	 * the `custom` half of a compact color-picker pick without needing
	 * branching logic at every callsite.
	 *
	 *   $title = sc_extract_styling_atts( $atts, array( 'title_color' ) );
	 *   // $atts no longer has title_color
	 *   // $title['classes'] = array( 'text-red' )           when a preset is picked
	 *   // $title['styles']  = array( 'color: #EB001B' )     when custom hex is picked
	 *   // both arrays may be empty when the field is unset
	 *
	 * Kind ('text' | 'bg') for the inline-style emission is inferred from
	 * the att key: any key containing 'bg' gets `background: …`, every
	 * other key gets `color: …`. The `_color` suffix on a typical key
	 * (icon_color, title_color, content_color, icon_badge_color) doesn't
	 * trip the heuristic.
	 *
	 * Use this in view.php whenever a shortcode applies styling-tab picks
	 * to an inner element (title, content, icon, etc.) AND wants the
	 * compact picker's custom-color half to work on that element. Wrapper-
	 * level custom-color picks are already handled by the
	 * sc_apply_styling_classes filter — this helper is for everything
	 * else.
	 *
	 * @param array $atts Reference. Modified in place — extracted keys are unset.
	 * @param array $keys Att keys to extract.
	 * @return array { classes: string[], styles: string[] }
	 */
	function sc_extract_styling_atts( &$atts, array $keys ) {
		$out = array( 'classes' => array(), 'styles' => array() );
		if ( ! is_array( $atts ) ) { return $out; }
		foreach ( $keys as $key ) {
			if ( ! empty( $atts[ $key ] ) ) {
				$kind = ( strpos( $key, 'bg' ) !== false ) ? 'bg' : 'text';
				$norm = sc_normalize_color_value( $atts[ $key ], $kind );
				if ( $norm['class'] !== '' ) {
					$cls = sc_sanitize_class( $norm['class'] );
					if ( $cls !== '' ) {
						$out['classes'][] = $cls;
					}
				}
				if ( $norm['style'] !== '' ) {
					$out['styles'][] = $norm['style'];
				}
			}
			unset( $atts[ $key ] );
		}
		return $out;
	}
endif;

if ( ! function_exists( 'sc_flatten_spacing_value' ) ) :
	/**
	 * Flatten the nested value of a `spacing` option (margin + padding subtrees,
	 * each with all/top/right/bottom/left slots holding Bootstrap utility class
	 * names) into a flat list of class-safe strings.
	 *
	 * Pairs with the `spacing` composite option type at
	 * framework/includes/option-types/spacing/. Used by sc_apply_styling_classes
	 * so a single `spacing` att on a shortcode resolves to wrapper classes the
	 * same way the legacy flat keys (margin, margin_top, padding_bottom, …) do.
	 *
	 * @param mixed $spacing Expected: array with 'margin' and/or 'padding' subarrays.
	 *                       Anything else returns an empty array.
	 * @return string[] Sanitized class names; never includes empty strings.
	 */
	function sc_flatten_spacing_value( $spacing ) {
		$out = array();
		if ( ! is_array( $spacing ) ) { return $out; }

		// Collect every slot's class name from a margin/padding subtree pair.
		$collect = function ( $layer ) use ( &$out ) {
			if ( ! is_array( $layer ) ) { return; }
			foreach ( array( 'margin', 'padding' ) as $section ) {
				if ( empty( $layer[ $section ] ) || ! is_array( $layer[ $section ] ) ) { continue; }
				foreach ( $layer[ $section ] as $slot_value ) {
					$cls = sc_sanitize_class( (string) $slot_value );
					if ( $cls !== '' ) { $out[] = $cls; }
				}
			}
		};

		// Base / phone layer (e.g. m-3, pt-2 — apply at all widths).
		$collect( $spacing );

		// Per-device overrides (e.g. m-md-3, pt-lg-2). The values already carry
		// Bootstrap's breakpoint infix, so this just gathers them; the matching
		// @media utility rules are emitted by css-tokens.php.
		if ( ! empty( $spacing['advanced'] ) && is_array( $spacing['advanced'] ) ) {
			foreach ( array( 'md', 'lg' ) as $dev ) {
				if ( isset( $spacing['advanced'][ $dev ] ) ) {
					$collect( $spacing['advanced'][ $dev ] );
				}
			}
		}

		return $out;
	}
endif;

if ( ! function_exists( 'sc_spacing_has_value' ) ) :
	/**
	 * True iff a `spacing` att has at least one non-empty leaf — i.e. the user
	 * actually picked a margin or padding value. The full default value tree
	 * (every slot empty) is the same as "no value", so a naive `! empty()` on
	 * the att would falsely say "has value" and force the wrapper to render.
	 *
	 * Used by sc_needs_wrapper.
	 */
	function sc_spacing_has_value( $spacing ) {
		return ! empty( sc_flatten_spacing_value( $spacing ) );
	}
endif;

if ( ! function_exists( 'sc_extract_spacing_classes' ) ) :
	/**
	 * Mirror of `sc_extract_styling_classes()` but for the nested `spacing` att
	 * produced by the composite `spacing` option type. Pull the spacing att out
	 * of $atts, flatten it into class-safe strings, and UNSET $atts['spacing']
	 * so the `sc_apply_styling_classes` filter won't re-apply the same classes
	 * to the wrapper.
	 *
	 * Use in view.php when a shortcode wants to push spacing classes to an
	 * inner element instead of the outer wrapper (currently: `[column]`).
	 *
	 *   $spacing_extras = sc_extract_spacing_classes( $atts );
	 *   // $atts['spacing'] is gone → outer wrapper won't get those classes
	 *   // $spacing_extras = array( 'm-3', 'pt-2' ) → append to inner element
	 *
	 * @param array $atts Reference. The shortcode's atts array. Modified in place.
	 * @return string[]   Flat list of sanitized class names from the spacing tree.
	 */
	function sc_extract_spacing_classes( &$atts ) {
		if ( ! is_array( $atts ) ) { return array(); }
		$classes = isset( $atts['spacing'] ) ? sc_flatten_spacing_value( $atts['spacing'] ) : array();
		unset( $atts['spacing'] );
		return $classes;
	}
endif;

/* -----------------------------------------------------------------------------
 * Field builders
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'sc_color_field' ) ) :
	/**
	 * Build a single color-picker select field for the Styling tab.
	 *
	 *   'subtitle_color' => sc_color_field( array(
	 *       'label' => __( 'Subtitle Color', 'fw' ),
	 *       'kind'  => 'text',   // 'text' or 'bg'
	 *       'desc'  => __( '...', 'fw' ),
	 *   ) ),
	 *
	 * The rendered <select> gets class="sc-color-text" or class="sc-color-bg" so the
	 * admin-CSS emitter can scope its option-coloring rules consistently regardless
	 * of what field name the shortcode chose.
	 */
	function sc_color_field( $args = array() ) {
		$defaults = array(
			'label' => __( 'Color', 'fw' ),
			'kind'  => 'text',
			'value' => '',
			'desc'  => '',
		);
		$args = array_merge( $defaults, $args );
		$kind = ( $args['kind'] === 'bg' ) ? 'bg' : 'text';

		$field = array(
			'label'   => $args['label'],
			'type'    => 'select',
			'value'   => $args['value'],
			'choices' => sc_get_color_select_choices( $kind ),
			'attr'    => array( 'class' => 'sc-color-' . $kind ),
			'help'    => sc_styling_help_text( 'color' ),
		);
		if ( $args['desc'] !== '' ) {
			$field['desc'] = $args['desc'];
		}
		return $field;
	}
endif;

if ( ! function_exists( 'sc_color_field_compact' ) ) :
	/**
	 * Drop-in replacement for {@see sc_color_field()} that returns the
	 * `predefined-colors-color-picker-compact` option type instead of a
	 * plain <select>. Same call signature, same `kind` ('text' | 'bg')
	 * semantics, same saved-class convention (`text-{slug}` / `bg-{slug}`).
	 *
	 * Difference: shortcode editors get a compact preset dropdown PLUS an
	 * inline custom color picker on the same row. Picking a preset stores
	 * the class name in `predefined` (consumer emits `class="..."`);
	 * picking a custom color stores the hex in `custom` (consumer emits
	 * inline `style="color: …"` / `style="background: …"`). Both halves
	 * are mutually exclusive — the picker keeps them in sync via the
	 * existing predefined-colors-color-picker-compact JS.
	 *
	 * Choices are built from the live plugin palette via
	 * {@see unysonplus_color_preset_slug_map()} so the dropdown matches
	 * whatever Theme Settings → General → Colors has configured.
	 *
	 * Usage: shortcodes call this directly when composing their Styling-tab
	 * `options` array — it's the standard builder for a preset+custom color
	 * field. `sc_color_field()` (plain <select>, no custom picker) remains
	 * available for fields that don't want the inline custom-color sidekick.
	 */
	function sc_color_field_compact( $args = array() ) {
		$defaults = array(
			'label'  => __( 'Color', 'fw' ),
			'kind'   => 'text',
			'value'  => '',
			'desc'   => '',
			'picker' => 'color-picker', // or 'rgba-color-picker' for alpha
		);
		$args = array_merge( $defaults, $args );
		$kind = ( $args['kind'] === 'bg' ) ? 'bg' : 'text';

		// Build the {'class-name' => {'label','color'}} choices map from
		// the live palette. The class-name keys ARE what gets saved into
		// the `predefined` half, so they're exactly what the view will
		// emit as `class="..."`.
		$choices = array();
		if ( function_exists( 'unysonplus_color_preset_slug_map' ) ) {
			$prefix = $kind === 'bg' ? 'bg-' : 'text-';
			foreach ( unysonplus_color_preset_slug_map() as $slug => $hex ) {
				$choices[ $prefix . $slug ] = array(
					'label' => ucwords( str_replace( '-', ' ', $slug ) ),
					'color' => $hex,
				);
			}
		}

		// Default values: accept the legacy string shape so a shortcode
		// passing `'value' => 'text-red'` still picks the right preset on
		// first render. New code should pass the array shape explicitly.
		$value = $args['value'];
		if ( is_string( $value ) ) {
			$value = array( 'predefined' => $value, 'custom' => '' );
		} elseif ( ! is_array( $value ) ) {
			$value = array( 'predefined' => '', 'custom' => '' );
		}

		$field = array(
			'label'   => $args['label'],
			'type'    => 'predefined-colors-color-picker-compact',
			'picker'  => $args['picker'],
			'value'   => $value,
			'choices' => $choices,
			'help'    => sc_styling_help_text( 'color' ),
		);
		if ( $args['desc'] !== '' ) {
			$field['desc'] = $args['desc'];
		}
		return $field;
	}
endif;

if ( ! function_exists( 'sc_normalize_color_value' ) ) :
	/**
	 * Resolve a Styling-tab color value (text_color / bg_color / any
	 * inner-element color picked via sc_color_field*) to a class + style
	 * pair the consuming view can emit verbatim.
	 *
	 * Accepts BOTH the legacy string shape produced by `sc_color_field()`'s
	 * plain <select> (`'text-red'`, `'bg-light-blue'`, `''`) AND the new
	 * array shape produced by `sc_color_field_compact()`
	 * (`{ predefined: 'text-red', custom: '' }` or
	 *  `{ predefined: '', custom: '#EB001B' }`). This is the single
	 * funnel both shapes flow through, so any consumer that calls this
	 * helper supports both option-types without branching.
	 *
	 * @param mixed  $value string|array as described above
	 * @param string $kind  'text' or 'bg' — controls the CSS property
	 *                      emitted when only the `custom` half is set
	 * @return array{ class: string, style: string }
	 */
	function sc_normalize_color_value( $value, $kind = 'text' ) {
		$out = array( 'class' => '', 'style' => '' );

		if ( is_string( $value ) ) {
			$out['class'] = trim( $value );
			return $out;
		}
		if ( ! is_array( $value ) ) {
			return $out;
		}

		$predefined = isset( $value['predefined'] ) ? trim( (string) $value['predefined'] ) : '';
		$custom     = isset( $value['custom'] )     ? trim( (string) $value['custom'] )     : '';

		// Preset wins when both halves are present — matches the option
		// type's UI behaviour (mutual exclusion clears the other side).
		if ( $predefined !== '' ) {
			$out['class'] = $predefined;
			return $out;
		}

		if ( $custom !== '' && $custom !== 'transparent' && $custom !== 'rgba(0,0,0,0)' ) {
			$prop = ( $kind === 'bg' ) ? 'background' : 'color';
			// Defense in depth — strip anything that isn't part of a
			// well-formed CSS color token. Allows hex, rgb()/rgba(), and
			// named colors; blocks colons / semicolons / declarations
			// injection.
			$custom = preg_replace( '/[^A-Za-z0-9#\(\),.%\s]/', '', $custom );
			if ( $custom !== '' ) {
				$out['style'] = $prop . ': ' . $custom;
			}
		}

		return $out;
	}
endif;

if ( ! function_exists( 'sc_color_to_css' ) ) :
	/**
	 * Resolve a preset-or-custom color value (from sc_color_field_compact) to a
	 * single CSS color STRING — for consumers that need a value (a CSS custom
	 * property, an inline `color:`/`background:`, a JS/canvas color), not a class.
	 *
	 *   - preset (`predefined` like 'text-red'/'bg-blue') → `var(--color-{slug})`
	 *     (live-linked to Theme Settings → General → Colors). When $as_hex is true
	 *     (e.g. WebGL / canvas, which can't read a CSS var) → the slug's hex from
	 *     unysonplus_color_preset_slug_map().
	 *   - custom hex/rgb(a) → the sanitised value.
	 *   - legacy plain string (pre-compact saves) → passed through.
	 *   - nothing set → $fallback.
	 *
	 * @param mixed  $value    string|array as produced by sc_color_field*()
	 * @param string $fallback returned when nothing usable is set
	 * @param bool   $as_hex   resolve a preset to its hex instead of var(--color-…)
	 * @return string a CSS color token (possibly empty if $fallback is '')
	 */
	function sc_color_to_css( $value, $fallback = '', $as_hex = false ) {
		if ( is_string( $value ) ) {
			return $value !== '' ? $value : $fallback;
		}
		if ( ! is_array( $value ) ) {
			return $fallback;
		}

		$predefined = isset( $value['predefined'] ) ? trim( (string) $value['predefined'] ) : '';
		$custom     = isset( $value['custom'] )     ? trim( (string) $value['custom'] )     : '';

		if ( $predefined !== '' ) {
			$slug = preg_replace( '/[^a-z0-9\-]/', '', preg_replace( '/^(text|bg)-/', '', $predefined ) );
			if ( $slug === '' ) {
				return $fallback;
			}
			if ( $as_hex && function_exists( 'unysonplus_color_preset_slug_map' ) ) {
				$map = unysonplus_color_preset_slug_map();
				return isset( $map[ $slug ] ) ? $map[ $slug ] : $fallback;
			}
			return 'var(--color-' . $slug . ')';
		}

		if ( $custom !== '' && $custom !== 'transparent' ) {
			$custom = preg_replace( '/[^A-Za-z0-9#\(\),.%\s]/', '', $custom );
			return $custom !== '' ? $custom : $fallback;
		}

		return $fallback;
	}
endif;

if ( ! function_exists( 'sc_font_size_field' ) ) :
	/**
	 * Build a single font-size-preset select field for the Styling tab.
	 *
	 *   'subtitle_size' => sc_font_size_field( array(
	 *       'label' => __( 'Subtitle Size Preset', 'fw' ),
	 *   ) ),
	 *
	 * The rendered <select> gets class="sc-font-size" so the admin-CSS emitter
	 * can size its options proportionally.
	 */
	function sc_font_size_field( $args = array() ) {
		$defaults = array(
			'label' => __( 'Font Size Preset', 'fw' ),
			'value' => '',
			'desc'  => '',
		);
		$args = array_merge( $defaults, $args );

		$field = array(
			'label'   => $args['label'],
			'type'    => 'select',
			'value'   => $args['value'],
			'choices' => sc_get_font_size_preset_choices(),
			'attr'    => array( 'class' => 'sc-font-size' ),
			'help'    => sc_styling_help_text( 'font_size' ),
		);
		if ( $args['desc'] !== '' ) {
			$field['desc'] = $args['desc'];
		}
		return $field;
	}
endif;

if ( ! function_exists( 'sc_alignment_field' ) ) :
	/**
	 * Build a horizontal-alignment image-picker field (Left / Center / Right),
	 * reusable across shortcodes. The swatches are the shared SVGs under
	 * `static/img/alignment/`; the stored value is `left` / `center` / `right`
	 * (or `''` when `inherit` is on — meaning "follow the parent/master").
	 *
	 *   'alignment'   => sc_alignment_field( array( 'label' => __( 'Alignment', 'fw' ) ) ),
	 *   'title_align' => sc_alignment_field( array( 'label' => __( 'Title Alignment', 'fw' ), 'inherit' => true ) ),
	 *
	 * Map the value to a CSS utility with sc_alignment_class() so callers don't
	 * duplicate the left→text-start / center→text-center / right→text-end mapping.
	 *
	 * @param array $args label, value (default 'left'), desc, inherit (bool — when
	 *                    true, prepend an "Inherit" choice and default to '').
	 */
	function sc_alignment_field( $args = array() ) {
		$defaults = array(
			'label'   => __( 'Alignment', 'fw' ),
			'value'   => 'left',
			'desc'    => '',
			'help'    => '',
			'inherit' => false,
		);
		$args = array_merge( $defaults, $args );

		$base   = fw_ext( 'shortcodes' )->get_declared_URI( '/static/img/alignment' );
		$swatch = function ( $file, $title ) use ( $base ) {
			return array( 'small' => array( 'src' => $base . '/' . $file, 'height' => 40, 'title' => $title ) );
		};

		$choices = array();
		if ( $args['inherit'] ) {
			$choices[''] = $swatch( 'inherit.svg', __( 'Inherit', 'fw' ) );
		}
		$choices['left']   = $swatch( 'left.svg',   __( 'Left', 'fw' ) );
		$choices['center'] = $swatch( 'center.svg', __( 'Center', 'fw' ) );
		$choices['right']  = $swatch( 'right.svg',  __( 'Right', 'fw' ) );

		$field = array(
			'type'    => 'image-picker',
			'label'   => $args['label'],
			'value'   => $args['inherit'] ? '' : $args['value'],
			'choices' => $choices,
			'attr'    => array( 'class' => 'sc-alignment' ),
		);
		if ( $args['desc'] !== '' ) {
			$field['desc'] = $args['desc'];
		}
		if ( $args['help'] !== '' ) {
			$field['help'] = $args['help'];
		}
		return $field;
	}
endif;

if ( ! function_exists( 'sc_alignment_class' ) ) :
	/**
	 * Map a stored alignment value to its Bootstrap text-* utility class.
	 * `''` (inherit / unset) returns `''` so the caller can fall back to a
	 * master value. Unknown values also return `''`.
	 *
	 * @param string $value left | center | right | ''
	 * @return string text-start | text-center | text-end | ''
	 */
	function sc_alignment_class( $value ) {
		switch ( $value ) {
			case 'left':   return 'text-start';
			case 'center': return 'text-center';
			case 'right':  return 'text-end';
			default:       return '';
		}
	}
endif;

if ( ! function_exists( 'sc_spacing_field' ) ) :
	/**
	 * Build a single Bootstrap-spacing select field for the Styling tab.
	 *
	 *   'margin'    => sc_spacing_field( array( 'label' => __( 'Margin', 'fw' ),    'prefix' => 'm' ) ),
	 *   'padding_y' => sc_spacing_field( array( 'label' => __( 'Padding Y', 'fw' ), 'prefix' => 'py' ) ),
	 *
	 * `prefix` is the Bootstrap utility shorthand: 'm', 'mt', 'mb', 'ms', 'me', 'mx', 'my',
	 * 'p', 'pt', 'pb', 'ps', 'pe', 'px', 'py'. The dropdown values become Bootstrap class
	 * names (e.g. 'm-3', 'py-2') which the spacing-override block in css-tokens.php
	 * makes site-customizable via Theme Settings → General → Spacing.
	 */
	function sc_spacing_field( $args = array() ) {
		$defaults = array(
			'label'  => __( '', 'fw' ),
			'prefix' => 'm',
			'value'  => '',
			'desc'   => '',
		);
		$args = array_merge( $defaults, $args );

		$field = array(
			'label'   => $args['label'],
			'type'    => 'short-select',
			'value'   => $args['value'],
			'choices' => sc_get_spacing_select_choices( $args['prefix'] ),
			'attr'    => array( 'class' => 'sc-spacing' ),
		);
		// Only attach the "add more" help link to the All-Sides field.
		// Per-side fields (Top / Right / Bottom / Left) intentionally have an
		// empty label — repeating the same help icon on every one of them is
		// visual clutter. The All-Sides field's help link covers both axes.
		if ( $args['label'] !== '' ) {
			$field['help'] = sc_styling_help_text( 'spacing' );
		}
		if ( $args['desc'] !== '' ) {
			$field['desc'] = $args['desc'];
		}
		return $field;
	}
endif;

/* -----------------------------------------------------------------------------
 * Choice builders (used by the field builders and reusable elsewhere)
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'sc_get_color_select_choices' ) ) :
	function sc_get_color_select_choices( $kind = 'text' ) {
		$out = array( '' => __( 'Default', 'fw' ) );
		if ( ! function_exists( 'unysonplus_get_color_presets' ) ) { return $out; }
		foreach ( unysonplus_get_color_presets() as $entry ) {
			if ( empty( $entry['name'] ) ) { continue; }
			$slug = trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( $entry['name'] ) ), '-' );
			if ( $slug === '' ) { continue; }
			$out[ "{$kind}-{$slug}" ] = $entry['name'];
		}
		return $out;
	}
endif;

if ( ! function_exists( 'sc_get_spacing_select_choices' ) ) :
	/**
	 * Returns the spacing-utility choices for a select field with the given prefix.
	 * Reads the live spacing scale (Theme Settings override or plugin defaults) so
	 * adding entries in Shortcode Settings → General → Spacing immediately appears in
	 * every Styling-tab Margin/Padding dropdown across all shortcodes.
	 *
	 * Values are Bootstrap-style class names (e.g. m-0, m-1, m-3, m-huge).
	 * Labels show the underlying spacing value so users can see what they're picking.
	 */
	function sc_get_spacing_select_choices( $prefix = 'm' ) {
		$prefix = sc_sanitize_class( $prefix );
		if ( $prefix === '' ) { $prefix = 'm'; }

		$out = array( '' => __( 'Default', 'fw' ) );

		$scale = function_exists( 'unysonplus_get_spacing_scale' ) ? unysonplus_get_spacing_scale() : array();
		if ( ! is_array( $scale ) ) { return $out; }

		foreach ( $scale as $entry ) {
			if ( ! is_array( $entry ) ) { continue; }
			if ( ! isset( $entry['name'] ) || $entry['name'] === '' ) { continue; }

			$slug = strtolower( sc_sanitize_class( $entry['name'] ) );
			if ( $slug === '' ) { continue; }

			$size  = isset( $entry['size'] ) ? $entry['size'] : '';
			$label = $entry['name'] . ( $size !== '' ? ' (' . $size . ')' : '' );

			$out[ $prefix . '-' . $slug ] = $label;
		}
		return $out;
	}
endif;

if ( ! function_exists( 'sc_get_gap_select_choices' ) ) :
	/**
	 * Returns dropdown choices for a column-gap picker, sourced from the live
	 * Gap Scale (Theme Settings → General → Spacing → Gaps, or plugin defaults).
	 *
	 * Values are scale slugs (e.g. `3`, `huge`) — NOT full utility class names.
	 * Callers resolve them on the output side by either appending a modifier
	 * class (`section--gap-{slug}`) or a utility class (`g-{slug}` / `gx-{slug}`
	 * / `gy-{slug}`); css-tokens.php emits the matching rules.
	 *
	 * @param string|null $empty_label Label for the empty-string entry at the top
	 *                                 of the dropdown. Pass `null` to omit it.
	 *                                 Typical: "Use Default Gap" on per-instance
	 *                                 fields, or "None" on the site-default field.
	 * @return array
	 */
	function sc_get_gap_select_choices( $empty_label = null ) {
		$out = array();
		if ( $empty_label !== null ) {
			$out[''] = $empty_label;
		}
		if ( ! function_exists( 'unysonplus_get_gap_scale' ) ) {
			return $out;
		}
		foreach ( unysonplus_get_gap_scale() as $entry ) {
			if ( ! is_array( $entry ) || ! isset( $entry['name'] ) || $entry['name'] === '' ) { continue; }
			$slug = strtolower( sc_sanitize_class( $entry['name'] ) );
			if ( $slug === '' ) { continue; }
			$size  = isset( $entry['size'] ) ? $entry['size'] : '';
			$label = $entry['name'] . ( $size !== '' ? ' (' . $size . ')' : '' );
			$out[ $slug ] = $label;
		}
		return $out;
	}
endif;

if ( ! function_exists( 'sc_get_button_size_choices' ) ) :
	/**
	 * Returns dropdown choices for a button's size picker, sourced from the
	 * user's saved button size presets (Theme Settings → Buttons → Sizes).
	 * Each preset's `slug` becomes the option value `btn-{slug}`. Adding a row
	 * in Theme Settings instantly shows up in every Button shortcode's Size
	 * dropdown.
	 */
	function sc_get_button_size_choices() {
		$out = array( '' => __( 'Normal', 'fw' ) );
		if ( ! function_exists( 'unysonplus_get_button_size_presets' ) ) { return $out; }
		foreach ( unysonplus_get_button_size_presets() as $bs ) {
			if ( empty( $bs['slug'] ) ) { continue; }
			$slug = sc_sanitize_class( $bs['slug'] );
			if ( $slug === '' ) { continue; }
			$name = ! empty( $bs['size_name'] ) ? $bs['size_name'] : $bs['slug'];
			$out[ 'btn-' . $slug ] = $name;
		}
		return $out;
	}
endif;

if ( ! function_exists( 'sc_get_hover_animation_choices' ) ) :
	/**
	 * Choices for a button's Hover Animation picker. The built-in values are CSS
	 * classes shipped in button/static/css/hover-fx.css — MOTION-ONLY effects
	 * (transform / shadow / radius / text) that layer over any button preset (solid,
	 * outline, gradient) without touching its colors. The user's Custom Hover
	 * Animations (Theme Settings → Buttons) are appended as `btnfx-c-{slug}` entries,
	 * generated into the preset stylesheet by css-tokens.php. (Flat map: no optgroups.)
	 */
	function sc_get_hover_animation_choices() {
		$choices = array(
			''                => __( 'None', 'fw' ),
			'btnfx-lift'       => __( 'Lift', 'fw' ),
			'btnfx-grow'       => __( 'Grow', 'fw' ),
			'btnfx-shine'      => __( 'Shine sweep', 'fw' ),
			'btnfx-glow'       => __( 'Glow pulse', 'fw' ),
			'btnfx-ring'       => __( 'Expanding ring', 'fw' ),
			'btnfx-longshadow' => __( 'Long shadow', 'fw' ),
			'btnfx-pill'       => __( 'Pill morph', 'fw' ),
			'btnfx-spacing'    => __( 'Letter spacing', 'fw' ),
			'btnfx-underline'  => __( 'Underline', 'fw' ),
			'btnfx-tilt'       => __( 'Tilt (3D)', 'fw' ),
			'btnfx-skew'       => __( 'Skew', 'fw' ),
			'btnfx-rotate'     => __( 'Rotate', 'fw' ),
			'btnfx-push'       => __( '3D push', 'fw' ),
			'btnfx-inset'      => __( 'Inset press', 'fw' ),
			'btnfx-pop'        => __( 'Pop', 'fw' ),
			'btnfx-bounce'     => __( 'Bounce', 'fw' ),
			'btnfx-float'      => __( 'Float', 'fw' ),
			'btnfx-jelly'      => __( 'Jelly', 'fw' ),
			'btnfx-heartbeat'  => __( 'Heartbeat', 'fw' ),
			'btnfx-wobble'     => __( 'Wobble', 'fw' ),
			'btnfx-shake'      => __( 'Shake', 'fw' ),
			'btnfx-glitch'     => __( 'Glitch', 'fw' ),
			// Ported from the demo (universal). Fills/ripple/split/sweep darken via a
			// background overlay (no-op on gradient presets); corners/meet/neon use
			// the button's own color; offset/blob are motion-only.
			'btnfx-offset'         => __( 'Offset shadow', 'fw' ),
			'btnfx-blob'           => __( 'Liquid blob', 'fw' ),
			'btnfx-fill-right'     => __( 'Fill: slide right', 'fw' ),
			'btnfx-fill-up'        => __( 'Fill: slide up', 'fw' ),
			'btnfx-fill-center'    => __( 'Fill: from center', 'fw' ),
			'btnfx-fill-center-v'  => __( 'Fill: center vertical', 'fw' ),
			'btnfx-fill-diagonal'  => __( 'Fill: diagonal', 'fw' ),
			'btnfx-ripple'         => __( 'Ripple', 'fw' ),
			'btnfx-split'          => __( 'Split wipe', 'fw' ),
			'btnfx-sweep'          => __( 'Shade sweep', 'fw' ),
			'btnfx-corners'        => __( 'Border: corners', 'fw' ),
			'btnfx-meet'           => __( 'Border: lines meet', 'fw' ),
			'btnfx-neon'           => __( 'Neon glow', 'fw' ),
		);

		// Append the user's custom hover animations (Theme Settings → Buttons).
		if ( function_exists( 'unysonplus_get_custom_hover_animations' ) && function_exists( 'unysonplus_custom_hover_animation_slug_map' ) ) {
			$slug_map = unysonplus_custom_hover_animation_slug_map();
			foreach ( unysonplus_get_custom_hover_animations() as $ca ) {
				if ( ! is_array( $ca ) || empty( $ca['id'] ) ) { continue; }
				$id = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $ca['id'] );
				if ( $id === '' || ! isset( $slug_map[ $id ] ) ) { continue; }
				if ( trim( (string) ( $ca['css'] ?? '' ) ) === '' ) { continue; } // skip empty entries
				$name = ! empty( $ca['name'] ) ? $ca['name'] : $slug_map[ $id ];
				$choices[ 'btnfx-c-' . $slug_map[ $id ] ] = $name;
			}
		}

		return $choices;
	}
endif;

if ( ! function_exists( 'sc_get_color_preset_slug_choices' ) ) :
	/**
	 * Slug-keyed choices for any select that picks a Color Preset by slug
	 * (e.g. Theme Settings → Buttons color fields). Returns
	 * `[ '' => 'Default', slug => display_name, … ]`. Pairs with
	 * `sc_emit_color_preset_select_admin_css` for option-level colouring.
	 */
	function sc_get_color_preset_slug_choices() {
		$out = array( '' => __( 'Default', 'fw' ) );
		if ( ! function_exists( 'unysonplus_get_color_presets' ) ) { return $out; }
		foreach ( unysonplus_get_color_presets() as $entry ) {
			if ( empty( $entry['name'] ) ) { continue; }
			$slug = trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( $entry['name'] ) ), '-' );
			if ( $slug === '' ) { continue; }
			$out[ $slug ] = $entry['name'];
		}
		return $out;
	}
endif;

if ( ! function_exists( 'sc_get_button_style_choices' ) ) :
	/**
	 * Returns dropdown choices for a button's style / outline picker, sourced
	 * from the user's saved button color presets (Theme Settings → Buttons).
	 * Each preset's `id` becomes the option value `btn-{id}` (filled) or
	 * `btn-outline-{id}` (outline). Adding a row in Theme Settings instantly
	 * shows up in every Button shortcode's dropdown.
	 *
	 * @param bool $outline true → `btn-outline-{id}` keys + a blank "No Outline"
	 *                      option prepended. false → `btn-{id}` keys.
	 */
	function sc_get_button_style_choices( $outline = false ) {
		// 'Default' (value '') = the bare `.btn` base — a basic bordered button with no color
		// preset. Listed first, so it's the picker's default; the Site Converter selects it so a
		// converted button is styled by the child theme's own `.btn` rule (which the user can
		// later override by switching to a Color Preset such as Primary).
		$out = $outline ? array( '' => __( 'No Outline', 'fw' ) ) : array( '' => __( 'Default', 'fw' ) );
		if ( ! function_exists( 'unysonplus_get_button_color_presets' ) ) { return $out; }
		// Readable, name-based class slug ('Primary' → btn-primary) — shared with
		// css-tokens so the saved option value matches the generated CSS class.
		$slug_map = function_exists( 'unysonplus_button_preset_slug_map' )
			? unysonplus_button_preset_slug_map()
			: array();
		$prefix = $outline ? 'btn-outline-' : 'btn-';
		foreach ( unysonplus_get_button_color_presets() as $bp ) {
			if ( empty( $bp['id'] ) ) { continue; }
			$id = sc_sanitize_class( $bp['id'] );
			if ( $id === '' ) { continue; }
			$slug = isset( $slug_map[ $id ] ) ? $slug_map[ $id ] : $id;
			$name = ! empty( $bp['color_name'] ) ? $bp['color_name'] : $bp['id'];
			$out[ $prefix . $slug ] = $outline ? sprintf( __( 'Outline %s', 'fw' ), $name ) : $name;
		}
		return $out;
	}
endif;

if ( ! function_exists( 'sc_get_border_preset_choices' ) ) :
	/**
	 * Dropdown choices for a column's Border Preset picker, sourced from the saved
	 * Border Presets (Theme Settings → General → Borders). Each preset's name-based
	 * slug becomes the option value `boxp-{slug}` (matching the generated CSS class
	 * in css-tokens.php). A blank "None" is prepended. Adding a preset in Theme
	 * Settings instantly shows up in every Column's Border Preset dropdown.
	 */
	function sc_get_border_preset_choices() {
		$out = array( '' => __( 'None', 'fw' ) );
		if ( ! function_exists( 'unysonplus_get_border_presets' ) ) { return $out; }
		$slug_map = function_exists( 'unysonplus_border_preset_slug_map' )
			? unysonplus_border_preset_slug_map()
			: array();
		foreach ( unysonplus_get_border_presets() as $bp ) {
			if ( empty( $bp['id'] ) ) { continue; }
			$id = sc_sanitize_class( $bp['id'] );
			if ( $id === '' ) { continue; }
			$slug = isset( $slug_map[ $id ] ) ? $slug_map[ $id ] : $id;
			$name = ! empty( $bp['preset_name'] ) ? $bp['preset_name'] : $bp['id'];
			$out[ 'boxp-' . $slug ] = $name;
		}
		return $out;
	}
endif;

if ( ! function_exists( 'sc_get_table_preset_choices' ) ) :
	/**
	 * Table Preset choices for the Table shortcode's `table-style-picker` field:
	 * `tbl-{slug} => Name`, with a blank "None" prepended. The slug matches the
	 * generated CSS class in css-tokens.php. Adding a preset in Shortcode Settings →
	 * Components → Tables instantly shows up here.
	 */
	function sc_get_table_preset_choices() {
		$out = array( '' => __( 'None', 'fw' ) );
		if ( ! function_exists( 'unysonplus_get_table_presets' ) ) { return $out; }
		$slug_map = function_exists( 'unysonplus_table_preset_slug_map' )
			? unysonplus_table_preset_slug_map()
			: array();
		foreach ( unysonplus_get_table_presets() as $tp ) {
			if ( empty( $tp['id'] ) ) { continue; }
			$id = sc_sanitize_class( $tp['id'] );
			if ( $id === '' ) { continue; }
			$slug = isset( $slug_map[ $id ] ) ? $slug_map[ $id ] : $id;
			$name = ! empty( $tp['preset_name'] ) ? $tp['preset_name'] : $tp['id'];
			$out[ 'tbl-' . $slug ] = $name;
		}
		return $out;
	}
endif;

if ( ! function_exists( 'sc_get_font_size_preset_choices' ) ) :
	function sc_get_font_size_preset_choices() {
		$out = array( '' => __( 'Default', 'fw' ) );
		if ( ! function_exists( 'unysonplus_get_font_size_presets' ) ) { return $out; }
		foreach ( unysonplus_get_font_size_presets() as $entry ) {
			if ( empty( $entry['size'] ) ) { continue; }
			if ( ! empty( $entry['class'] ) ) {
				$class = sc_sanitize_class( $entry['class'] );
				if ( $class === '' ) { continue; }
			} else {
				if ( empty( $entry['name'] ) ) { continue; }
				$slug = trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( $entry['name'] ) ), '-' );
				if ( $slug === '' ) { continue; }
				$class = 'font-' . $slug;
			}
			$out[ $class ] = $entry['name'];
		}
		return $out;
	}
endif;

/* -----------------------------------------------------------------------------
 * The standard Styling-tab field set
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'sc_styling_att_keys' ) ) :
	/**
	 * Single source of truth for the att keys that the Styling tab produces.
	 * Used by sc_needs_wrapper() and sc_apply_styling_classes() so adding a
	 * new field only requires updating one place.
	 */
	function sc_styling_att_keys() {
		return array(
			// Typography & Colors
			'text_color', 'bg_color', 'font_size_preset',
			// Margins
			'margin', 'margin_top', 'margin_bottom', 'margin_start', 'margin_end',
			// Paddings
			'padding', 'padding_top', 'padding_bottom', 'padding_start', 'padding_end',
		);
	}
endif;

/*
 * NOTE: the former `sc_get_styling_fields()` aggregator was removed in
 * shortcodes 1.4.49. Each shortcode now writes its Styling tab inline in
 * its own options.php — the tab varied enough per shortcode that the
 * skip/extras/compact_colors indirection cost more than it saved. The
 * per-field builders below (`sc_color_field_compact()`, `sc_font_size_field()`,
 * the `'type' => 'spacing'` composite, `sc_styling_help_text()`) are still
 * the canonical way to declare each field; shortcodes just compose them
 * directly now.
 */

/* -----------------------------------------------------------------------------
 * View helper — should the wrapper render?
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'sc_needs_wrapper' ) ) :
	/**
	 * Decide whether a shortcode view.php should render its wrapper element.
	 * Returns true if any of the wrapper-affecting atts are set. Filter
	 * `sc_needs_wrapper` lets future tabs opt in without per-shortcode edits.
	 */
	function sc_needs_wrapper( $atts ) {
		if ( ! is_array( $atts ) ) { return false; }

		// Advanced tab — CSS ID / Class
		if ( ! empty( $atts['css_id'] ) || ! empty( $atts['css_class'] ) ) { return true; }

		// Advanced tab — Custom HTML Attributes (any non-empty row)
		if ( ! empty( $atts['custom_attrs'] ) && is_array( $atts['custom_attrs'] ) ) {
			foreach ( $atts['custom_attrs'] as $row ) {
				if ( ! empty( $row['name'] ) ) { return true; }
			}
		}

		// Styling tab — colors. These are compact-picker values that are ALWAYS
		// a non-empty array { predefined:'', custom:'' } even when nothing is
		// picked, so naive empty() is wrong. Use the same normalizer the wrapper
		// uses: a truly-set value yields a class or an inline style.
		foreach ( array( 'text_color' => 'text', 'bg_color' => 'bg' ) as $key => $kind ) {
			if ( isset( $atts[ $key ] ) && function_exists( 'sc_normalize_color_value' ) ) {
				$norm = sc_normalize_color_value( $atts[ $key ], $kind );
				if ( $norm['class'] !== '' || $norm['style'] !== '' ) { return true; }
			}
		}

		// Styling tab — remaining scalar keys (font size preset + flat margins/
		// paddings). These are plain strings, so empty() is the right test.
		foreach ( sc_styling_att_keys() as $k ) {
			if ( $k === 'text_color' || $k === 'bg_color' ) { continue; } // handled above
			if ( ! empty( $atts[ $k ] ) ) { return true; }
		}

		// Spacing composite att — naive empty() is wrong here because the
		// default value tree has every slot keyed but empty, so empty() = false
		// even when the user hasn't picked anything. Walk the leaves instead.
		if ( ! empty( $atts['spacing'] ) && sc_spacing_has_value( $atts['spacing'] ) ) {
			return true;
		}

		// Animations tab — an entrance effect is chosen when animation.effect is set & not 'none'.
		if ( isset( $atts['animation']['effect'] ) && $atts['animation']['effect'] !== 'none' && $atts['animation']['effect'] !== '' ) {
			return true;
		}

		return apply_filters( 'sc_needs_wrapper', false, $atts );
	}
endif;

/* -----------------------------------------------------------------------------
 * Frontend wiring — Styling-tab atts → wrapper classes (default behavior)
 * -------------------------------------------------------------------------- */

/* -----------------------------------------------------------------------------
 * Wire the framework's spacing scale into the standalone spacing option type
 * -------------------------------------------------------------------------- */

/**
 * `Fw_Option_Type_Spacing` ships theme-agnostic — its built-in default scale
 * is Bootstrap 5's $spacers, and it exposes `fw_option_type_spacing_scale`
 * as its only escape hatch for site-wide overrides. This hookup plugs the
 * existing `unysonplus_get_spacing_scale()` (Theme Settings → General →
 * Spacing on unysonplus-theme, or plugin defaults from `presets.php` on any
 * other theme) into that filter — so editing the scale in one place still
 * propagates to every Margin & Padding dropdown produced by the option
 * type, with no direct coupling between the type's source file and any
 * theme-specific function.
 */
add_filter( 'fw_option_type_spacing_scale', function ( $scale ) {
	if ( function_exists( 'unysonplus_get_spacing_scale' ) ) {
		$custom = unysonplus_get_spacing_scale();
		if ( is_array( $custom ) && ! empty( $custom ) ) {
			return $custom;
		}
	}
	return $scale;
}, 10, 1 );

if ( ! function_exists( 'sc_apply_styling_classes' ) ) :
	/**
	 * Append Styling-tab picks to a wrapper's class list.
	 *
	 * Reads two shapes for back-compat:
	 *  - Legacy flat keys (margin, margin_top, padding_bottom, …) — produced by
	 *    pre-spacing-composite saves and by the still-supported sc_spacing_field()
	 *    helper (used by section.padding_top/bottom and accordion.item_spacing).
	 *  - The new nested `spacing` att — produced by the composite spacing option
	 *    type each shortcode now declares inline in its Styling tab.
	 *
	 * Both flow through the same sc_sanitize_class() filter and end up in the
	 * same flat class list. Existing posts saved before the composite migration
	 * keep rendering correctly.
	 */
	function sc_apply_styling_classes( $attr, $atts ) {
		if ( ! is_array( $atts ) ) { return $attr; }
		$classes = array();
		$styles  = array();
		foreach ( sc_styling_att_keys() as $k ) {
			if ( empty( $atts[ $k ] ) ) { continue; }

			// text_color / bg_color may arrive in either shape — the legacy
			// plain string from `sc_color_field()` OR the {predefined,
			// custom} array from `sc_color_field_compact()`. Funnel both
			// through the normaliser so the result is unambiguous: a class
			// (the `predefined` slug or the legacy string) goes onto the
			// wrapper's class list, a `custom` hex goes into inline style.
			if ( $k === 'text_color' || $k === 'bg_color' ) {
				$norm = sc_normalize_color_value( $atts[ $k ], $k === 'bg_color' ? 'bg' : 'text' );
				if ( $norm['class'] !== '' ) {
					$cls = sc_sanitize_class( $norm['class'] );
					if ( $cls !== '' ) { $classes[] = $cls; }
				}
				if ( $norm['style'] !== '' ) {
					$styles[] = $norm['style'];
				}
				continue;
			}

			$val = sc_sanitize_class( $atts[ $k ] );
			if ( $val !== '' ) { $classes[] = $val; }
		}
		if ( ! empty( $atts['spacing'] ) ) {
			$classes = array_merge( $classes, sc_flatten_spacing_value( $atts['spacing'] ) );
		}
		if ( $classes ) {
			$existing      = isset( $attr['class'] ) ? $attr['class'] : '';
			$attr['class'] = trim( $existing . ' ' . implode( ' ', $classes ) );
		}
		if ( $styles ) {
			$extra    = implode( '; ', $styles );
			$existing = isset( $attr['style'] ) ? rtrim( $attr['style'], "; \t\n\r\0\x0B" ) : '';
			$attr['style'] = $existing === ''
				? $extra . ';'
				: $existing . '; ' . $extra . ';';
		}
		return $attr;
	}
endif;
add_filter( 'sc_build_wrapper_attr', 'sc_apply_styling_classes', 30, 2 );

/* -----------------------------------------------------------------------------
 * Admin UX — colored / sized <option> previews
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'sc_emit_color_select_admin_css' ) ) :
	/**
	 * Color each <option> in any Styling-tab color dropdown according to its
	 * palette color. Scoped by `select.sc-color-text` and `select.sc-color-bg`,
	 * which sc_color_field() adds automatically — so adding a new custom color
	 * field (via sc_color_field) gets the visual preview for free, with no
	 * change needed to this emitter.
	 *
	 * Chrome / Firefox / Edge honor <option> coloring. Safari ignores it (plain
	 * text fallback) — same trade-off as any native-select-styling approach.
	 */
	function sc_emit_color_select_admin_css() {
		if ( ! function_exists( 'unysonplus_get_color_presets' ) ) { return; }
		$presets = unysonplus_get_color_presets();
		if ( empty( $presets ) ) { return; }
		echo '<style id="sc-color-select-admin">';
		foreach ( $presets as $entry ) {
			if ( empty( $entry['name'] ) || empty( $entry['color'] ) ) { continue; }
			$slug = trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( $entry['name'] ) ), '-' );
			if ( $slug === '' ) { continue; }
			$hex = function_exists( 'sanitize_hex_color' ) ? sanitize_hex_color( $entry['color'] ) : $entry['color'];
			if ( empty( $hex ) ) { continue; }

			// Light colours (white, near-white, lime, yellow…) would render
			// invisibly on the dropdown's white background. Force a dark
			// contrast backdrop so the option label stays visible at rest;
			// the swatch is still the picked colour. Bg flavour: invert the
			// overlay text to dark when the bg itself is light.
			$is_light  = sc_color_is_light( $hex );
			$text_bg   = $is_light ? "background-color:#444 !important;"        : '';
			$bg_text   = $is_light ? '#000'                                     : '#fff';

			echo "select.sc-color-text option[value=\"text-{$slug}\"]{color:{$hex} !important;{$text_bg}font-weight:600;}";
			echo "select.sc-color-bg option[value=\"bg-{$slug}\"]{background-color:{$hex} !important;color:{$bg_text} !important;}";
		}
		echo '</style>';
	}
endif;
add_action( 'admin_head', 'sc_emit_color_select_admin_css' );

if ( ! function_exists( 'sc_emit_color_preset_select_admin_css' ) ) :
	/**
	 * Colour each <option> in dropdowns whose value is a raw Color Preset slug
	 * (vs. utility-class values handled by sc_emit_color_select_admin_css).
	 * Two flavours:
	 *   select.sc-color-preset-text → options get colored TEXT
	 *   select.sc-color-preset-bg   → options get a colored BACKGROUND
	 *
	 * Used by Theme Settings → Buttons (each row has 4 such selects).
	 */
	function sc_emit_color_preset_select_admin_css() {
		if ( ! function_exists( 'unysonplus_get_color_presets' ) ) { return; }
		$presets = unysonplus_get_color_presets();
		if ( empty( $presets ) ) { return; }
		echo '<style id="sc-color-preset-select-admin">';
		foreach ( $presets as $entry ) {
			if ( empty( $entry['name'] ) || empty( $entry['color'] ) ) { continue; }
			$slug = trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( $entry['name'] ) ), '-' );
			if ( $slug === '' ) { continue; }
			$hex = function_exists( 'sanitize_hex_color' ) ? sanitize_hex_color( $entry['color'] ) : $entry['color'];
			if ( empty( $hex ) ) { continue; }

			// Same contrast handling as sc_emit_color_select_admin_css —
			// keep white/near-white options visible at rest.
			$is_light = sc_color_is_light( $hex );
			$text_bg  = $is_light ? "background-color:#444 !important;" : '';
			$bg_text  = $is_light ? '#000'                              : '#fff';

			echo "select.sc-color-preset-text option[value=\"{$slug}\"]{color:{$hex} !important;{$text_bg}font-weight:600;}";
			echo "select.sc-color-preset-bg option[value=\"{$slug}\"]{background-color:{$hex} !important;color:{$bg_text} !important;}";
		}
		echo '</style>';
	}
endif;
add_action( 'admin_head', 'sc_emit_color_preset_select_admin_css' );

if ( ! function_exists( 'sc_emit_font_size_select_admin_css' ) ) :
	/**
	 * Size each <option> in any Styling-tab font-size dropdown proportionally
	 * to its preset value. Linear-mapped to [12, 32]px so the dropdown stays
	 * usable while preserving relative ordering. Scoped by `select.sc-font-size`,
	 * which sc_font_size_field() adds automatically.
	 */
	function sc_emit_font_size_select_admin_css() {
		if ( ! function_exists( 'unysonplus_get_font_size_presets' ) ) { return; }
		$presets = unysonplus_get_font_size_presets();
		if ( empty( $presets ) ) { return; }

		$sizes = array();
		foreach ( $presets as $entry ) {
			if ( ! empty( $entry['size'] ) && is_numeric( $entry['size'] ) ) {
				$sizes[] = floatval( $entry['size'] );
			}
		}
		if ( empty( $sizes ) ) { return; }

		$min_actual = min( $sizes );
		$max_actual = max( $sizes );
		$disp_min   = 12;
		$disp_max   = 32;

		echo '<style id="sc-font-size-select-admin">';
		foreach ( $presets as $entry ) {
			if ( empty( $entry['size'] ) || ! is_numeric( $entry['size'] ) ) { continue; }

			if ( ! empty( $entry['class'] ) ) {
				$class = sc_sanitize_class( $entry['class'] );
				if ( $class === '' ) { continue; }
			} else {
				if ( empty( $entry['name'] ) ) { continue; }
				$slug = trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( $entry['name'] ) ), '-' );
				if ( $slug === '' ) { continue; }
				$class = 'font-' . $slug;
			}

			$size = floatval( $entry['size'] );
			if ( $max_actual > $min_actual ) {
				$ratio     = ( $size - $min_actual ) / ( $max_actual - $min_actual );
				$displayed = round( $disp_min + $ratio * ( $disp_max - $disp_min ) );
			} else {
				$displayed = $disp_min;
			}

			echo "select.sc-font-size option[value=\"{$class}\"]{font-size:{$displayed}px;line-height:1.2;}";
		}
		echo '</style>';
	}
endif;
add_action( 'admin_head', 'sc_emit_font_size_select_admin_css' );

if ( ! function_exists( 'sc_emit_button_style_select_admin_css' ) ) :
	/**
	 * Color each <option> in the Button shortcode's Style dropdown by the
	 * corresponding Button Preset's DEFAULT-state text/background colors, so the
	 * dropdown previews each preset. Same pattern as sc_emit_color_select_admin_css.
	 * Scoped by `select.sc-button-style`.
	 *
	 * Reads the nested SKIN shape: $bp['states']['default']['text_color'|'bg_color']
	 * are compact-picker values { predefined: <color-preset-slug>, custom: '#hex' }.
	 * Resolves predefined slugs via the Color Presets lookup (custom hex wins).
	 * Outline / link presets (no background) preview as colored text on a neutral
	 * backdrop. Chrome/Firefox/Edge honor option styling; Safari plain-text falls back.
	 */
	function sc_emit_button_style_select_admin_css() {
		if ( ! function_exists( 'unysonplus_get_button_color_presets' ) ) { return; }
		if ( ! function_exists( 'unysonplus_color_preset_slug_map' ) )    { return; }
		$presets = unysonplus_get_button_color_presets();
		if ( empty( $presets ) ) { return; }
		$slug_to_hex = unysonplus_color_preset_slug_map();

		// Resolve a compact color value { predefined, custom } (or legacy slug
		// string) to a hex. Custom wins; else map the predefined slug.
		$resolve = function ( $v ) use ( $slug_to_hex ) {
			if ( is_array( $v ) ) {
				$custom = isset( $v['custom'] ) ? trim( (string) $v['custom'] ) : '';
				if ( $custom !== '' ) { return $custom; }
				$v = isset( $v['predefined'] ) ? (string) $v['predefined'] : '';
			}
			$v = (string) $v;
			if ( $v === '' ) { return ''; }
			if ( $v[0] === '#' ) { return $v; }
			// Tolerate text-/bg-/border- prefixes from legacy utility-class values.
			$slug = preg_replace( '/^(text|bg|background|border)-/', '', $v );
			if ( isset( $slug_to_hex[ $v ] ) )    { return $slug_to_hex[ $v ]; }
			if ( isset( $slug_to_hex[ $slug ] ) ) { return $slug_to_hex[ $slug ]; }
			return '';
		};

		// Readable name-based class slug — must match css-tokens + the dropdown
		// choice value so the right <option> gets the preview styling.
		$slug_map = function_exists( 'unysonplus_button_preset_slug_map' )
			? unysonplus_button_preset_slug_map()
			: array();

		echo '<style id="sc-button-style-select-admin">';
		foreach ( $presets as $bp ) {
			if ( ! is_array( $bp ) || empty( $bp['id'] ) ) { continue; }
			$id = sc_sanitize_class( $bp['id'] );
			if ( $id === '' ) { continue; }
			$slug = isset( $slug_map[ $id ] ) ? $slug_map[ $id ] : $id;

			// Default-state colors from the nested states shape; fall back to the
			// legacy flat keys so pre-tabs saved presets still preview.
			$def = ( isset( $bp['states']['default'] ) && is_array( $bp['states']['default'] ) )
				? $bp['states']['default'] : array();
			$nt = $resolve( $def['text_color'] ?? ( $bp['normal_text_color'] ?? '' ) );
			$nb = $resolve( $def['bg_color']   ?? ( $bp['normal_bg_color']   ?? '' ) );

			$parts = array();
			if ( $nb !== '' ) {
				// Filled preset: text on its bg (force dark text if bg is light).
				if ( $nt !== '' ) {
					$nt_for_option = sc_color_is_light( $nb ) ? '#000' : $nt;
					$parts[] = "color:{$nt_for_option} !important";
				}
				$parts[] = "background-color:{$nb} !important";
			} elseif ( $nt !== '' ) {
				// Outline / link preset (no bg): colored text; if the color is
				// light, add a dark backdrop so the label stays visible.
				$parts[] = "color:{$nt} !important";
				if ( sc_color_is_light( $nt ) ) { $parts[] = 'background-color:#444 !important'; }
			}
			if ( $parts ) {
				$parts[] = 'font-weight:600';
				echo "select.sc-button-style option[value=\"btn-{$slug}\"]{" . implode( ';', $parts ) . ";}";
			}
		}
		echo '</style>';
	}
endif;
add_action( 'admin_head', 'sc_emit_button_style_select_admin_css' );

if ( ! function_exists( 'sc_emit_button_size_select_admin_css' ) ) :
	/**
	 * Size each <option> in the Button shortcode's Size dropdown by the
	 * corresponding Button Size Preset's font_size. Mirrors
	 * sc_emit_font_size_select_admin_css's approach but uses raw px values
	 * directly (typical button sizes are 12px–22px, all readable in the
	 * dropdown without normalisation).
	 *
	 * Scoped by `select.sc-button-size`.
	 */
	function sc_emit_button_size_select_admin_css() {
		if ( ! function_exists( 'unysonplus_get_button_size_presets' ) ) { return; }
		$presets = unysonplus_get_button_size_presets();
		if ( empty( $presets ) ) { return; }

		echo '<style id="sc-button-size-select-admin">';
		foreach ( $presets as $bs ) {
			if ( ! is_array( $bs ) || empty( $bs['slug'] ) ) { continue; }
			$slug = sc_sanitize_class( $bs['slug'] );
			if ( $slug === '' ) { continue; }

			// font_size is a unit-input array { value, unit } (legacy: string).
			$font_size = ( is_array( $bs['font_size'] ?? null ) && class_exists( 'FW_Option_Type_Unit_Input' ) )
				? FW_Option_Type_Unit_Input::to_string( $bs['font_size'] )
				: trim( (string) ( $bs['font_size'] ?? '' ) );
			if ( $font_size === '' ) { continue; }

			echo "select.sc-button-size option[value=\"btn-{$slug}\"]{font-size:{$font_size};line-height:1.4;font-weight:500;}";
		}
		echo '</style>';
	}
endif;
add_action( 'admin_head', 'sc_emit_button_size_select_admin_css' );

if ( ! function_exists( 'sc_emit_button_preview_saved_css' ) ) :
	/**
	 * Saved-state colour rules for Theme Settings → Buttons preview spans.
	 * The addable-box template's inline `<style>` provides live-edit updates,
	 * but it gets re-rendered (and briefly cleared for siblings) when postbox
	 * toggles fire. This admin_head emitter gives every `.btn-preview-{id}`
	 * a stable baseline so toggling one row doesn't blank another row's
	 * preview. No `!important` — the inline rule still wins (DOM-late source
	 * order, same specificity) when present.
	 */
	function sc_emit_button_preview_saved_css() {
		if ( ! function_exists( 'unysonplus_get_button_color_presets' ) ) { return; }
		if ( ! function_exists( 'unysonplus_color_preset_slug_map' ) )    { return; }
		$presets = unysonplus_get_button_color_presets();
		if ( empty( $presets ) ) { return; }
		$slug_to_hex = unysonplus_color_preset_slug_map();

		echo '<style id="sc-button-preview-saved">';
		foreach ( $presets as $bp ) {
			if ( ! is_array( $bp ) || empty( $bp['id'] ) ) { continue; }
			$id = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $bp['id'] );
			if ( $id === '' ) { continue; }

			$nt_slug = isset( $bp['normal_text_color'] ) ? (string) $bp['normal_text_color'] : '';
			$nb_slug = isset( $bp['normal_bg_color'] )   ? (string) $bp['normal_bg_color']   : '';
			$nt = isset( $slug_to_hex[ $nt_slug ] ) ? $slug_to_hex[ $nt_slug ] : '';
			$nb = isset( $slug_to_hex[ $nb_slug ] ) ? $slug_to_hex[ $nb_slug ] : '';

			$parts = array();
			if ( $nt !== '' ) { $parts[] = "color:{$nt}"; }
			if ( $nb !== '' ) { $parts[] = "background:{$nb}"; }
			if ( ! empty( $parts ) ) {
				echo ".btn-preview-{$id}{" . implode( ';', $parts ) . ";min-width:160px;}";
			}
		}
		echo '</style>';
	}
endif;
add_action( 'admin_head', 'sc_emit_button_preview_saved_css' );

if ( ! function_exists( 'sc_emit_button_size_preview_saved_css' ) ) :
	/**
	 * Saved-state rules for Theme Settings → Buttons → Sizes preview spans.
	 * The addable-box template's inline `<style>` provides live-edit updates,
	 * but it gets re-rendered (and briefly cleared for siblings) when postbox
	 * toggles fire. This admin_head emitter gives every `.btn-size-preview-{id}`
	 * a stable baseline so toggling one row doesn't blank another row's preview.
	 * No `!important` — the inline rule still wins (DOM-late source order)
	 * when present, so live-edit isn't blocked.
	 */
	function sc_emit_button_size_preview_saved_css() {
		if ( ! function_exists( 'unysonplus_get_button_size_presets' ) ) { return; }
		$presets = unysonplus_get_button_size_presets();
		if ( empty( $presets ) ) { return; }

		echo '<style id="sc-button-size-preview-saved">';
		foreach ( $presets as $bs ) {
			if ( ! is_array( $bs ) || empty( $bs['id'] ) ) { continue; }
			$id = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $bs['id'] );
			if ( $id === '' ) { continue; }

			// Size values may be unit-input arrays { value, unit }, a legacy
			// 4-side padding array, or plain strings — resolve any shape.
			$size_len = function ( $v ) {
				if ( is_array( $v ) && class_exists( 'FW_Option_Type_Unit_Input' ) ) {
					return FW_Option_Type_Unit_Input::to_string( $v );
				}
				return is_array( $v ) ? '' : trim( (string) $v );
			};

			$parts = array();

			$fs = $size_len( $bs['font_size'] ?? '' );
			if ( $fs !== '' ) { $parts[] = "font-size:{$fs}"; }

			if ( ! empty( $bs['line_height'] ) ) { $parts[] = "line-height:{$bs['line_height']}"; }

			$py = $size_len( $bs['padding_y'] ?? '' );
			$px = $size_len( $bs['padding_x'] ?? '' );
			if ( $py !== '' || $px !== '' ) {
				$parts[] = 'padding:' . ( $py !== '' ? $py : '0' ) . ' ' . ( $px !== '' ? $px : '0' );
			} elseif ( is_array( $bs['padding'] ?? null )
			     && isset( $bs['padding']['top'], $bs['padding']['right'], $bs['padding']['bottom'], $bs['padding']['left'] )
			     && ( $bs['padding']['top'] !== '' || $bs['padding']['right'] !== '' || $bs['padding']['bottom'] !== '' || $bs['padding']['left'] !== '' ) ) {
				$parts[] = "padding:{$bs['padding']['top']} {$bs['padding']['right']} {$bs['padding']['bottom']} {$bs['padding']['left']}";
			}

			$rad = $size_len( $bs['border_radius'] ?? '' );
			if ( $rad !== '' ) { $parts[] = "border-radius:{$rad}"; }

			// Default fill so the preview reads as a real button, not a transparent shape.
			// Tracks the user's Color Presets so a Blue recolor propagates here too.
			$parts[] = 'color:var(--color-white)';
			$parts[] = 'background-color:var(--color-blue)';
			$parts[] = 'border-color:var(--color-blue)';

			if ( ! empty( $parts ) ) {
				echo ".btn-size-preview-{$id}{" . implode( ';', $parts ) . ";}";
			}
		}
		echo '</style>';
	}
endif;
add_action( 'admin_head', 'sc_emit_button_size_preview_saved_css' );

if ( ! function_exists( 'sc_emit_button_hover_animation_preview_css' ) ) :
	/**
	 * Theme Settings → Buttons → Hover Animations row previews. Each row's template
	 * renders `<span class="btn btn-primary btnfx-preview-{id}">`; this admin_head
	 * emitter replays the saved CSS for that row with {{BTN}} -> .btnfx-preview-{id}
	 * and {{ANIM}} -> a per-id keyframes name, so hovering the row's button plays the
	 * effect. Mirrors the front-end generation in css-tokens.php (same scrub), but
	 * keyed by the box id (the template has the id, not the name-derived slug).
	 */
	function sc_emit_button_hover_animation_preview_css() {
		if ( ! function_exists( 'unysonplus_get_custom_hover_animations' ) ) { return; }
		$anims = unysonplus_get_custom_hover_animations();
		if ( empty( $anims ) ) { return; }

		echo '<style id="sc-button-hover-animation-preview">';
		foreach ( $anims as $ca ) {
			if ( ! is_array( $ca ) || empty( $ca['id'] ) ) { continue; }
			$id = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $ca['id'] );
			if ( $id === '' ) { continue; }

			$css = isset( $ca['css'] ) ? (string) $ca['css'] : '';
			if ( trim( $css ) === '' ) { continue; }

			// Same scrub as css-tokens: no markup / @import / script tricks.
			$css = preg_replace( '#</?(style|script)[^>]*>#i', '', $css );
			$css = str_replace( array( '<', '>' ), '', $css );
			$css = preg_replace( '/@import\b/i', '', $css );
			$css = preg_replace( '/javascript\s*:/i', '', $css );
			$css = preg_replace( '/expression\s*\(/i', '', $css );

			$css = str_replace(
				array( '{{BTN}}', '{{ANIM}}' ),
				array( ".btnfx-preview-{$id}", "btnfxprev-{$id}" ),
				$css
			);

			echo $css;
		}
		echo '</style>';
	}
endif;
add_action( 'admin_head', 'sc_emit_button_hover_animation_preview_css' );

if ( ! function_exists( 'sc_emit_button_admin_preview_css' ) ) :
	/**
	 * Theme Settings → Buttons → Color Presets renders each row's preview as
	 * `<span class="btn btn-preview-{id}">Name</span>` inside the postbox header.
	 * fw-settings.css supplies the base `.btn` shape, but its color/bg come from
	 * an inline `<style>` block that postbox-header CSS can steamroll. This
	 * emitter adds a more-specific rule that forces a visible button look in
	 * that exact context.
	 *
	 * Scoped to `.btn-preview-` only (not `btn-size-preview-`) so size previews
	 * can express their own font-size / padding / border-radius without being
	 * forced into a uniform 4px×14px/13px shape.
	 */
	function sc_emit_button_admin_preview_css() {
		echo '<style id="sc-button-admin-preview">'
			. '.fw-postbox .hndle .btn[class*="btn-preview-"]{display:inline-block;padding:4px 14px;margin:2px 0;border:1px solid currentColor;border-radius:4px;line-height:1.4;font-weight:500;font-size:13px;min-width:120px;text-align:center;text-decoration:none;}'
			. '</style>';
	}
endif;
add_action( 'admin_head', 'sc_emit_button_admin_preview_css' );

if ( ! function_exists( 'sc_emit_styling_admin_css' ) ) :
	/**
	 * Admin-CSS for the Styling tab — flexes the nested per-side group
	 * (`.fw-backend-options-group.sc-spacing-row`) so the 4 Top/Right/Bottom/Left
	 * dropdowns share a single row, and overrides short-select's fixed 100px
	 * width so they fill the available cell.
	 */
	function sc_emit_styling_admin_css() {
		echo '<style id="sc-styling-admin">'
			. '.fw-backend-options-group.sc-spacing-row{display:flex;flex-wrap:wrap;gap:0 12px;}'
			. '.fw-backend-options-group.sc-spacing-row > .fw-backend-option{flex:1 1 0;min-width:0;}'
			. '.fw-backend-options-group.sc-spacing-row > .fw-backend-option .fw-option-width-short{width:100% !important;}'
			. '</style>';
	}
endif;
add_action( 'admin_head', 'sc_emit_styling_admin_css' );

/* -------------------------------------------------------------------------
 * Bare mode: when Styling Presets is OFF (Page Builder settings), strip the
 * styling layer from every shortcode's options — the Styling tab and the
 * preset pickers (which depend on presets.css) — so the editor only offers
 * structure + the Advanced CSS ID/Class fields. One filter covers all 25+
 * shortcodes (fw_shortcode_get_options, class-fw-shortcode.php). Animation +
 * Advanced tabs and all Content options are left intact.
 * ------------------------------------------------------------------------- */

if ( ! function_exists( 'sc_remove_styling_options' ) ) :
	/**
	 * Recursively drop the `tab_styling` tab and any preset-picker option
	 * (button-style-picker / border-style-picker / table-style-picker), and
	 * prune containers (tab/box/group) that become empty as a result.
	 */
	function sc_remove_styling_options( $options ) {
		if ( ! is_array( $options ) ) {
			return $options;
		}
		$picker_types = array( 'button-style-picker', 'border-style-picker', 'table-style-picker' );
		$out = array();

		foreach ( $options as $id => $opt ) {
			if ( $id === 'tab_styling' ) {
				continue; // whole Styling tab
			}
			if ( is_array( $opt ) ) {
				if ( isset( $opt['type'] ) && in_array( $opt['type'], $picker_types, true ) ) {
					continue; // a preset picker field
				}
				if ( isset( $opt['options'] ) && is_array( $opt['options'] ) ) {
					$opt['options'] = sc_remove_styling_options( $opt['options'] );
					// Drop now-empty containers (e.g. a group that held only pickers).
					if ( empty( $opt['options'] )
						&& isset( $opt['type'] )
						&& in_array( $opt['type'], array( 'tab', 'box', 'group' ), true ) ) {
						continue;
					}
				}
			}
			$out[ $id ] = $opt;
		}

		return $out;
	}
endif;

if ( ! function_exists( 'sc_filter_styling_options' ) ) :
	function sc_filter_styling_options( $options, $tag = '' ) {
		// Styling on → no change. Off → strip the styling layer.
		if ( function_exists( 'unysonplus_styling_presets_enabled' ) && ! unysonplus_styling_presets_enabled() ) {
			return sc_remove_styling_options( $options );
		}
		return $options;
	}
endif;
add_filter( 'fw_shortcode_get_options', 'sc_filter_styling_options', 10, 2 );

if ( ! function_exists( 'sc_bg_pro_style' ) ) :
	/**
	 * Compile a `background-pro` value into an inline CSS style string.
	 *
	 * Stacks the CSS-able layers exactly like the theme site-background and the
	 * Section view: solid color, then `background-image: url(image), gradient`
	 * (image over gradient), plus position / repeat / attachment (Fixed = parallax)
	 * / size when there's a raster image. The video layer is NOT emitted here —
	 * use sc_bg_pro_video_attr() for that (it needs the Formstone data-attr + class).
	 *
	 * Shared by the Section, Masonry Section and Bleed Section shortcodes.
	 *
	 * @param array $bgv A background-pro value (or null/array).
	 * @return string Inline style declarations (may be '').
	 */
	function sc_bg_pro_style( $bgv ) {
		if ( ! is_array( $bgv ) ) { return ''; }

		$style = '';

		// Color — predefined or custom; both hold a concrete colour in the live control.
		$cv    = fw_akg( 'color/value', $bgv, array() );
		$color = '';
		if ( is_array( $cv ) ) {
			if ( ! empty( $cv['custom'] ) )         { $color = (string) $cv['custom']; }
			elseif ( ! empty( $cv['predefined'] ) ) { $color = (string) $cv['predefined']; }
		}
		if ( $color !== '' ) {
			$style .= 'background-color:' . esc_attr( $color ) . ';';
		}

		// Image over gradient.
		$images  = array();
		$img_url = fw_akg( 'image/src/url', $bgv, '' );
		if ( $img_url ) {
			$images[] = 'url(' . esc_url( $img_url ) . ')';
		}
		$stops = fw_akg( 'gradient/data/stops', $bgv );
		if ( is_array( $stops ) && count( $stops ) >= 2 && class_exists( 'FW_Option_Type_Gradient_V2' ) ) {
			$grad = FW_Option_Type_Gradient_V2::to_css( fw_akg( 'gradient/data', $bgv ) );
			if ( $grad ) { $images[] = $grad; }
		}

		// Overlay — a tint rendered ON TOP of the image/gradient/colour (gradient over colour).
		// Prepended to the image list so it sits first (topmost) in `background-image`.
		$overlay  = array();
		$ov_stops = fw_akg( 'overlay/gradient/stops', $bgv );
		if ( is_array( $ov_stops ) && count( $ov_stops ) >= 2 && class_exists( 'FW_Option_Type_Gradient_V2' ) ) {
			$og = FW_Option_Type_Gradient_V2::to_css( fw_akg( 'overlay/gradient', $bgv ) );
			if ( $og ) { $overlay[] = $og; }
		}
		$ov_color = trim( (string) fw_akg( 'overlay/color', $bgv, '' ) );
		if ( $ov_color !== '' && $ov_color !== 'rgba(0,0,0,0)' && $ov_color !== 'transparent'
			&& preg_match( '/^(#[0-9a-fA-F]{3,8}|rgba?\([0-9.,%\s]+\))$/', $ov_color ) ) {
			$overlay[] = 'linear-gradient(' . $ov_color . ',' . $ov_color . ')'; // flat colour tint
		}
		if ( $overlay ) { $images = array_merge( $overlay, $images ); }

		if ( $images ) {
			$style .= 'background-image:' . implode( ', ', $images ) . ';';
			if ( $img_url ) {
				$pos      = fw_akg( 'image/position', $bgv, 'center center' );
				$rep      = fw_akg( 'image/repeat', $bgv, 'no-repeat' );
				$att      = fw_akg( 'image/attachment', $bgv, 'scroll' );
				$size_sel = fw_akg( 'image/size/selected', $bgv, 'cover' );
				$size     = ( 'custom' === $size_sel ) ? fw_akg( 'image/size/custom', $bgv, 'auto' ) : $size_sel;
				if ( $pos )  { $style .= 'background-position:' . esc_attr( $pos ) . ';'; }
				if ( $rep )  { $style .= 'background-repeat:' . esc_attr( $rep ) . ';'; }
				if ( $att )  { $style .= 'background-attachment:' . esc_attr( $att ) . ';'; }
				if ( $size ) { $style .= 'background-size:' . esc_attr( $size ) . ';'; }
			}
		}

		return $style;
	}
endif;

if ( ! function_exists( 'sc_bg_pro_video_attr' ) ) :
	/**
	 * Compile a `background-pro` value's video layer into the Formstone
	 * `data-background-options` attribute (the existing section video player).
	 * Returns an empty array when video is disabled / has no source — the caller
	 * then knows not to add the `background-video` class.
	 *
	 * @param array $bgv A background-pro value.
	 * @return array data-attr name => JSON string (or empty array).
	 */
	function sc_bg_pro_video_attr( $bgv ) {
		// No enable toggle — the video is active whenever a source is set (checked below).
		if ( ! is_array( $bgv ) ) {
			return array();
		}

		$source   = array();
		$mp4      = fw_akg( 'video/source_mp4/url', $bgv, '' );
		$webm     = fw_akg( 'video/source_webm/url', $bgv, '' );
		$external = fw_akg( 'video/external_url', $bgv, '' );
		$poster   = fw_akg( 'video/poster/url', $bgv, '' );

		if ( $mp4 )  { $source['mp4']  = $mp4; }
		if ( $webm ) { $source['webm'] = $webm; }
		if ( ! $mp4 && ! $webm && $external ) { $source['video'] = $external; }
		if ( $poster ) { $source['poster'] = $poster; }

		if ( empty( $source ) ) { return array(); }

		// A background video ALWAYS autoplays muted and inline — that's what makes it a
		// *background*, and, crucially, browsers only autoplay video that is muted (an un-muted
		// video is blocked for any visitor without a media-engagement history, i.e. essentially
		// all real visitors). So mute + autoPlay are forced on with no Sound option. Looping is
		// the one visitor-facing playback choice.
		$loop = ( fw_akg( 'video/loop', $bgv, 'yes' ) !== 'no' );
		$opts = array(
			'source'   => $source,
			'loop'     => $loop,
			'autoPlay' => true,
			'mute'     => true,
		);

		$data_name = ( function_exists( 'fw_ext' ) && version_compare( fw_ext( 'shortcodes' )->manifest->get_version(), '1.3.9', '>=' ) )
			? 'data-background-options'
			: 'data-wallpaper-options';

		// Return RAW JSON — the consumer (section / flexbox view) prints it through
		// fw_attr_to_html(), which HTML-encodes the attribute once. Pre-encoding here too
		// double-escaped it ("&amp;quot;"), so the browser saw invalid JSON and the video
		// never initialised.
		$out = array( $data_name => json_encode( $opts ) );

		// "Allow pause" → flag the wrapper so CSS re-enables pointer events on the video
		// (visitors can click to pause). Off (default) → the video is decorative and ignores
		// clicks, so no play/pause icon shows. See section styles.css .background-video rules.
		if ( fw_akg( 'video/allow_interaction', $bgv, 'no' ) === 'yes' ) {
			$out['data-bg-video-interactive'] = '1';
		}

		return $out;
	}
endif;

if ( ! function_exists( 'sc_migrate_atts' ) ) :
	/**
	 * Reusable atts-migration runner.
	 *
	 * Each option's *value transform* is necessarily option-specific (a tiny
	 * callback), but the *plumbing* — which att, whether it still needs
	 * migrating, how the callback is invoked, writing the result back — is the
	 * same every time. This runs a declarative spec of those transforms over a
	 * shortcode's atts so option upgrades (scalar → array, renamed shapes, merged
	 * legacy fields, …) only need a few-line migrator each.
	 *
	 * Spec — `att_id => migration`, where migration is either:
	 *   - a callable (shorthand)  →  arg:'value', when:'not_array'
	 *   - an array:
	 *       'cb'   => callable,                 // required
	 *       'arg'  => 'value' | 'atts',         // pass the att's value (default) or the whole atts
	 *       'when' => 'not_array'|'missing'|'always',
	 *
	 *   'value'     → $cb( $atts[$id] )         (transform one option's value)
	 *   'atts'      → $cb( $atts )              (build from several legacy atts, e.g. background)
	 *   when 'not_array' (default) → runs only while the value isn't already an array
	 *   when 'missing'            → runs only while the att is empty/unset
	 *   when 'always'            → runs every time
	 * A callback returning null leaves the att untouched (e.g. "no legacy data").
	 *
	 *   $atts = sc_migrate_atts( $atts, array(
	 *       'min_height' => 'section_migrate_min_height',                                    // scalar → multi-picker
	 *       'background'  => array( 'cb' => 'section_migrate_legacy_background', 'arg' => 'atts', 'when' => 'missing' ),
	 *   ) );
	 *
	 * @param array $atts
	 * @param array $specs
	 * @return array
	 */
	function sc_migrate_atts( $atts, array $specs ) {
		if ( ! is_array( $atts ) ) {
			return $atts;
		}

		foreach ( $specs as $att_id => $spec ) {
			if ( ! is_array( $spec ) ) {
				$spec = array( 'cb' => $spec );
			}
			$cb   = isset( $spec['cb'] ) ? $spec['cb'] : null;
			$arg  = isset( $spec['arg'] ) ? $spec['arg'] : 'value';
			$when = isset( $spec['when'] ) ? $spec['when'] : 'not_array';

			if ( ! is_callable( $cb ) ) {
				continue;
			}

			$current = array_key_exists( $att_id, $atts ) ? $atts[ $att_id ] : null;

			$run = true;
			if ( $when === 'not_array' )    { $run = ! is_array( $current ); }
			elseif ( $when === 'missing' )  { $run = empty( $current ); }
			// 'always' → leave $run true.

			if ( ! $run ) {
				continue;
			}

			$new = ( $arg === 'atts' ) ? call_user_func( $cb, $atts ) : call_user_func( $cb, $current );

			if ( $new !== null ) {
				$atts[ $att_id ] = $new;
			}
		}

		return $atts;
	}
endif;

/* -----------------------------------------------------------------------------
 * Icon rendering — the single source of truth for turning an icon-v2 value
 * into HTML. Every shortcode that displays a picked icon should call this
 * instead of hand-rolling <i class> / <img>, so that when a new icon kind is
 * added (SVG, emoji, Lottie…) it lights up everywhere from one place.
 *
 * Accepts the icon-v2 value array (['type' => 'icon-font'|'custom-upload'|
 * 'none', ...]) or a legacy bare class string ('fa fa-star'). Returns escaped
 * HTML, or '' when there is nothing to render. Also enqueues the pack CSS the
 * chosen font icon needs (so callers no longer need a separate
 * enqueue_pack_for_icon() call), unless 'enqueue' => false.
 *
 * $args:
 *   'class'       => ''      extra classes on BOTH the <i> and the <img>
 *   'font_class'  => ''      extra classes only on the font <i> (after its own icon-class)
 *   'img_class'   => ''      extra classes only on the uploaded <img>
 *   'style'       => ''      inline style string on the element
 *   'aria_hidden' => true    add aria-hidden="true" to the font <i> (decorative default)
 *   'img_alt'     => null    <img> alt; null → use the value's own 'alt' (or '')
 *   'img_loading' => 'lazy'  <img> loading attr ('' to omit)
 *   'enqueue'     => true    auto-enqueue the pack CSS for font icons
 *   'attr'        => array() extra HTML attributes (assoc; keys+values escaped)
 * -------------------------------------------------------------------------- */
if ( ! function_exists( 'sc_icon_enqueue_pack' ) ) :
	/**
	 * Enqueue only the icon pack CSS a single icon-v2 value needs. Safe to call
	 * repeatedly (WP dedupes by handle). No-op for uploads / none / unknown.
	 */
	function sc_icon_enqueue_pack( $value ) {
		if ( ! function_exists( 'fw' ) ) { return; }
		$ot = fw()->backend->option_type( 'icon-v2' );
		if ( $ot && isset( $ot->packs_loader ) ) {
			$ot->packs_loader->enqueue_pack_for_icon( $value );
		}
	}
endif;

if ( ! function_exists( 'sc_icon_join_classes' ) ) :
	/** Join class fragments, dropping empties and collapsing internal gaps. */
	function sc_icon_join_classes( $parts ) {
		$parts = array_filter( array_map( 'trim', (array) $parts ), 'strlen' );
		return implode( ' ', $parts );
	}
endif;

if ( ! function_exists( 'sc_icon_render' ) ) :
	function sc_icon_render( $value, $args = array() ) {
		$args = array_merge( array(
			'class'       => '',
			'font_class'  => '',
			'img_class'   => '',
			'style'       => '',
			'aria_hidden' => true,
			'img_alt'     => null,
			'img_loading' => 'lazy',
			'enqueue'     => true,
			'attr'        => array(),
		), $args );

		// Legacy bare-string value → treat as a font icon class (or nothing).
		if ( is_string( $value ) ) {
			$value = ( $value === '' )
				? array( 'type' => 'none' )
				: array( 'type' => 'icon-font', 'icon-class' => $value );
		}

		if ( ! is_array( $value ) ) { return ''; }

		$type = isset( $value['type'] ) ? $value['type'] : '';

		// Shared attribute fragment (style + any extra attributes). aria-hidden
		// is handled per-element below because it only applies to the font <i>.
		$extra = '';
		if ( $args['style'] !== '' ) {
			$extra .= ' style="' . esc_attr( $args['style'] ) . '"';
		}
		foreach ( (array) $args['attr'] as $k => $v ) {
			$extra .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
		}

		if ( $type === 'icon-font' ) {
			$icon_class = isset( $value['icon-class'] ) ? trim( (string) $value['icon-class'] ) : '';
			if ( $icon_class === '' ) { return ''; }

			if ( $args['enqueue'] ) { sc_icon_enqueue_pack( $value ); }

			$cls  = sc_icon_join_classes( array( $icon_class, $args['font_class'], $args['class'] ) );
			$aria = $args['aria_hidden'] ? ' aria-hidden="true"' : '';

			return '<i class="' . esc_attr( $cls ) . '"' . $extra . $aria . '></i>';
		}

		if ( $type === 'custom-upload' ) {
			$url = isset( $value['url'] ) ? (string) $value['url'] : '';
			if ( $url === '' ) { return ''; }

			$alt = ( $args['img_alt'] !== null )
				? $args['img_alt']
				: ( isset( $value['alt'] ) ? $value['alt'] : '' );

			$cls      = sc_icon_join_classes( array( $args['img_class'], $args['class'] ) );
			$cls_attr = ( $cls !== '' ) ? ' class="' . esc_attr( $cls ) . '"' : '';
			$load     = ( $args['img_loading'] !== '' ) ? ' loading="' . esc_attr( $args['img_loading'] ) . '"' : '';

			return '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '"' . $cls_attr . $extra . $load . '>';
		}

		if ( $type === 'emoji' ) {
			$char = isset( $value['char'] ) ? trim( (string) $value['char'] ) : '';
			if ( $char === '' ) { return ''; }
			$cls      = sc_icon_join_classes( array( $args['font_class'], $args['class'] ) );
			$cls_attr = ( $cls !== '' ) ? ' class="' . esc_attr( $cls ) . '"' : '';
			// Emoji colour is fixed, so no colour plumbing; role/aria for a11y.
			$role     = $args['aria_hidden'] ? ' aria-hidden="true"' : ' role="img"';
			return '<span' . $cls_attr . $extra . $role . '>' . esc_html( $char ) . '</span>';
		}

		if ( $type === 'svg' ) {
			// Resolve markup: a library pick (svg-id) resolves from the bundled
			// set; otherwise use the stored inline/pasted markup. Both are run
			// through the shared sanitiser (defence-in-depth — library markup is
			// trusted, user markup is not).
			// Decide by svg-source so a stale library svg-id left over from a
			// previous pick can't win over freshly pasted/uploaded markup:
			// only a `library` source resolves the id; inline/upload use markup.
			$source = isset( $value['svg-source'] ) ? (string) $value['svg-source'] : '';
			$markup = '';
			if ( $source !== 'inline' && $source !== 'upload' && ! empty( $value['svg-id'] ) ) {
				$markup = sc_icon_svg_library_markup( (string) $value['svg-id'] );
			}
			if ( $markup === '' && ! empty( $value['markup'] ) ) {
				$markup = (string) $value['markup'];
			}

			if ( $markup !== '' ) {
				$markup   = sc_icon_sanitize_svg( $markup );
				$cls      = sc_icon_join_classes( array( $args['font_class'], $args['class'] ) );
				$cls_attr = ( $cls !== '' ) ? ' class="' . esc_attr( $cls ) . '"' : '';
				$aria     = $args['aria_hidden'] ? ' aria-hidden="true"' : '';
				// Wrap so classes/style attach without editing the <svg> tag; the
				// inner SVG uses currentColor, so it inherits the wrapper colour.
				return '<span' . $cls_attr . $extra . $aria . '>' . $markup . '</span>';
			}

			// Uploaded SVG stored only as a URL → render as an <img> (same path
			// as a custom-upload; loses currentColor but is safe and simple).
			$url = isset( $value['url'] ) ? (string) $value['url'] : '';
			if ( $url !== '' ) {
				$alt      = ( $args['img_alt'] !== null ) ? $args['img_alt'] : ( isset( $value['alt'] ) ? $value['alt'] : '' );
				$cls      = sc_icon_join_classes( array( $args['img_class'], $args['class'] ) );
				$cls_attr = ( $cls !== '' ) ? ' class="' . esc_attr( $cls ) . '"' : '';
				$load     = ( $args['img_loading'] !== '' ) ? ' loading="' . esc_attr( $args['img_loading'] ) . '"' : '';
				return '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '"' . $cls_attr . $extra . $load . '>';
			}

			return '';
		}

		// 'none' / unknown. Lottie plugs in here in a later phase — every
		// consumer that already calls sc_icon_render() then renders it too.
		return '';
	}
endif;

/* -----------------------------------------------------------------------------
 * Shared inline-SVG sanitiser + custom-icon (emoji / SVG) renderer.
 *
 * Promoted from the per-shortcode copies that icon-box / image-box /
 * notification each hand-rolled, so there is ONE allowlist to audit. Any
 * element that accepts pasted/inline SVG (or the icon type's SVG kind) runs it
 * through sc_icon_sanitize_svg(); a "Custom Icon (emoji / SVG)" text value goes
 * through sc_icon_custom_markup() (SVG → sanitised, anything else → escaped as
 * emoji/text).
 * -------------------------------------------------------------------------- */
if ( ! function_exists( 'sc_icon_svg_allowed' ) ) :
	/** wp_kses allowlist for inline icon SVG (scripts / handlers / external refs stripped). */
	function sc_icon_svg_allowed() {
		$stroke = array(
			'fill' => true, 'stroke' => true, 'stroke-width' => true,
			'stroke-linecap' => true, 'stroke-linejoin' => true,
			'fill-rule' => true, 'clip-rule' => true, 'class' => true,
		);
		return array(
			'svg'      => array(
				'xmlns' => true, 'viewbox' => true, 'width' => true, 'height' => true,
				'fill' => true, 'stroke' => true, 'stroke-width' => true,
				'stroke-linecap' => true, 'stroke-linejoin' => true,
				'preserveaspectratio' => true, 'class' => true, 'role' => true,
				'aria-hidden' => true, 'aria-label' => true, 'focusable' => true,
			),
			'g'        => array_merge( $stroke, array( 'transform' => true ) ),
			'path'     => array_merge( $stroke, array( 'd' => true ) ),
			'circle'   => array_merge( $stroke, array( 'cx' => true, 'cy' => true, 'r' => true ) ),
			'ellipse'  => array_merge( $stroke, array( 'cx' => true, 'cy' => true, 'rx' => true, 'ry' => true ) ),
			'rect'     => array_merge( $stroke, array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true ) ),
			'line'     => array_merge( $stroke, array( 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true ) ),
			'polyline' => array_merge( $stroke, array( 'points' => true ) ),
			'polygon'  => array_merge( $stroke, array( 'points' => true ) ),
			'title'    => array(),
			'desc'     => array(),
		);
	}
endif;

if ( ! function_exists( 'sc_icon_sanitize_svg' ) ) :
	/** Sanitise inline SVG markup against the shared allowlist. Returns '' if not SVG. */
	function sc_icon_sanitize_svg( $markup ) {
		$markup = (string) $markup;
		if ( stripos( $markup, '<svg' ) === false ) { return ''; }
		return wp_kses( $markup, sc_icon_svg_allowed() );
	}
endif;

if ( ! function_exists( 'sc_icon_custom_markup' ) ) :
	/**
	 * Render a free-form "Custom Icon (emoji / SVG)" value: inline SVG is
	 * sanitised, anything else (an emoji or short text) is HTML-escaped.
	 */
	function sc_icon_custom_markup( $custom ) {
		if ( ! is_string( $custom ) || $custom === '' ) { return ''; }
		if ( stripos( $custom, '<svg' ) !== false ) {
			return sc_icon_sanitize_svg( $custom );
		}
		return esc_html( $custom );
	}
endif;

if ( ! function_exists( 'sc_icon_svg_library_markup' ) ) :
	/**
	 * Resolve a library SVG id (e.g. 'lucide/star') to its raw markup. The
	 * bundled Lucide set is wired in Phase 2B; until then this is filterable so
	 * a set can be provided without touching this file. Returns '' if unknown.
	 */
	function sc_icon_svg_library_markup( $id ) {
		$markup = '';
		// Built-in Lucide set (resolver lives in core, with the bundled data).
		if ( strpos( (string) $id, 'lucide/' ) === 0 && function_exists( 'fw_icon_lucide_markup' ) ) {
			$markup = fw_icon_lucide_markup( $id );
		}
		// Other libraries can register via the filter.
		return (string) apply_filters( 'sc_icon_svg_library_markup', $markup, $id );
	}
endif;
