<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Reusable Easing Picker — an anime.js-style easing set exposed as a POPOVER image picker.
 *
 * The picker stores a scalar easing KEY (e.g. 'out_elastic') — the same value shape as a plain
 * select, so it is a drop-in replacement with no migration. Two resolvers turn a key into a usable
 * value for either kind of consumer:
 *   - sc_easing_css( $key )  → a CSS `animation-timing-function` value (cubic curves are emitted as
 *                              `linear(…)` sampled from the real easing; `steps(…)` for the step
 *                              families; '' for Default / no override). Legacy raw CSS strings
 *                              ('ease-in', 'cubic-bezier(…)') pass straight through.
 *   - sc_easing_gsap( $key ) → the nearest GSAP ease name, for JS-driven modules (future reuse).
 *
 * The curve THUMBNAILS in static/img/easings/<key>.svg are generated from the SAME samples as the
 * CSS, so each tile always matches its behaviour (see scratchpad/gen-easings.cjs).
 *
 * NOTE on browser support: `linear()` easing needs a 2023+ browser (Chrome 113, Firefox 112,
 * Safari 17.4). Older browsers ignore an unknown timing-function and fall back to the effect's
 * built-in curve — a graceful degradation, not a break.
 */

if ( ! function_exists( 'sc_easing_defs' ) ) :
	/** All easing definitions, keyed by easing key. [ 'label', 'group', 'css', 'gsap' ]. */
	function sc_easing_defs() {
		static $defs = null;
		if ( $defs === null ) {
			$file = __DIR__ . '/easing-defs.php';
			$defs = file_exists( $file ) ? (array) include $file : array();
		}
		return $defs;
	}
endif;

if ( ! function_exists( 'sc_easing_css' ) ) :
	/** Resolve an easing key to a CSS animation-timing-function value ('' = no override / Default). */
	function sc_easing_css( $key ) {
		$key = (string) $key;
		if ( $key === '' || $key === 'default' ) {
			return '';
		}
		$defs = sc_easing_defs();
		if ( isset( $defs[ $key ] ) ) {
			return (string) $defs[ $key ]['css'];
		}
		// Legacy raw CSS timing-function value (from before this picker existed) → pass through.
		if ( preg_match( '/^(ease|ease-in|ease-out|ease-in-out|linear|step-start|step-end|cubic-bezier\(|steps\(|linear\()/', $key ) ) {
			return $key;
		}
		return '';
	}
endif;

if ( ! function_exists( 'sc_easing_gsap' ) ) :
	/** Resolve an easing key to the nearest GSAP ease name ('' = default). */
	function sc_easing_gsap( $key ) {
		$key  = (string) $key;
		$defs = sc_easing_defs();
		return isset( $defs[ $key ] ) ? (string) $defs[ $key ]['gsap'] : '';
	}
endif;

if ( ! function_exists( 'sc_easing_image_choices' ) ) :
	/** Build the image-picker tiles (key => {small,large,label}) pointing at the curve SVGs. */
	function sc_easing_image_choices() {
		$ext  = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' ) : null;
		$base = $ext ? $ext->get_declared_URI( '/static/img/easings' ) : '';
		$tiles = array();
		foreach ( sc_easing_defs() as $key => $d ) {
			$tiles[ $key ] = array(
				'small' => array( 'src' => $base . '/' . $key . '.svg', 'height' => 84 ),
				'large' => array( 'src' => $base . '/' . $key . '.svg', 'height' => 150 ),
				'label' => $d['label'],
			);
		}
		return $tiles;
	}
endif;

if ( ! function_exists( 'sc_easing_field' ) ) :
	/**
	 * Build a POPOVER easing picker option (scalar passthrough value = the easing key).
	 * $args: label, desc, value (default key, defaults to 'default').
	 */
	function sc_easing_field( $args = array() ) {
		$a = array_merge(
			array(
				'label' => __( 'Easing Function', 'fw' ),
				'desc'  => __( 'Override the animation timing curve. Default keeps the effect\'s built-in curve. Spring / Elastic / Bounce need a 2023+ browser (older ones fall back to Default).', 'fw' ),
				'value' => 'default',
			),
			$args
		);
		$summary = array();
		foreach ( sc_easing_defs() as $key => $d ) {
			$summary[ $key ] = $d['label'];
		}
		return array(
			'type'          => 'popover',
			'label'         => $a['label'],
			'desc'          => $a['desc'],
			'value'         => $a['value'],
			'summary'       => $summary,
			'trigger_label' => __( 'Default', 'fw' ),
			'inner-options' => array(
				'easing' => array(
					'type'    => 'image-picker',
					'label'   => false,
					'value'   => $a['value'],
					'choices' => sc_easing_image_choices(),
				),
			),
		);
	}
endif;
