<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * `easing-picker` option type — a compact, memory-safe curve picker for ~40 anime.js-style easings.
 *
 * WHY a bespoke type instead of a popover+image-picker: the Entrance settings panel is rebuilt for
 * every one of the ~56 Animate.css effects, so a 41-tile image picker there renders ~56×41 tiles
 * per element and exhausts memory. This type renders only a LIGHT trigger (one thumbnail + label)
 * per instance; the full 41-tile grid is built ONCE, client-side, as a single SHARED palette that
 * every trigger on the page opens. So it stays cheap even when placed inside a 56×-duplicated
 * reveal, and it is reusable across every module.
 *
 * Value = a scalar easing KEY (e.g. 'out_elastic'); 'default' = no override. Resolve it with
 * sc_easing_css() (CSS) or sc_easing_gsap() (GSAP). Legacy raw-CSS values pass through.
 */
class FW_Option_Type_Easing_Picker extends FW_Option_Type {

	private static $localized = false;

	public function _init() {}

	public function get_type() {
		return 'easing-picker';
	}

	private function get_uri( $append = '' ) {
		return fw_get_framework_directory_uri( '/extensions/shortcodes/includes/option-types/easing-picker' . $append );
	}

	/** @internal */
	protected function _get_defaults() {
		return array(
			'value' => 'default',
			'label' => __( 'Easing', 'fw' ),
			'desc'  => false,
		);
	}

	/** @internal */
	protected function _enqueue_static( $id, $option, $data ) {
		// Cache-bust on the shortcodes extension's version (this asset lives there), not core.
		$sc_ext = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' ) : null;
		$ver    = $sc_ext ? $sc_ext->manifest->get_version() : fw()->manifest->get_version();
		wp_enqueue_style(
			'fw-option-type-easing-picker',
			$this->get_uri( '/static/css/styles.css' ),
			array(),
			$ver
		);
		wp_enqueue_script(
			'fw-option-type-easing-picker',
			$this->get_uri( '/static/js/scripts.js' ),
			array( 'jquery', 'fw', 'fw-events' ),
			$ver,
			true
		);

		// Localize the palette data ONCE (shared by every trigger on the page).
		if ( ! self::$localized ) {
			self::$localized = true;
			$ext  = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' ) : null;
			$base = $ext ? $ext->get_declared_URI( '/static/img/easings' ) : '';
			$defs = function_exists( 'sc_easing_defs' ) ? sc_easing_defs() : array();
			$out  = array();
			foreach ( $defs as $key => $d ) {
				$out[] = array(
					'key'   => $key,
					'label' => $d['label'],
					'group' => $d['group'],
					'svg'   => $base . '/' . $key . '.svg',
				);
			}
			wp_localize_script( 'fw-option-type-easing-picker', 'upwEasingData', array(
				'items'   => $out,
				'default' => 'default',
				'i18n'    => array( 'search' => __( 'Search easings…', 'fw' ) ),
			) );
		}
	}

	/** @internal */
	protected function _render( $id, $option, $data ) {
		$option['value'] = (string) $data['value'];
		return fw_render_view( dirname( __FILE__ ) . '/view.php', compact( 'id', 'option', 'data' ) );
	}

	/** @internal */
	protected function _get_value_from_input( $option, $input_value ) {
		$val = (string) ( is_null( $input_value ) ? $option['value'] : $input_value );
		$val = trim( $val );
		if ( $val === '' ) {
			return 'default';
		}
		$defs = function_exists( 'sc_easing_defs' ) ? sc_easing_defs() : array();
		if ( $val === 'default' || isset( $defs[ $val ] ) ) {
			return $val;
		}
		// Legacy raw CSS timing-function value → keep it (sc_easing_css passes it through).
		if ( preg_match( '/^(ease|ease-in|ease-out|ease-in-out|linear|step-start|step-end|cubic-bezier\(|steps\(|linear\()/', $val ) ) {
			return $val;
		}
		return 'default';
	}
}

FW_Option_Type::register( 'FW_Option_Type_Easing_Picker' );
