<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Shared helpers for the plugin's built-in Theme Settings sections.
 *
 * The built-in sections live in this folder as small per-feature files named like
 * the theme's own option files (components-color.php, miscellaneous-custom-css.php,
 * …). Each file sets `$options = array( … )` and is loaded with upw_ts_get_options(),
 * mirroring how the theme loads framework-customizations/theme/options/*.php via
 * fw()->theme->get_options().
 */

if ( ! function_exists( 'upw_ts_get_options' ) ) :
	/**
	 * Load a built-in Theme Settings section file and return its `$options` array.
	 *
	 * @param string $name      File name without extension, relative to this folder
	 *                          (e.g. 'components-color', 'miscellaneous-performance').
	 * @param array  $variables Extra variables made available inside the file (e.g.
	 *                          'color_choices', 'gap_choices'). Mirrors the $variables
	 *                          arg of fw()->theme->get_options().
	 * @return array
	 */
	function upw_ts_get_options( $name, array $variables = array() ) {
		$path = __DIR__ . '/' . $name . '.php';
		if ( ! is_file( $path ) ) {
			return array();
		}
		if ( function_exists( 'fw_get_variables_from_file' ) ) {
			$vars = fw_get_variables_from_file( $path, array( 'options' => array() ), $variables );
			return ( isset( $vars['options'] ) && is_array( $vars['options'] ) ) ? $vars['options'] : array();
		}
		return array();
	}
endif;

if ( ! function_exists( 'upw_ts_setting' ) ) :
	/**
	 * Read a leaf value out of a `multi` container saved in the Theme Settings store
	 * (fw_theme_settings_options:{theme-id}). Mirrors the theme's unysonplus_misc_get()
	 * read path so the plugin's built-in Miscellaneous features reuse the SAME storage
	 * keys (zero migration) and work under any theme.
	 *
	 * @param string $bucket  The `multi` container id (e.g. 'misc_custom_css').
	 * @param string $key     The leaf id inside it (e.g. 'custom_css').
	 * @param mixed  $default Returned when unset/empty.
	 * @return mixed
	 */
	function upw_ts_setting( $bucket, $key, $default = '' ) {
		if ( ! function_exists( 'fw_get_db_settings_option' ) ) {
			return $default;
		}
		$data = fw_get_db_settings_option( $bucket, array() );
		if ( is_array( $data ) && array_key_exists( $key, $data ) ) {
			$val = $data[ $key ];
			if ( $val !== null && $val !== '' ) {
				// `switch` fields inside a multi save as bool; normalise to 'yes'/'no'.
				if ( is_bool( $val ) ) {
					return $val ? 'yes' : 'no';
				}
				return $val;
			}
		}
		return $default;
	}
endif;

if ( ! function_exists( 'upw_ts_merge_into_misc' ) ) :
	/**
	 * Merge built-in sub-tabs into the Miscellaneous section of the Theme Settings
	 * options (the section keyed `misc_container`, whose sub-tabs live inside its
	 * `box` container). If the active theme provides no Miscellaneous section (e.g. a
	 * non-Unyson theme), a Miscellaneous section is created to host them.
	 *
	 * @param array $options  The full Theme Settings options (list of nav sections).
	 * @param array $subtabs  tab_id => tab-definition entries to append.
	 * @return array
	 */
	function upw_ts_merge_into_misc( array $options, array $subtabs ) {
		if ( ! $subtabs ) {
			return $options;
		}
		foreach ( $options as $i => $section ) {
			if ( ! is_array( $section ) || ! isset( $section['misc_container']['options'] ) || ! is_array( $section['misc_container']['options'] ) ) {
				continue;
			}
			foreach ( $section['misc_container']['options'] as $bk => $box ) {
				if ( isset( $box['type'], $box['options'] ) && $box['type'] === 'box' && is_array( $box['options'] ) ) {
					$options[ $i ]['misc_container']['options'][ $bk ]['options'] = array_merge( $box['options'], $subtabs );
					return $options;
				}
			}
		}
		// No Miscellaneous section found — create one to host the built-ins.
		$options[] = array(
			'misc_container' => array(
				'title'   => __( 'Miscellaneous', 'fw' ),
				'type'    => 'tab',
				'options' => array(
					'misc' => array( 'title' => __( 'Miscellaneous', 'fw' ), 'type' => 'box', 'options' => $subtabs ),
				),
			),
		);
		return $options;
	}
endif;

if ( ! function_exists( 'unysonplus_collect_preset_leaf_keys' ) ) :
	/**
	 * Walk an options schema and collect the LEAF option ids (the keys that actually
	 * store a value). Containers (tab / box / group) hold no value of their own, so we
	 * recurse through them. Used by the preset migration to copy exactly the preset
	 * keys — and nothing else — out of the legacy extension store.
	 *
	 * @param array $options Options array.
	 * @param array $keys    Accumulator (id => true), passed by reference.
	 */
	function unysonplus_collect_preset_leaf_keys( array $options, array &$keys ) {
		foreach ( $options as $id => $opt ) {
			if ( ! is_array( $opt ) ) {
				continue;
			}
			$type = isset( $opt['type'] ) ? $opt['type'] : '';
			if ( in_array( $type, array( 'tab', 'box', 'group' ), true ) ) {
				if ( isset( $opt['options'] ) && is_array( $opt['options'] ) ) {
					unysonplus_collect_preset_leaf_keys( $opt['options'], $keys );
				}
				continue;
			}
			if ( is_string( $id ) && $id !== '' ) {
				$keys[ $id ] = true;
			}
		}
	}
endif;
