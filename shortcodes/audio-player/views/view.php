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

if ( ! function_exists( 'sc_ap_render' ) ) {
	function sc_ap_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$design   = sc_get( 'design', $atts, 'classic' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'classic'; }

		$raw_tracks = sc_get( 'tracks', $atts, array() );
		if ( ! is_array( $raw_tracks ) ) { $raw_tracks = array(); }

		$tracks = array();
		foreach ( $raw_tracks as $t ) {
			if ( ! is_array( $t ) ) { continue; }
			$src = ( isset( $t['audio'] ) && is_array( $t['audio'] ) && ! empty( $t['audio']['url'] ) ) ? $t['audio']['url'] : '';
			if ( $src === '' ) { $src = isset( $t['audio_url'] ) ? trim( (string) $t['audio_url'] ) : ''; }
			if ( $src === '' ) { continue; }
			$tracks[] = array(
				'src'    => $src,
				'title'  => isset( $t['title'] ) ? trim( (string) $t['title'] ) : '',
				'artist' => isset( $t['artist'] ) ? trim( (string) $t['artist'] ) : '',
				'cover'  => ( isset( $t['cover'] ) && is_array( $t['cover'] ) && ! empty( $t['cover']['url'] ) ) ? $t['cover']['url'] : '',
			);
		}

		if ( empty( $tracks ) ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-ap__empty">' . esc_html__( 'Add at least one track with an audio file or URL.', 'fw' ) . '</div>';
			}
			return '';
		}

		$autoplay = sc_get( 'autoplay', $atts, 'no' ) === 'yes';
		$loop     = sc_get( 'loop', $atts, 'no' ) === 'yes';
		$show_vol = sc_get( 'show_volume', $atts, 'yes' ) === 'yes';
		$show_dl  = sc_get( 'show_download', $atts, 'no' ) === 'yes';
		$rounded  = sc_get( 'rounded', $atts, 'rounded' );
		$is_list  = count( $tracks ) > 1;

		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$style_var  = $var( 'accent_color', '--ap-accent' );
		$style_var .= $var( 'bg_color', '--ap-bg' );
		$style_var .= $var( 'text_color', '--ap-text' );

		$classes = array(
			'fw-ap',
			'fw-ap--design-' . sanitize_html_class( $design ),
			sanitize_html_class( $rounded ),
		);
		if ( $is_list ) { $classes[] = 'fw-ap--has-list'; }

		$atts['base_class']       = 'audio-player';
		$atts['unique_id_prefix'] = 'ap-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $style_var !== '' ) {
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;
		}

		// Inline control icons.
		$ico = array(
			'play'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>',
			'pause' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 5h3v14H7zM14 5h3v14h-3z"/></svg>',
			'prev'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 6h2v12H7zm3 6l9 6V6z"/></svg>',
			'next'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 6h2v12h-2zM5 18l9-6-9-6z"/></svg>',
			'vol'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10v4h4l5 5V5L7 10zM16 12a4 4 0 00-2-3.46v6.92A4 4 0 0016 12z"/></svg>',
			'mute'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10v4h4l5 5V5L7 10zM19 12l2 2m0-4l-2 2m-2-2l4 4m0-4l-4 4" stroke="currentColor" stroke-width="2" fill="none"/></svg>',
			'dl'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v10m0 0l-4-4m4 4l4-4M5 19h14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		);

		$first = $tracks[0];

		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . ' data-ap data-autoplay="' . ( $autoplay ? '1' : '0' ) . '" data-loop="' . ( $loop ? '1' : '0' ) . '">';
		echo '<audio class="fw-ap__audio" preload="metadata"' . ( $loop && ! $is_list ? ' loop' : '' ) . '></audio>';

		echo '<div class="fw-ap__player">';

		// Cover (card / playlist designs).
		echo '<div class="fw-ap__cover"' . ( $first['cover'] === '' ? ' data-empty="1"' : '' ) . '>';
		echo '<img class="fw-ap__cover-img" src="' . esc_url( $first['cover'] ) . '" alt="" />';
		echo '<span class="fw-ap__cover-note" aria-hidden="true">&#9835;</span>';
		echo '</div>';

		echo '<div class="fw-ap__main">';
		echo '<div class="fw-ap__info"><span class="fw-ap__title">' . esc_html( $first['title'] ) . '</span><span class="fw-ap__artist">' . esc_html( $first['artist'] ) . '</span></div>';

		echo '<div class="fw-ap__controls">';
		if ( $is_list ) { echo '<button type="button" class="fw-ap__btn fw-ap__prev" aria-label="' . esc_attr__( 'Previous', 'fw' ) . '">' . $ico['prev'] . '</button>'; }
		echo '<button type="button" class="fw-ap__btn fw-ap__play" aria-label="' . esc_attr__( 'Play', 'fw' ) . '"><span class="fw-ap__i-play">' . $ico['play'] . '</span><span class="fw-ap__i-pause">' . $ico['pause'] . '</span></button>';
		if ( $is_list ) { echo '<button type="button" class="fw-ap__btn fw-ap__next" aria-label="' . esc_attr__( 'Next', 'fw' ) . '">' . $ico['next'] . '</button>'; }

		echo '<div class="fw-ap__bar"><div class="fw-ap__progress" role="slider" tabindex="0" aria-label="' . esc_attr__( 'Seek', 'fw' ) . '" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="fw-ap__buffered"></div><div class="fw-ap__played"></div><div class="fw-ap__handle"></div></div></div>';
		echo '<span class="fw-ap__time"><span class="fw-ap__cur">0:00</span><span class="fw-ap__sep">/</span><span class="fw-ap__dur">0:00</span></span>';

		if ( $show_vol ) {
			echo '<div class="fw-ap__volwrap"><button type="button" class="fw-ap__btn fw-ap__volbtn" aria-label="' . esc_attr__( 'Mute', 'fw' ) . '"><span class="fw-ap__i-vol">' . $ico['vol'] . '</span><span class="fw-ap__i-mute">' . $ico['mute'] . '</span></button><input type="range" class="fw-ap__vol" min="0" max="1" step="0.05" value="1" aria-label="' . esc_attr__( 'Volume', 'fw' ) . '" /></div>';
		}
		if ( $show_dl ) {
			echo '<a class="fw-ap__btn fw-ap__dl" href="' . esc_url( $first['src'] ) . '" download aria-label="' . esc_attr__( 'Download', 'fw' ) . '">' . $ico['dl'] . '</a>';
		}
		echo '</div>'; // controls
		echo '</div>'; // main
		echo '</div>'; // player

		// Track list (data source for the JS; shown by the Playlist design).
		echo '<ol class="fw-ap__list">';
		foreach ( $tracks as $i => $t ) {
			echo '<li class="fw-ap__track' . ( $i === 0 ? ' is-active' : '' ) . '" data-src="' . esc_url( $t['src'] ) . '" data-title="' . esc_attr( $t['title'] ) . '" data-artist="' . esc_attr( $t['artist'] ) . '" data-cover="' . esc_url( $t['cover'] ) . '">';
			echo '<span class="fw-ap__track-num">' . ( $i + 1 ) . '</span>';
			if ( $t['cover'] !== '' ) { echo '<img class="fw-ap__track-cover" src="' . esc_url( $t['cover'] ) . '" alt="" loading="lazy" />'; }
			echo '<span class="fw-ap__track-meta"><span class="fw-ap__track-title">' . esc_html( $t['title'] !== '' ? $t['title'] : __( 'Untitled', 'fw' ) ) . '</span>';
			if ( $t['artist'] !== '' ) { echo '<span class="fw-ap__track-artist">' . esc_html( $t['artist'] ) . '</span>'; }
			echo '</span>';
			echo '<span class="fw-ap__track-dur"></span>';
			echo '</li>';
		}
		echo '</ol>';

		echo '</div>';
		return ob_get_clean();
	}
}

echo sc_ap_render( $atts );
