<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 */

$atts['base_class']         = 'code-block';
$atts['unique_id_prefix']   = 'cb-';

$atts['extra_attrs'] = [];

$attr = sc_build_wrapper_attr( $atts );

$needs_wrapper = ! empty( $atts['css_id'] ) || ! empty( $atts['css_class'] );

/*
|--------------------------------------------------------------------------
| Render-as-code helpers
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_code_block_detect_language' ) ) {
	/**
	 * Cheap heuristic language sniffer for the "Auto-detect" choice. Good enough to pick the
	 * right Prism `language-*` class for the common cases (markup / php / css / js / json).
	 */
	function sc_code_block_detect_language( $code ) {
		$c = trim( (string) $code );
		if ( $c === '' ) {
			return 'markup';
		}
		if ( stripos( $c, '<?php' ) !== false ) {
			return 'php';
		}
		if ( $c[0] === '<' ) {
			return 'markup'; // starts with a tag → HTML/SVG/XML
		}
		if ( ( $c[0] === '{' || $c[0] === '[' ) && preg_match( '/"\s*:\s*/', $c ) ) {
			return 'json';
		}
		if ( preg_match( '/[#.]?[\w-]+\s*\{[^{}]*[\w-]+\s*:[^{}]+;/s', $c ) ) {
			return 'css'; // selector { prop: value; }
		}
		if ( preg_match( '/\b(function|const|let|var|=>|console\.|document\.|window\.)\b/', $c ) ) {
			return 'javascript';
		}
		return 'markup';
	}
}

if ( ! function_exists( 'sc_code_block_indent_html' ) ) {
	/**
	 * Tab-indent a (normalized, single-line) HTML string with a simple element STACK. Structural
	 * containers (div, section, ul, table, tr, …) each own an indented line and indent their
	 * children; text-level "leaf" elements (p, li, h1–6, td, th, span, strong, …) sit on their own
	 * line at block context but keep their inline content + closing tag on the SAME line; any
	 * element nested inside a leaf renders fully inline. Each open frame is closed in the same
	 * mode it was opened, so inline/leaf nesting can't unbalance the indentation (the failure mode
	 * of tags that are both inline and leaf, e.g. <span>). <svg>/<pre>/… are protected upstream.
	 */
	function sc_code_block_indent_html( $html ) {
		$void  = array_flip( explode( ' ', 'area base br col embed hr img input link meta param source track wbr' ) );
		$block = array_flip( explode( ' ', 'html head body section div article aside header footer nav main figure picture form fieldset ul ol dl table thead tbody tfoot tr colgroup select optgroup video audio map' ) );
		preg_match_all( '#<[^>]+>|[^<]+#s', $html, $mm );
		$out = ''; $depth = 0; $stack = array();
		$pad = function ( $d ) { return str_repeat( "\t", max( 0, $d ) ); };
		foreach ( $mm[0] as $tok ) {
			if ( $tok === '' ) { continue; }
			$mode = $stack ? end( $stack ) : 'block';
			if ( $tok[0] !== '<' ) { // text node — collapse whitespace runs to a single space
				$text = preg_replace( '#\s+#', ' ', $tok );
				if ( $mode === 'leaf' || $mode === 'inline' ) { $out .= $text; }
				else { $t = trim( $text ); if ( $t !== '' ) { $out .= "\n" . $pad( $depth ) . $t; } }
				continue;
			}
			if ( ! preg_match( '#^<(/?)\s*([a-zA-Z0-9-]+)#', $tok, $g ) ) { $out .= $tok; continue; } // comments / placeholders
			$close = ( $g[1] === '/' );
			$name  = strtolower( $g[2] );
			$self  = ( substr( $tok, -2 ) === '/>' ) || isset( $void[ $name ] );
			if ( $close ) {
				$popped = $stack ? array_pop( $stack ) : 'inline';
				if ( $popped === 'block' ) { $depth = max( 0, $depth - 1 ); $out .= "\n" . $pad( $depth ) . $tok; }
				else { $out .= $tok; } // leaf / inline → same line as content
				continue;
			}
			if ( $self ) { // void / self-closing — no frame
				if ( $mode === 'leaf' || $mode === 'inline' ) { $out .= $tok; }
				else { $out .= "\n" . $pad( $depth ) . $tok; }
				continue;
			}
			if ( isset( $block[ $name ] ) ) {                       // structural container
				$out .= "\n" . $pad( $depth ) . $tok; $depth++; $stack[] = 'block';
			} elseif ( $mode === 'leaf' || $mode === 'inline' ) {   // text-level inside a leaf → inline
				$out .= $tok; $stack[] = 'inline';
			} else {                                                // text-level at block context → leaf
				$out .= "\n" . $pad( $depth ) . $tok; $stack[] = 'leaf';
			}
		}
		return ltrim( $out, "\n" );
	}
}

if ( ! function_exists( 'sc_code_block_beautify_html' ) ) {
	/**
	 * Normalize + re-indent arbitrary (possibly minified or messily-formatted) HTML into clean,
	 * tab-indented markup. <pre>/<textarea>/<script>/<style>/<svg> bodies are protected from
	 * reflow so their internal formatting is preserved verbatim.
	 */
	function sc_code_block_beautify_html( $html ) {
		$store = array();
		$html  = preg_replace_callback( '#<(pre|textarea|script|style|svg)\b[^>]*>.*?</\1>#is', function ( $m ) use ( &$store ) {
			$key           = 'SCCBPROTECT' . count( $store ) . 'X';
			$store[ $key ] = $m[0];
			return $key;
		}, $html );
		$html = preg_replace_callback( '#<[a-zA-Z!/][^>]*>#s', function ( $m ) { return preg_replace( '#\s+#', ' ', $m[0] ); }, $html ); // single-line tag attributes
		$html = preg_replace( '#\s+>#', '>', $html );          // tidy "<tag … >"
		$html = preg_replace( '#>\s+<#', '><', trim( $html ) ); // collapse inter-tag whitespace
		$html = sc_code_block_indent_html( $html );
		if ( $store ) { $html = str_replace( array_keys( $store ), array_values( $store ), $html ); }
		return $html;
	}
}

// "Render as Code" — show the markup instead of running it. The value is (optionally) beautified,
// then HTML-escaped (so tags display literally), newlines encoded as &#10; (immune to wpautop/nl2br
// turning them into <br> inside the <pre>), and wrapped in a Prism-ready <pre><code class="language-*">.
// The stored att stays RAW HTML, so re-opening the builder shows editable markup — not pre-escaped
// entities that get mangled on save (the failure mode of hand-pasting escaped code into a block).
$render_as_code = ! empty( $atts['render_as_code'] );

if ( $render_as_code ) {
	$code_value = (string) $atts['code'];

	// Language: explicit pick, or auto-detect from the code.
	$lang = ! empty( $atts['code_language'] ) ? strtolower( $atts['code_language'] ) : 'auto';
	if ( $lang === 'auto' || $lang === '' ) { $lang = sc_code_block_detect_language( $code_value ); }
	$lang = preg_replace( '/[^a-z0-9-]/', '', $lang );
	if ( $lang === '' ) { $lang = 'markup'; }

	// Beautify (markup only — the indenter is HTML-aware). Default ON.
	$beautify = ! isset( $atts['beautify'] ) || ! empty( $atts['beautify'] );
	if ( $beautify && $lang === 'markup' ) { $code_value = sc_code_block_beautify_html( $code_value ); }

	$escaped = str_replace( "\n", '&#10;', htmlspecialchars( $code_value, ENT_QUOTES ) );
	// The Prism `language-*` class lives on <code> (where Prism reads the grammar and where the
	// highlighted .token children sit). We deliberately DON'T repeat it on the <pre> — Prism adds it
	// to the <pre> itself at runtime for its theme styling, so emitting it twice is redundant.
	$block   = '<pre class="code-block__pre"><code class="language-' . esc_attr( $lang ) . '">' . $escaped . '</code></pre>';
}

?>

<?php if ( ! empty( $atts['code'] ) ) : ?>
    <?php if ( $render_as_code ) : ?>
        <?php if ( $needs_wrapper ) : ?>
            <div <?php echo fw_attr_to_html( $attr ); ?>><?php echo $block; ?></div>
        <?php else : ?>
            <?php echo $block; ?>
        <?php endif; ?>
    <?php elseif ( $needs_wrapper ) : ?>
        <div <?php echo fw_attr_to_html( $attr ); ?>>
            <?php echo do_shortcode( $atts['code'] ); ?>
        </div>
    <?php else : ?>
        <?php echo do_shortcode( $atts['code'] ); ?>
    <?php endif; ?>
<?php endif; ?>
