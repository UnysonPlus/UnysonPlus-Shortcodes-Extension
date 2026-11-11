<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Per-device image fields, shared by every image element (Carousel / Slider first;
 * Image, Before/After, Image Box … follow). One call adds the fields, one call
 * reads them back into the 'art' spec fw_image_tag() renders as a <picture>:
 *
 *   options.php:  $options += sc_responsive_image_fields( array( 'prefix' => 'img_' ) );
 *   view.php:     $art = sc_responsive_image_art( $atts, 'img_', $attachment_id, $main_ratio );
 *                 echo fw_image_tag( $attachment_id, array( 'art' => $art, … ) );
 *
 * The model is desktop-first: the element's own Image (and ratio, if it has one) is
 * the desktop picture, and these fields only override smaller screens, each of which
 * inherits the next larger one when left empty. That is why the device images use
 * the `responsive` option type with a device subset (Tablet + Phone) instead of
 * wrapping the main Image field: wrapping it would make the existing image the
 * *phone* image (that type is mobile-first) and change every saved slider.
 * See docs.unysonplus.com → Developers → Responsive images.
 */

if ( ! function_exists( 'sc_image_ratio_choices' ) ) :
	/**
	 * Crop shapes offered for per-device crops.
	 *
	 * @param bool $inherit Include '' = "Same as the larger screen" (per-device fields).
	 * @return array
	 */
	function sc_image_ratio_choices( $inherit = true ) {
		$c = $inherit ? array( '' => __( 'Same as the larger screen', 'fw' ) ) : array();
		return $c + array(
			'original' => __( 'Original shape (no crop)', 'fw' ),
			'16/9'     => __( 'Widescreen 16:9', 'fw' ),
			'3/2'      => __( 'Landscape 3:2', 'fw' ),
			'4/3'      => __( 'Landscape 4:3', 'fw' ),
			'1/1'      => __( 'Square 1:1', 'fw' ),
			'4/5'      => __( 'Portrait 4:5', 'fw' ),
			'3/4'      => __( 'Portrait 3:4', 'fw' ),
			'2/3'      => __( 'Portrait 2:3', 'fw' ),
			'9/16'     => __( 'Tall 9:16 (full phone screen)', 'fw' ),
		);
	}
endif;

if ( ! function_exists( 'sc_focal_point_choices' ) ) :
	/** The nine focal points (object-position values), as the Image element offers them. */
	function sc_focal_point_choices() {
		return array(
			'left top'      => __( 'Top Left', 'fw' ),
			'center top'    => __( 'Top', 'fw' ),
			'right top'     => __( 'Top Right', 'fw' ),
			'left center'   => __( 'Left', 'fw' ),
			'center center' => __( 'Center', 'fw' ),
			'right center'  => __( 'Right', 'fw' ),
			'left bottom'   => __( 'Bottom Left', 'fw' ),
			'center bottom' => __( 'Bottom', 'fw' ),
			'right bottom'  => __( 'Bottom Right', 'fw' ),
		);
	}
endif;

if ( ! function_exists( 'sc_responsive_image_fields' ) ) :
	/**
	 * The per-device image options, ready to merge into an options array.
	 *
	 * @param array $args {
	 *     @type string   $prefix  Option-id prefix, e.g. 'img_' → img_focal, img_crops, img_images.
	 *     @type string[] $fields  Which to include: 'focal', 'crops', 'images' (default all three).
	 *     @type string   $focal_label, $crops_label, $images_label  Optional label overrides.
	 * }
	 * @return array
	 */
	function sc_responsive_image_fields( $args = array() ) {
		$args = array_merge( array(
			'prefix' => 'img_',
			'fields' => array( 'focal', 'crops', 'images' ),
		), $args );
		$p   = (string) $args['prefix'];
		$out = array();

		if ( in_array( 'focal', $args['fields'], true ) ) {
			$out[ $p . 'focal' ] = array(
				'type'    => 'select',
				'label'   => isset( $args['focal_label'] ) ? $args['focal_label'] : __( 'Focal Point', 'fw' ),
				'desc'    => __( 'The part of the picture that must stay in view when it is cropped for tablets and phones.', 'fw' ),
				'value'   => 'center center',
				'choices' => sc_focal_point_choices(),
			);
		}
		if ( in_array( 'crops', $args['fields'], true ) ) {
			$out[ $p . 'crops' ] = array(
				'type'     => 'responsive',
				'label'    => isset( $args['crops_label'] ) ? $args['crops_label'] : __( 'Crop on Smaller Screens', 'fw' ),
				'desc'     => __( 'Crop the picture to a different shape per device, so a phone gets a sharp portrait file instead of a stretched landscape one. Use the device switcher; empty = same as the larger screen.', 'fw' ),
				'help'     => __( 'Large desktops always show the main picture. The crop keeps the Focal Point in view, and every device downloads a file made for its own size.', 'fw' ),
				'devices'  => array( 'lg', 'md', 'base' ),
				'off_note' => __( 'Large desktops show the main picture as it is.', 'fw' ),
				'value'    => array( 'base' => '', 'md' => '', 'lg' => '', 'xl' => '' ),
				'inner'    => array( 'type' => 'select', 'choices' => sc_image_ratio_choices( true ) ),
			);
		}
		if ( in_array( 'images', $args['fields'], true ) ) {
			$out[ $p . 'images' ] = array(
				'type'     => 'responsive',
				'label'    => isset( $args['images_label'] ) ? $args['images_label'] : __( 'Tablet & Phone Image', 'fw' ),
				'desc'     => __( 'Optional: a different picture for tablets or phones, for photos that do not crop well (a wide panorama, a subject at the edge). Empty = crop the larger screen\'s picture automatically.', 'fw' ),
				'devices'  => array( 'md', 'base' ),
				'off_note' => __( 'Desktop uses the main image above.', 'fw' ),
				'value'    => array( 'base' => '', 'md' => '', 'lg' => '', 'xl' => '' ),
				'inner'    => array( 'type' => 'upload', 'images_only' => true ),
			);
		}
		return $out;
	}
endif;

if ( ! function_exists( 'sc_responsive_image_art' ) ) :
	/**
	 * Read the fields back into fw_image_tag()'s 'art' spec.
	 *
	 * @param array  $atts       The element atts holding the fields (crops/images may
	 *                           also come from different arrays: pass $crops / $images).
	 * @param string $prefix     Same prefix as sc_responsive_image_fields().
	 * @param int    $main_id    The main (desktop) attachment id.
	 * @param string $main_ratio The main ratio ('' = none).
	 * @param array  $override   Optional 'focal' / 'crops' / 'images' values that win over $atts
	 *                           (e.g. slider-wide crops + per-slide images).
	 * @return array Empty when nothing differs per device.
	 */
	function sc_responsive_image_art( $atts, $prefix, $main_id, $main_ratio = '', $override = array() ) {
		if ( ! function_exists( 'fw_image_art_resolve' ) || (int) $main_id <= 0 ) {
			return array();
		}
		$get = function ( $k ) use ( $atts, $prefix, $override ) {
			if ( array_key_exists( $k, $override ) ) {
				return $override[ $k ];
			}
			return isset( $atts[ $prefix . $k ] ) ? $atts[ $prefix . $k ] : '';
		};
		$focal = (string) $get( 'focal' );
		return fw_image_art_resolve( (int) $main_id, (string) $main_ratio, '' !== $focal ? $focal : 'center center', $get( 'crops' ), $get( 'images' ) );
	}
endif;

if ( ! function_exists( 'sc_ratio_from_class' ) ) :
	/** A ratio class ('ratio-16-9', 'original') as a ratio string ('16/9', '' = uncropped). */
	function sc_ratio_from_class( $class ) {
		return preg_match( '/^ratio-(\d+)-(\d+)$/', (string) $class, $m ) ? $m[1] . '/' . $m[2] : '';
	}
endif;

if ( ! function_exists( 'sc_responsive_image_box_vars' ) ) :
	/**
	 * Per-device box shape, for elements whose image fills a box sized by CSS
	 * aspect-ratio (Before/After, Image Box …). Returns inline CSS variables
	 * "{$var}-lg / -md / -base: <ratio>" when some device's crop differs from the
	 * desktop ratio, else ''. The element's stylesheet applies them inside matching
	 * max-width media queries (1199.98 / 991.98 / 767.98px), e.g.
	 *   @media (max-width: 767.98px) { .el[style*="--x-ar-base"] .el__box { aspect-ratio: var(--x-ar-base); } }
	 * 'Original shape' resolves to that device's photo's own proportions.
	 *
	 * @param array  $atts
	 * @param string $prefix     Field prefix (sc_responsive_image_fields).
	 * @param int    $main_id    Main (desktop) attachment.
	 * @param string $main_ratio Desktop ratio ('' = original).
	 * @param string $var        CSS variable stem, e.g. '--bac-ar'.
	 * @return string
	 */
	function sc_responsive_image_box_vars( $atts, $prefix, $main_id, $main_ratio, $var ) {
		if ( (int) $main_id <= 0 ) {
			return '';
		}
		$art = sc_responsive_image_art( $atts, $prefix, (int) $main_id, (string) $main_ratio );
		$css = '';
		$any = false;
		foreach ( array( 'lg', 'md', 'base' ) as $k ) {
			$r   = isset( $art[ $k ]['ratio'] ) ? (string) $art[ $k ]['ratio'] : (string) $main_ratio;
			$src = isset( $art[ $k ]['source'] ) ? (int) $art[ $k ]['source'] : (int) $main_id;
			if ( $r !== (string) $main_ratio || $src !== (int) $main_id ) {
				$any = true;
			}
			if ( '' === $r || 'original' === $r ) {
				$m = wp_get_attachment_metadata( $src );
				$r = ( ! empty( $m['width'] ) && ! empty( $m['height'] ) ) ? (int) $m['width'] . '/' . (int) $m['height'] : 'auto';
			}
			$css .= $var . '-' . $k . ':' . str_replace( '/', ' / ', $r ) . ';';
		}
		return $any ? $css : '';
	}
endif;
