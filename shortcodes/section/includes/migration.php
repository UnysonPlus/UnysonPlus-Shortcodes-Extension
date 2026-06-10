<?php if ( ! defined( 'FW' ) ) die( 'Forbidden' );

/**
 * Section legacy background → background-pro migration.
 *
 * The Section shortcode used to carry four separate background atts:
 *   - `background_color` (color-picker, Layout tab)
 *   - `background_image` (background-image type, Layout tab)
 *   - `video`           (text URL, Layout tab)
 *   - `bg_color`        (predefined-colors-color-picker preset, Styling tab)
 * These are now consolidated into a single `background` (background-pro) control.
 *
 * These helpers synthesize a background-pro-shaped value from the legacy atts so
 * existing sections (a) show their background pre-filled in the new control when
 * edited, and (b) keep rendering identically on the frontend until re-saved.
 * Non-destructive: the stored legacy atts are left untouched; the frontend and
 * the editor both prefer the new `background` value once it exists.
 *
 * Shared by views/view.php (frontend) and the page-builder section item (admin).
 */

if ( ! function_exists( 'section_bg_preset_to_hex' ) ) :
	/**
	 * Resolve a legacy bg_color value to a hex/rgba string.
	 * Accepts a raw color ('#abc' / 'rgba(...)'), or a palette class slug
	 * ('bg-primary' / 'primary'); returns '' if it cannot resolve.
	 *
	 * Note: resolving a palette slug FREEZES the (formerly palette-adaptive)
	 * preset into a static color — the intended tradeoff of folding the bg_color
	 * preset into background-pro's inline color layer.
	 */
	function section_bg_preset_to_hex( $value ) {
		$value = trim( (string) $value );
		if ( $value === '' ) { return ''; }
		// Already a concrete color.
		if ( $value[0] === '#' || stripos( $value, 'rgb' ) === 0 ) { return $value; }
		$slug = preg_replace( '/^(bg|text|background)-/', '', $value );
		if ( function_exists( 'unysonplus_color_preset_slug_map' ) ) {
			$map = unysonplus_color_preset_slug_map();
			if ( isset( $map[ $value ] ) ) { return $map[ $value ]; }
			if ( isset( $map[ $slug ] ) )  { return $map[ $slug ]; }
		}
		return '';
	}
endif;

if ( ! function_exists( 'section_migrate_legacy_background' ) ) :
	/**
	 * Build a background-pro-shaped value from the legacy Section atts.
	 *
	 * @param array $atts The section shortcode atts.
	 * @return array|null background-pro value, or null when no legacy bg is set.
	 */
	function section_migrate_legacy_background( $atts ) {
		if ( ! is_array( $atts ) ) { return null; }

		$has_any = ! empty( $atts['background_color'] )
			|| ! empty( $atts['background_image'] )
			|| ! empty( $atts['video'] )
			|| ! empty( $atts['bg_color'] );
		if ( ! $has_any ) { return null; }

		// Full default skeleton — mirrors background-pro's _get_defaults value shape
		// so every key the control/view reads is present.
		$val = array(
			'color'    => array( 'value' => array( 'predefined' => '', 'custom' => '' ) ),
			'gradient' => array( 'data' => array( 'type' => 'linear', 'angle' => 90, 'stops' => array() ) ),
			'image'    => array(
				'src'        => array(),
				'position'   => 'center center',
				'size'       => array( 'selected' => 'cover', 'custom' => '' ),
				'repeat'     => 'no-repeat',
				'attachment' => 'scroll',
			),
			'video'    => array(
				'enabled'      => 'no',
				'external_url' => '',
				'source_mp4'   => array(),
				'source_webm'  => array(),
				'poster'       => array(),
				'fallback'     => array(),
				'loop'         => 'yes',
				'autoplay'     => 'yes',
				'mute'         => 'yes',
				'playsinline'  => 'yes',
			),
			'advanced' => array(),
		);

		// --- COLOR --- legacy hex wins; else resolve the bg_color preset to a color.
		$color = '';
		if ( ! empty( $atts['background_color'] ) ) {
			$color = (string) $atts['background_color'];
		} elseif ( ! empty( $atts['bg_color'] ) ) {
			$bg = $atts['bg_color'];
			if ( is_array( $bg ) ) {
				$custom = isset( $bg['custom'] ) ? trim( (string) $bg['custom'] ) : '';
				$color  = ( $custom !== '' )
					? $custom
					: section_bg_preset_to_hex( isset( $bg['predefined'] ) ? (string) $bg['predefined'] : '' );
			} else {
				$color = section_bg_preset_to_hex( (string) $bg );
			}
		}
		if ( $color !== '' ) {
			$val['color']['value']['custom'] = $color;
		}

		// --- IMAGE --- legacy background_image['data']['icon'] is an absolute URL.
		if ( ! empty( $atts['background_image']['data']['icon'] ) ) {
			$val['image']['src'] = array( 'url' => (string) $atts['background_image']['data']['icon'] );
			// position/size/repeat/attachment keep the defaults above
			// (center center / cover / no-repeat / scroll) — matches the legacy
			// 'background-size:cover;background-position:center;' emission.
		}

		// --- VIDEO --- legacy `video` is a single URL string. Route by extension.
		if ( ! empty( $atts['video'] ) ) {
			$url = (string) $atts['video'];
			$ft  = function_exists( 'wp_check_filetype' ) ? wp_check_filetype( $url ) : array( 'ext' => '' );
			$ext = isset( $ft['ext'] ) ? strtolower( (string) $ft['ext'] ) : '';
			$val['video']['enabled'] = 'yes';
			if ( $ext === 'mp4' || $ext === 'm4v' ) {
				$val['video']['source_mp4'] = array( 'url' => $url );
			} elseif ( $ext === 'webm' ) {
				$val['video']['source_webm'] = array( 'url' => $url );
			} else {
				// YouTube / Vimeo / other oEmbed
				$val['video']['external_url'] = $url;
			}
		}

		return $val;
	}
endif;

if ( ! function_exists( 'section_migrate_min_height' ) ) :
	/**
	 * Normalize a `min_height` value to the multi-picker shape:
	 *   { preset: '' | '40vh' | '60vh' | '80vh' | '100vh' | 'custom',
	 *     custom: { custom_height: { value, unit } } }
	 *
	 * Old sections stored `min_height` as a plain string (e.g. '' or '40vh'),
	 * which makes the new multi-picker option throw when the modal renders — so
	 * convert it on load. Already-migrated array values pass through untouched.
	 *
	 * @param mixed $value
	 * @return array
	 */
	function section_migrate_min_height( $value ) {
		if ( is_array( $value ) ) {
			return $value; // already in the multi-picker shape
		}

		$v = trim( (string) $value );

		if ( $v === '' || $v === 'auto' ) {
			return array( 'preset' => 'auto' ); // Auto (fit content)
		}

		if ( in_array( $v, array( '40vh', '60vh', '80vh', '100vh' ), true ) ) {
			return array( 'preset' => $v ); // one of the presets
		}

		// Any other legacy value (e.g. "600px", "75vh") → Custom; split num + unit.
		if ( preg_match( '/^([0-9.]+)\s*([a-z%]+)$/i', $v, $m ) ) {
			$num  = $m[1];
			$unit = strtolower( $m[2] );
		} else {
			$num  = preg_replace( '/[^0-9.]/', '', $v );
			$unit = 'px';
		}

		return array(
			'preset' => 'custom',
			'custom' => array(
				'custom_height' => array( 'value' => $num, 'unit' => $unit ),
			),
		);
	}
endif;
