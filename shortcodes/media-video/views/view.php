<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * @var array $atts
 */

/* --- Resolve the source (Embed vs Self-hosted), tolerating the legacy flat shape ------
   New shape: $atts['source_type'] = array( 'source' => 'embed'|'self_hosted',
   'embed' => array(...), 'self_hosted' => array(...) ). Legacy instances stored a flat
   $atts['url'] with no source_type — treat those as an Embed with that URL. */
$st       = ( isset( $atts['source_type'] ) && is_array( $atts['source_type'] ) ) ? $atts['source_type'] : array();
$source   = isset( $st['source'] ) ? (string) $st['source'] : '';
$embed_v  = ( isset( $st['embed'] ) && is_array( $st['embed'] ) ) ? $st['embed'] : array();
$self_v   = ( isset( $st['self_hosted'] ) && is_array( $st['self_hosted'] ) ) ? $st['self_hosted'] : array();
$legacy   = isset( $atts['url'] ) ? trim( (string) $atts['url'] ) : '';
if ( $source === '' ) { $source = 'embed'; } // legacy + default

// Pull a URL out of an `upload` field value ({attachment_id, url}).
$upload_url = static function ( $v ) {
	return ( is_array( $v ) && ! empty( $v['url'] ) ) ? (string) $v['url'] : '';
};
$sw = static function ( $v ) { return $v === 'yes'; }; // switch → bool

/* --- Build the inner media HTML -------------------------------------------------- */
$inner = '';

if ( $source === 'self_hosted' ) {
	$mp4    = $upload_url( $self_v['video_file'] ?? null );
	$webm   = $upload_url( $self_v['video_webm'] ?? null );
	if ( $mp4 === '' && $webm === '' && ! empty( $self_v['video_url'] ) ) {
		$mp4 = trim( (string) $self_v['video_url'] ); // external file URL fallback
	}
	if ( $mp4 === '' && $webm === '' ) {
		// Editor-only note; a visitor still gets nothing. See media-image for why
		// silence is the wrong answer inside a block preview.
		if ( fw_is_editor_context() ) {
			echo sc_editor_notice( __( 'Choose a video file, or paste a file URL.', 'fw' ) );
		}
		return; // nothing to play
	}

	$poster      = $upload_url( $self_v['poster'] ?? null );
	$autoplay    = $sw( $self_v['autoplay'] ?? 'no' );
	$muted       = $sw( $self_v['muted'] ?? 'no' ) || $autoplay; // autoplay requires muted
	$loop        = $sw( $self_v['loop'] ?? 'no' );
	$controls    = $sw( $self_v['controls'] ?? 'yes' );
	$playsinline = $sw( $self_v['playsinline'] ?? 'yes' );
	$preload     = in_array( ( $self_v['preload'] ?? 'metadata' ), array( 'auto', 'metadata', 'none' ), true ) ? $self_v['preload'] : 'metadata';
	$object_fit  = ( ( $self_v['object_fit'] ?? 'contain' ) === 'cover' ) ? 'cover' : 'contain';

	$flags = '';
	if ( $autoplay )    { $flags .= ' autoplay data-upw-autoplay="1"'; } // data-* = reduce-motion hook
	if ( $muted )       { $flags .= ' muted'; }
	if ( $loop )        { $flags .= ' loop'; }
	if ( $controls )    { $flags .= ' controls'; }
	if ( $playsinline ) { $flags .= ' playsinline'; }

	$sources = '';
	if ( $webm !== '' ) { $sources .= '<source src="' . esc_url( $webm ) . '" type="video/webm">'; }
	if ( $mp4  !== '' ) { $sources .= '<source src="' . esc_url( $mp4 )  . '" type="video/mp4">'; }

	$inner = '<video class="video-el"'
		. ( $poster !== '' ? ' poster="' . esc_url( $poster ) . '"' : '' )
		. ' preload="' . esc_attr( $preload ) . '"'
		. $flags
		. ' style="width:100%;height:100%;object-fit:' . $object_fit . ';display:block;">'
		. $sources
		. '</video>';

} else {
	/* --- Embed (WordPress oEmbed) --- */
	$url = ! empty( $embed_v['url'] ) ? trim( (string) $embed_v['url'] ) : $legacy;
	if ( $url === '' ) {
		if ( fw_is_editor_context() ) {
			echo sc_editor_notice( __( 'Paste a video URL — YouTube, Vimeo or another oEmbed provider.', 'fw' ) );
		}
		return;
	}

	global $wp_embed;
	$embed = $wp_embed->run_shortcode( '[embed]' . $url . '[/embed]' );
	if ( $embed === '' || $embed === $url ) {
		// A URL that oEmbed cannot resolve is a DIFFERENT failure from an empty one,
		// and worth saying so: the address is usually a watch-page variant, a private
		// video, or a provider WordPress does not support.
		if ( fw_is_editor_context() ) {
			echo sc_editor_notice( __( 'That URL could not be embedded. Check it is a public video from a supported provider.', 'fw' ) );
		}
		return; // oEmbed couldn't resolve it
	}

	// Privacy: route YouTube through youtube-nocookie.com.
	if ( $sw( $embed_v['youtube_nocookie'] ?? 'no' ) ) {
		$embed = str_replace(
			array( '//www.youtube.com/embed/', '//youtube.com/embed/' ),
			'//www.youtube-nocookie.com/embed/',
			$embed
		);
	}

	if ( $sw( $embed_v['lazy_facade'] ?? 'no' )
		&& preg_match( '/<iframe[^>]+src=["\\\']([^"\\\']+)["\\\']/i', $embed, $m ) ) {
		// Lazy-load facade: a poster + play button; the JS swaps in the real iframe on click.
		$iframe_src = $m[1];
		$poster     = $upload_url( $embed_v['poster'] ?? null );
		// No poster given? Use the YouTube thumbnail if we can pull the id from the src.
		if ( $poster === '' && preg_match( '#youtube(?:-nocookie)?\.com/embed/([\w-]+)#', $iframe_src, $y ) ) {
			$poster = 'https://i.ytimg.com/vi/' . $y[1] . '/hqdefault.jpg';
		}
		$style = $poster !== '' ? ' style="background-image:url(' . esc_url( $poster ) . ');"' : '';
		$inner = '<button type="button" class="video-facade" data-video-src="' . esc_attr( $iframe_src ) . '" aria-label="' . esc_attr__( 'Play video', 'fw' ) . '"' . $style . '>'
			. '<span class="video-facade__play" aria-hidden="true"></span></button>';
	} else {
		// Defer a below-the-fold embed's iframe (Core Web Vitals) when the provider
		// didn't already set it. The Lazy-load facade above is the stronger option —
		// it loads no provider iframe at all until the visitor clicks.
		if ( strpos( $embed, '<iframe' ) !== false && strpos( $embed, 'loading=' ) === false ) {
			$embed = preg_replace( '/<iframe(\s)/i', '<iframe loading="lazy"$1', $embed, 1 );
		}
		$inner = $embed; // WordPress oEmbed output (already-sanitized HTML)
	}
}

/* --- "Use as Section Background" (shared sc-bg-fill runtime) ----------------------- */
// When on, the wrapper carries the shared `.sc-bg-fill` class (the runtime moves it into
// the nearest <section> as a backdrop, lifting the section's content on top) plus our own
// `video--bg` class (styles.css neutralises the max-width + aspect-ratio box so the video
// fills the section, object-fit: cover). Max Width / Aspect Ratio no longer apply.
$as_bg_val = isset( $atts['as_background'] ) ? $atts['as_background'] : 'no';
$as_bg = function_exists( 'sc_section_background_is_on' )
	? sc_section_background_is_on( $as_bg_val )
	: ( $as_bg_val === 'yes' || $as_bg_val === true || $as_bg_val === '1' || $as_bg_val === 1 );

/* --- Wrapper (Styling-tab bg + spacing, max-width, centered) ----------------------- */
$atts['base_class']       = 'video';
$atts['unique_id_prefix'] = 'vid-';
$attr = sc_build_wrapper_attr( $atts );

// `.video-wrapper` centers itself (media-video.css: margin-inline:auto) — no
// dependency on the builder's global Bootstrap-style `.mx-auto` helper.
$classes = array_values( array_filter( preg_split( '/\s+/', trim( $attr['class'] ?? '' ) ) ) );
foreach ( array( 'video-wrapper', 'shortcode-container' ) as $fixed ) {
	if ( ! in_array( $fixed, $classes, true ) ) { $classes[] = $fixed; }
}
if ( $as_bg ) {
	$classes[] = 'sc-bg-fill'; // shared runtime: fill the parent Section + sit behind its content
	$classes[] = 'video--bg';  // element-specific fill CSS (neutralise ratio box + max-width)
	if ( function_exists( 'sc_section_background_use' ) ) { sc_section_background_use(); }
}
$attr['class'] = implode( ' ', $classes );

// Max-width — unit-input compiled to a CSS length; legacy bare-number → "<n>px".
$raw_width = isset( $atts['width'] ) ? $atts['width'] : '';
if ( is_array( $raw_width ) ) {
	$max_width = class_exists( 'FW_Option_Type_Unit_Input' )
		? FW_Option_Type_Unit_Input::to_string( $raw_width )
		: ( ( isset( $raw_width['value'] ) && trim( (string) $raw_width['value'] ) !== '' )
			? trim( (string) $raw_width['value'] ) . ( isset( $raw_width['unit'] ) ? $raw_width['unit'] : 'px' )
			: '' );
} else {
	$max_width = trim( (string) $raw_width );
	if ( $max_width !== '' && is_numeric( $max_width ) ) { $max_width .= 'px'; }
}
if ( $max_width === '' ) { $max_width = '600px'; }

$existing_style = isset( $attr['style'] ) ? rtrim( trim( $attr['style'] ), ';' ) : '';
// In Section-Background mode the video FILLS the section, so a max-width cap must not apply
// (styles.css also forces max-width:none, but keeping it out of the inline style is cleaner).
$attr['style']  = $as_bg
	? ( $existing_style !== '' ? $existing_style . ';' : '' )
	: ( $existing_style !== '' ? $existing_style . '; ' : '' ) . "max-width: {$max_width};";

// Aspect ratio → a data attribute the CSS maps to the box's padding-top (--vid-aspect),
// replacing the Bootstrap `.ratio ratio-16x9` class pair.
$valid_ratios = array( '16x9', '4x3', '1x1', '21x9', '9x16', '3x4' );
$ratio_val    = in_array( $atts['ratio'] ?? '16x9', $valid_ratios, true ) ? $atts['ratio'] : '16x9';

// Flatten one <div>: when the wrapper is bare (no Styling / Spacing / Animation / id /
// class / custom CSS or attrs), the wrapper ITSELF becomes the aspect box — one fewer
// element in the DOM. When the wrapper DOES carry something (e.g. padding + a background
// = a framed video), keep a nested `.video-ratiobox` so that frame still shows AROUND the
// video instead of being covered by the edge-to-edge media.
$merge_box = function_exists( 'sc_wrapper_is_bare' ) ? sc_wrapper_is_bare( $atts ) : false;

if ( $merge_box ) {
	$attr['class'] = trim( ( isset( $attr['class'] ) ? $attr['class'] : '' ) . ' video-ratiobox' );
	echo '<div ' . fw_attr_to_html( $attr ) . ' data-ratio="' . esc_attr( $ratio_val ) . '">'
		. $inner // self-hosted <video>, oEmbed iframe, or lazy facade
		. '</div>';
} else {
	echo '<div ' . fw_attr_to_html( $attr ) . '>'
		. '<div class="video-ratiobox" data-ratio="' . esc_attr( $ratio_val ) . '">'
		. $inner
		. '</div></div>';
}
