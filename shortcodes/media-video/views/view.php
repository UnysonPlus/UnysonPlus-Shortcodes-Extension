<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * @var array $atts
 */

$url = isset( $atts['url'] ) ? trim( $atts['url'] ) : '';

// Nothing to embed — render nothing rather than an empty ratio box.
if ( $url === '' ) {
	return;
}

global $wp_embed;

// Let WordPress' oEmbed system turn the URL into an embed. This supports every
// oEmbed provider WP knows (YouTube, Vimeo, Dailymotion, TikTok, SoundCloud, …),
// not a hardcoded few. The result is final HTML (usually an <iframe>).
$embed = $wp_embed->run_shortcode( '[embed]' . $url . '[/embed]' );

// oEmbed couldn't resolve it (returns the raw text unchanged) — bail.
if ( $embed === '' || $embed === $url ) {
	return;
}

// Wrapper attributes — carries the Styling-tab background color + spacing
// (as classes and/or inline style) from sc_build_wrapper_attr().
$atts['base_class']       = 'video';
$atts['unique_id_prefix'] = 'vid-';
$attr = sc_build_wrapper_attr( $atts );

// Ensure the structural classes are present (class order is irrelevant to CSS,
// so just append any that are missing — no fragile positional reshuffling).
$classes = array_values( array_filter( preg_split( '/\s+/', trim( $attr['class'] ?? '' ) ) ) );
foreach ( array( 'video-wrapper', 'shortcode-container', 'mx-auto' ) as $fixed ) {
	if ( ! in_array( $fixed, $classes, true ) ) {
		$classes[] = $fixed;
	}
}
$attr['class'] = implode( ' ', $classes );

// Max-width is a unit-input (array('value','unit')) compiled to a CSS length
// ("600px", "80%"). Legacy saves stored a bare pixel number — migrate to "<n>px".
$raw_width = isset( $atts['width'] ) ? $atts['width'] : '';
if ( is_array( $raw_width ) ) {
	$max_width = class_exists( 'FW_Option_Type_Unit_Input' )
		? FW_Option_Type_Unit_Input::to_string( $raw_width )
		: ( ( isset( $raw_width['value'] ) && trim( (string) $raw_width['value'] ) !== '' )
			? trim( (string) $raw_width['value'] ) . ( isset( $raw_width['unit'] ) ? $raw_width['unit'] : 'px' )
			: '' );
} else {
	$max_width = trim( (string) $raw_width );
	if ( $max_width !== '' && is_numeric( $max_width ) ) {
		$max_width .= 'px';
	}
}
if ( $max_width === '' ) {
	$max_width = '600px';
}

// APPEND to any existing Styling-tab inline style (background color / custom
// style) instead of overwriting it, then center the wrapper.
$existing_style = isset( $attr['style'] ) ? rtrim( trim( $attr['style'] ), ';' ) : '';
$attr['style']  = ( $existing_style !== '' ? $existing_style . '; ' : '' ) . "max-width: {$max_width};";

// Bootstrap responsive aspect-ratio container.
$ratio_class_map = [
	'16x9' => 'ratio ratio-16x9',
	'4x3'  => 'ratio ratio-4x3',
	'1x1'  => 'ratio ratio-1x1',
	'21x9' => 'ratio ratio-21x9',
	'9x16' => 'ratio ratio-9x16',
	'3x4'  => 'ratio ratio-3x4',
];
$ratio_class = $ratio_class_map[ $atts['ratio'] ?? '16x9' ] ?? 'ratio ratio-16x9';
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
    <div class="<?php echo esc_attr( $ratio_class ); ?>">
        <?php echo $embed; // WordPress oEmbed output (already-sanitized HTML) ?>
    </div>
</div>
