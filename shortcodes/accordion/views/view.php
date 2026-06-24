<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 */

$icon_style    = ! empty( $atts['icon_style'] )    ? $atts['icon_style']    : 'plus-minus';
$icon_position = ! empty( $atts['icon_position'] )  ? $atts['icon_position']  : 'left';
$initially_open = ! empty( $atts['initially_open'] ) ? $atts['initially_open'] : 'first';

// Title heading level — picked from the Layout tab. Strict whitelist so a
// stale / corrupted saved value can never inject an arbitrary tag name.
$title_tag = ( isset( $atts['title_tag'] ) && in_array( $atts['title_tag'], array( 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) )
    ? $atts['title_tag']
    : 'h3';
$collapsible   = ( ! empty( $atts['collapsible'] )   && $atts['collapsible'] === 'yes' );
$multiple_open = ( ! empty( $atts['multiple_open'] ) && $atts['multiple_open'] === 'yes' );

// New options (2.7.99): hash linking, expand/collapse-all controls,
// title alignment. (Stagger Item Reveal was removed in 2.7.106 — when an
// Animations-tab effect is enabled, the cascade across items now happens
// automatically; see the cascade block below `sc_build_wrapper_attr`.)
$hash_linking            = ( ! isset( $atts['hash_linking'] )            || $atts['hash_linking']            === 'yes' );
$show_expand_collapse    = ( ! empty( $atts['show_expand_collapse_all'] ) && $atts['show_expand_collapse_all'] === 'yes' );
$title_alignment         = isset( $atts['title_alignment'] ) && in_array( $atts['title_alignment'], array( 'left', 'center', 'right' ), true )
    ? $atts['title_alignment']
    : 'left';

$numbering_style    = fw_akg( 'numbering/style',           $atts, 'none' );
$numbering_template = fw_akg( 'numbering/custom/template', $atts, 'Q{n}' );
$numbering_start    = (int) fw_akg( 'numbering_start',     $atts, 1 );

// Custom-icon inputs (only consulted when $icon_style === 'custom').
// Defensive fallbacks to '+' / '−' so cleared text fields + no image won't render blank.
$icon_closed_image = ! empty( $atts['icon_closed_image']['url'] ) ? $atts['icon_closed_image']['url'] : '';
$icon_open_image   = ! empty( $atts['icon_open_image']['url'] )   ? $atts['icon_open_image']['url']   : '';
$icon_closed_text  = ( isset( $atts['icon_closed_text'] ) && $atts['icon_closed_text'] !== '' ) ? $atts['icon_closed_text'] : '+';
$icon_open_text    = ( isset( $atts['icon_open_text'] )   && $atts['icon_open_text']   !== '' ) ? $atts['icon_open_text']   : '−';

if ( ! function_exists( 'fw_sc_accordion_int_to_alpha' ) ) {
    /**
     * Excel-style alpha index: 1=a/A, 26=z/Z, 27=aa/AA, 28=ab/AB, ...
     * Clamps n<1 to 1 so non-positive inputs still produce a letter.
     */
    function fw_sc_accordion_int_to_alpha( $n, $upper = false ) {
        if ( $n < 1 ) { $n = 1; }
        $result = '';
        while ( $n > 0 ) {
            $r      = ( $n - 1 ) % 26;
            $result = chr( 65 + $r ) . $result;
            $n      = intdiv( $n - 1, 26 );
        }
        return $upper ? $result : strtolower( $result );
    }
}

if ( ! function_exists( 'fw_sc_accordion_int_to_roman' ) ) {
    /**
     * Returns the Roman numeral form of a positive integer. Clamps n<1 to 1.
     */
    function fw_sc_accordion_int_to_roman( $n ) {
        if ( $n < 1 ) { $n = 1; }
        $map = array(
            1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD',
            100  => 'C', 90  => 'XC', 50  => 'L', 40  => 'XL',
            10   => 'X', 9   => 'IX', 5   => 'V', 4   => 'IV',
            1    => 'I',
        );
        $result = '';
        foreach ( $map as $v => $sym ) {
            while ( $n >= $v ) {
                $result .= $sym;
                $n      -= $v;
            }
        }
        return $result;
    }
}

if ( ! function_exists( 'fw_sc_accordion_format_number' ) ) {
    /**
     * Render the numbering label for one accordion item.
     *
     * @param string $style    Numbering style key from options.
     * @param string $template Custom template string (only used when $style is 'custom').
     * @param int    $index    Zero-based item index.
     * @param int    $start    The number assigned to the first item (default 1).
     * @return string          Empty string when $style is 'none'.
     */
    function fw_sc_accordion_format_number( $style, $template, $index, $start ) {
        $n = (int) $start + (int) $index;
        switch ( $style ) {
            case 'none':
                return '';
            case 'decimal':
                return (string) $n;
            case 'decimal-leading-zero':
                return ( $n >= 0 && $n < 10 ) ? '0' . $n : (string) $n;
            case 'lower-alpha':
                return fw_sc_accordion_int_to_alpha( $n, false );
            case 'upper-alpha':
                return fw_sc_accordion_int_to_alpha( $n, true );
            case 'lower-roman':
                return strtolower( fw_sc_accordion_int_to_roman( $n ) );
            case 'upper-roman':
                return fw_sc_accordion_int_to_roman( $n );
            case 'q-prefix':
                return 'Q' . $n;
            case 'custom':
                if ( $template === '' || $template === null ) {
                    return (string) $n;
                }
                return strtr( $template, array(
                    '{n}'  => (string) $n,
                    '{0n}' => ( $n >= 0 && $n < 10 ) ? '0' . $n : (string) $n,
                    '{a}'  => fw_sc_accordion_int_to_alpha( $n, false ),
                    '{A}'  => fw_sc_accordion_int_to_alpha( $n, true ),
                    '{i}'  => strtolower( fw_sc_accordion_int_to_roman( $n ) ),
                    '{I}'  => fw_sc_accordion_int_to_roman( $n ),
                ) );
        }
        return '';
    }
}

// Per-element color picks — applied across all accordion items.
// sc_extract_styling_atts gives both preset classes AND compact-picker
// custom-hex inline-style fragments for each field.
$tab_title_styling   = sc_extract_styling_atts( $atts, array( 'tab_title_color' ) );
$tab_content_styling = sc_extract_styling_atts( $atts, array( 'tab_content_color' ) );
$title_bg_styling    = sc_extract_styling_atts( $atts, array( 'title_bg_color' ) );
$content_bg_styling  = sc_extract_styling_atts( $atts, array( 'content_bg_color' ) );
$icon_closed_styling = sc_extract_styling_atts( $atts, array( 'icon_closed_color' ) );
$icon_open_styling   = sc_extract_styling_atts( $atts, array( 'icon_open_color' ) );
$tab_title_extras   = $tab_title_styling['classes'];
$tab_content_extras = $tab_content_styling['classes'];
$title_bg_extras    = $title_bg_styling['classes'];
$content_bg_extras  = $content_bg_styling['classes'];
$icon_closed_extras = $icon_closed_styling['classes'];
$icon_open_extras   = $icon_open_styling['classes'];

// Compose inline-style fragments for the per-element targets. Combined
// title-bar style (text color + bg) goes on the <h3>; content style
// (text color + bg) goes on the content panel. Icon styles route through
// CSS variables on the wrapper further below.
$tab_title_inline_parts = array_merge( $tab_title_styling['styles'], $title_bg_styling['styles'] );
$tab_content_inline_parts = array_merge( $tab_content_styling['styles'], $content_bg_styling['styles'] );
$tab_title_inline_style   = $tab_title_inline_parts   ? implode( '; ', $tab_title_inline_parts )   : '';
$tab_content_inline_style = $tab_content_inline_parts ? implode( '; ', $tab_content_inline_parts ) : '';

// Inner-element class lists.
// Note: tab_title_color applies to the <h3 class="accordion-title"> bar
// (so the whole title row takes the color, not just the inner text span).
$tab_title_class      = trim( implode( ' ', $tab_title_extras ) );        // appended to the <h3 class="accordion-title"> below
$tab_title_text_class = 'accordion-title-text';                            // inner text span keeps its plain class
$title_bg_class       = trim( implode( ' ', $title_bg_extras ) );
$tab_content_color    = trim( implode( ' ', $tab_content_extras ) );
$content_bg_class     = trim( implode( ' ', $content_bg_extras ) );
$tab_content_class    = trim( 'accordion-content ' . $tab_content_color . ' ' . $content_bg_class );
$icon_closed_class    = trim( 'accordion-icon-state-closed' );             // pure default — color is now routed via CSS variables, not via a class on the state span
$icon_open_class      = trim( 'accordion-icon-state-open' );

// Icon Color — route via CSS custom properties on the wrapper so the
// pseudo-element icons (plus-minus / chevron / arrow / etc.) pick up the
// color through `currentColor`. The Custom-icon state spans also inherit
// from `.accordion-icon`, so the same vars cover both modes.
// `sc_color_field` stores values like `text-red` — strip the kind prefix
// to derive the slug (`red`) for `var(--color-{slug})`.
$strip_kind_prefix = function ( $classes, $kind ) {
    if ( empty( $classes ) ) {
        return '';
    }
    $cls = (string) reset( $classes );
    $pfx = $kind . '-';
    return ( strpos( $cls, $pfx ) === 0 ) ? substr( $cls, strlen( $pfx ) ) : '';
};
$icon_closed_slug = $strip_kind_prefix( $icon_closed_extras, 'text' );
$icon_open_slug   = $strip_kind_prefix( $icon_open_extras,   'text' );

// Compact-picker custom-hex picks for the icon-state colors arrive here as
// inline-style fragments like `color: #abc123` (kind='text' inference). For
// the CSS-var pathway we just need the hex itself; pull it out so we can
// inline it as the `--ws-icon-*-color` value instead of building a
// `var(--color-{slug})` lookup.
$extract_hex = function ( array $styles ) {
    if ( empty( $styles ) ) { return ''; }
    $first = (string) reset( $styles );
    if ( strpos( $first, ':' ) === false ) { return ''; }
    return trim( substr( $first, strpos( $first, ':' ) + 1 ) );
};
$icon_closed_hex = $extract_hex( $icon_closed_styling['styles'] );
$icon_open_hex   = $extract_hex( $icon_open_styling['styles'] );

$wrapper_style_parts = array();
if ( $icon_closed_hex !== '' ) {
    $wrapper_style_parts[] = '--ws-icon-closed-color:' . $icon_closed_hex;
} elseif ( $icon_closed_slug !== '' ) {
    $wrapper_style_parts[] = '--ws-icon-closed-color:var(--color-' . sanitize_html_class( $icon_closed_slug ) . ')';
}
if ( $icon_open_hex !== '' ) {
    $wrapper_style_parts[] = '--ws-icon-open-color:' . $icon_open_hex;
} elseif ( $icon_open_slug !== '' ) {
    $wrapper_style_parts[] = '--ws-icon-open-color:var(--color-' . sanitize_html_class( $icon_open_slug ) . ')';
}
if ( ! empty( $wrapper_style_parts ) ) {
    $atts['css_style'] = trim( ( isset( $atts['css_style'] ) ? $atts['css_style'] . ';' : '' ) . implode( ';', $wrapper_style_parts ) );
}

$atts['base_class']       = 'accordion';
$atts['unique_id_prefix'] = 'ac-';

$attr = sc_build_wrapper_attr( $atts );

/*
|--------------------------------------------------------------------------
| Cascade the Animations-tab effect across items (automatic when enabled)
|--------------------------------------------------------------------------
| When the editor picks an animation in the Animations tab, the shared
| `sc_build_wrapper_attr` filter writes `sc-anim-pending` + `data-sc-anim`
| + a `--animate-delay` CSS var onto the wrapper. For accordions we want
| each item to animate in turn, not the whole wrapper at once. So:
|
|   1. Detect the animation on the wrapper after the filter has run.
|   2. Capture the animation class string and the base delay (if any).
|   3. STRIP all three (sc-anim-pending class, data-sc-anim attr,
|      --animate-delay style) off the wrapper.
|   4. Below, in the items loop, attach those same hooks to each
|      `.accordion-item` with a per-item delay of base + 0.2s × index.
|
| The shared sc-animations.js then watches every item independently and
| triggers the animation as each scrolls into view; combined with the
| staggered delay, items reveal one after another.
*/
$item_anim_classes = '';
$item_anim_replay  = false;
$item_anim_base_delay = 0.0;
$cascade_animation = false;

if ( isset( $attr['data-sc-anim'] ) && $attr['data-sc-anim'] !== '' ) {
    $cascade_animation   = true;
    $item_anim_classes   = (string) $attr['data-sc-anim'];
    $item_anim_replay    = isset( $attr['data-sc-anim-replay'] ) && $attr['data-sc-anim-replay'] === '1';

    // Pull --animate-delay out of the wrapper's inline style, if present.
    $wrapper_style = isset( $attr['style'] ) ? (string) $attr['style'] : '';
    if ( preg_match( '/--animate-delay:\s*([0-9.]+)s/', $wrapper_style, $m ) ) {
        $item_anim_base_delay = (float) $m[1];
        $wrapper_style = preg_replace( '/--animate-delay:\s*[0-9.]+s;?\s*/', '', $wrapper_style );
    }
    $attr['style'] = trim( $wrapper_style );
    if ( $attr['style'] === '' ) { unset( $attr['style'] ); }

    // Drop the wrapper-level animation hooks — items take over below.
    unset( $attr['data-sc-anim'] );
    unset( $attr['data-sc-anim-replay'] );
    $attr['class'] = trim( preg_replace( '/\bsc-anim-pending\b/', '', (string) $attr['class'] ) );
    $attr['class'] = preg_replace( '/\s+/', ' ', $attr['class'] );
}

$wrapper_classes = [
    'accordion-icon-' . esc_attr( $icon_style ),
    'accordion-icon-' . esc_attr( $icon_position ),
    'accordion-title-align-' . esc_attr( $title_alignment ),
];

if ( $numbering_style !== 'none' ) {
    $wrapper_classes[] = 'accordion-has-numbering';
}

if ( ! empty( $attr['class'] ) ) {
    $attr['class'] .= ' ' . implode( ' ', $wrapper_classes );
} else {
    $attr['class'] = implode( ' ', $wrapper_classes );
}

$attr['data-icon-style']    = esc_attr( $icon_style );
$attr['data-icon-position'] = esc_attr( $icon_position );
$attr['data-initially-open'] = esc_attr( $initially_open );
$attr['data-collapsible']   = $collapsible ? 'true' : 'false';
$attr['data-multiple-open'] = $multiple_open ? 'true' : 'false';
$attr['data-hash-linking']  = $hash_linking ? 'true' : 'false';

$tabs = fw_akg( 'tabs', $atts, array() );

// Item Spacing — Bootstrap mb-{n} class applied to every .accordion-item
// except the last so the gap between items is consistent without adding a
// trailing margin after the final entry.
$item_spacing_class = isset( $atts['item_spacing'] ) ? sanitize_html_class( (string) $atts['item_spacing'] ) : '';
$tab_count          = count( $tabs );

// A — Per-item "Open by Default" picks. If any item has the per-item flag
// set, those picks fully override the shortcode-level Initially Open. When
// Multiple Open is OFF, only the FIRST item flagged stays open.
$has_per_item_open  = false;
foreach ( $tabs as $t ) {
    if ( isset( $t['is_open'] ) && $t['is_open'] === 'yes' ) {
        $has_per_item_open = true;
        break;
    }
}
$single_open_chosen = false; // tracks whether the single-open slot has been claimed in !multiple_open mode

if ( empty( $attr['id'] ) ) {
    $attr['id'] = wp_unique_id( 'accordion-' );
}
$accordion_id = $attr['id'];
?>

<?php if ( ! empty( $tabs ) ) : ?>
        <div <?php echo fw_attr_to_html( $attr ); ?> role="tablist" aria-multiselectable="<?php echo $multiple_open ? 'true' : 'false'; ?>">
                <?php if ( $show_expand_collapse ) : ?>
                        <div class="accordion-controls" aria-hidden="true">
                                <button type="button" class="accordion-controls__btn accordion-controls__btn--expand" data-accordion-action="expand-all"><?php echo esc_html__( 'Expand All', 'fw' ); ?></button>
                                <button type="button" class="accordion-controls__btn accordion-controls__btn--collapse" data-accordion-action="collapse-all"><?php echo esc_html__( 'Collapse All', 'fw' ); ?></button>
                        </div>
                <?php endif; ?>
                <?php foreach ( $tabs as $index => $tab ) :
                    $panel_id  = $accordion_id . '-panel-' . $index;
                    $header_id = $accordion_id . '-header-' . $index;

                    $item_open_flag = ( isset( $tab['is_open'] ) && $tab['is_open'] === 'yes' );

                    if ( $has_per_item_open ) {
                        // Per-item picks fully override the shortcode-level setting.
                        // In single-open mode, only the FIRST flagged item wins; later flags are ignored.
                        if ( $item_open_flag && ( $multiple_open || ! $single_open_chosen ) ) {
                            $is_open            = true;
                            $single_open_chosen = true;
                        } else {
                            $is_open = false;
                        }
                    } else {
                        // No per-item flags — fall back to the shortcode-level Initially Open.
                        $is_open = false;
                        if ( $initially_open === 'all' ) {
                            $is_open = true;
                        } elseif ( $initially_open === 'first' && $index === 0 ) {
                            $is_open = true;
                        }
                    }

                    $number_label = fw_sc_accordion_format_number( $numbering_style, $numbering_template, $index, $numbering_start );

                    // Item Spacing: apply the mb-* preset to every item except the last.
                    $is_last_item = ( $index === $tab_count - 1 );

                    // Build per-item class + inline-style + extra attrs.
                    // Two orthogonal sources contribute:
                    //   (1) Item Spacing → `mb-*` class on all but last
                    //   (2) Cascaded Animations-tab effect → sc-anim-pending
                    //       class, data-sc-anim attr, direct animation-delay
                    //       inline style per item.
                    $item_classes_arr = array( 'accordion-item' );
                    if ( $item_spacing_class !== '' && ! $is_last_item ) {
                        $item_classes_arr[] = $item_spacing_class;
                    }
                    if ( $cascade_animation ) {
                        $item_classes_arr[] = 'sc-anim-pending';
                    }
                    $item_class = implode( ' ', $item_classes_arr );

                    $item_style_parts = array();
                    if ( $cascade_animation ) {
                        // IMPORTANT: set `animation-delay` directly rather than
                        // via the `--animate-delay` CSS variable. Animate.css
                        // only reads `var(--animate-delay)` from its
                        // `.animate__delay-Ns` utility classes — and we don't
                        // add those — so setting the variable alone has no
                        // effect on `.animate__animated`. The direct property
                        // (+ webkit prefix for Safari) lands on the same
                        // animation cascade.
                        $item_delay = $item_anim_base_delay + ( 0.1 * (float) $index );
                        // Trim trailing zeros so "1.50s" → "1.5s", "0.00s" → "0s".
                        $item_delay_str = rtrim( rtrim( number_format( $item_delay, 2, '.', '' ), '0' ), '.' );
                        if ( $item_delay_str === '' ) { $item_delay_str = '0'; }
                        $item_style_parts[] = 'animation-delay:' . $item_delay_str . 's';
                        $item_style_parts[] = '-webkit-animation-delay:' . $item_delay_str . 's';
                    }
                    $item_inline_style = ! empty( $item_style_parts )
                        ? ' style="' . esc_attr( implode( ';', $item_style_parts ) ) . '"'
                        : '';

                    $item_extra_attrs = '';
                    if ( $cascade_animation ) {
                        $item_extra_attrs .= ' data-sc-anim="' . esc_attr( $item_anim_classes ) . '"';
                        if ( $item_anim_replay ) {
                            $item_extra_attrs .= ' data-sc-anim-replay="1"';
                        }
                    }
                ?>
                        <div class="<?php echo esc_attr( $item_class ); ?>"<?php echo $item_inline_style; ?><?php echo $item_extra_attrs; ?>>
                        <?php
                        // Title bar class composition: base + per-instance Title Color
                        // (text-{slug}) + per-instance Title Background (bg-{slug}) +
                        // optional ui-state-active.
                        $title_bar_classes = array_filter( array(
                            'accordion-title',
                            $is_open ? 'ui-state-active' : '',
                            $tab_title_class,
                            $title_bg_class,
                        ) );
                        ?>
                        <<?php echo esc_attr( $title_tag ); ?> class="<?php echo esc_attr( implode( ' ', $title_bar_classes ) ); ?>"<?php echo $tab_title_inline_style !== '' ? ' style="' . esc_attr( $tab_title_inline_style ) . '"' : ''; ?>
                            id="<?php echo esc_attr( $header_id ); ?>"
                            role="tab"
                            aria-controls="<?php echo esc_attr( $panel_id ); ?>"
                            aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
                            tabindex="0">
                                <span class="accordion-icon" aria-hidden="true">
                                        <?php if ( $icon_style === 'custom' ) : ?>
                                                <span class="<?php echo esc_attr( $icon_closed_class ); ?>">
                                                        <?php if ( $icon_closed_image !== '' ) : ?>
                                                                <img src="<?php echo esc_url( $icon_closed_image ); ?>" alt="">
                                                        <?php else : ?>
                                                                <?php echo esc_html( $icon_closed_text ); ?>
                                                        <?php endif; ?>
                                                </span>
                                                <span class="<?php echo esc_attr( $icon_open_class ); ?>">
                                                        <?php if ( $icon_open_image !== '' ) : ?>
                                                                <img src="<?php echo esc_url( $icon_open_image ); ?>" alt="">
                                                        <?php else : ?>
                                                                <?php echo esc_html( $icon_open_text ); ?>
                                                        <?php endif; ?>
                                                </span>
                                        <?php endif; ?>
                                </span>
                                <?php if ( $number_label !== '' ) : ?>
                                        <span class="accordion-number" aria-hidden="true"><?php echo esc_html( $number_label ); ?></span>
                                <?php endif; ?>
                                <span class="<?php echo esc_attr( $tab_title_text_class ); ?>"><?php echo esc_html( $tab['tab_title'] ); ?></span>
                        </<?php echo esc_attr( $title_tag ); ?>>
                        <div class="<?php echo esc_attr( $tab_content_class ); ?>"
                             id="<?php echo esc_attr( $panel_id ); ?>"
                             role="tabpanel"
                             aria-labelledby="<?php echo esc_attr( $header_id ); ?>"
                             aria-hidden="<?php echo $is_open ? 'false' : 'true'; ?>"
                             style="display:<?php echo $is_open ? 'block' : 'none'; ?>;<?php echo $tab_content_inline_style !== '' ? ' ' . esc_attr( $tab_content_inline_style ) . ';' : ''; ?>">
                                <?php echo do_shortcode( $tab['tab_content'] ); ?>
                        </div>
                        </div><!-- /.accordion-item -->
                <?php endforeach; ?>
        </div>
        <?php
        // FAQ Rich Snippet — emit FAQPage JSON-LD when enabled. Each item title
        // becomes a Question, its content (shortcodes expanded, tags stripped)
        // the accepted Answer. Skipped on empty Q/A pairs.
        $faq_schema = ( ! empty( $atts['faq_schema'] ) && $atts['faq_schema'] === 'yes' );
        if ( $faq_schema ) :
            $faq_entities = array();
            foreach ( $tabs as $tab ) {
                $q = isset( $tab['tab_title'] ) ? trim( wp_strip_all_tags( (string) $tab['tab_title'] ) ) : '';
                $a_raw = isset( $tab['tab_content'] ) ? (string) $tab['tab_content'] : '';
                $a = trim( wp_strip_all_tags( strip_shortcodes( do_shortcode( $a_raw ) ), true ) );
                $a = preg_replace( '/\s+/u', ' ', $a );
                if ( $q === '' || $a === '' ) { continue; }
                $faq_entities[] = array(
                    '@type'          => 'Question',
                    'name'           => $q,
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text'  => $a,
                    ),
                );
            }
            if ( ! empty( $faq_entities ) ) {
                $faq_ld = array(
                    '@context'   => 'https://schema.org',
                    '@type'      => 'FAQPage',
                    'mainEntity' => $faq_entities,
                );
                echo '<script type="application/ld+json">' . wp_json_encode( $faq_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
            }
        endif;
        ?>
<?php endif; ?>
