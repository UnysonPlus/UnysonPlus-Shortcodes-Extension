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
	function sc_section_background_use() {
		$GLOBALS['_sc_bg_fill_used'] = true;
	}
}

if ( ! function_exists( 'sc_section_background_flag' ) ) {
	function sc_section_background_flag() {
		return ! empty( $GLOBALS['_sc_bg_fill_used'] );
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
	}
	add_action( 'wp_footer', 'sc_section_background_enqueue_runtime', 5 );
}
