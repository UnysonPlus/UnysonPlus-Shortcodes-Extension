<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Shared image-mask shape library — the single source of truth for image masking,
 * consumed by BOTH the Image Style component preset (Theme Settings → Components →
 * Image Styles) and the Image Box shortcode. Add a shape here and both get it.
 *
 * Each entry: label, plus a `kind` + `value` that says how the shape is produced:
 *   - 'none'   : no mask.
 *   - 'radius' : a CSS border-radius value (rounded / circle / squircle / arch / leaf).
 *   - 'clip'   : a CSS clip-path polygon (hexagon / star / …).
 *   - 'svg'    : a CSS mask-image url() (organic shapes — inline data-URI, CSP-safe,
 *                or a hosted .svg for the couple that are file-based).
 *   - 'custom' : the user supplies their own SVG / clip-path.
 * `square => true` means the shape needs a 1:1 crop (the consumer forces aspect-ratio).
 *
 * Kept identical to the Image Box CSS resolution (its `.imgbox--mask-{key}` rules) so
 * the two render the same shape.
 */
if ( ! function_exists( 'sc_image_mask_library' ) ) :
	function sc_image_mask_library() {
		static $lib = null;
		if ( $lib !== null ) {
			return $lib;
		}
		// The two file-based organic masks live with the Image Box assets (always bundled).
		$mbase = ( function_exists( 'fw_ext' ) && fw_ext( 'shortcodes' ) )
			? fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/image-box/static/img/mask' )
			: '';

		$lib = array(
			'none'       => array( 'label' => __( 'None', 'fw' ),        'kind' => 'none' ),
			'rounded'    => array( 'label' => __( 'Rounded', 'fw' ),     'kind' => 'radius', 'value' => '14px' ),
			'rounded-xl' => array( 'label' => __( 'Rounded XL', 'fw' ),  'kind' => 'radius', 'value' => '28px' ),
			'circle'     => array( 'label' => __( 'Circle', 'fw' ),      'kind' => 'radius', 'value' => '50%',   'square' => true ),
			'squircle'   => array( 'label' => __( 'Squircle', 'fw' ),    'kind' => 'radius', 'value' => '28%',   'square' => true ),
			'arch'       => array( 'label' => __( 'Arch', 'fw' ),        'kind' => 'radius', 'value' => '50% 50% 8px 8px / 62% 62% 8px 8px' ),
			'leaf'       => array( 'label' => __( 'Leaf', 'fw' ),        'kind' => 'radius', 'value' => '0 50% 0 50% / 0 50% 0 50%', 'square' => true ),
			'diagonal'   => array( 'label' => __( 'Diagonal cut', 'fw' ), 'kind' => 'clip', 'value' => 'polygon(0 0,100% 0,100% 85%,0 100%)' ),
			'hexagon'    => array( 'label' => __( 'Hexagon', 'fw' ),     'kind' => 'clip', 'value' => 'polygon(50% 0,100% 25%,100% 75%,50% 100%,0 75%,0 25%)', 'square' => true ),
			'diamond'    => array( 'label' => __( 'Diamond', 'fw' ),     'kind' => 'clip', 'value' => 'polygon(50% 0,100% 50%,50% 100%,0 50%)', 'square' => true ),
			'triangle'   => array( 'label' => __( 'Triangle', 'fw' ),    'kind' => 'clip', 'value' => 'polygon(50% 2%,100% 100%,0 100%)', 'square' => true ),
			'pentagon'   => array( 'label' => __( 'Pentagon', 'fw' ),    'kind' => 'clip', 'value' => 'polygon(50% 0,100% 38%,82% 100%,18% 100%,0 38%)', 'square' => true ),
			'star'       => array( 'label' => __( 'Star', 'fw' ),        'kind' => 'clip', 'value' => 'polygon(50% 0,61% 35%,98% 35%,68% 57%,79% 91%,50% 70%,21% 91%,32% 57%,2% 35%,39% 35%)', 'square' => true ),
			'chevron'    => array( 'label' => __( 'Chevron', 'fw' ),     'kind' => 'clip', 'value' => 'polygon(0 0,75% 0,100% 50%,75% 100%,0 100%,25% 50%)', 'square' => true ),
			'octagon'    => array( 'label' => __( 'Octagon', 'fw' ),     'kind' => 'clip', 'value' => 'polygon(30% 0,70% 0,100% 30%,100% 70%,70% 100%,30% 100%,0 70%,0 30%)', 'square' => true ),
			'shield'     => array( 'label' => __( 'Shield', 'fw' ),      'kind' => 'clip', 'value' => 'polygon(0 0,100% 0,100% 62%,50% 100%,0 62%)', 'square' => true ),
			'heart'      => array( 'label' => __( 'Heart', 'fw' ),  'kind' => 'svg', 'square' => true, 'value' => "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 29.6'%3E%3Cpath d='M16 29.6l-2.3-2.1C5.4 19.9 0 15 0 9 0 4 4 0 9 0c2.8 0 5.5 1.3 7 3.4C17.5 1.3 20.2 0 23 0c5 0 9 4 9 9 0 6-5.4 10.9-13.7 18.5z'/%3E%3C/svg%3E\")" ),
			'blob-1'     => array( 'label' => __( 'Blob 1', 'fw' ), 'kind' => 'svg', 'square' => true, 'value' => "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cpath d='M54 8c20-4 38 14 36 36 0 18 8 30-4 42S48 94 32 88C14 81 4 66 6 46 8 28 20 14 36 10c6-2 12-1 18-2z'/%3E%3C/svg%3E\")" ),
			'blob-2'     => array( 'label' => __( 'Blob 2', 'fw' ), 'kind' => 'svg', 'square' => true, 'value' => "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cpath d='M50 6c18 4 28 14 34 30s4 34-10 46-34 10-48 2S8 58 12 42 24 14 38 9c4-2 8-4 12-3z'/%3E%3C/svg%3E\")" ),
			'flower'     => array( 'label' => __( 'Flower', 'fw' ), 'kind' => 'svg', 'square' => true, 'value' => "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cpath d='M50 12C56 12 60 18 60 26C68 20 78 22 82 30C86 38 82 46 74 50C82 54 86 62 82 70C78 78 68 80 60 74C60 82 56 88 50 88C44 88 40 82 40 74C32 80 22 78 18 70C14 62 18 54 26 50C18 46 14 38 18 30C22 22 32 20 40 26C40 18 44 12 50 12Z'/%3E%3C/svg%3E\")" ),
			'brush'      => array( 'label' => __( 'Brush', 'fw' ),  'kind' => 'svg', 'value' => "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cpath d='M6 30C20 24 32 34 46 30C60 26 74 22 94 28L92 62C78 70 62 60 48 64C34 68 20 74 8 66Z'/%3E%3C/svg%3E\")" ),
			'water-splash' => array( 'label' => __( 'Water Splash', 'fw' ), 'kind' => 'svg', 'value' => $mbase !== '' ? 'url("' . $mbase . '/src-water-splash.svg")' : '' ),
			'grunge-frame' => array( 'label' => __( 'Grunge Frame', 'fw' ), 'kind' => 'svg', 'value' => $mbase !== '' ? 'url("' . $mbase . '/src-grunge-frame.svg")' : '' ),
			'custom'     => array( 'label' => __( 'Custom', 'fw' ), 'kind' => 'custom' ),
		);
		return $lib;
	}
endif;

if ( ! function_exists( 'sc_image_mask_choices' ) ) :
	/** [ key => label ] for a select (or image-picker). Includes None + Custom. */
	function sc_image_mask_choices() {
		$out = array();
		foreach ( sc_image_mask_library() as $k => $def ) {
			$out[ $k ] = $def['label'];
		}
		return $out;
	}
endif;

if ( ! function_exists( 'sc_image_mask_svg_uri' ) ) :
	/** The picker-thumbnail SVG URI for a shape (shared Image Box asset dir). */
	function sc_image_mask_svg_uri( $key ) {
		if ( ! function_exists( 'fw_ext' ) || ! fw_ext( 'shortcodes' ) ) {
			return '';
		}
		$key = preg_replace( '/[^a-z0-9_-]/', '', (string) $key );
		return fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/image-box/static/img/mask/' . $key . '.svg' );
	}
endif;

if ( ! function_exists( 'sc_image_mask_imagepicker_choices' ) ) :
	/**
	 * image-picker choices for the mask control — each shape as a thumbnail tile
	 * (the shared mask SVGs). Same shape as the Image Box mask picker, so both render
	 * an identical visual grid.
	 */
	function sc_image_mask_imagepicker_choices() {
		$out = array();
		foreach ( sc_image_mask_library() as $k => $def ) {
			$out[ $k ] = array(
				'small' => array( 'src' => sc_image_mask_svg_uri( $k ), 'height' => 57, 'title' => $def['label'] ),
				'label' => $def['label'],
			);
		}
		return $out;
	}
endif;
