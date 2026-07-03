<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Image Box — design resolver (shared by view.php and static.php).
 *
 * Collapses the popover multi-picker value
 *   design_settings => [ 'family' => 'overlay', 'overlay' => [ … ] ]
 * (or a legacy flat scalar `design="overlay-slide"`) into ONE flat design key
 * plus the render metadata (part + content-over / hover-reveal flags). Kept in
 * its own file so both the frontend render and the per-design CSS enqueue read
 * the exact same logic without re-executing view.php.
 */

if ( ! function_exists( 'sc_imgbox_family_to_key' ) ) {
	/**
	 * Map a family + its variation values to an existing flat design key.
	 *
	 * @param string $family One of stacked|side|overlay|card|frame.
	 * @param array  $sub    The family's saved sub-option values.
	 * @param array  $reg    The registry array.
	 * @return string A flat key that exists in $reg['designs'].
	 */
	function sc_imgbox_family_to_key( $family, $sub, $reg ) {
		$sub = is_array( $sub ) ? $sub : array();
		$get = function ( $k, $d = '' ) use ( $sub ) {
			return isset( $sub[ $k ] ) ? $sub[ $k ] : $d;
		};

		switch ( $family ) {
			case 'stacked':
				// Alignment → Content Alignment (universal); image size / shape →
				// universal Image Size / Image Mask. Stacking order is a separate
				// class axis (not part of the flat key). So Stacked is one key.
				return 'stacked';

			case 'side':
				if ( in_array( $get( 'panel', 'no' ), array( 'yes', '1', 1, true ), true ) ) {
					return 'split-panel';
				}
				// Round image → universal Image Mask = Circle (not a design key).
				return $get( 'image_side', 'left' ) === 'right' ? 'side-right' : 'side-left';

			case 'overlay':
				$map = array(
					'scrim'   => 'overlay-scrim',
					'fade'    => 'overlay-fade',
					'slide'   => 'overlay-slide',
					'center'  => 'overlay-center',
					'frame'   => 'overlay-frame',
					'bar'     => 'caption-bar',
					'cover'   => 'editorial-cover',
					'overlap' => 'overlay-offset',
				);
				$reveal = $get( 'reveal', 'scrim' );
				// A reveal value may itself already be a flat design key (new
				// magazine looks registered directly in `designs`).
				if ( isset( $map[ $reveal ] ) ) {
					return $map[ $reveal ];
				}
				if ( isset( $reg['designs'][ $reveal ] ) ) {
					return $reveal;
				}
				return 'overlay-scrim';

			case 'card':
				return $get( 'style', 'card' ) === 'caption-below' ? 'caption-below' : 'card';

			case 'frame':
				$style = $get( 'style', 'polaroid' );
				return in_array( $style, array( 'polaroid', 'postcard', 'badge', 'photo-stack' ), true ) ? $style : 'polaroid';
		}

		return 'stacked';
	}
}

if ( ! function_exists( 'sc_imgbox_resolve_design' ) ) {
	/**
	 * Resolve an instance's atts to its flat design.
	 *
	 * @param array $atts Decoded shortcode atts.
	 * @param array $reg  The registry array.
	 * @return array { key, part, family, sub, content_over, hover_reveal }
	 */
	function sc_imgbox_resolve_design( $atts, $reg ) {
		$designs = isset( $reg['designs'] ) && is_array( $reg['designs'] ) ? $reg['designs'] : array();

		$family = null;
		if ( function_exists( 'fw_akg' ) ) {
			$family = fw_akg( 'design_settings/family', $atts, null );
		} elseif ( isset( $atts['design_settings']['family'] ) ) {
			$family = $atts['design_settings']['family'];
		}

		$sub = array();
		$key = 'stacked';

		if ( is_string( $family ) && $family !== '' ) {
			$sub = function_exists( 'fw_akg' )
				? fw_akg( 'design_settings/' . $family, $atts, array() )
				: ( isset( $atts['design_settings'][ $family ] ) ? $atts['design_settings'][ $family ] : array() );
			$key = sc_imgbox_family_to_key( $family, $sub, $reg );
		} else {
			// Legacy: a bare `design` scalar (old saves / items-corrector).
			$legacy = isset( $atts['design'] ) && is_string( $atts['design'] ) ? $atts['design'] : '';
			$key    = ( $legacy !== '' && isset( $designs[ $legacy ] ) ) ? $legacy : 'stacked';
			if ( isset( $reg['legacy'][ $key ]['family'] ) ) {
				$family = $reg['legacy'][ $key ]['family'];
			}
		}

		if ( ! isset( $designs[ $key ] ) ) {
			$key = 'stacked';
		}
		$meta = isset( $designs[ $key ] ) ? $designs[ $key ] : array();

		return array(
			'key'          => $key,
			'part'         => isset( $meta['part'] ) ? $meta['part'] : 'stacked',
			'family'       => is_string( $family ) ? $family : 'stacked',
			'sub'          => is_array( $sub ) ? $sub : array(),
			'content_over' => ! empty( $meta['content_over_image'] ),
			'hover_reveal' => ! empty( $meta['hover_reveal'] ),
		);
	}
}
