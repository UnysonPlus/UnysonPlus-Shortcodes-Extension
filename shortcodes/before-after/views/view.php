<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Before / After — frontend render.
 *
 * @var array $atts
 *
 * TWO types (an inline image-picker multi-picker `type`):
 *
 *   • comparison — two stacked image layers (the top "before" layer is clipped to
 *     reveal the bottom "after" layer), a draggable handle, and optional labels.
 *     scripts.js drives drag / hover / toggle, both orientations, the intro sweep
 *     and keyboard accessibility. All visual variation is CSS, keyed off the
 *     `--design-<key>` and behaviour classes + a few CSS custom properties.
 *   • spotlight  — the same two layers, but the top "before" layer carries a soft
 *     INVERSE radial mask that follows the cursor: a circular hole under the pointer
 *     reveals the "after" beneath (the Lithos effect). Optionally fills its Section
 *     as a background (`as_background`), lifting the Section's own content on top.
 */

if ( ! function_exists( 'sc_get' ) ) {
	function sc_get( $path, $atts, $default = '' ) {
		if ( function_exists( 'fw_akg' ) ) {
			$v = fw_akg( $path, $atts, null );
			if ( $v !== null ) {
				return $v;
			}
		}
		return $default;
	}
}

if ( ! function_exists( 'sc_bac_registry' ) ) {
	function sc_bac_registry() {
		static $registry = null;
		if ( $registry === null ) {
			$registry = require __DIR__ . '/parts/registry.php';
			if ( ! is_array( $registry ) ) {
				$registry = array();
			}
		}
		return $registry;
	}
}

if ( ! function_exists( 'sc_bac_image' ) ) {
	/**
	 * Resolve an upload att to [ url, alt ] (full-size url, alt from the library).
	 */
	function sc_bac_image( $raw ) {
		$out = array( 'url' => '', 'alt' => '' );
		if ( ! is_array( $raw ) ) {
			return $out;
		}
		$id  = ! empty( $raw['attachment_id'] ) ? (int) $raw['attachment_id'] : 0;
		$url = ! empty( $raw['url'] ) ? $raw['url'] : '';
		if ( $id ) {
			$full = wp_get_attachment_image_url( $id, 'full' );
			if ( $full ) {
				$url = $full;
			}
			$out['alt'] = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
		}
		$out['url'] = $url;
		return $out;
	}
}

if ( ! function_exists( 'sc_bac_color_var' ) ) {
	/**
	 * Read a compact-color att and, if a CUSTOM hex was picked, return a CSS var
	 * declaration "<name>:<hex>;". Preset (class) picks fall back to the
	 * stylesheet default (return ''). Mirrors image-box's accent/overlay vars.
	 */
	function sc_bac_color_var( $raw, $name ) {
		if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
			$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
			if ( $hex !== '' ) {
				return $name . ':' . $hex . ';';
			}
		}
		return '';
	}
}

if ( ! function_exists( 'sc_bac_render' ) ) {
	function sc_bac_render( $atts ) {
		/* --- Type + the two images (shared) ------------------------------- */
		$type = sc_get( 'type/type', $atts, 'comparison' );
		if ( ! in_array( $type, array( 'comparison', 'spotlight' ), true ) ) {
			$type = 'comparison';
		}

		$before = sc_bac_image( sc_get( 'before_image', $atts, array() ) );
		$after  = sc_bac_image( sc_get( 'after_image', $atts, array() ) );

		// Need both images. In the editor show a hint; on the front-end render nothing.
		if ( $before['url'] === '' || $after['url'] === '' ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-bac__empty">' . esc_html__( 'Add both a Before and an After image.', 'fw' ) . '</div>';
			}
			return '';
		}

		/* --- Shared appearance -------------------------------------------- */
		$ratio     = sc_get( 'ratio', $atts, 'ratio-16-9' );
		$rounded   = sc_get( 'rounded', $atts, 'rounded' );
		$max_width = trim( (string) sc_get( 'max_width', $atts, '' ) );

		// Shared "Use as Section Background" toggle (applies to BOTH types) via the
		// reusable helper. When on, the wrapper carries the shared `.sc-bg-fill` class
		// and the runtime is enqueued on demand.
		$as_bg = function_exists( 'sc_section_background_is_on' )
			&& sc_section_background_is_on( sc_get( 'as_background', $atts, 'no' ) );

		$b_alt = $before['alt'];
		$a_alt = $after['alt'] !== '' ? $after['alt'] : $before['alt'];

		if ( $type === 'spotlight' ) {
			return sc_bac_render_spotlight( $atts, $before, $after, $b_alt, $a_alt, $ratio, $rounded, $max_width, $as_bg );
		}
		return sc_bac_render_comparison( $atts, $before, $after, $b_alt, $a_alt, $ratio, $rounded, $max_width, $as_bg );
	}
}

if ( ! function_exists( 'sc_bac_render_comparison' ) ) {
	function sc_bac_render_comparison( $atts, $before, $after, $b_alt, $a_alt, $ratio, $rounded, $max_width, $as_bg = false ) {
		$registry = sc_bac_registry();

		$design = sc_get( 'type/comparison/design', $atts, 'classic' );
		if ( ! isset( $registry[ $design ] ) ) {
			$design = 'classic';
		}
		$meta = $registry[ $design ];

		/* --- Behaviour ---------------------------------------------------- */
		$orientation = sc_get( 'type/comparison/orientation', $atts, 'horizontal' ) === 'vertical' ? 'vertical' : 'horizontal';
		$interaction = sc_get( 'type/comparison/interaction', $atts, 'drag' );
		if ( ! in_array( $interaction, array( 'drag', 'hover', 'toggle' ), true ) ) {
			$interaction = 'drag';
		}
		$start = (int) sc_get( 'type/comparison/start_position', $atts, 50 );
		$start = max( 0, min( 100, $start ) );
		$auto  = sc_get( 'type/comparison/auto_intro', $atts, 'yes' ) === 'yes' && $interaction !== 'toggle';

		$handle_size = sc_get( 'type/comparison/handle_size', $atts, 'md' );

		/* --- Labels ------------------------------------------------------- */
		$show_labels  = sc_get( 'type/comparison/show_labels', $atts, 'yes' ) === 'yes' || ! empty( $meta['force_labels'] );
		$before_label = trim( (string) sc_get( 'type/comparison/before_label', $atts, __( 'Before', 'fw' ) ) );
		$after_label  = trim( (string) sc_get( 'type/comparison/after_label', $atts, __( 'After', 'fw' ) ) );

		/* --- Color CSS vars (custom hex only) ----------------------------- */
		$style_var  = '--bac-pos:' . $start . '%;';
		$style_var .= sc_bac_color_var( sc_get( 'divider_color', $atts, '' ), '--bac-divider' );
		$style_var .= sc_bac_color_var( sc_get( 'handle_color', $atts, '' ), '--bac-handle' );
		$style_var .= sc_bac_color_var( sc_get( 'handle_icon_color', $atts, '' ), '--bac-handle-icon' );
		$style_var .= sc_bac_color_var( sc_get( 'label_bg', $atts, '' ), '--bac-label-bg' );
		$style_var .= sc_bac_color_var( sc_get( 'label_text', $atts, '' ), '--bac-label-text' );
		if ( ! $as_bg && $max_width !== '' ) {
			$mw = preg_replace( '/[^0-9a-zA-Z.%]/', '', $max_width );
			if ( $mw !== '' ) {
				$style_var .= 'max-width:' . $mw . ';';
			}
		}

		/* --- Wrapper classes ---------------------------------------------- */
		$classes = array(
			'fw-bac',
			'fw-bac--design-' . sanitize_html_class( $design ),
			'fw-bac--' . $orientation,
			'fw-bac--int-' . sanitize_html_class( $interaction ),
			'fw-bac--knob-' . sanitize_html_class( $handle_size ),
		);
		if ( $as_bg ) {
			$classes[] = 'fw-bac--bg';
			$classes[] = 'sc-bg-fill'; // shared Section-background runtime hook
			if ( function_exists( 'sc_section_background_use' ) ) {
				sc_section_background_use();
			}
		} else {
			$classes[] = 'fw-bac--' . sanitize_html_class( $ratio );
			$classes[] = sanitize_html_class( $rounded );
		}
		if ( ! $show_labels ) {
			$classes[] = 'fw-bac--no-labels';
		}

		$atts['base_class']       = 'before-after';
		$atts['unique_id_prefix'] = 'bac-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );

		$attr = sc_build_wrapper_attr( $atts );
		$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;

		$chevrons = '<svg class="fw-bac__chev" viewBox="0 0 24 24" width="13" height="13" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>'
			. '<svg class="fw-bac__chev" viewBox="0 0 24 24" width="13" height="13" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>';

		$aria_label = sprintf( __( 'Before/after comparison: %1$s vs %2$s', 'fw' ), $before_label, $after_label );

		ob_start();
		?>
		<div <?php echo fw_attr_to_html( $attr ); ?>
			role="slider" tabindex="0"
			aria-label="<?php echo esc_attr( $aria_label ); ?>"
			aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( $start ); ?>"
			data-bac
			<?php if ( $as_bg ) : ?>data-bg="1" data-sc-bg-managed<?php endif; ?>
			data-orientation="<?php echo esc_attr( $orientation ); ?>"
			data-interaction="<?php echo esc_attr( $interaction ); ?>"
			data-start="<?php echo esc_attr( $start ); ?>"
			data-auto="<?php echo $auto ? '1' : '0'; ?>">
			<div class="fw-bac__media">
				<img class="fw-bac__sizer" src="<?php echo esc_url( $before['url'] ); ?>" alt="" aria-hidden="true" />

				<div class="fw-bac__layer fw-bac__after">
					<img src="<?php echo esc_url( $after['url'] ); ?>" alt="<?php echo esc_attr( $a_alt ); ?>" loading="lazy" decoding="async" />
				</div>
				<div class="fw-bac__layer fw-bac__before">
					<img src="<?php echo esc_url( $before['url'] ); ?>" alt="<?php echo esc_attr( $b_alt ); ?>" loading="lazy" decoding="async" />
				</div>

				<?php if ( $show_labels && $before_label !== '' ) : ?>
					<span class="fw-bac__label fw-bac__label--before"><?php echo esc_html( $before_label ); ?></span>
				<?php endif; ?>
				<?php if ( $show_labels && $after_label !== '' ) : ?>
					<span class="fw-bac__label fw-bac__label--after"><?php echo esc_html( $after_label ); ?></span>
				<?php endif; ?>

				<div class="fw-bac__handle" aria-hidden="true">
					<span class="fw-bac__line"></span>
					<span class="fw-bac__knob"><?php echo $chevrons; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — static SVG ?></span>
				</div>

				<?php if ( $interaction === 'toggle' ) : ?>
					<span class="fw-bac__hint" aria-hidden="true"><?php esc_html_e( 'Tap to compare', 'fw' ); ?></span>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}

if ( ! function_exists( 'sc_bac_render_spotlight' ) ) {
	function sc_bac_render_spotlight( $atts, $before, $after, $b_alt, $a_alt, $ratio, $rounded, $max_width, $as_bg = false ) {
		$radius   = (int) sc_get( 'type/spotlight/spotlight_radius', $atts, 240 );
		$radius   = max( 40, min( 900, $radius ) );
		$softness = (int) sc_get( 'type/spotlight/spotlight_softness', $atts, 55 );
		$softness = max( 0, min( 95, $softness ) );
		$smooth   = sc_get( 'type/spotlight/smooth_follow', $atts, 'yes' ) === 'yes';
		$idle     = sc_get( 'type/spotlight/reveal_on_load', $atts, 'yes' ) === 'yes';

		// Inner (fully-transparent) stop of the reveal, as a % of the radius.
		// 0% softness = hard edge (inner 100%), 95% softness = very feathered (inner 5%).
		$inner = 100 - $softness;

		/* --- Wrapper classes ---------------------------------------------- */
		$classes = array( 'fw-bac', 'fw-bac--spotlight' );
		if ( $as_bg ) {
			$classes[] = 'fw-bac--bg';
			$classes[] = 'sc-bg-fill'; // shared Section-background runtime hook
			if ( function_exists( 'sc_section_background_use' ) ) {
				sc_section_background_use();
			}
		} else {
			$classes[] = 'fw-bac--' . sanitize_html_class( $ratio );
			$classes[] = sanitize_html_class( $rounded );
		}

		$style_var = '--sr-r:' . $radius . 'px;--sr-in:' . $inner . '%;';
		if ( ! $as_bg && $max_width !== '' ) {
			$mw = preg_replace( '/[^0-9a-zA-Z.%]/', '', $max_width );
			if ( $mw !== '' ) {
				$style_var .= 'max-width:' . $mw . ';';
			}
		}

		$atts['base_class']       = 'before-after';
		$atts['unique_id_prefix'] = 'bac-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );

		$attr = sc_build_wrapper_attr( $atts );
		$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;

		$aria_label = __( 'Spotlight image reveal: move the pointer to reveal the second image.', 'fw' );

		ob_start();
		?>
		<div <?php echo fw_attr_to_html( $attr ); ?>
			role="img"
			aria-label="<?php echo esc_attr( $aria_label ); ?>"
			data-bac-spot
			data-bg="<?php echo $as_bg ? '1' : '0'; ?>"
			<?php if ( $as_bg ) : ?>data-sc-bg-managed<?php endif; ?>
			data-smooth="<?php echo $smooth ? '1' : '0'; ?>"
			data-idle="<?php echo $idle ? '1' : '0'; ?>">
			<div class="fw-bac__media">
				<img class="fw-bac__sizer" src="<?php echo esc_url( $before['url'] ); ?>" alt="" aria-hidden="true" />

				<div class="fw-bac__layer fw-bac__after">
					<img src="<?php echo esc_url( $after['url'] ); ?>" alt="<?php echo esc_attr( $a_alt ); ?>" loading="lazy" decoding="async" />
				</div>
				<div class="fw-bac__layer fw-bac__before">
					<img src="<?php echo esc_url( $before['url'] ); ?>" alt="<?php echo esc_attr( $b_alt ); ?>" loading="lazy" decoding="async" />
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}

echo sc_bac_render( $atts );
