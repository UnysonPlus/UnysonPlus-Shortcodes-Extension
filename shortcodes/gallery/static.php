<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$shortcodes_extension = fw_ext( 'shortcodes' );
$g_version            = $shortcodes_extension->manifest->get_version();

/* Base styles (wrapper, item, captions, lightbox) + the lightbox script. These
   always load whenever a [gallery] is on the page — they cover the default Grid
   design and the shared lightbox used by EVERY design. The lightbox script has
   no dependencies and self-initialises (a delegated click handler), so it works
   regardless of load order. Enqueued from the source files directly (no .min is
   shipped); $g_version cache-busts on every plugin update. Per-design css/js are
   enqueued conditionally below. */
wp_enqueue_style(
	'fw-shortcode-gallery',
	$shortcodes_extension->get_declared_URI( '/shortcodes/gallery/static/css/styles.css' ),
	array( 'fw-ext-builder-frontend-grid' ),
	$g_version
);
wp_enqueue_script(
	'fw-shortcode-gallery',
	$shortcodes_extension->get_declared_URI( '/shortcodes/gallery/static/js/lightbox.js' ),
	array(),
	$g_version,
	true
);

/* ---------------------------------------------------------------------------
 * Per-design assets (registry-driven). A design's OWN css/js — and Splide for
 * the Carousel design — is enqueued only for instances that pick it, via the
 * per-instance `fw_ext_shortcodes_enqueue_static:gallery` action (which, unlike
 * the body of static.php, receives the instance atts).
 * ------------------------------------------------------------------------- */
if ( ! function_exists( '_fw_gallery_enqueue_design_static' ) ) :
	function _fw_gallery_enqueue_design_static( $data ) {
		$atts = shortcode_parse_atts( $data['atts_string'] );
		if ( ! is_array( $atts ) ) {
			return;
		}
		$post_id = ( isset( $data['post'] ) && isset( $data['post']->ID ) ) ? $data['post']->ID : 0;
		$atts    = fw_ext_shortcodes_decode_attr( $atts, 'gallery', $post_id );
		if ( is_wp_error( $atts ) || ! is_array( $atts ) ) {
			return;
		}

		$design = fw_akg( 'design_settings/design', $atts, null );
		if ( ! is_string( $design ) || $design === '' ) {
			$design = ( isset( $atts['design'] ) && is_string( $atts['design'] ) ) ? $atts['design'] : 'grid';
		}
		$registry = require dirname( __FILE__ ) . '/views/designs/registry.php';
		if ( ! isset( $registry[ $design ] ) ) {
			$design = 'grid';
		}

		$ext      = fw_ext( 'shortcodes' );
		$base     = '/shortcodes/gallery/static';
		$version  = $ext->manifest->get_version();
		$design_d = $registry[ $design ];

		$deps = array( 'fw-shortcode-gallery' );

		// Splide (vendored under the Carousel shortcode) for slider designs.
		if ( ! empty( $design_d['splide'] ) ) {
			wp_enqueue_style(
				'splide',
				$ext->get_declared_URI( '/shortcodes/carousel/static/vendor/splide-core.min.css' )
			);
			wp_enqueue_script(
				'splide',
				$ext->get_declared_URI( '/shortcodes/carousel/static/vendor/splide.min.js' ),
				array(),
				'4.1.4',
				true
			);
			$deps[] = 'splide';
		}

		if ( ! empty( $design_d['css'] ) ) {
			wp_enqueue_style(
				'fw-shortcode-gallery-' . $design,
				$ext->get_declared_URI( $base . '/css/designs/' . $design_d['css'] ),
				array( 'fw-shortcode-gallery' ),
				$version
			);
		}
		if ( ! empty( $design_d['js'] ) ) {
			wp_enqueue_script(
				'fw-shortcode-gallery-' . $design,
				$ext->get_declared_URI( $base . '/js/designs/' . $design_d['js'] ),
				$deps,
				$version,
				true
			);
		}
	}
	add_action( 'fw_ext_shortcodes_enqueue_static:gallery', '_fw_gallery_enqueue_design_static' );
endif;


/* ---------------------------------------------------------------------------
 * Shared rendering helpers (used by every design template).
 * ------------------------------------------------------------------------- */

if ( ! function_exists( 'sc_gallery_get_items' ) ) :
	/**
	 * Normalize the multi-upload `images` value into a flat list of render-ready
	 * items. Each saved entry is `{ attachment_id, url }`; we resolve real URLs,
	 * dimensions, alt/caption/title/description and the full-size source (for the
	 * lightbox) from the Media Library so output never depends on the stored url.
	 *
	 * @param array  $images The saved `images` att (array of {attachment_id,url}).
	 * @param string $size   Registered image size used for the on-page thumbnail.
	 * @return array[] List of items with keys: id, url, w, h, srcset, sizes,
	 *                 full, full_w, full_h, alt, caption, title, description.
	 */
	function sc_gallery_get_items( $images, $size = 'large' ) {
		$items = array();
		if ( ! is_array( $images ) ) {
			return $items;
		}

		foreach ( $images as $img ) {
			if ( ! is_array( $img ) ) {
				continue;
			}
			$id = isset( $img['attachment_id'] ) ? (int) $img['attachment_id'] : 0;

			$item = array(
				'id'          => $id,
				'url'         => '',
				'w'           => 0,
				'h'           => 0,
				'srcset'      => '',
				'sizes'       => '',
				'full'        => '',
				'full_w'      => 0,
				'full_h'      => 0,
				'alt'         => '',
				'caption'     => '',
				'title'       => '',
				'description' => '',
			);

			if ( $id ) {
				$src  = wp_get_attachment_image_src( $id, $size );
				$full = wp_get_attachment_image_src( $id, 'full' );
				if ( $src ) {
					$item['url'] = $src[0];
					$item['w']   = (int) $src[1];
					$item['h']   = (int) $src[2];
				} elseif ( ! empty( $img['url'] ) ) {
					$item['url'] = $img['url'];
				}
				$item['full']   = $full ? $full[0] : $item['url'];
				$item['full_w'] = $full ? (int) $full[1] : 0;
				$item['full_h'] = $full ? (int) $full[2] : 0;

				$item['srcset'] = (string) wp_get_attachment_image_srcset( $id, $size );
				$item['sizes']  = (string) wp_get_attachment_image_sizes( $id, $size );

				$item['alt'] = trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) );
				$post        = get_post( $id );
				if ( $post ) {
					$item['caption']     = (string) $post->post_excerpt;
					$item['title']       = (string) $post->post_title;
					$item['description'] = (string) $post->post_content;
				}
			} else {
				// No attachment id (e.g. an imported URL-only entry) — degrade gracefully.
				if ( empty( $img['url'] ) ) {
					continue;
				}
				$item['url']  = $img['url'];
				$item['full'] = $img['url'];
			}

			if ( $item['url'] === '' ) {
				continue;
			}
			$items[] = $item;
		}

		return $items;
	}
endif;

if ( ! function_exists( 'sc_gallery_caption_text' ) ) :
	/**
	 * Resolve the caption string for one item from the chosen source field.
	 * Returns '' when the source field is empty.
	 */
	function sc_gallery_caption_text( $item, $source ) {
		switch ( $source ) {
			case 'title':       $val = $item['title']; break;
			case 'alt':         $val = $item['alt']; break;
			case 'description': $val = $item['description']; break;
			case 'caption':
			default:            $val = $item['caption']; break;
		}
		return trim( (string) $val );
	}
endif;

if ( ! function_exists( 'sc_gallery_img_html' ) ) :
	/**
	 * Build the responsive <img> for one item. Alt falls back to the caption /
	 * title only for accessibility (never the URL). Always lazy + async.
	 */
	function sc_gallery_img_html( $item, $args = array() ) {
		$alt = $item['alt'];
		if ( $alt === '' ) {
			$alt = $item['title'] !== '' ? $item['title'] : $item['caption'];
		}

		$attr = array(
			'src'      => esc_url( $item['url'] ),
			'alt'      => $alt,
			'loading'  => 'lazy',
			'decoding' => 'async',
			'class'    => 'fw-gallery__img',
		);
		if ( ! empty( $item['w'] ) ) { $attr['width']  = $item['w']; }
		if ( ! empty( $item['h'] ) ) { $attr['height'] = $item['h']; }
		if ( $item['srcset'] !== '' ) { $attr['srcset'] = $item['srcset']; }
		if ( $item['sizes'] !== '' )  { $attr['sizes']  = $item['sizes']; }

		return fw_html_tag( 'img', $attr );
	}
endif;

if ( ! function_exists( 'sc_gallery_render_tile' ) ) :
	/**
	 * Render one gallery item as a <figure> (the unit every grid-like design
	 * reuses). The image is wrapped in the appropriate click target (lightbox
	 * anchor / file / attachment / none) and an optional caption is emitted as a
	 * hover overlay or a below-image figcaption.
	 *
	 * @param array $item One entry from sc_gallery_get_items().
	 * @param array $args {
	 *     click_action   : lightbox|file|attachment|none
	 *     group          : lightbox group id (data-fw-lightbox value)
	 *     captions       : none|hover|below
	 *     caption_source : caption|title|alt|description
	 *     rounded        : a corner-radius class (rounded, rounded-lg, …)
	 *     hover_zoom     : bool
	 *     caption_class  : extra class(es) for the caption element
	 *     caption_style  : inline style for the caption element
	 *     item_class     : extra class(es) for the outer <figure>
	 *     item_style     : inline style for the outer <figure>
	 *     media_class    : extra class(es) for the media wrapper
	 * }
	 * @return string
	 */
	function sc_gallery_render_tile( $item, $args = array() ) {
		$a = array_merge( array(
			'click_action'   => 'lightbox',
			'group'          => '',
			'captions'       => 'none',
			'caption_source' => 'caption',
			'rounded'        => 'rounded',
			'hover_zoom'     => true,
			'caption_class'  => '',
			'caption_style'  => '',
			'item_class'     => '',
			'item_style'     => '',
			'media_class'    => '',
			'box_style'      => '', // a `boxp-{slug}` Box Preset class for the card <figure>
			'image_style'    => '', // an `imgs-{slug}` Image Style class for the media wrapper
			'item_hover'     => array(), // engine Hover attr array to stamp on each card <figure>
		), $args );

		// Box Style → the card <figure> (validated to a boxp-{slug} class).
		$box_style   = ( is_string( $a['box_style'] ) && preg_match( '/^boxp-[a-z0-9_-]+$/i', $a['box_style'] ) ) ? $a['box_style'] : '';

		// Image Style → the `.fw-gallery__media` wrapper doubles as `.imgs-wrap` so the
		// preset's crop / mask / filter / scrim apply to each tile image (the base rule
		// targets the inner <img>; scrim/duotone layers overlay the media, pointer-events:none).
		$image_style = ( is_string( $a['image_style'] ) && preg_match( '/^imgs-[a-z0-9_-]+$/i', $a['image_style'] ) ) ? $a['image_style'] : '';

		// Per-card Hover Interaction → the card <figure>. The engine already escaped every value
		// (class / data-hover* / style) in upw_hover_apply_instances, so split them out here: class
		// merges into the figure class, style merges into the figure style, the rest are attributes.
		$ih        = ( isset( $a['item_hover'] ) && is_array( $a['item_hover'] ) ) ? $a['item_hover'] : array();
		$ih_class  = isset( $ih['class'] ) ? (string) $ih['class'] : '';
		$ih_style  = isset( $ih['style'] ) ? (string) $ih['style'] : '';
		$ih_attrs  = '';
		foreach ( $ih as $ih_k => $ih_v ) {
			if ( $ih_k === 'class' || $ih_k === 'style' || ! is_scalar( $ih_v ) ) { continue; }
			$ih_attrs .= ' ' . $ih_k . '="' . $ih_v . '"'; // value pre-escaped by the engine
		}

		$caption    = sc_gallery_caption_text( $item, $a['caption_source'] );
		$has_overlay = ( $a['captions'] === 'hover' && $caption !== '' );
		$has_below   = ( $a['captions'] === 'below' && $caption !== '' );

		$media_classes = trim(
			'fw-gallery__media '
			. ( $image_style !== '' ? 'imgs-wrap ' . $image_style . ' ' : '' )
			// Legacy corner-radius class — applied ONLY when no Image Style is set, so the
			// preset (when chosen) fully owns the shape and the two never fight.
			. ( $image_style === '' && $a['rounded'] !== '' ? $a['rounded'] . ' ' : '' )
			. ( $a['hover_zoom'] ? 'fw-gallery--zoom ' : '' )
			. ( $has_overlay ? 'fw-gallery--has-overlay ' : '' )
			. $a['media_class']
		);

		$img = sc_gallery_img_html( $item, $a );

		// Inner media (link target + image + optional hover overlay).
		$inner  = $img;
		if ( $has_overlay ) {
			$inner .= '<figcaption class="fw-gallery__overlay"><span class="fw-gallery__overlay-text'
				. ( $a['caption_class'] !== '' ? ' ' . esc_attr( $a['caption_class'] ) : '' ) . '"'
				. ( $a['caption_style'] !== '' ? ' style="' . esc_attr( $a['caption_style'] ) . '"' : '' )
				. '>' . esc_html( $caption ) . '</span></figcaption>';
		}

		switch ( $a['click_action'] ) {
			case 'lightbox':
				$open = '<a class="' . esc_attr( $media_classes ) . '" href="' . esc_url( $item['full'] ) . '"'
					. ' data-fw-lightbox="' . esc_attr( $a['group'] ) . '"'
					. ( $caption !== '' ? ' data-fw-caption="' . esc_attr( $caption ) . '"' : '' )
					. '>';
				$close = '</a>';
				break;
			case 'file':
				$open  = '<a class="' . esc_attr( $media_classes ) . '" href="' . esc_url( $item['full'] ) . '" target="_blank" rel="noopener noreferrer">';
				$close = '</a>';
				break;
			case 'attachment':
				$link  = $item['id'] ? get_attachment_link( $item['id'] ) : $item['full'];
				$open  = '<a class="' . esc_attr( $media_classes ) . '" href="' . esc_url( $link ) . '">';
				$close = '</a>';
				break;
			case 'none':
			default:
				$open  = '<span class="' . esc_attr( $media_classes ) . '">';
				$close = '</span>';
				break;
		}

		$figure_class = preg_replace( '/\s+/', ' ', trim( 'fw-gallery__item ' . ( $box_style !== '' ? $box_style . ' ' : '' ) . $a['item_class'] . ( $ih_class !== '' ? ' ' . $ih_class : '' ) ) );
		$fig_style    = trim( (string) $a['item_style'] );
		if ( $ih_style !== '' ) { $fig_style = ( $fig_style === '' ? '' : rtrim( $fig_style, '; ' ) . '; ' ) . $ih_style; }
		$out  = '<figure class="' . esc_attr( $figure_class ) . '"'
			. ( $fig_style !== '' ? ' style="' . esc_attr( $fig_style ) . '"' : '' )
			. $ih_attrs . '>';
		$out .= $open . $inner . $close;
		if ( $has_below ) {
			$out .= '<figcaption class="fw-gallery__caption'
				. ( $a['caption_class'] !== '' ? ' ' . esc_attr( $a['caption_class'] ) : '' ) . '"'
				. ( $a['caption_style'] !== '' ? ' style="' . esc_attr( $a['caption_style'] ) . '"' : '' )
				. '>' . esc_html( $caption ) . '</figcaption>';
		}
		$out .= '</figure>';

		return $out;
	}
endif;

if ( ! function_exists( 'sc_gallery_gap_css' ) ) :
	/**
	 * Resolve a Gap-Scale slug (e.g. "3") to a CSS length for the layout `gap`.
	 * Returns `var(--gap-<slug>, <fallback>)` so it stays live with the site's
	 * Spacing → Gap Scale presets (css-tokens.php emits the `--gap-*` tokens).
	 * Empty slug (the "None" choice) → 0.
	 */
	function sc_gallery_gap_css( $slug, $fallback = '1rem' ) {
		$slug = preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) $slug ) );
		if ( $slug === '' ) {
			return '0px';
		}
		return 'var(--gap-' . $slug . ', ' . $fallback . ')';
	}
endif;

if ( ! function_exists( 'sc_gallery_gap_size' ) ) :
	/**
	 * Like sc_gallery_gap_css() but returns the CONCRETE size string (e.g. "1rem")
	 * from the live Gap Scale — for places that need a real length, not a CSS var
	 * (e.g. Splide's JS `gap` option in the Carousel design).
	 */
	function sc_gallery_gap_size( $slug, $fallback = '1rem' ) {
		$slug = preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) $slug ) );
		if ( $slug === '' ) {
			return '0px';
		}
		if ( function_exists( 'unysonplus_get_gap_scale' ) ) {
			foreach ( unysonplus_get_gap_scale() as $e ) {
				if ( ! is_array( $e ) || ! isset( $e['name'], $e['size'] ) ) {
					continue;
				}
				$s = strtolower( preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $e['name'] ) );
				if ( $s === $slug && (string) $e['size'] !== '' ) {
					return (string) $e['size'];
				}
			}
		}
		return $fallback;
	}
endif;

if ( ! function_exists( 'sc_gallery_ratio_css' ) ) :
	/**
	 * Map a saved ratio key (e.g. '4-3') to a CSS aspect-ratio value ('4 / 3').
	 * 'original' (or unknown) returns '' so the caller can skip the property.
	 */
	function sc_gallery_ratio_css( $ratio ) {
		$map = array(
			'1-1'  => '1 / 1',
			'4-3'  => '4 / 3',
			'3-2'  => '3 / 2',
			'16-9' => '16 / 9',
			'3-4'  => '3 / 4',
			'2-1'  => '2 / 1',
			'21-9' => '21 / 9',
			'3-1'  => '3 / 1',
			'4-1'  => '4 / 1',
		);
		return isset( $map[ $ratio ] ) ? $map[ $ratio ] : '';
	}
endif;
