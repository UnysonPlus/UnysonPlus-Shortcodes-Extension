<?php
if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 */

/*
|--------------------------------------------------------------------------
| Enqueue icon-v2 frontend CSS (only when a picked icon will actually render)
|--------------------------------------------------------------------------
*/
if (
    empty( $atts['custom_icon'] ) &&
    ! empty( $atts['icon'] ) &&
    isset( fw()->backend->option_type( 'icon-v2' )->packs_loader )
) {
    fw()->backend->option_type( 'icon-v2' )->packs_loader->enqueue_pack_for_icon( $atts['icon'] );
}

/*f
|--------------------------------------------------------------------------
| Normalize incoming attributes
|--------------------------------------------------------------------------
*/
$style          = ! empty( $atts['style'] ) ? $atts['style'] : 'top-title';
$title_tag      = ! empty( $atts['title_tag'] ) ? $atts['title_tag'] : 'h3';
$allowed_tags   = [ 'h3', 'h4', 'h5', 'h6', 'span', 'p' ];
$title_tag      = in_array( $title_tag, $allowed_tags, true ) ? $title_tag : 'h3';
$mobile_stack   = ! empty( $atts['mobile_stack'] );
$custom_icon    = isset( $atts['custom_icon'] ) ? trim( (string) $atts['custom_icon'] ) : '';
$picked_icon    = ! empty( $atts['icon'] ) ? $atts['icon'] : null;
$title          = isset( $atts['title'] ) ? trim( (string) $atts['title'] ) : '';
$content        = isset( $atts['content'] ) ? (string) $atts['content'] : '';
$has_content    = $content !== '' && trim( wp_strip_all_tags( $content ) ) !== '';
$has_icon       = ( $custom_icon !== '' ) || ! empty( $picked_icon );

$box_link       = isset( $atts['box_link'] ) ? trim( (string) $atts['box_link'] ) : '';
$link_target    = ! empty( $atts['link_target'] );
$link_rel_value = isset( $atts['link_rel'] ) ? $atts['link_rel'] : 'sponsored';

/*
|--------------------------------------------------------------------------
| Alignment — emit Bootstrap text-* utility classes per element
|--------------------------------------------------------------------------
| Empty value ('') means "inherit the layout default" → no class emitted, so
| existing content is untouched. Icon alignment only applies to the block
| layouts (top-title, between-title-content); inline / stack layouts position
| the icon via flexbox and ignore it.
*/
// Maps the alignment value to a Bootstrap text-* utility. The fields now use the
// shared sc_alignment_field() picker (left/center/right); the legacy start/end keys
// are kept so content saved before the switch still resolves.
$align_map = [
    'left'   => 'text-start',
    'start'  => 'text-start',
    'center' => 'text-center',
    'right'  => 'text-end',
    'end'    => 'text-end',
];
$icon_align          = isset( $atts['icon_align'] )    ? (string) $atts['icon_align']    : '';
$title_align         = isset( $atts['title_align'] )   ? (string) $atts['title_align']   : '';
$content_align       = isset( $atts['content_align'] ) ? (string) $atts['content_align'] : '';
$icon_align_class    = isset( $align_map[ $icon_align ] )    ? $align_map[ $icon_align ]    : '';
$title_align_class   = isset( $align_map[ $title_align ] )   ? $align_map[ $title_align ]   : '';
$content_align_class = isset( $align_map[ $content_align ] ) ? $align_map[ $content_align ] : '';

/*
|--------------------------------------------------------------------------
| Icon Badge — coloured background or outlined ring around the icon
|--------------------------------------------------------------------------
| Layout tab picks one of seven variants (none / {solid|outline}-{square|
| rounded|circle}); Styling tab supplies the colour via the compact picker.
|
| The compact picker stores `{ predefined: 'bg-{slug}', custom: '#hex' }`;
| pre-migration saves still hold the flat string `'bg-{slug}'`. Both paths
| funnel through here.
|
| Output split:
|   - Preset pick → emit a UTILITY CLASS on the icon span (themeable,
|     cascade-friendly, no inline-style override)
|       solid   → `bg-{slug}`
|       outline → `border border-{slug} text-{slug}`
|         (border-{slug} paints the ring, text-{slug} paints currentColor
|          so the inner SVG / font-icon picks up the same tone)
|   - Custom-hex pick → emit an inline style, since no utility class can
|     express an arbitrary hex
|       solid   → `background-color: #hex`
|       outline → `border-color: #hex; color: #hex`
*/

/*
 * TEMP MIGRATION — remove once all icon boxes are re-saved under the new keys.
 * "Icon Fill" was renamed to "Icon Badge" (option keys icon_fill → icon_badge,
 * icon_fill_color → icon_badge_color). These two fallbacks let content saved
 * under the OLD keys keep its badge shape + colour. Delete the `: ( … icon_fill … )`
 * halves (and the legacy keys in the unset() below) when no longer needed.
 */
$icon_badge = ! empty( $atts['icon_badge'] )
    ? (string) $atts['icon_badge']
    : ( ! empty( $atts['icon_fill'] ) ? (string) $atts['icon_fill'] : 'none' );

$icon_badge_color_raw = ( isset( $atts['icon_badge_color'] ) && $atts['icon_badge_color'] !== '' )
    ? $atts['icon_badge_color']
    : ( isset( $atts['icon_fill_color'] ) ? $atts['icon_fill_color'] : '' );
/* END TEMP MIGRATION */

// Peel apart whichever half of the value is live.
$icon_badge_preset = '';
$icon_badge_custom = '';
if ( is_array( $icon_badge_color_raw ) ) {
    $icon_badge_preset = isset( $icon_badge_color_raw['predefined'] ) ? (string) $icon_badge_color_raw['predefined'] : '';
    $icon_badge_custom = isset( $icon_badge_color_raw['custom'] )     ? (string) $icon_badge_color_raw['custom']     : '';
} elseif ( is_string( $icon_badge_color_raw ) ) {
    // Legacy plain-string save from before the compact-picker migration.
    $icon_badge_preset = $icon_badge_color_raw;
}

unset( $atts['icon_badge'], $atts['icon_badge_color'], $atts['icon_fill'], $atts['icon_fill_color'] );

$allowed_badges   = array( 'solid-square', 'solid-rounded', 'solid-circle', 'outline-square', 'outline-rounded', 'outline-circle' );
$has_badge        = in_array( $icon_badge, $allowed_badges, true );
$icon_badge_class = '';
$icon_badge_attr  = '';

if ( $has_badge ) {
    $icon_badge_class = 'icon-box__icon--has-badge icon-box__icon--badge-' . sanitize_html_class( $icon_badge );
    $is_solid   = strpos( $icon_badge, 'solid-' )   === 0;
    $is_outline = strpos( $icon_badge, 'outline-' ) === 0;

    if ( $icon_badge_preset !== '' ) {
        // Preset path — emit utility classes. The saved slug carries the
        // `bg-` prefix because the field uses `kind => 'bg'`. Strip it,
        // then rebuild the class(es) appropriate to the badge variant so
        // the CSS cascade paints the right property.
        $slug = sanitize_html_class( preg_replace( '/^bg-/', '', $icon_badge_preset ) );
        if ( $slug !== '' ) {
            if ( $is_solid ) {
                $icon_badge_class .= ' bg-' . $slug;
            } elseif ( $is_outline ) {
                $icon_badge_class .= ' border border-' . $slug . ' text-' . $slug;
            }
        }
    } elseif ( $icon_badge_custom !== '' ) {
        // Custom-hex path — inline style, since no utility class can
        // express an arbitrary hex.
        $hex = $icon_badge_custom;
        if ( $is_solid ) {
            $icon_badge_attr = ' style="background-color:' . esc_attr( $hex ) . '"';
        } elseif ( $is_outline ) {
            $icon_badge_attr = ' style="border-color:' . esc_attr( $hex ) . ';color:' . esc_attr( $hex ) . '"';
        }
    }
}

/*
|--------------------------------------------------------------------------
| Wrapper attributes (uses shared helper for css_class / css_id / unique)
|--------------------------------------------------------------------------
*/
$wrapper_classes = [ 'icon-box__wrapper', 'icon-box--style-' . sanitize_html_class( $style ) ];

if ( $mobile_stack ) {
    $wrapper_classes[] = 'icon-box--mobile-stack';
}
if ( $box_link !== '' ) {
    $wrapper_classes[] = 'icon-box--linked';
}
if ( ! $has_content ) {
    $wrapper_classes[] = 'icon-box--no-content';
}
if ( ! $has_icon ) {
    $wrapper_classes[] = 'icon-box--no-icon';
}

// Route per-element color picks to specific inner elements (kept out of wrapper).
// The Styling tab's general "Text Color" is left on the wrapper as the base.
// sc_extract_styling_atts() returns both classes (preset picks) AND inline
// styles (compact-picker custom-hex picks) so each named field can be either.
$title_styling   = sc_extract_styling_atts( $atts, array( 'title_color' ) );
$content_styling = sc_extract_styling_atts( $atts, array( 'content_color' ) );
$icon_styling    = sc_extract_styling_atts( $atts, array( 'icon_color' ) );
$title_extras    = $title_styling['classes'];
$content_extras  = $content_styling['classes'];
$icon_extras     = $icon_styling['classes'];
$title_style     = $title_styling['styles']   ? implode( '; ', $title_styling['styles'] )   : '';
$content_style   = $content_styling['styles'] ? implode( '; ', $content_styling['styles'] ) : '';
$icon_style      = $icon_styling['styles']    ? implode( '; ', $icon_styling['styles'] )    : '';

$atts['base_class']       = 'icon-box';
$atts['unique_id_prefix'] = 'ib-';
$atts['css_class']        = trim( implode( ' ', $wrapper_classes ) . ' ' . ( $atts['css_class'] ?? '' ) );

$attr = sc_build_wrapper_attr( $atts );

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_iconbox_render_icon_markup' ) ) {
    /**
     * Render the inner markup for the icon container.
     * Priority: the picked icon (the unified picker now covers font / SVG /
     * emoji / Lucide) wins when set; a legacy Custom Icon value is the fallback
     * for content saved before the picker gained those kinds.
     * The caller is responsible for the surrounding container (with aria-hidden).
     */
    function sc_iconbox_render_icon_markup( $custom_icon, $picked_icon ) {

        // 1. icon-v2 picked icon (font / svg / emoji / upload) via the central
        // renderer. Only return it when non-empty so a legacy Custom Icon still
        // shows for content where the picker was left as none.
        if ( function_exists( 'sc_icon_render' ) ) {
            $picked_html = sc_icon_render( $picked_icon, array(
                'aria_hidden' => false,
                'font_class'  => 'icon-box__icon-font',
                'img_class'   => 'icon-box__icon-image',
            ) );
            if ( $picked_html !== '' ) {
                return $picked_html;
            }
        }

        // 2. Legacy Custom Icon (emoji / inline SVG) fallback — the field is
        //    retired, so only pre-existing content has a value here.
        if ( is_string( $custom_icon ) && $custom_icon !== '' && function_exists( 'sc_icon_custom_markup' ) ) {
            return sc_icon_custom_markup( $custom_icon );
        }

        // Fallback (helper not loaded):
        // 2. icon-v2 image upload
        if (
            is_array( $picked_icon ) &&
            isset( $picked_icon['type'] ) &&
            $picked_icon['type'] === 'custom-upload' &&
            ! empty( $picked_icon['url'] )
        ) {
            return sprintf(
                '<img src="%s" alt="" class="icon-box__icon-image" loading="lazy">',
                esc_url( $picked_icon['url'] )
            );
        }

        // 3. icon-v2 font icon
        if (
            is_array( $picked_icon ) &&
            isset( $picked_icon['type'] ) &&
            $picked_icon['type'] === 'icon-font' &&
            ! empty( $picked_icon['icon-class'] )
        ) {
            return '<i class="icon-box__icon-font ' . esc_attr( $picked_icon['icon-class'] ) . '"></i>';
        }

        return '';
    }
}

if ( ! function_exists( 'sc_iconbox_render_icon_container' ) ) {
    /**
     * @param string $extra_attrs  Optional. Pre-built attribute fragment with a
     *                             leading space (e.g. ` style="background-color:#000"`).
     *                             The caller is responsible for escaping; we
     *                             append it verbatim into the opening tag.
     */
    function sc_iconbox_render_icon_container( $custom_icon, $picked_icon, $extra_class = '', $extra_attrs = '' ) {
        $markup = sc_iconbox_render_icon_markup( $custom_icon, $picked_icon );
        if ( $markup === '' ) {
            return '';
        }
        $class = 'icon-box__icon' . ( $extra_class ? ' ' . esc_attr( $extra_class ) : '' );
        return '<span class="' . $class . '"' . $extra_attrs . ' aria-hidden="true">' . $markup . '</span>';
    }
}

/*
|--------------------------------------------------------------------------
| Pre-rendered fragments
|--------------------------------------------------------------------------
*/
$icon_class_str = trim( implode( ' ', $icon_extras ) . ( $icon_badge_class !== '' ? ' ' . $icon_badge_class : '' ) );

// Compose the icon's extra-attrs fragment. The icon-badge inline style (set
// earlier from icon_badge_color) and the Icon Color custom-hex inline style
// (from sc_extract_styling_atts above) are both per-icon — merge into a
// single style="…" so we don't emit two style attributes on one tag.
$icon_extra_attrs = $icon_badge_attr; // may be ` style="background-color:…"`
if ( $icon_style !== '' ) {
    if ( $icon_extra_attrs !== '' && preg_match( '/\bstyle="([^"]*)"/', $icon_extra_attrs, $m ) ) {
        $merged = rtrim( $m[1], '; ' ) . '; ' . $icon_style;
        $icon_extra_attrs = ' style="' . esc_attr( $merged ) . '"';
    } else {
        $icon_extra_attrs = ' style="' . esc_attr( $icon_style ) . '"';
    }
}

$icon_html = $has_icon
    ? sc_iconbox_render_icon_container( $custom_icon, $picked_icon, $icon_class_str, $icon_extra_attrs )
    : '';

$title_html = '';
if ( $title !== '' ) {
    $title_class      = trim( 'icon-box__title ' . implode( ' ', $title_extras ) . ( $title_align_class ? ' ' . $title_align_class : '' ) );
    $title_style_attr = $title_style !== '' ? ' style="' . esc_attr( $title_style ) . '"' : '';
    $title_html       = sprintf(
        '<%1$s class="%2$s"%3$s>%4$s</%1$s>',
        $title_tag,
        esc_attr( $title_class ),
        $title_style_attr,
        wp_kses_post( $title )
    );
}

$content_html = '';
if ( $has_content ) {
    $content_class      = trim( 'icon-box__content ' . implode( ' ', $content_extras ) . ( $content_align_class ? ' ' . $content_align_class : '' ) );
    $content_style_attr = $content_style !== '' ? ' style="' . esc_attr( $content_style ) . '"' : '';
    $content_html       = '<div class="' . esc_attr( $content_class ) . '"' . $content_style_attr . '>' . wp_kses_post( $content ) . '</div>';
}

/*
|--------------------------------------------------------------------------
| Link wrapper (optional) — wraps the whole box
|--------------------------------------------------------------------------
*/
$open_link  = '';
$close_link = '';

if ( $box_link !== '' ) {
    $rel_parts = [];
    if ( $link_rel_value === 'nofollow' || $link_rel_value === 'sponsored' ) {
        $rel_parts[] = $link_rel_value;
    }
    if ( $link_target ) {
        $rel_parts[] = 'noopener';
        $rel_parts[] = 'noreferrer';
    }

    $link_attrs = sprintf( ' href="%s"', esc_url( $box_link ) );

    if ( $link_target ) {
        $link_attrs .= ' target="_blank"';
    }
    if ( ! empty( $rel_parts ) ) {
        $link_attrs .= ' rel="' . esc_attr( implode( ' ', array_unique( $rel_parts ) ) ) . '"';
    }

    // Accessible label — falls back to a generic description if no title set.
    $aria_label = $title !== ''
        ? sprintf( __( 'Read more about %s', 'fw' ), wp_strip_all_tags( $title ) )
        : __( 'Read more', 'fw' );

    $link_attrs .= ' aria-label="' . esc_attr( $aria_label ) . '"';

    $open_link  = '<a class="icon-box__link"' . $link_attrs . '>';
    $close_link = '</a>';
}
?>

<div <?php echo fw_attr_to_html( $attr ); ?>>
    <?php echo $open_link; ?>

    <?php if ( $style === 'between-title-content' ) : ?>

        <div class="icon-box__inner icon-box__inner--divider">
            <?php if ( $title_html !== '' ) : ?>
                <div class="icon-box__head"><?php echo $title_html; ?></div>
            <?php endif; ?>

            <?php if ( $icon_html !== '' ) : ?>
                <div class="icon-box__divider<?php echo $icon_align_class ? ' ' . esc_attr( $icon_align_class ) : ''; ?>" role="presentation">
                    <?php echo $icon_html; ?>
                </div>
            <?php endif; ?>

            <?php if ( $content_html !== '' ) : ?>
                <div class="icon-box__body"><?php echo $content_html; ?></div>
            <?php endif; ?>
        </div>

    <?php elseif ( $style === 'inline-left' || $style === 'inline-right' ) : ?>

        <div class="icon-box__inner icon-box__inner--inline icon-box__inner--<?php echo esc_attr( $style ); ?>">
            <div class="icon-box__head">
                <?php if ( $style === 'inline-left' ) : ?>
                    <?php echo $icon_html; ?>
                    <?php echo $title_html; ?>
                <?php else : ?>
                    <?php echo $title_html; ?>
                    <?php echo $icon_html; ?>
                <?php endif; ?>
            </div>

            <?php if ( $content_html !== '' ) : ?>
                <div class="icon-box__body"><?php echo $content_html; ?></div>
            <?php endif; ?>
        </div>

    <?php elseif ( $style === 'stack-left' || $style === 'stack-right' ) : ?>

        <div class="icon-box__inner icon-box__inner--stack icon-box__inner--<?php echo esc_attr( $style ); ?>">
            <?php if ( $style === 'stack-left' ) : ?>
                <?php echo $icon_html; ?>
            <?php endif; ?>

            <div class="icon-box__body">
                <?php echo $title_html; ?>
                <?php echo $content_html; ?>
            </div>

            <?php if ( $style === 'stack-right' ) : ?>
                <?php echo $icon_html; ?>
            <?php endif; ?>
        </div>

    <?php else : /* top-title (default) */ ?>

        <div class="icon-box__inner icon-box__inner--top">
            <?php if ( $icon_html !== '' ) : ?>
                <div class="icon-box__icon-align<?php echo $icon_align_class ? ' ' . esc_attr( $icon_align_class ) : ''; ?>">
                    <?php echo $icon_html; ?>
                </div>
            <?php endif; ?>
            <?php echo $title_html; ?>
            <?php echo $content_html; ?>
        </div>

    <?php endif; ?>

    <?php echo $close_link; ?>
</div>
