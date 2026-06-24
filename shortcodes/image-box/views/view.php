<?php
if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * Image Box — frontend render.
 *
 * @var array $atts
 *
 * Flow: normalize atts → build the reusable HTML fragments (image, icon,
 * eyebrow, title, text, button) → resolve the link / lightbox shell → dispatch
 * to views/parts/box-<part>.php (chosen via the design registry) for the actual
 * layout. The part files only arrange the pre-built fragments, so a new design
 * is a registry entry + (maybe) a new part + a thumbnail + optional CSS.
 */

/*
|--------------------------------------------------------------------------
| Shared getter (defined once across all shortcodes)
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_get' ) ) {
    function sc_get( $path, $atts, $default = '' ) {
        if ( function_exists( 'fw_akg' ) ) {
            $v = fw_akg( $path, $atts, null );
            if ( $v !== null ) {
                return $v;
            }
        }
        return $default;
    }
}

/*
|--------------------------------------------------------------------------
| Design registry loader (single source of truth, loaded once)
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_imgbox_registry' ) ) {
    function sc_imgbox_registry() {
        static $registry = null;
        if ( $registry === null ) {
            $registry = require __DIR__ . '/parts/registry.php';
            if ( ! is_array( $registry ) ) {
                $registry = array();
            }
        }
        return $registry;
    }
}

if ( ! function_exists( 'sc_imgbox_locate_part' ) ) {
    function sc_imgbox_locate_part( $part ) {
        $part = preg_replace( '/[^a-z0-9_-]/', '', (string) $part );
        return __DIR__ . '/parts/box-' . $part . '.php';
    }
}

/*
|--------------------------------------------------------------------------
| Icon markup — custom_icon (emoji / inline SVG) wins over the icon-v2 pick.
| Mirrors icon-box's renderer (kept local per the self-contained rule).
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_imgbox_icon_markup' ) ) {
    function sc_imgbox_icon_markup( $custom_icon, $picked_icon ) {
        if ( is_string( $custom_icon ) && $custom_icon !== '' ) {
            $svg_allowed = array(
                'svg'      => array( 'xmlns' => true, 'viewbox' => true, 'width' => true, 'height' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'class' => true, 'role' => true, 'aria-hidden' => true, 'focusable' => true ),
                'g'        => array( 'fill' => true, 'stroke' => true, 'transform' => true, 'class' => true ),
                'path'     => array( 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'class' => true ),
                'circle'   => array( 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'class' => true ),
                'rect'     => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'class' => true ),
                'line'     => array( 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'class' => true ),
                'polyline' => array( 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'class' => true ),
                'polygon'  => array( 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'class' => true ),
                'title'    => array(),
                'desc'     => array(),
            );
            if ( stripos( $custom_icon, '<svg' ) !== false ) {
                return wp_kses( $custom_icon, $svg_allowed );
            }
            return esc_html( $custom_icon );
        }

        if ( is_array( $picked_icon ) && isset( $picked_icon['type'] ) ) {
            if ( $picked_icon['type'] === 'custom-upload' && ! empty( $picked_icon['url'] ) ) {
                return sprintf( '<img src="%s" alt="" class="imgbox__icon-image" loading="lazy">', esc_url( $picked_icon['url'] ) );
            }
            if ( $picked_icon['type'] === 'icon-font' && ! empty( $picked_icon['icon-class'] ) ) {
                return '<i class="imgbox__icon-font ' . esc_attr( $picked_icon['icon-class'] ) . '"></i>';
            }
        }

        return '';
    }
}

/*
|--------------------------------------------------------------------------
| Main render
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_imgbox_render' ) ) {
    function sc_imgbox_render( $atts ) {

        $registry = sc_imgbox_registry();

        /* --- Resolve the design + its registry meta ----------------------- */
        $design = sc_get( 'design', $atts, 'stacked' );
        if ( ! isset( $registry[ $design ] ) ) {
            $design = 'stacked';
        }
        $meta = isset( $registry[ $design ] ) ? $registry[ $design ] : array();
        $part = isset( $meta['part'] ) ? $meta['part'] : 'stacked';

        $part_file = sc_imgbox_locate_part( $part );
        if ( ! file_exists( $part_file ) ) {
            $part_file = sc_imgbox_locate_part( 'stacked' );
        }

        /* --- Content values ----------------------------------------------- */
        $subtitle    = trim( (string) sc_get( 'subtitle', $atts, '' ) );
        $title       = trim( (string) sc_get( 'title', $atts, '' ) );
        $text        = trim( (string) sc_get( 'text', $atts, '' ) );
        $title_tag   = sc_get( 'title_tag', $atts, 'h3' );
        $allowed     = array( 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p' );
        $title_tag   = in_array( $title_tag, $allowed, true ) ? $title_tag : 'h3';
        $custom_icon = trim( (string) sc_get( 'custom_icon', $atts, '' ) );
        $picked_icon = sc_get( 'icon', $atts, null );

        $btn_style = sc_get( 'button_style', $atts, 'none' );
        $btn_label = trim( (string) sc_get( 'button_label', $atts, '' ) );

        /* --- Image -------------------------------------------------------- */
        $image      = is_array( sc_get( 'image', $atts, null ) ) ? $atts['image'] : array();
        $image_url  = ! empty( $image['url'] ) ? $image['url'] : '';
        $att_id     = ! empty( $image['attachment_id'] ) ? (int) $image['attachment_id'] : 0;
        $alt_ovr    = trim( (string) sc_get( 'image_alt', $atts, '' ) );
        $alt        = $alt_ovr !== ''
            ? $alt_ovr
            : ( $att_id ? (string) get_post_meta( $att_id, '_wp_attachment_image_alt', true ) : '' );
        $full_url   = $att_id ? ( wp_get_attachment_image_url( $att_id, 'full' ) ?: $image_url ) : $image_url;

        // Nothing to show at all → render nothing.
        if ( $image_url === '' && $title === '' && $text === '' && $subtitle === '' ) {
            return '';
        }

        /* --- Enqueue only the pack this font icon needs (not every pack) --- */
        if (
            $custom_icon === '' && is_array( $picked_icon ) &&
            isset( $picked_icon['type'] ) && $picked_icon['type'] === 'icon-font' &&
            isset( fw()->backend->option_type( 'icon-v2' )->packs_loader )
        ) {
            fw()->backend->option_type( 'icon-v2' )->packs_loader->enqueue_pack_for_icon( $picked_icon );
        }

        /* --- Design behavior flags ---------------------------------------- */
        $content_over = ! empty( $meta['content_over_image'] );
        $hover_reveal = ! empty( $meta['hover_reveal'] );

        /* --- Effects ------------------------------------------------------ */
        $hover_fx   = sc_get( 'hover_effect', $atts, 'zoom-in' );
        $speed      = sc_get( 'transition_speed', $atts, 'normal' );
        $ratio      = sc_get( 'image_ratio', $atts, 'ratio-4-3' );
        $media_w    = sc_get( 'media_width', $atts, '50' );
        $align      = sc_get( 'content_align', $atts, '' );
        $ov_opacity = (int) sc_get( 'overlay_opacity', $atts, 60 );

        $align_map = array( 'left' => 'is-left', 'start' => 'is-left', 'center' => 'is-center', 'right' => 'is-right', 'end' => 'is-right' );
        $align_cls = isset( $align_map[ $align ] ) ? $align_map[ $align ] : '';

        /* --- Per-element colors (preset classes + custom-hex inline) ------ */
        $title_st = sc_extract_styling_atts( $atts, array( 'title_color' ) );
        $sub_st   = sc_extract_styling_atts( $atts, array( 'subtitle_color' ) );
        $text_st  = sc_extract_styling_atts( $atts, array( 'content_color' ) );
        $icon_st  = sc_extract_styling_atts( $atts, array( 'icon_color' ) );

        $cls_str   = function ( $extras ) { return $extras ? ' ' . implode( ' ', $extras ) : ''; };
        $style_str = function ( $styles ) { return $styles ? ' style="' . esc_attr( implode( '; ', $styles ) ) . '"' : ''; };

        /* --- Build the image <img> ---------------------------------------- */
        $img_html = '';
        if ( $image_url !== '' ) {
            $img_attr = array(
                'src'      => esc_url( $image_url ),
                'alt'      => $alt,
                'class'    => 'imgbox__img',
                'loading'  => 'lazy',
                'decoding' => 'async',
            );
            $img_html = fw_html_tag( 'img', $img_attr );
        }

        /* --- Build the icon ----------------------------------------------- */
        $icon_inner = sc_imgbox_icon_markup( $custom_icon, $picked_icon );
        $icon_html  = '';
        if ( $icon_inner !== '' ) {
            $icon_html = '<span class="imgbox__icon' . esc_attr( $cls_str( $icon_st['classes'] ) ) . '"'
                . $style_str( $icon_st['styles'] ) . ' aria-hidden="true">' . $icon_inner . '</span>';
        }

        /* --- Build the text fragments ------------------------------------- */
        $subtitle_html = '';
        if ( $subtitle !== '' ) {
            $subtitle_html = '<span class="imgbox__eyebrow' . esc_attr( $cls_str( $sub_st['classes'] ) ) . '"'
                . $style_str( $sub_st['styles'] ) . '>' . esc_html( $subtitle ) . '</span>';
        }

        $title_html = '';
        if ( $title !== '' ) {
            $title_html = sprintf(
                '<%1$s class="imgbox__title%2$s"%3$s>%4$s</%1$s>',
                $title_tag,
                esc_attr( $cls_str( $title_st['classes'] ) ),
                $style_str( $title_st['styles'] ),
                wp_kses_post( $title )
            );
        }

        $text_html = '';
        if ( $text !== '' ) {
            $text_html = '<div class="imgbox__text' . esc_attr( $cls_str( $text_st['classes'] ) ) . '"'
                . $style_str( $text_st['styles'] ) . '>' . wp_kses_post( wpautop( $text ) ) . '</div>';
        }

        /* --- Link / lightbox shell ---------------------------------------- */
        $behavior   = sc_get( 'link_behavior', $atts, 'none' );
        $link_url   = trim( (string) sc_get( 'link_url', $atts, '' ) );
        $new_tab    = sc_get( 'link_target', $atts, '_self' ) === '_blank';

        $box_is_link = false;
        $link_open   = '';
        $link_close  = '';
        $btn_href    = $link_url !== '' ? $link_url : '#';

        if ( $behavior === 'url' && $link_url !== '' ) {
            $box_is_link = true;
            $link_open   = '<a class="imgbox__link" href="' . esc_url( $link_url ) . '"'
                . ( $new_tab ? ' target="_blank" rel="noopener noreferrer"' : '' )
                . ( $title !== '' ? ' aria-label="' . esc_attr( wp_strip_all_tags( $title ) ) . '"' : '' ) . '>';
            $link_close  = '</a>';
        } elseif ( $behavior === 'lightbox' && $full_url !== '' ) {
            $box_is_link = true;
            $link_open   = '<a class="imgbox__link imgbox__lightbox" href="' . esc_url( $full_url ) . '" data-imgbox-lightbox="image">';
            $link_close  = '</a>';
        } elseif ( $behavior === 'video' && $link_url !== '' ) {
            $box_is_link = true;
            $link_open   = '<a class="imgbox__link imgbox__lightbox" href="' . esc_url( $link_url ) . '" data-imgbox-lightbox="video">';
            $link_close  = '</a>';
        }

        /* --- Button: a real <a> when the box itself isn't a link; otherwise
               a styled <span> so we never nest anchors. ---------------------- */
        $button_html = '';
        if ( $btn_style !== 'none' && ( $btn_label !== '' || $btn_style === 'arrow' ) ) {
            $btn_cls = 'imgbox__btn imgbox__btn--' . sanitize_html_class( $btn_style );
            $arrow   = $btn_style === 'arrow' || $btn_style === 'link'
                ? ' <span class="imgbox__btn-arrow" aria-hidden="true">→</span>' : '';
            $inner   = ( $btn_label !== '' ? esc_html( $btn_label ) : '' ) . ( $btn_style === 'arrow' && $btn_label === '' ? '<span class="imgbox__btn-arrow" aria-hidden="true">→</span>' : $arrow );

            if ( $box_is_link ) {
                $button_html = '<span class="' . esc_attr( $btn_cls ) . '">' . $inner . '</span>';
            } else {
                $button_html = '<a class="' . esc_attr( $btn_cls ) . '" href="' . esc_url( $btn_href ) . '"'
                    . ( $new_tab && $link_url !== '' ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . $inner . '</a>';
            }
        }

        /* --- Wrapper attributes (styling tab + animation + css id/class) --- */
        $wrapper_classes = array(
            'imgbox',
            'imgbox--design-' . sanitize_html_class( $design ),
            'imgbox--part-' . sanitize_html_class( $part ),
            'imgbox--ratio-' . sanitize_html_class( $ratio ),
            'imgbox--fx-' . sanitize_html_class( $hover_fx ),
            'imgbox--speed-' . sanitize_html_class( $speed ),
        );
        if ( $align_cls )    { $wrapper_classes[] = 'imgbox--' . $align_cls; }
        if ( $content_over ) { $wrapper_classes[] = 'imgbox--content-over'; }
        if ( $hover_reveal ) { $wrapper_classes[] = 'imgbox--hover-reveal'; }
        if ( $box_is_link )  { $wrapper_classes[] = 'imgbox--linked'; }
        if ( $img_html === '' ) { $wrapper_classes[] = 'imgbox--no-image'; }

        $atts['base_class']       = 'imgbox-wrap';
        $atts['unique_id_prefix'] = 'ibx-';
        $atts['css_class']        = trim( implode( ' ', $wrapper_classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );

        // Side media-width + overlay opacity ride along as CSS custom properties.
        $extra_attrs = array();
        $atts['extra_attrs'] = $extra_attrs;

        $attr = sc_build_wrapper_attr( $atts );

        // Accent color → CSS var. Honor a custom-hex pick directly (the most
        // direct intent); preset picks fall back to the stylesheet default.
        $accent_raw = sc_get( 'accent_color', $atts, '' );
        $accent_hex = ( is_array( $accent_raw ) && ! empty( $accent_raw['custom'] ) ) ? $accent_raw['custom'] : '';

        // Overlay color → CSS var (custom hex), else the default dark scrim.
        $ov_raw = sc_get( 'overlay_color', $atts, '' );
        $ov_hex = ( is_array( $ov_raw ) && ! empty( $ov_raw['custom'] ) ) ? $ov_raw['custom'] : '';

        $style_var = '--imgbox-media-w:' . (int) $media_w . '%;--imgbox-ov-opacity:' . ( max( 0, min( 100, $ov_opacity ) ) / 100 ) . ';';
        if ( $accent_hex !== '' ) {
            $style_var .= '--imgbox-accent:' . preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', $accent_hex ) . ';';
        }
        if ( $ov_hex !== '' ) {
            $style_var .= '--imgbox-ov-color:' . preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', $ov_hex ) . ';';
        }
        $attr['style'] = isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' . $style_var : $style_var;

        /* --- Variables exposed to the part -------------------------------- */
        $sc_atts        = $atts;
        $sc_meta        = $meta;
        $sc_img_html    = $img_html;
        $sc_icon_html   = $icon_html;
        $sc_subtitle    = $subtitle_html;
        $sc_title       = $title_html;
        $sc_text        = $text_html;
        $sc_button      = $button_html;
        $sc_align_cls   = $align_cls;

        ob_start();
        echo '<div ' . fw_attr_to_html( $attr ) . '>';
        echo $link_open;
        include $part_file;
        echo $link_close;
        echo '</div>';
        return ob_get_clean();
    }
}

echo sc_imgbox_render( $atts );
