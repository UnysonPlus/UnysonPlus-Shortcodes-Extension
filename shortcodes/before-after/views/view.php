<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Before / After — frontend render.
 *
 * @var array $atts
 *
 * Normalizes the two images, resolves the design (skin) + behaviour, then emits
 * a single shared structure: two stacked image layers (the top "before" layer is
 * clipped to reveal the bottom "after" layer), a draggable handle, and optional
 * labels. scripts.js drives drag / hover / toggle, both orientations, the intro
 * sweep and keyboard accessibility. All visual variation is CSS, keyed off the
 * `--design-<key>` and behaviour classes + a few CSS custom properties.
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
		$registry = sc_bac_registry();

		$design = sc_get( 'design', $atts, 'classic' );
		if ( ! isset( $registry[ $design ] ) ) {
			$design = 'classic';
		}
		$meta = $registry[ $design ];

		$before = sc_bac_image( sc_get( 'before_image', $atts, array() ) );
		$after  = sc_bac_image( sc_get( 'after_image', $atts, array() ) );

		// Need both images. In the editor show a hint; on the front-end render nothing.
		if ( $before['url'] === '' || $after['url'] === '' ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-bac__empty">' . esc_html__( 'Add both a Before and an After image.', 'fw' ) . '</div>';
			}
			return '';
		}

		/* --- Behaviour ---------------------------------------------------- */
		$orientation = sc_get( 'orientation', $atts, 'horizontal' ) === 'vertical' ? 'vertical' : 'horizontal';
		$interaction = sc_get( 'interaction', $atts, 'drag' );
		if ( ! in_array( $interaction, array( 'drag', 'hover', 'toggle' ), true ) ) {
			$interaction = 'drag';
		}
		$start = (int) sc_get( 'start_position', $atts, 50 );
		$start = max( 0, min( 100, $start ) );
		$auto  = sc_get( 'auto_intro', $atts, 'yes' ) === 'yes' && $interaction !== 'toggle';

		$ratio       = sc_get( 'ratio', $atts, 'ratio-16-9' );
		$rounded     = sc_get( 'rounded', $atts, 'rounded' );
		$handle_size = sc_get( 'handle_size', $atts, 'md' );
		$max_width   = trim( (string) sc_get( 'max_width', $atts, '' ) );

		/* --- Labels ------------------------------------------------------- */
		$show_labels  = sc_get( 'show_labels', $atts, 'yes' ) === 'yes' || ! empty( $meta['force_labels'] );
		$before_label = trim( (string) sc_get( 'before_label', $atts, __( 'Before', 'fw' ) ) );
		$after_label  = trim( (string) sc_get( 'after_label', $atts, __( 'After', 'fw' ) ) );

		/* --- Color CSS vars (custom hex only) ----------------------------- */
		$style_var  = '--bac-pos:' . $start . '%;';
		$style_var .= sc_bac_color_var( sc_get( 'divider_color', $atts, '' ), '--bac-divider' );
		$style_var .= sc_bac_color_var( sc_get( 'handle_color', $atts, '' ), '--bac-handle' );
		$style_var .= sc_bac_color_var( sc_get( 'handle_icon_color', $atts, '' ), '--bac-handle-icon' );
		$style_var .= sc_bac_color_var( sc_get( 'label_bg', $atts, '' ), '--bac-label-bg' );
		$style_var .= sc_bac_color_var( sc_get( 'label_text', $atts, '' ), '--bac-label-text' );
		if ( $max_width !== '' ) {
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
			'fw-bac--' . sanitize_html_class( $ratio ),
			'fw-bac--knob-' . sanitize_html_class( $handle_size ),
			sanitize_html_class( $rounded ),
		);
		if ( ! $show_labels ) {
			$classes[] = 'fw-bac--no-labels';
		}

		$atts['base_class']       = 'before-after';
		$atts['unique_id_prefix'] = 'bac-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );

		$attr = sc_build_wrapper_attr( $atts );
		$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;

		/* --- Build the media + handle ------------------------------------- */
		$b_alt = $before['alt'];
		$a_alt = $after['alt'] !== '' ? $after['alt'] : $before['alt'];

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

echo sc_bac_render( $atts );
