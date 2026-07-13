<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 */

$atts['base_class']       = 'text-block';
$atts['unique_id_prefix'] = 'tb-';

// Wraps the first $chars text characters of $html in a drop-cap span (after any leading tags /
// whitespace). $cap_style carries the inline custom properties the cap needs: --dc-lines (drives the
// CSS-computed font-size so it spans N lines) and, when chosen, font-family (a single decorative
// glyph that doesn't map to a preset class). No JavaScript. Bails gracefully (no cap) if the content
// doesn't start with plain text.
if ( ! function_exists( 'sc_text_block_dropcap_wrap' ) ) {
    function sc_text_block_dropcap_wrap( $html, $chars, $cap_style ) {
        $chars      = max( 1, min( 10, (int) $chars ) );
        $style_attr = ( $cap_style !== '' ) ? ' style="' . esc_attr( $cap_style ) . '"' : '';
        // Wrap the first $chars LETTERS (after any leading tags/space), counting across spaces so
        // "How many letters" is honoured even when the first word is short — each iteration eats any
        // non-letter run then one letter, stopping on the Nth letter. Bails (no cap) if no leading text.
        // The non-letter run treats an HTML character entity (&ldquo; &#8220; &#x201C;) as ONE atomic
        // unit so a leading entity (e.g. a smart quote) is consumed whole instead of being split mid-
        // entity at the letter inside it (&ldquo; → &l…). Entities are matched before the bare-char
        // fallback, so a lone "&" still works.
        $entity = '&(?:[a-zA-Z][a-zA-Z0-9]*|#\d+|#x[0-9a-fA-F]+);';
        return preg_replace_callback(
            '/^(\s*(?:<[^>]+>\s*)*)((?:(?:' . $entity . '|[^<\p{L}])*\p{L}){1,' . $chars . '})/u',
            function ( $m ) use ( $style_attr ) {
                return $m[1] . '<span class="tb-dropcap__cap"' . $style_attr . '>' . $m[2] . '</span>';
            },
            $html, 1
        );
    }
}

// ---- Styling-tab layout/typography options → wrapper CLASSES (kept off the prose) ----
$layout = array();

// Text alignment → Bootstrap text-* utility (theme ships Bootstrap; no shortcode CSS needed).
$align_map = array( 'left' => 'text-start', 'center' => 'text-center', 'right' => 'text-end', 'justify' => 'text-justify' );
$al = isset( $atts['text_align'] ) ? (string) $atts['text_align'] : '';
if ( isset( $align_map[ $al ] ) ) { $layout[] = $align_map[ $al ]; }

// Max width (reading measure) — multi-picker: presets emit a class (which also centers the block);
// "Custom" emits an inline max-width. Saved shape: { preset, custom: { custom_width: {value,unit} } }.
// Tolerates a legacy bare-slug string (the option was a plain select before it became a multi-picker).
$mw_style = '';
$mwv      = isset( $atts['max_width'] ) ? $atts['max_width'] : '';
if ( is_array( $mwv ) ) {
    $preset = isset( $mwv['preset'] ) ? sc_sanitize_class( $mwv['preset'] ) : '';
    if ( $preset === 'custom' ) {
        $cw   = ( isset( $mwv['custom']['custom_width'] ) && is_array( $mwv['custom']['custom_width'] ) ) ? $mwv['custom']['custom_width'] : array();
        $val  = isset( $cw['value'] ) ? trim( (string) $cw['value'] ) : '';
        $unit = isset( $cw['unit'] ) ? (string) $cw['unit'] : 'px';
        if ( $val !== '' && is_numeric( $val ) ) {
            if ( ! in_array( $unit, array( 'px', 'rem', 'em', '%', 'ch', 'vw' ), true ) ) { $unit = 'px'; }
            // Respect the block's alignment: a capped block should sit where the text is
            // aligned — left (default) keeps it left, center centers it, right pins it right.
            // (Previously it ALWAYS centered via margin-inline:auto, so a left-aligned block
            // with a max-width appeared wrongly indented.)
            $mw_margin = ( $al === 'center' ) ? 'margin-inline:auto'
                : ( ( $al === 'right' ) ? 'margin-inline-start:auto' : 'margin-inline-end:auto' );
            $mw_style = 'max-width:' . $val . $unit . ';' . $mw_margin;
        }
    } elseif ( in_array( $preset, array( 'narrow', 'read', 'medium', 'wide' ), true ) ) {
        $layout[] = 'tb-mw-' . $preset;
    }
    // 'full' / '' → no constraint
} elseif ( is_string( $mwv ) && in_array( $mwv, array( 'narrow', 'read', 'medium', 'wide' ), true ) ) {
    $layout[] = 'tb-mw-' . $mwv; // legacy scalar
}

// Newspaper columns.
$cols = isset( $atts['columns'] ) ? (string) $atts['columns'] : '';
if ( $cols === '2' || $cols === '3' ) { $layout[] = 'tb-cols-' . $cols; }

// Balanced wrapping.
if ( ! empty( $atts['balance'] ) && $atts['balance'] === 'yes' ) { $layout[] = 'tb-balance'; }

// Line height → class.
$lh = isset( $atts['line_height'] ) ? (string) $atts['line_height'] : '';
if ( in_array( $lh, array( 'tight', 'snug', 'normal', 'relaxed', 'loose' ), true ) ) { $layout[] = 'tb-lh-' . $lh; }

// Paragraph spacing → class.
$ps = isset( $atts['para_spacing'] ) ? (string) $atts['para_spacing'] : '';
if ( in_array( $ps, array( 'sm', 'md', 'lg' ), true ) ) { $layout[] = 'tb-pspace-' . $ps; }

// Lead paragraph (enlarge first paragraph) → class.
if ( ! empty( $atts['lead'] ) && $atts['lead'] === 'yes' ) { $layout[] = 'tb-lead'; }

// Link underline behavior → class.
$lu = isset( $atts['link_underline'] ) ? (string) $atts['link_underline'] : '';
if ( in_array( $lu, array( 'always', 'hover', 'none' ), true ) ) { $layout[] = 'tb-links-' . $lu; }

// Link color → a --tb-link CSS variable (gated by the .tb-linkcolor class so the theme's own
// link color is untouched unless one is picked). Custom hex wins; a palette swatch resolves to its
// preset hex (the bg-*/text-* utility class can't recolor a link). Mirrors the blockquote resolver.
$tb_resolve_color = function ( $raw ) {
    if ( ! is_array( $raw ) ) { return ''; }
    if ( ! empty( $raw['custom'] ) ) {
        $hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
        if ( $hex !== '' ) { return $hex; }
    }
    if ( ! empty( $raw['predefined'] ) && function_exists( 'unysonplus_color_preset_slug_map' ) ) {
        $slug = preg_replace( '/^(?:bg|text)-/', '', (string) $raw['predefined'] );
        $map  = unysonplus_color_preset_slug_map();
        if ( isset( $map[ $slug ] ) && $map[ $slug ] !== '' ) { return $map[ $slug ]; }
    }
    return '';
};
$link_color = $tb_resolve_color( isset( $atts['link_color'] ) ? $atts['link_color'] : '' );
if ( $link_color !== '' ) { $layout[] = 'tb-linkcolor'; }

// Drop cap (multi-picker switch). Saved shape: { enabled, yes: { dropcap_style/font/lines/chars/gap } }.
// "Lines to Drop" and "Characters" are free numbers. Sizing is PURE CSS: we emit --dc-lines (N) on
// the cap and styles.css computes the font-size so the cap spans exactly N lines. N letters are
// wrapped. Style + distance are classes; the only other inline bit is the optional font-family.
$dc           = ( isset( $atts['dropcap'] ) && is_array( $atts['dropcap'] ) ) ? $atts['dropcap'] : array();
$dc_on        = ( isset( $dc['enabled'] ) && $dc['enabled'] === 'yes' );
$dc_chars     = 1;
$dc_cap_style = '';
$dc_accent    = '';
if ( $dc_on ) {
    $sub   = ( isset( $dc['yes'] ) && is_array( $dc['yes'] ) ) ? $dc['yes'] : array();
    $style = isset( $sub['dropcap_style'] ) ? sc_sanitize_class( $sub['dropcap_style'] ) : 'dropped';
    if ( $style === '' ) { $style = 'dropped'; }
    $lines    = isset( $sub['dropcap_lines'] ) ? max( 2, min( 8, (int) $sub['dropcap_lines'] ) ) : 3;
    $dc_chars = isset( $sub['dropcap_chars'] ) ? max( 1, min( 10, (int) $sub['dropcap_chars'] ) ) : 1;
    $gap      = isset( $sub['dropcap_gap'] ) ? sc_sanitize_class( $sub['dropcap_gap'] ) : 'md';
    if ( ! in_array( $gap, array( 'none', 'sm', 'md', 'lg' ), true ) ) { $gap = 'md'; }

    // --dc-lines drives the CSS-computed cap font-size (spans N lines); font-family is optional.
    $decls = array( '--dc-lines:' . $lines );
    $fonts = array(
        'serif'     => "Georgia, 'Times New Roman', serif",
        'sans'      => "system-ui, -apple-system, 'Segoe UI', sans-serif",
        'mono'      => "ui-monospace, SFMono-Regular, Menlo, monospace",
        'condensed' => "'Arial Narrow', 'Helvetica Neue Condensed', sans-serif",
    );
    $fkey = isset( $sub['dropcap_font'] ) ? (string) $sub['dropcap_font'] : '';
    if ( isset( $fonts[ $fkey ] ) ) { $decls[] = 'font-family:' . $fonts[ $fkey ]; }
    $dc_cap_style = implode( ';', $decls );

    // Per-block drop-cap accent (accent / boxed / outline styles read --fw-dropcap-accent).
    $dc_accent = $tb_resolve_color( isset( $sub['dropcap_color'] ) ? $sub['dropcap_color'] : '' );

    $layout[] = 'tb-dropcap';
    $layout[] = 'tb-dropcap--' . $style;
    $layout[] = 'tb-dropcap--gap-' . $gap;
}

if ( $layout ) {
    $atts['css_class'] = trim( ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) . ' ' . implode( ' ', $layout ) );
}

// Build attributes for wrapper
$attr = sc_build_wrapper_attr( $atts );

// Inline-style fragments — the few values that can't be a preset class: a custom max-width, the
// link-color and drop-cap-accent CSS variables. Each also forces a wrapper (below) when set.
$inline_styles = array();
if ( $mw_style !== '' )   { $inline_styles[] = $mw_style; }
if ( $link_color !== '' ) { $inline_styles[] = '--tb-link:' . $link_color; }
if ( $dc_accent !== '' )  { $inline_styles[] = '--fw-dropcap-accent:' . $dc_accent; }
if ( $inline_styles ) {
    $existing      = ( isset( $attr['style'] ) && $attr['style'] !== '' ) ? rtrim( $attr['style'], "; \t\n\r" ) . '; ' : '';
    $attr['style'] = $existing . implode( '; ', $inline_styles ) . ';';
}

// Determine if wrapper is needed (an inline-style fragment forces one even with no classes/id).
$should_wrap = ( function_exists( 'sc_needs_wrapper' )
    ? sc_needs_wrapper( $atts )
    : ( ! empty( $atts['css_id'] ) || ! empty( $atts['css_class'] ) ) )
    || ! empty( $inline_styles );

// Match WordPress's the_content pipeline order (wpautop → shortcode_unautop → do_shortcode) so a
// block authored in the editor's Text/HTML mode with blank-line paragraphs still auto-formats, while
// block-level nested shortcodes don't get wrapped in stray <p>. wpautop is idempotent on the
// already-<p>-tagged HTML TinyMCE stores in Visual mode, so existing blocks render unchanged.
$content = ! empty( $atts['text'] ) ? do_shortcode( shortcode_unautop( wpautop( (string) $atts['text'] ) ) ) : '';
if ( $dc_on && $content !== '' ) {
    $content = sc_text_block_dropcap_wrap( $content, $dc_chars, $dc_cap_style );
}

?>

<?php if ( $content !== '' ) : ?>
    <?php if ( $should_wrap ) : ?>
        <div <?php echo fw_attr_to_html( $attr ); ?>>
            <?php echo $content; ?>
        </div>
    <?php else : ?>
        <?php echo $content; ?>
    <?php endif; ?>
<?php endif; ?>
