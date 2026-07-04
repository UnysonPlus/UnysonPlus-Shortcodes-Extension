<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Section-Background helper — turn ANY element into a full-bleed Section background.
 * =============================================================================
 *
 * A small, reusable convention so any shortcode (image, image-box, before/after,
 * WebGL object, video …) can offer a "Use as Section Background" toggle that makes
 * the element FILL its parent Section (edge-to-edge) and sit BEHIND the Section's
 * own content, which is automatically lifted on top. One tested implementation,
 * loaded on demand (only when a page actually uses it).
 *
 * Three pieces:
 *   1. sc_section_background_field()  — the option definition (a Yes/No switch).
 *   2. sc_section_background_use()     — call at render time when the toggle is ON;
 *      it flags the page so the shared JS + CSS enqueue (on-demand).
 *   3. The shared runtime (static/js/sc-bg-fill.js + static/css/sc-bg-fill.css):
 *      window.scBgFill(el) finds the nearest <section>, moves the element in as its
 *      backdrop, and lifts the Section's other children above it.
 *
 * HOW A SHORTCODE ADOPTS IT
 * -------------------------
 *   options.php : 'as_background' => sc_section_background_field(),
 *   view.php    : if ( sc_section_background_is_on( sc_get('as_background',$atts,'no') ) ) {
 *                    $classes[] = 'sc-bg-fill';          // shared: fill + behind
 *                    // + your own '<el>--bg' class for the inner media (object-fit:cover)
 *                    sc_section_background_use();          // enqueue the runtime
 *                 }
 *   styles.css  : .<el>--bg .<inner-media> { height:100%; } // element-specific fill
 *
 * The shared JS auto-inits every `.sc-bg-fill` element. An element that needs the
 * HOST for its own interaction (e.g. before/after Spotlight binds pointer events to
 * the whole Section) instead adds `data-sc-bg-managed` and calls
 * `window.scBgFill(el)` itself (which returns the host) — the auto-init skips it.
 */

if ( ! function_exists( 'sc_section_background_field' ) ) {
	/**
	 * The reusable "Use as Section Background" switch. Drop it straight into an
	 * options array. Override label / desc / help / value via $args as needed.
	 *
	 * @param array $args label, desc, help, value ('yes'/'no', default 'no').
	 * @return array Unyson `switch` option definition.
	 */
	function sc_section_background_field( $args = array() ) {
		$args = array_merge( array(
			'label' => __( 'Use as Section Background', 'fw' ),
			'desc'  => __( 'Fill the parent Section and sit behind its content — the Section\'s own elements are automatically lifted on top.', 'fw' ),
			'help'  => __( 'The element stretches to cover its Section (its Max Width / Image Ratio / Corner Radius no longer apply). Give the Section a min-height so it has room to fill. Pairs best with a hover / cursor interaction.', 'fw' ),
			'value' => 'no',
		), $args );

		return array(
			'type'         => 'switch',
			'label'        => $args['label'],
			'desc'         => $args['desc'],
			'help'         => $args['help'],
			'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
			'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
			'value'        => $args['value'],
		);
	}
}

if ( ! function_exists( 'sc_section_background_is_on' ) ) {
	/**
	 * Tolerant truthiness for a switch value ('yes' / true / '1' / 1).
	 */
	function sc_section_background_is_on( $value ) {
		return $value === 'yes' || $value === true || $value === '1' || $value === 1;
	}
}

if ( ! function_exists( 'sc_section_background_use' ) ) {
	/**
	 * Flag the current page as using the section-background feature, so the shared
	 * runtime (JS + CSS) is enqueued in wp_footer. Call once per element that
	 * renders with the toggle ON.
	 */
	function sc_section_background_use( $effect = '' ) {
		$GLOBALS['_sc_bg_fill_used'] = true;

		// Optionally record a named custom effect so ONLY the effects a page actually
		// uses get their CSS/JS enqueued (same on-demand principle as the base runtime).
		if ( $effect !== '' && is_string( $effect ) ) {
			if ( empty( $GLOBALS['_sc_bg_fill_effects'] ) || ! is_array( $GLOBALS['_sc_bg_fill_effects'] ) ) {
				$GLOBALS['_sc_bg_fill_effects'] = array();
			}
			$GLOBALS['_sc_bg_fill_effects'][ $effect ] = true;
		}
	}
}

if ( ! function_exists( 'sc_section_background_flag' ) ) {
	function sc_section_background_flag() {
		return ! empty( $GLOBALS['_sc_bg_fill_used'] );
	}
}

if ( ! function_exists( 'sc_section_background_used_effects' ) ) {
	/**
	 * The named custom effects that rendered on this page (keys passed to
	 * sc_section_background_use()).
	 *
	 * @return string[]
	 */
	function sc_section_background_used_effects() {
		return ( ! empty( $GLOBALS['_sc_bg_fill_effects'] ) && is_array( $GLOBALS['_sc_bg_fill_effects'] ) )
			? array_keys( $GLOBALS['_sc_bg_fill_effects'] )
			: array();
	}
}

if ( ! function_exists( 'sc_section_background_effects' ) ) {
	/**
	 * The registry of custom Section-Background effects. A child theme / plugin adds
	 * its own from `functions.php` via the `sc_section_background_effects` filter:
	 *
	 *   add_filter( 'sc_section_background_effects', function ( $effects ) {
	 *       $effects['starfield'] = array(
	 *           'label'  => 'Starfield',
	 *           'css'    => get_stylesheet_directory_uri() . '/bg-effects/starfield.css',
	 *           'js'     => get_stylesheet_directory_uri() . '/bg-effects/starfield.js',
	 *           'ver'    => '1.0.0',                 // optional (cache-bust)
	 *           'deps'   => array(),                 // optional extra script deps
	 *           'class'  => '',                      // optional extra wrapper class
	 *           'render' => function ( $args ) {     // optional inner markup
	 *               return '<canvas class="starfield-canvas"></canvas>';
	 *           },
	 *       );
	 *       return $effects;
	 *   } );
	 *
	 * Each effect's `css`/`js` load ON DEMAND (only when the effect is actually used
	 * on a page), depending on the shared `sc-bg-fill` runtime.
	 *
	 * @return array[] keyed by effect id.
	 */
	function sc_section_background_effects() {
		static $cache = null;
		if ( $cache === null ) {
			$cache = apply_filters( 'sc_section_background_effects', array() );
			$cache = is_array( $cache ) ? $cache : array();
		}
		return $cache;
	}
}

if ( ! function_exists( 'sc_section_background_render' ) ) {
	/**
	 * Render a registered custom effect as a Section backdrop. Output it INSIDE a
	 * `<section>` (e.g. from a template, a Theme Builder block, or a custom shortcode's
	 * view) — the shared runtime lifts it to fill the Section, behind the content.
	 *
	 * Registers the flag + records the effect so its assets enqueue on demand.
	 *
	 * @param string $effect_id key registered via the `sc_section_background_effects` filter.
	 * @param array  $args      passed to the effect's `render` callback.
	 * @return string backdrop HTML (empty string if the effect isn't registered).
	 */
	function sc_section_background_render( $effect_id, $args = array() ) {
		$effects = sc_section_background_effects();
		if ( ! is_string( $effect_id ) || ! isset( $effects[ $effect_id ] ) ) {
			return '';
		}
		$effect = $effects[ $effect_id ];

		sc_section_background_use( $effect_id );

		$inner = '';
		if ( isset( $effect['render'] ) && is_callable( $effect['render'] ) ) {
			$inner = (string) call_user_func( $effect['render'], $args );
		}

		$class = 'sc-bg-fill sc-bg-effect sc-bg-effect--' . sanitize_html_class( $effect_id );
		if ( ! empty( $effect['class'] ) && is_string( $effect['class'] ) ) {
			$class .= ' ' . $effect['class'];
		}

		return '<div class="' . esc_attr( $class ) . '" data-sc-bg-effect="' . esc_attr( $effect_id ) . '">'
			. $inner
			. '</div>';
	}
}

/**
 * On-demand enqueue of the shared runtime — only when a section background actually
 * rendered on this page (mirrors the Animation helper's wp_footer/priority-5 model).
 */
if ( ! has_action( 'wp_footer', 'sc_section_background_enqueue_runtime' ) ) {
	function sc_section_background_enqueue_runtime() {
		if ( ! sc_section_background_flag() ) {
			return;
		}
		$ext = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' ) : null;
		if ( ! $ext ) {
			return;
		}
		$ver     = $ext->manifest->get_version();
		$css_dir = $ext->get_declared_path( '/static/css/sc-bg-fill.css' );
		$js_dir  = $ext->get_declared_path( '/static/js/sc-bg-fill.js' );
		$css_ver = file_exists( $css_dir ) ? $ver . '.' . filemtime( $css_dir ) : $ver;
		$js_ver  = file_exists( $js_dir )  ? $ver . '.' . filemtime( $js_dir )  : $ver;

		wp_enqueue_style( 'sc-bg-fill', $ext->get_declared_URI( '/static/css/sc-bg-fill.css' ), array(), $css_ver );
		wp_enqueue_script( 'sc-bg-fill', fw_min_uri( $ext->get_declared_URI( '/static/js/sc-bg-fill.js' ) ), array(), $js_ver, true );

		// On-demand assets for each CUSTOM effect used on this page (registered via the
		// `sc_section_background_effects` filter). Each depends on the shared runtime.
		$effects = sc_section_background_effects();
		foreach ( sc_section_background_used_effects() as $id ) {
			if ( ! isset( $effects[ $id ] ) ) {
				continue;
			}
			$e     = $effects[ $id ];
			$e_ver = ! empty( $e['ver'] ) ? $e['ver'] : $ver;
			$deps  = ( ! empty( $e['deps'] ) && is_array( $e['deps'] ) ) ? $e['deps'] : array();
			if ( ! empty( $e['css'] ) ) {
				wp_enqueue_style( 'sc-bg-effect-' . sanitize_key( $id ), $e['css'], array( 'sc-bg-fill' ), $e_ver );
			}
			if ( ! empty( $e['js'] ) ) {
				wp_enqueue_script( 'sc-bg-effect-' . sanitize_key( $id ), $e['js'], array_merge( array( 'sc-bg-fill' ), $deps ), $e_ver, true );
			}
		}
	}
	add_action( 'wp_footer', 'sc_section_background_enqueue_runtime', 5 );
}
