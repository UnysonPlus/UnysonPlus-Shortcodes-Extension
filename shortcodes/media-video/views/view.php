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
	if ( $mp4 === '' && $webm === '' ) { return; } // nothing to play

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
	if ( $url === '' ) { return; }

	global $wp_embed;
	$embed = $wp_embed->run_shortcode( '[embed]' . $url . '[/embed]' );
	if ( $embed === '' || $embed === $url ) { return; } // oEmbed couldn't resolve it

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
		$inner = $embed; // WordPress oEmbed output (already-sanitized HTML)
	}
}

/* --- Wrapper (Styling-tab bg + spacing, max-width, centered) ----------------------- */
$atts['base_class']       = 'video';
$atts['unique_id_prefix'] = 'vid-';
$attr = sc_build_wrapper_attr( $atts );

$classes = array_values( array_filter( preg_split( '/\s+/', trim( $attr['class'] ?? '' ) ) ) );
foreach ( array( 'video-wrapper', 'shortcode-container', 'mx-auto' ) as $fixed ) {
	if ( ! in_array( $fixed, $classes, true ) ) { $classes[] = $fixed; }
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
$attr['style']  = ( $existing_style !== '' ? $existing_style . '; ' : '' ) . "max-width: {$max_width};";

// Responsive aspect-ratio container.
$ratio_class_map = array(
	'16x9' => 'ratio ratio-16x9',
	'4x3'  => 'ratio ratio-4x3',
	'1x1'  => 'ratio ratio-1x1',
	'21x9' => 'ratio ratio-21x9',
	'9x16' => 'ratio ratio-9x16',
	'3x4'  => 'ratio ratio-3x4',
);
$ratio_class = $ratio_class_map[ $atts['ratio'] ?? '16x9' ] ?? 'ratio ratio-16x9';
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
    <div class="<?php echo esc_attr( $ratio_class ); ?>">
        <?php echo $inner; // self-hosted <video>, oEmbed iframe, or lazy facade ?>
    </div>
</div>
