<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */

if ( ! function_exists( 'fw_carousel_color' ) ) {
	/** A preset/custom colour value → [ class, style-decls ] for text. */
	function fw_carousel_color( $value ) {
		if ( is_string( $value ) && $value !== '' && preg_match( '/^(#|rgb)/i', trim( $value ) ) ) {
			$value = array( 'predefined' => '', 'custom' => trim( $value ) );
		}
		if ( function_exists( 'sc_normalize_color_value' ) ) {
			$c = sc_normalize_color_value( $value, 'text' );
			return array( ! empty( $c['class'] ) ? $c['class'] : '', ! empty( $c['style'] ) ? $c['style'] . ';' : '' );
		}
		return array( '', '' );
	}
}

$slides = isset( $atts['slides'] ) && is_array( $atts['slides'] ) ? $atts['slides'] : array();
if ( empty( $slides ) ) {
	return; // nothing to render
}

$yes = function ( $k, $default = true ) use ( $atts ) {
	if ( ! isset( $atts[ $k ] ) ) {
		return $default;
	}
	return $atts[ $k ] !== 'no';
};

$per         = max( 1, (int) ( $atts['per_page'] ?? 1 ) );
$per_tablet  = max( 1, (int) ( $atts['per_page_tablet'] ?? 2 ) );
$per_mobile  = max( 1, (int) ( $atts['per_page_mobile'] ?? 1 ) );
$gap         = isset( $atts['gap'] ) ? trim( (string) $atts['gap'] ) : '';
$height      = isset( $atts['height'] ) ? trim( (string) $atts['height'] ) : '';
$effect      = ( isset( $atts['effect'] ) && $atts['effect'] === 'fade' ) ? 'fade' : 'slide';
$loop        = $yes( 'loop' );
$autoplay    = $yes( 'autoplay' );
$interval    = max( 0, (int) ( $atts['interval'] ?? 5000 ) );
$speed       = max( 0, (int) ( $atts['speed'] ?? 600 ) );
$slow_zoom   = $yes( 'slow_zoom', false );

// "Use as Section Background": the shared sc-bg-fill runtime stretches the carousel over its
// Section and lifts the Section's content on top. A backdrop shows one slide at a time, plays
// on its own and takes no input (sc-bg-fill sets pointer-events:none), so per-view counts,
// gaps, a fixed height and the arrows / dots / drag do not apply.
$as_bg = function_exists( 'sc_section_background_is_on' )
	? sc_section_background_is_on( $atts['as_background'] ?? 'no' )
	: ( ( $atts['as_background'] ?? 'no' ) === 'yes' );
if ( $as_bg ) {
	$per = $per_tablet = $per_mobile = 1;
	$gap    = '';
	$height = '';
}

// Splide type: fade overrides (1 per view); else loop/slide. rewind when not looping.
if ( $effect === 'fade' ) {
	$type = 'fade';
	$per  = 1;
} else {
	$type = $loop ? 'loop' : 'slide';
}

$config = array(
	'type'         => $type,
	'perPage'      => $per,
	'perMove'      => 1,
	'arrows'       => $as_bg ? false : $yes( 'arrows' ),
	'pagination'   => $as_bg ? false : $yes( 'pagination' ),
	'drag'         => $as_bg ? false : $yes( 'drag' ),
	'speed'        => $speed,
	// Splide's fade type cannot "loop", so Loop means rewind to the first slide there.
	'rewind'       => ( $type === 'fade' ) ? $loop : ( ! $loop ),
	'autoplay'     => $autoplay,
	'interval'     => $interval,
	'pauseOnHover' => $as_bg ? false : $yes( 'pause_hover' ),
	'pauseOnFocus' => true,
	'lazyLoad'     => 'nearby', // background-slide images after the first (see the slide markup)
	'preloadPages' => 1,
);
if ( $gap !== '' ) {
	$config['gap'] = $gap;
}
if ( $as_bg ) {
	$config['height'] = '100%';
}
if ( $height !== '' ) {
	$config['height']      = $height;
	$config['fixedHeight'] = $height;
	// NOTE: do NOT set Splide's `cover` — the slide already renders its own
	// `.fw-carousel__bg` (position:absolute; object-fit:cover) image, which is
	// reliable. `cover` would also try to convert that <img> to a slide
	// background and can leave blank slides depending on markup.
}
if ( $per > 1 || $per_tablet !== $per || $per_mobile !== $per ) {
	$config['breakpoints'] = array(
		992 => array( 'perPage' => min( $per_tablet, $per ) === 0 ? 1 : $per_tablet ),
		576 => array( 'perPage' => $per_mobile ),
	);
}

$overlay         = $yes( 'overlay' );
$overlay_opacity = isset( $atts['overlay_opacity'] ) ? max( 0, min( 90, (int) $atts['overlay_opacity'] ) ) : 45;

list( $h_class, $h_style ) = fw_carousel_color( $atts['heading_color'] ?? '' );
list( $t_class, $t_style ) = fw_carousel_color( $atts['text_color'] ?? '' );

// Advanced.
$css_id    = ! empty( $atts['css_id'] ) ? $atts['css_id'] : '';
$css_class = ! empty( $atts['css_class'] ) ? $atts['css_class'] : '';
$hide_keys = array_keys( array_filter( (array) ( $atts['responsive_hide'] ?? array() ) ) );

$wrap_classes = array_merge( array( 'fw-carousel' ), $css_class !== '' ? array( $css_class ) : array(), $hide_keys );
if ( $as_bg ) {
	$wrap_classes[] = 'sc-bg-fill';
	$wrap_classes[] = 'fw-carousel--bg';
	if ( function_exists( 'sc_section_background_use' ) ) {
		sc_section_background_use();
	}
}
if ( $slow_zoom ) {
	$wrap_classes[] = 'fw-carousel--zoom';
}
// The zoom lasts as long as a slide stays on screen.
$zoom_style = $slow_zoom ? '--fwc-zoom-dur:' . ( ( $autoplay ? $interval : 8000 ) + $speed ) . 'ms;' : '';

$align_class = function ( $a ) {
	$a = in_array( $a, array( 'left', 'center', 'right' ), true ) ? $a : 'center';
	return 'fw-carousel__content--' . $a;
};
?>
<div<?php echo $css_id !== '' ? ' id="' . esc_attr( $css_id ) . '"' : ''; ?> class="<?php echo esc_attr( implode( ' ', array_unique( $wrap_classes ) ) ); ?>"<?php echo $as_bg ? ' aria-hidden="true"' : ''; ?><?php echo ( function_exists( 'sc_position_style' ) && ( $sc_pos = sc_position_style( $atts ) ) !== '' ) ? ' style="' . esc_attr( $sc_pos ) . '"' : ''; ?>>
	<div class="splide" data-splide="<?php echo esc_attr( wp_json_encode( $config ) ); ?>"<?php echo ( $height !== '' || $zoom_style !== '' ) ? ' style="' . esc_attr( ( $height !== '' ? '--fwc-h:' . $height . ';' : '' ) . $zoom_style ) . '"' : ''; ?>>
		<div class="splide__track">
			<div class="splide__list">
				<?php foreach ( $slides as $slide_i => $s ) :
					$img      = ( isset( $s['image']['url'] ) && $s['image']['url'] !== '' ) ? $s['image']['url'] : '';
					$mode     = ( isset( $s['image_mode'] ) && $s['image_mode'] === 'inline' ) ? 'inline' : 'background';
					$heading  = isset( $s['heading'] ) ? trim( (string) $s['heading'] ) : '';
					// Prefer the attachment's own alt text; fall back to the heading only
					// for inline images. Background images sit behind the (visible) heading,
					// so they use the attachment alt or "" to avoid duplicating it for AT.
					$att_id   = isset( $s['image']['attachment_id'] ) ? (int) $s['image']['attachment_id'] : 0;
					$att_alt  = $att_id ? trim( (string) get_post_meta( $att_id, '_wp_attachment_image_alt', true ) ) : '';
					$img_alt  = $att_alt !== '' ? $att_alt : $heading;
					$text     = isset( $s['text'] ) ? trim( (string) $s['text'] ) : '';
					$btn_lbl  = isset( $s['button_label'] ) ? trim( (string) $s['button_label'] ) : '';
					$btn_link = isset( $s['button_link'] ) ? trim( (string) $s['button_link'] ) : '#';
					$link     = isset( $s['link'] ) ? trim( (string) $s['link'] ) : '';
					$align    = isset( $s['content_align'] ) ? $s['content_align'] : 'center';
					$is_bg    = ( $mode === 'background' && $img !== '' );
					$has_content = ( $heading !== '' || $text !== '' || $btn_lbl !== '' );
					$slide_cls = 'splide__slide fw-carousel__slide' . ( $is_bg ? ' fw-carousel__slide--bg' : '' );
					// Per-device picture: slider-wide crops (Layout tab) + this slide's focal point
					// and optional tablet / phone image. Empty when nothing differs per device.
					$slide_art = ( $att_id && function_exists( 'sc_responsive_image_art' ) )
						? sc_responsive_image_art( $s, 'img_', $att_id, '', array( 'crops' => isset( $atts['img_crops'] ) ? $atts['img_crops'] : array() ) )
						: array();
					?>
					<div class="<?php echo esc_attr( $slide_cls ); ?>">
						<?php if ( $is_bg ) : ?>
							<?php
							// Responsive sizes from the Media Library, so a phone fetches a phone-sized file.
							$bg_srcset = $att_id ? (string) wp_get_attachment_image_srcset( $att_id, 'full' ) : '';
							$bg_sizes  = ( $as_bg || $per <= 1 ) ? '100vw' : '(max-width: 576px) 100vw, ' . round( 100 / $per ) . 'vw';
							if ( $att_id && function_exists( 'fw_image_tag' ) ) :
								// Shared renderer: per-device crops / images become a <picture> (one source
								// per breakpoint, each a set of real pre-cropped files), so a phone gets a
								// sharp portrait crop instead of a stretched landscape file. The first
								// slide loads now with preload hints per device (often the page's LCP);
								// later slides carry data-fw-lazy-* until scripts.js loads them just
								// before they show. The slide's CSS sizes the image (cover), so no inline style.
								$img_args = array(
									'class'         => 'fw-carousel__bg',
									'alt'           => $att_alt,
									'sizes'         => $bg_sizes,
									'inline_style'  => false,
									'fallback_size' => 'full',
									'art'           => $slide_art,
								);
								if ( 0 === $slide_i ) {
									$img_args['fetchpriority'] = 'high';
									$img_args['preload']       = true;
								} else {
									$img_args['lazy_attrs'] = true;
								}
								echo fw_image_tag( $att_id, $img_args ); // escaped inside fw_image_tag()
							elseif ( 0 === $slide_i ) :
								// The first slide is what shows on load (often the page's largest image): load it now.
								?>
								<img class="fw-carousel__bg" src="<?php echo esc_url( $img ); ?>"<?php echo $bg_srcset !== '' ? ' srcset="' . esc_attr( $bg_srcset ) . '" sizes="' . esc_attr( $bg_sizes ) . '"' : ''; ?> alt="<?php echo esc_attr( $att_alt ); ?>"<?php echo $as_bg ? ' fetchpriority="high"' : ''; ?> decoding="async">
							<?php else :
								// Later slides: Splide's lazy loader fetches each one just before it is shown
								// (lazyLoad 'nearby'). With Fade every slide is stacked in view, so the browser's
								// own loading="lazy" would fetch them all at once.
								?>
								<img class="fw-carousel__bg" data-splide-lazy="<?php echo esc_url( $img ); ?>"<?php echo $bg_srcset !== '' ? ' data-splide-lazy-srcset="' . esc_attr( $bg_srcset ) . '" sizes="' . esc_attr( $bg_sizes ) . '"' : ''; ?> alt="<?php echo esc_attr( $att_alt ); ?>" decoding="async">
							<?php endif; ?>
							<?php if ( $overlay ) : ?>
								<span class="fw-carousel__overlay" style="background:rgba(0,0,0,<?php echo esc_attr( $overlay_opacity / 100 ); ?>)"></span>
							<?php endif; ?>
						<?php endif; ?>

						<?php if ( $mode === 'inline' && $img !== '' ) : ?>
							<div class="fw-carousel__media"><?php
								if ( $att_id && function_exists( 'fw_image_tag' ) ) {
									echo fw_image_tag( $att_id, array(
										'class'         => 'fw-carousel__img',
										'alt'           => $img_alt,
										'sizes'         => $per <= 1 ? '100vw' : '(max-width: 576px) 100vw, ' . round( 100 / $per ) . 'vw',
										'inline_style'  => false,
										'fallback_size' => 'full',
										'art'           => $slide_art,
									) ); // escaped inside fw_image_tag()
								} else {
									?><img class="fw-carousel__img" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" loading="lazy"><?php
								}
							?></div>
						<?php endif; ?>

						<?php if ( $has_content ) : ?>
							<div class="fw-carousel__content <?php echo esc_attr( $align_class( $align ) ); ?>">
								<?php if ( $heading !== '' ) : ?>
									<h2 class="fw-carousel__heading<?php echo $h_class ? ' ' . esc_attr( $h_class ) : ''; ?>"<?php echo $h_style ? ' style="' . esc_attr( $h_style ) . '"' : ''; ?>><?php echo esc_html( $heading ); ?></h2>
								<?php endif; ?>
								<?php if ( $text !== '' ) : ?>
									<p class="fw-carousel__text<?php echo $t_class ? ' ' . esc_attr( $t_class ) : ''; ?>"<?php echo $t_style ? ' style="' . esc_attr( $t_style ) . '"' : ''; ?>><?php echo esc_html( $text ); ?></p>
								<?php endif; ?>
								<?php if ( $btn_lbl !== '' ) : ?>
									<a class="fw-carousel__btn btn" href="<?php echo esc_url( $btn_link === '' ? '#' : $btn_link ); ?>"><?php echo esc_html( $btn_lbl ); ?></a>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( $link !== '' && $btn_lbl === '' ) : ?>
							<a class="fw-carousel__link" href="<?php echo esc_url( $link ); ?>" aria-label="<?php echo esc_attr( $heading !== '' ? $heading : __( 'View slide', 'fw' ) ); ?>"></a>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
