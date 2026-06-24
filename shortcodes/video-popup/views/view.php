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

if ( ! function_exists( 'sc_vp_parse' ) ) {
	/** Resolve a video URL to [ type, src ] where type is youtube|vimeo|file. */
	function sc_vp_parse( $url ) {
		$url = trim( (string) $url );
		if ( $url === '' ) { return array( '', '' ); }
		if ( preg_match( '~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})~i', $url, $m ) ) {
			return array( 'youtube', $m[1] );
		}
		if ( preg_match( '~vimeo\.com/(?:video/)?(\d+)~i', $url, $m ) ) {
			return array( 'vimeo', $m[1] );
		}
		return array( 'file', $url );
	}
}

if ( ! function_exists( 'sc_vp_render' ) ) {
	function sc_vp_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$design   = sc_get( 'design', $atts, 'classic' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'classic'; }

		$poster = sc_get( 'poster', $atts, array() );
		$poster_url = ( is_array( $poster ) && ! empty( $poster['url'] ) ) ? $poster['url'] : '';
		$poster_id  = ( is_array( $poster ) && ! empty( $poster['attachment_id'] ) ) ? (int) $poster['attachment_id'] : 0;
		if ( $poster_id ) {
			$full = wp_get_attachment_image_url( $poster_id, 'large' );
			if ( $full ) { $poster_url = $full; }
		}

		list( $vtype, $vsrc ) = sc_vp_parse( sc_get( 'video_url', $atts, '' ) );

		if ( $poster_url === '' && $vsrc === '' ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-vp__empty">' . esc_html__( 'Add a poster image and a video URL.', 'fw' ) . '</div>';
			}
			return '';
		}

		$label   = trim( (string) sc_get( 'play_label', $atts, '' ) );
		$caption = trim( (string) sc_get( 'caption', $atts, __( 'Play video', 'fw' ) ) );
		$ratio   = sc_get( 'ratio', $atts, 'ratio-16-9' );
		$psize   = sc_get( 'play_size', $atts, 'md' );
		$rounded = sc_get( 'rounded', $atts, 'rounded' );
		$overlay = sc_get( 'overlay', $atts, 'yes' ) === 'yes';
		$hzoom   = sc_get( 'hover_zoom', $atts, 'yes' ) === 'yes';

		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$style_var  = $var( 'accent_color', '--vp-accent' );
		$style_var .= $var( 'icon_color', '--vp-icon' );
		$style_var .= $var( 'overlay_color', '--vp-overlay' );
		$style_var .= $var( 'label_color', '--vp-label' );

		$classes = array(
			'fw-vp',
			'fw-vp--design-' . sanitize_html_class( $design ),
			'fw-vp--' . sanitize_html_class( $ratio ),
			'fw-vp--play-' . sanitize_html_class( $psize ),
			sanitize_html_class( $rounded ),
		);
		if ( $overlay ) { $classes[] = 'fw-vp--overlay'; }
		if ( $hzoom )   { $classes[] = 'fw-vp--zoom'; }
		if ( $label !== '' ) { $classes[] = 'fw-vp--has-label'; }

		$atts['base_class']       = 'video-popup';
		$atts['unique_id_prefix'] = 'vp-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $style_var !== '' ) {
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;
		}

		$play_svg = '<svg class="fw-vp__triangle" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round" stroke-linecap="round" d="M9 6.3 L18.5 12 L9 17.7 Z"/></svg>';

		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . '>';
		echo '<button type="button" class="fw-vp__trigger" aria-label="' . esc_attr( $caption ) . '"'
			. ' data-vp-type="' . esc_attr( $vtype ) . '" data-vp-src="' . esc_attr( $vsrc ) . '">';

		echo '<span class="fw-vp__media">';
		if ( $poster_url !== '' ) {
			$alt = $poster_id ? (string) get_post_meta( $poster_id, '_wp_attachment_image_alt', true ) : '';
			echo '<img class="fw-vp__poster" src="' . esc_url( $poster_url ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy" decoding="async" />';
		}
		echo '<span class="fw-vp__btn" aria-hidden="true"><span class="fw-vp__pulse"></span><span class="fw-vp__icon">' . $play_svg . '</span></span>';
		echo '</span>';

		if ( $label !== '' ) {
			echo '<span class="fw-vp__label">' . esc_html( $label ) . '</span>';
		}

		echo '</button>';
		echo '</div>';
		return ob_get_clean();
	}
}

echo sc_vp_render( $atts );
