<?php
/**
 * Text Expander shortcode — render template.
 *
 * Flat-DOM rendering: every paragraph is a direct child of the single
 * wrapper <div>. Visibility is driven by a `data-expander-hidden="true"`
 * attribute on individual elements (paragraphs and inline spans) plus
 * the wrapper class `.fw-text-expander--open` which scopes the CSS rule.
 *
 * Three orthogonal options drive layout:
 *   show_btn_position : inline | block_left | block_center | block_right
 *   hide_btn_position : inherit | inline | block_left | block_center | block_right
 *   merge_boundary    : yes | no
 *
 * `native_details` (separate switch) renders as <details>/<summary> and
 * bypasses every other layout option.
 *
 * Two real <button> elements (Show + Hide) are emitted; CSS picks which
 * is visible based on the wrapper state. No JS button-moving, no inner
 * structural wrappers around paragraphs.
 *
 * @package unysonplus\shortcodes\text_expander
 */

if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/* =====================================================================
 *  Helpers (guarded so re-includes don't redeclare them).
 * =================================================================== */

if ( ! function_exists( 'fw_text_expander_parse_paragraphs' ) ) {
    /**
     * Tokenise an HTML string into an ordered list of paragraph tokens.
     * Each token preserves the original opening <p> tag (with all its
     * attributes), inner HTML, and closing tag separately so we can mutate
     * each independently without losing author-supplied classes/ids.
     *
     * Plain-text input (no <p> at all) becomes a single implicit paragraph
     * so the flat-DOM model still applies.
     */
    function fw_text_expander_parse_paragraphs( $html ) {
        if ( $html === '' || $html === null ) {
            return [];
        }
        if ( preg_match_all( '/(<p\b[^>]*>)(.*?)(<\/p>)/is', $html, $m, PREG_SET_ORDER ) ) {
            $out = [];
            foreach ( $m as $match ) {
                $out[] = [
                    'open'  => $match[1],
                    'inner' => $match[2],
                    'close' => $match[3],
                ];
            }
            return $out;
        }
        $stripped = trim( $html );
        if ( $stripped !== '' ) {
            return [ [ 'open' => '<p>', 'inner' => $stripped, 'close' => '</p>' ] ];
        }
        return [];
    }
}

if ( ! function_exists( 'fw_text_expander_mark_hidden' ) ) {
    /**
     * Inject `data-expander-hidden="true"` into a paragraph token's opening
     * tag, preserving every attribute that was already there.
     */
    function fw_text_expander_mark_hidden( $tok ) {
        $tok['open'] = preg_replace(
            '/^<p\b/i',
            '<p data-expander-hidden="true"',
            $tok['open'],
            1
        );
        return $tok;
    }
}

if ( ! function_exists( 'fw_text_expander_add_class' ) ) {
    /**
     * Append CSS classes to a paragraph token's opening tag. If the token
     * already has a `class="..."` attribute, merge into it; otherwise add a
     * fresh class attribute. Used by the per-element color picks to color
     * visible / hidden paragraphs independently.
     */
    function fw_text_expander_add_class( $tok, $extra ) {
        $extra = trim( (string) $extra );
        if ( $extra === '' ) {
            return $tok;
        }
        if ( preg_match( '/\bclass\s*=\s*"([^"]*)"/i', $tok['open'] ) ) {
            $tok['open'] = preg_replace_callback(
                '/\bclass\s*=\s*"([^"]*)"/i',
                function ( $m ) use ( $extra ) {
                    return 'class="' . trim( $m[1] . ' ' . $extra ) . '"';
                },
                $tok['open'],
                1
            );
        } else {
            $tok['open'] = preg_replace(
                '/^<p\b/i',
                '<p class="' . esc_attr( $extra ) . '"',
                $tok['open'],
                1
            );
        }
        return $tok;
    }
}

if ( ! function_exists( 'fw_text_expander_append_html_simple' ) ) {
    /**
     * Append HTML to a paragraph token's inner (immutable return).
     * Used to inject buttons / bridge spans into specific paragraphs.
     */
    function fw_text_expander_append_html_simple( $tok, $html ) {
        $tok['inner'] .= $html;
        return $tok;
    }
}

if ( ! function_exists( 'fw_text_expander_inline_text' ) ) {
    /**
     * Reduce HTML to a clean inline string by stripping every <p> wrapper.
     * Used only for the native <details> summary.
     */
    function fw_text_expander_inline_text( $html ) {
        if ( $html === '' || $html === null ) {
            return '';
        }
        $html = preg_replace( '/<\/p\s*>/i', ' ', $html );
        $html = preg_replace( '/<p\b[^>]*>/i', '', $html );
        return trim( preg_replace( '/\s{2,}/', ' ', $html ) );
    }
}

/* =====================================================================
 *  1. Read attributes.
 * =================================================================== */

// Per-element color picks (kept off the wrapper). sc_extract_styling_atts
// returns both preset classes AND compact-picker custom-hex inline styles.
$visible_styling  = sc_extract_styling_atts( $atts, array( 'visible_color' ) );
$hidden_styling   = sc_extract_styling_atts( $atts, array( 'hidden_color' ) );
$btn_show_styling = sc_extract_styling_atts( $atts, array( 'btn_show_color' ) );
$btn_hide_styling = sc_extract_styling_atts( $atts, array( 'btn_hide_color' ) );

$visible_class_extra  = implode( ' ', $visible_styling['classes'] );
$hidden_class_extra   = implode( ' ', $hidden_styling['classes'] );
$btn_show_class_extra = implode( ' ', $btn_show_styling['classes'] );
$btn_hide_class_extra = implode( ' ', $btn_hide_styling['classes'] );

$visible_style_extra  = $visible_styling['styles']  ? implode( '; ', $visible_styling['styles'] )  : '';
$hidden_style_extra   = $hidden_styling['styles']   ? implode( '; ', $hidden_styling['styles'] )   : '';
$btn_show_style_extra = $btn_show_styling['styles'] ? implode( '; ', $btn_show_styling['styles'] ) : '';
$btn_hide_style_extra = $btn_hide_styling['styles'] ? implode( '; ', $btn_hide_styling['styles'] ) : '';

$atts['base_class']       = 'fw-text-expander';
$atts['unique_id_prefix'] = 'te-';

$visible_content = ! empty( $atts['visible_content'] ) ? $atts['visible_content'] : '';
$hidden_content  = ! empty( $atts['hidden_content'] )  ? $atts['hidden_content']  : '';
$btn_show        = ! empty( $atts['btn_show'] )        ? $atts['btn_show']        : __( 'Show More', 'fw' );
$btn_hide        = ! empty( $atts['btn_hide'] )        ? $atts['btn_hide']        : __( 'Show Less', 'fw' );
$initially_open  = ( ! empty( $atts['initially_open'] ) && $atts['initially_open'] === 'yes' );
$btn_color       = ! empty( $atts['btn_color'] )       ? $atts['btn_color']       : '';
$toggle_icon     = ! empty( $atts['toggle_icon'] )     ? $atts['toggle_icon']     : 'none';

$show_btn_position = ! empty( $atts['show_btn_position'] ) ? $atts['show_btn_position'] : 'inline';
$hide_btn_position = ! empty( $atts['hide_btn_position'] ) ? $atts['hide_btn_position'] : 'inherit';
$merge_boundary    = ! empty( $atts['merge_boundary'] )    ? $atts['merge_boundary']    : 'yes';

$native_details = ( ! empty( $atts['native_details'] ) && $atts['native_details'] === 'yes' );
$show_ellipsis  = ( ! empty( $atts['show_ellipsis'] )  && $atts['show_ellipsis']  === 'yes' );
$count_mode     = ! empty( $atts['count_mode'] )       ? $atts['count_mode'] : 'none';
$click_anywhere = ( ! empty( $atts['click_anywhere'] ) && $atts['click_anywhere'] === 'yes' );

$allowed_positions = [ 'inline', 'block_left', 'block_center', 'block_right' ];
if ( ! in_array( $show_btn_position, $allowed_positions, true ) ) {
    $show_btn_position = 'inline';
}
$allowed_hide_positions = array_merge( [ 'inherit' ], $allowed_positions );
if ( ! in_array( $hide_btn_position, $allowed_hide_positions, true ) ) {
    $hide_btn_position = 'inherit';
}

$hide_effective = ( $hide_btn_position === 'inherit' ) ? $show_btn_position : $hide_btn_position;
$show_inline    = ( $show_btn_position === 'inline' );
$hide_inline    = ( $hide_effective === 'inline' );
$merge          = ( $merge_boundary === 'yes' );

$align_from = function ( $pos ) {
    if ( strpos( $pos, 'block_' ) === 0 ) {
        return substr( $pos, strlen( 'block_' ) );
    }
    return '';
};
$show_align = $align_from( $show_btn_position );
$hide_align = $align_from( $hide_effective );

/* =====================================================================
 *  2. Validate the button colour.
 * =================================================================== */

$btn_color_safe = '';
if ( $btn_color && preg_match( '/^(#[0-9a-fA-F]{3,8}|rgba?\([\d.,\s%]+\))$/', $btn_color ) ) {
    $btn_color_safe = $btn_color;
}
$btn_style = $btn_color_safe ? ' style="color:' . esc_attr( $btn_color_safe ) . ';"' : '';

/* =====================================================================
 *  3. Wrapper id (also serves as aria-controls target — a single panel).
 * =================================================================== */

// Match the unique-class format sc_build_wrapper_attr emits for every
// other shortcode (`te-{8chars}`, see shortcode-build-helper.php).
// The id reuses that same string so aria-controls has a valid target.
$unique_base = ! empty( $atts['unique_id'] )
	? substr( sanitize_key( strtolower( trim( $atts['unique_id'] ) ) ), 0, 8 )
	: wp_unique_id();
$wrapper_id  = sanitize_html_class( 'te-' . $unique_base );

$has_icon       = ( $toggle_icon !== 'none' ) && ! $native_details;
$btn_icon_class = $has_icon ? ' fw-text-expander--icon-' . sanitize_html_class( $toggle_icon ) : '';

/* =====================================================================
 *  4. Build wrapper attributes.
 * =================================================================== */

$attr = sc_build_wrapper_attr( $atts );

$wrapper_extra = [];
if ( $native_details ) {
    $wrapper_extra[] = 'fw-text-expander--native';
} else {
    $wrapper_extra[] = 'fw-text-expander--show-' . sanitize_html_class( $show_btn_position );
    $wrapper_extra[] = 'fw-text-expander--hide-' . sanitize_html_class( $hide_effective );
    if ( $merge ) {
        $wrapper_extra[] = 'fw-text-expander--merge';
    }
}
if ( $show_ellipsis )                          $wrapper_extra[] = 'fw-text-expander--ellipsis';
if ( $click_anywhere && ! $native_details )    $wrapper_extra[] = 'fw-text-expander--click-anywhere';
if ( $initially_open && ! $native_details )    $wrapper_extra[] = 'fw-text-expander--open';

$attr['class'] = ( ! empty( $attr['class'] ) ? $attr['class'] . ' ' : '' ) . esc_attr( implode( ' ', $wrapper_extra ) );
if ( empty( $attr['id'] ) ) {
    $attr['id'] = esc_attr( $wrapper_id );
}

if ( ! $native_details ) {
    if ( $count_mode !== 'none' ) {
        $attr['data-count-mode'] = sanitize_html_class( $count_mode );
    }
    if ( $click_anywhere ) {
        $attr['data-click-anywhere'] = '1';
    }
}

/* =====================================================================
 *  5. Button renderers (both Show and Hide are emitted; CSS picks which
 *     one is visible based on wrapper state). aria-controls points to
 *     the single wrapper element — the disclosure target.
 * =================================================================== */

$aria_controls = $attr['id'];

$render_button = function ( $role ) use (
    $btn_icon_class, $btn_show, $btn_hide, $initially_open,
    $aria_controls, $btn_style, $has_icon,
    $btn_show_class_extra, $btn_hide_class_extra
) {
    $label       = ( $role === 'show' ) ? $btn_show : $btn_hide;
    $variant     = ( $role === 'show' ) ? 'fw-text-expander__btn--show' : 'fw-text-expander__btn--hide';
    $color_extra = ( $role === 'show' ) ? $btn_show_class_extra : $btn_hide_class_extra;
    $expanded    = $initially_open ? 'true' : 'false';
    $extra_class = trim( $variant . $btn_icon_class . ( $color_extra !== '' ? ' ' . $color_extra : '' ) );
    ob_start();
    ?><button type="button"
            class="fw-text-expander__toggle <?php echo esc_attr( $extra_class ); ?>"
            data-role="<?php echo esc_attr( $role ); ?>"
            data-label="<?php echo esc_attr( $label ); ?>"
            aria-expanded="<?php echo esc_attr( $expanded ); ?>"
            aria-controls="<?php echo esc_attr( $aria_controls ); ?>"
            <?php echo $btn_style; ?>><?php if ( $has_icon ) : ?><span class="fw-text-expander__icon" aria-hidden="true"></span><?php endif; ?><span class="fw-text-expander__label"><?php echo esc_html( $label ); ?></span></button><?php
    return ob_get_clean();
};

$render_block_btn = function ( $role, $align ) use ( $render_button ) {
    $align_class = $align !== '' ? ' fw-text-expander__btn-wrap--' . sanitize_html_class( $align ) : '';
    $role_class  = ' fw-text-expander__btn-wrap--' . $role;
    return '<div class="fw-text-expander__btn-wrap'
        . esc_attr( $role_class . $align_class )
        . '">'
        . $render_button( $role )
        . '</div>';
};

/* =====================================================================
 *  6. Edge cases handled first.
 * =================================================================== */

if ( ! $visible_content && ! $hidden_content ) {
    return;
}

if ( $visible_content && ! $hidden_content && ! $native_details ) {
    echo do_shortcode( $visible_content );
    return;
}
?>

<?php if ( $native_details ) : /* ----------------- native --------------
 *  Native <details> / <summary>. Body paragraphs render flat under the
 *  <details> element (the browser is already flat-DOM-friendly here).
 * -------------------------------------------------------------------- */ ?>
    <?php
    $native_vis_class  = trim( 'fw-text-expander__last-visible ' . $visible_class_extra );
    $native_show_class = trim( 'fw-text-expander__label-show ' . $btn_show_class_extra );
    $native_hide_class = trim( 'fw-text-expander__label-hide ' . $btn_hide_class_extra );
    $native_hidden_attr = $hidden_class_extra !== '' ? ' class="' . esc_attr( $hidden_class_extra ) . '"' : '';
    ?>
    <details <?php echo fw_attr_to_html( $attr ); ?><?php if ( $initially_open ) echo ' open'; ?>>
        <summary class="fw-text-expander__summary">
            <?php if ( $visible_content ) : ?>
                <span class="<?php echo esc_attr( $native_vis_class ); ?>"><?php echo do_shortcode( fw_text_expander_inline_text( $visible_content ) ); ?></span>
            <?php endif; ?>
            <span class="<?php echo esc_attr( $native_show_class ); ?>"<?php echo $btn_style; ?>><?php echo esc_html( $btn_show ); ?></span>
            <span class="<?php echo esc_attr( $native_hide_class ); ?>"<?php echo $btn_style; ?>><?php echo esc_html( $btn_hide ); ?></span>
        </summary>
        <div<?php echo $native_hidden_attr; ?>><?php echo do_shortcode( $hidden_content ); ?></div>
    </details>

<?php else : /* ----------------- flat-DOM standard render -------------- */ ?>
    <?php
    $vis_tokens = fw_text_expander_parse_paragraphs( $visible_content );
    $hid_tokens = fw_text_expander_parse_paragraphs( $hidden_content );

    /* Wrap the LAST visible paragraph's inner content in a span so the
       ellipsis ::after pseudo-element lands at the end of the visible
       TEXT — not at the end of the whole <p>. Anything appended after
       this point (show button, bridge span, etc.) stays outside the
       wrapped span, so they appear AFTER the ellipsis visually. */
    if ( ! empty( $vis_tokens ) ) {
        $last_idx = count( $vis_tokens ) - 1;
        $vis_tokens[ $last_idx ]['inner'] =
            '<span class="fw-text-expander__last-visible">'
            . $vis_tokens[ $last_idx ]['inner']
            . '</span>';
    }

    /* Decide whether merge applies (needs at least one ¶ on each side). */
    $do_merge = $merge && ! empty( $vis_tokens ) && ! empty( $hid_tokens );

    /* If merge, peel off the first hidden token — it's going into the
       bridge inside the last visible token. */
    $bridge_hidden_inner = '';
    if ( $do_merge ) {
        $bridge_tok          = array_shift( $hid_tokens );
        $bridge_hidden_inner = $bridge_tok['inner'];
    }

    /* Tag every remaining hidden token with data-expander-hidden. */
    foreach ( $hid_tokens as $i => $tok ) {
        $hid_tokens[ $i ] = fw_text_expander_mark_hidden( $tok );
    }

    /* Per-element color picks: paint every visible <p> with the Visible Content
       Color, every hidden <p> with the Hidden Content Color. */
    if ( $visible_class_extra !== '' ) {
        foreach ( $vis_tokens as $i => $tok ) {
            $vis_tokens[ $i ] = fw_text_expander_add_class( $tok, $visible_class_extra );
        }
    }
    if ( $hidden_class_extra !== '' ) {
        foreach ( $hid_tokens as $i => $tok ) {
            $hid_tokens[ $i ] = fw_text_expander_add_class( $tok, $hidden_class_extra );
        }
    }

    /* Append show button + bridge content to the last visible token. The
       bridge content (hidden_first_inner) lives inside a span that itself
       carries data-expander-hidden so CSS hides only that span when
       collapsed, leaving the visible last paragraph readable. */
    if ( ! empty( $vis_tokens ) ) {
        $last_vis_idx  = count( $vis_tokens ) - 1;
        $append_buffer = '';

        if ( $show_inline ) {
            $append_buffer .= $render_button( 'show' );
        }

        if ( $do_merge ) {
            $bridge_class = $hidden_class_extra !== '' ? ' class="' . esc_attr( $hidden_class_extra ) . '"' : '';
            $append_buffer .= '<span data-expander-hidden="true"' . $bridge_class . '> ' . do_shortcode( $bridge_hidden_inner ) . '</span>';
        }

        /* If hide is inline AND hidden is single-paragraph (so it was
           entirely consumed by the bridge — no remaining hidden tokens),
           the hide button needs to live inside the bridge too. */
        if ( $do_merge && $hide_inline && empty( $hid_tokens ) ) {
            $append_buffer .= $render_button( 'hide' );
        }

        if ( $append_buffer !== '' ) {
            $vis_tokens[ $last_vis_idx ] = fw_text_expander_append_html_simple(
                $vis_tokens[ $last_vis_idx ],
                $append_buffer
            );
        }
    }

    /* If hide=inline AND there are remaining hidden tokens, append the
       hide button to the LAST hidden token's inner. */
    if ( $hide_inline && ! empty( $hid_tokens ) ) {
        $last_hid_idx = count( $hid_tokens ) - 1;
        $hid_tokens[ $last_hid_idx ] = fw_text_expander_append_html_simple(
            $hid_tokens[ $last_hid_idx ],
            $render_button( 'hide' )
        );
    }
    ?>
    <div <?php echo fw_attr_to_html( $attr ); ?>>

        <?php
        /* Emit every visible token in order. */
        foreach ( $vis_tokens as $tok ) {
            /* do_shortcode runs on the inner so nested shortcodes resolve. */
            echo $tok['open'] . do_shortcode( $tok['inner'] ) . $tok['close'];
        }

        /* Show button block-wrap (only when show is block_*). */
        if ( ! $show_inline ) {
            echo $render_block_btn( 'show', $show_align );
        }

        /* Emit every remaining hidden token in order. */
        foreach ( $hid_tokens as $tok ) {
            echo $tok['open'] . do_shortcode( $tok['inner'] ) . $tok['close'];
        }

        /* Hide button block-wrap (only when hide is block_*). */
        if ( ! $hide_inline ) {
            echo $render_block_btn( 'hide', $hide_align );
        }
        ?>

    </div>
<?php endif; ?>

