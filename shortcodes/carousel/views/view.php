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
	'arrows'       => $yes( 'arrows' ),
	'pagination'   => $yes( 'pagination' ),
	'drag'         => $yes( 'drag' ),
	'speed'        => $speed,
	'rewind'       => ( ! $loop ),
	'autoplay'     => $autoplay,
	'interval'     => $interval,
	'pauseOnHover' => $yes( 'pause_hover' ),
	'pauseOnFocus' => true,
);
if ( $gap !== '' ) {
	$config['gap'] = $gap;
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

$align_class = function ( $a ) {
	$a = in_array( $a, array( 'left', 'center', 'right' ), true ) ? $a : 'center';
	return 'fw-carousel__content--' . $a;
};
?>
<div<?php echo $css_id !== '' ? ' id="' . esc_attr( $css_id ) . '"' : ''; ?> class="<?php echo esc_attr( implode( ' ', array_unique( $wrap_classes ) ) ); ?>">
	<div class="splide" data-splide="<?php echo esc_attr( wp_json_encode( $config ) ); ?>"<?php echo $height !== '' ? ' style="--fwc-h:' . esc_attr( $height ) . '"' : ''; ?>>
		<div class="splide__track">
			<ul class="splide__list">
				<?php foreach ( $slides as $s ) :
					$img      = ( isset( $s['image']['url'] ) && $s['image']['url'] !== '' ) ? $s['image']['url'] : '';
					$mode     = ( isset( $s['image_mode'] ) && $s['image_mode'] === 'inline' ) ? 'inline' : 'background';
					$heading  = isset( $s['heading'] ) ? trim( (string) $s['heading'] ) : '';
					$text     = isset( $s['text'] ) ? trim( (string) $s['text'] ) : '';
					$btn_lbl  = isset( $s['button_label'] ) ? trim( (string) $s['button_label'] ) : '';
					$btn_link = isset( $s['button_link'] ) ? trim( (string) $s['button_link'] ) : '#';
					$link     = isset( $s['link'] ) ? trim( (string) $s['link'] ) : '';
					$align    = isset( $s['content_align'] ) ? $s['content_align'] : 'center';
					$is_bg    = ( $mode === 'background' && $img !== '' );
					$has_content = ( $heading !== '' || $text !== '' || $btn_lbl !== '' );
					$slide_cls = 'splide__slide fw-carousel__slide' . ( $is_bg ? ' fw-carousel__slide--bg' : '' );
					?>
					<li class="<?php echo esc_attr( $slide_cls ); ?>">
						<?php if ( $is_bg ) : ?>
							<img class="fw-carousel__bg" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $heading ); ?>" loading="lazy">
							<?php if ( $overlay ) : ?>
								<span class="fw-carousel__overlay" style="background:rgba(0,0,0,<?php echo esc_attr( $overlay_opacity / 100 ); ?>)"></span>
							<?php endif; ?>
						<?php endif; ?>

						<?php if ( $mode === 'inline' && $img !== '' ) : ?>
							<div class="fw-carousel__media"><img class="fw-carousel__img" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $heading ); ?>" loading="lazy"></div>
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
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</div>
