<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** @var array $atts */

if ( ! function_exists( 'sc_get' ) ) {
	function sc_get( $path, $atts, $default = '' ) {
		if ( function_exists( 'fw_akg' ) ) {
			$v = fw_akg( $path, $atts, null );
			if ( $v !== null ) { return $v; }
		}
		return $default;
	}
}

if ( ! function_exists( 'sc_lg_item' ) ) {
	function sc_lg_item( $logo, $linkable = true ) {
		$img = ( is_array( $logo ) && isset( $logo['image'] ) && is_array( $logo['image'] ) && ! empty( $logo['image']['url'] ) ) ? $logo['image']['url'] : '';
		if ( $img === '' ) { return ''; }
		$name = isset( $logo['name'] ) ? trim( (string) $logo['name'] ) : '';
		$url  = isset( $logo['link_url'] ) ? trim( (string) $logo['link_url'] ) : '';
		$tgt  = ( isset( $logo['link_target'] ) && $logo['link_target'] === '_self' ) ? '_self' : '_blank';
		$imgt = '<img class="fw-lg__img" src="' . esc_url( $img ) . '" alt="' . esc_attr( $name ) . '" loading="lazy" decoding="async" />';
		if ( $linkable && $url !== '' ) {
			return '<a class="fw-lg__item" href="' . esc_url( $url ) . '"' . ( $tgt === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '' )
				. ( $name !== '' ? ' aria-label="' . esc_attr( $name ) . '"' : '' ) . '>' . $imgt . '</a>';
		}
		return '<span class="fw-lg__item">' . $imgt . '</span>';
	}
}

if ( ! function_exists( 'sc_lg_render' ) ) {
	function sc_lg_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$design   = sc_get( 'design', $atts, 'grid' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'grid'; }

		$logos = sc_get( 'logos', $atts, array() );
		if ( ! is_array( $logos ) ) { $logos = array(); }
		$logos = array_values( array_filter( $logos, function ( $l ) {
			return is_array( $l ) && isset( $l['image'] ) && is_array( $l['image'] ) && ! empty( $l['image']['url'] );
		} ) );

		if ( empty( $logos ) ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-lg__empty">' . esc_html__( 'Add at least one logo.', 'fw' ) . '</div>';
			}
			return '';
		}

		$columns = (int) sc_get( 'columns', $atts, 4 );
		$columns = max( 2, min( 6, $columns ) );
		$height  = (int) sc_get( 'logo_height', $atts, 48 );
		$height  = max( 16, min( 200, $height ) );
		$gray    = sc_get( 'grayscale', $atts, 'yes' ) === 'yes';
		$gap_slug = preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) sc_get( 'gap', $atts, '4' ) ) );
		$gap_css  = $gap_slug === '' ? '0px' : 'var(--gap-' . $gap_slug . ', 1.5rem)';

		$autoplay  = sc_get( 'autoplay', $atts, 'yes' ) === 'yes';
		$speed     = sc_get( 'speed', $atts, 'normal' );
		$direction = sc_get( 'direction', $atts, 'left' );

		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$style_var  = '--lg-cols:' . $columns . ';--lg-h:' . $height . 'px;--lg-gap:' . $gap_css . ';';
		$style_var .= $var( 'box_bg', '--lg-box-bg' );

		$classes = array( 'fw-lg', 'fw-lg--design-' . sanitize_html_class( $design ) );
		if ( $gray ) { $classes[] = 'fw-lg--grayscale'; }

		$atts['base_class']       = 'logo-grid';
		$atts['unique_id_prefix'] = 'lg-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;

		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . '>';

		if ( $design === 'carousel' ) {
			$show_nav = count( $logos ) > $columns;
			$cfg = array(
				'type'         => 'loop',
				'perPage'      => $columns,
				'perMove'      => 1,
				'arrows'       => false,
				'pagination'   => false,
				'autoplay'     => $autoplay,
				'interval'     => $speed === 'slow' ? 3500 : ( $speed === 'fast' ? 1500 : 2500 ),
				'speed'        => 600,
				'pauseOnHover' => true,
				'gap'          => '0px',
				'drag'         => $show_nav,
				'arrowPath'    => '',
				'breakpoints'  => array( 992 => array( 'perPage' => max( 2, min( 4, $columns ) ) ), 576 => array( 'perPage' => 2 ) ),
			);
			echo '<div class="splide fw-lg__carousel" role="group" aria-label="' . esc_attr__( 'Logos', 'fw' ) . '" data-splide="' . esc_attr( wp_json_encode( $cfg ) ) . '">';
			echo '<div class="splide__track"><ul class="splide__list">';
			foreach ( $logos as $l ) { echo '<li class="splide__slide">' . sc_lg_item( $l ) . '</li>'; }
			echo '</ul></div></div>';
		} elseif ( $design === 'marquee' ) {
			$per = array( 'slow' => 4.2, 'normal' => 2.8, 'fast' => 1.7 );
			$dur = max( 8, count( $logos ) * ( isset( $per[ $speed ] ) ? $per[ $speed ] : 2.8 ) );
			echo '<div class="fw-lg__marquee fw-lg__marquee--' . ( $direction === 'right' ? 'right' : 'left' ) . '" style="--lg-dur:' . esc_attr( rtrim( rtrim( number_format( $dur, 2, '.', '' ), '0' ), '.' ) ) . 's;">';
			echo '<div class="fw-lg__track">';
			foreach ( $logos as $l ) { echo sc_lg_item( $l ); }
			foreach ( $logos as $l ) { echo sc_lg_item( $l, false ); }
			echo '</div></div>';
		} else { // grid / boxed
			echo '<div class="fw-lg__grid">';
			foreach ( $logos as $l ) { echo sc_lg_item( $l ); }
			echo '</div>';
		}

		echo '</div>';
		return ob_get_clean();
	}
}

echo sc_lg_render( $atts );
