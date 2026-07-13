<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * `svg-code` option type — an inline-SVG field.
 *
 * A textarea for `<svg>…</svg>` markup plus an "Upload SVG file" button that reads the chosen .svg
 * CLIENT-SIDE (FileReader.readAsText) straight into the textarea. Because the file never goes
 * through the WordPress media uploader, there is no SVG-MIME block and no "Safe SVG" plugin needed.
 * The markup is sanitised server-side on save (sc_icon_sanitize_svg) so scripts / event handlers /
 * remote refs are stripped. Value = the sanitised SVG markup string. Reusable anywhere options run.
 */
class FW_Option_Type_Svg_Code extends FW_Option_Type {

	public function _init() {}

	public function get_type() {
		return 'svg-code';
	}

	private function get_uri( $append = '' ) {
		return fw_get_framework_directory_uri( '/extensions/shortcodes/includes/option-types/svg-code' . $append );
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type() {
		return 'full';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'value'       => '',
			'label'       => false,
			'desc'        => false,
			'placeholder' => '<svg viewBox="0 0 24 24">…</svg>',
		);
	}

	/**
	 * @internal
	 */
	protected function _enqueue_static( $id, $option, $data ) {
		$ver = fw()->manifest->get_version();
		wp_enqueue_style(
			'fw-option-type-svg-code',
			$this->get_uri( '/static/css/svg-code.css' ),
			array(),
			$ver
		);
		wp_enqueue_script(
			'fw-option-type-svg-code',
			$this->get_uri( '/static/js/svg-code.js' ),
			array( 'jquery', 'fw', 'fw-events' ),
			$ver,
			true
		);
	}

	/**
	 * @internal
	 */
	protected function _render( $id, $option, $data ) {
		$option['value'] = (string) $data['value'];
		return fw_render_view( dirname( __FILE__ ) . '/view.php', compact( 'id', 'option', 'data' ) );
	}

	/**
	 * @internal
	 *
	 * @param array $option
	 * @param array|null|string $input_value
	 * @return string
	 */
	protected function _get_value_from_input( $option, $input_value ) {
		$val = (string) ( is_null( $input_value ) ? $option['value'] : $input_value );
		$val = trim( $val );
		if ( $val === '' ) {
			return '';
		}
		// Only keep something that actually looks like SVG; sanitise it hard.
		if ( stripos( $val, '<svg' ) === false ) {
			return '';
		}
		if ( function_exists( 'sc_icon_sanitize_svg' ) ) {
			$val = (string) sc_icon_sanitize_svg( $val );
		}
		return $val;
	}
}

FW_Option_Type::register( 'FW_Option_Type_Svg_Code' );
