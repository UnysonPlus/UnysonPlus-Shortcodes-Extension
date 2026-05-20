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
    fw()->backend->option_type( 'icon-v2' )->packs_loader->enqueue_frontend_css();
}

/*
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
     * Priority: custom_icon (emoji / inline SVG) > icon-v2 picked icon.
     * The caller is responsible for the surrounding container (with aria-hidden).
     */
    function sc_iconbox_render_icon_markup( $custom_icon, $picked_icon ) {

        // 1. Custom icon override (emoji or inline SVG)
        if ( is_string( $custom_icon ) && $custom_icon !== '' ) {

            // Allow inline SVG markup, while still filtering anything dangerous.
            $svg_allowed = [
                'svg'      => [ 'xmlns' => true, 'viewbox' => true, 'width' => true, 'height' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'class' => true, 'role' => true, 'aria-hidden' => true, 'focusable' => true ],
                'g'        => [ 'fill' => true, 'stroke' => true, 'transform' => true, 'class' => true ],
                'path'     => [ 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'class' => true ],
                'circle'   => [ 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'class' => true ],
                'rect'     => [ 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'class' => true ],
                'line'     => [ 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true, 'class' => true ],
                'polyline' => [ 'points' => true, 'fill' => true, 'stroke' => true, 'class' => true ],
                'polygon'  => [ 'points' => true, 'fill' => true, 'stroke' => true, 'class' => true ],
                'title'    => [],
                'desc'     => [],
            ];

            if ( stripos( $custom_icon, '<svg' ) !== false ) {
                return wp_kses( $custom_icon, $svg_allowed );
            }

            // Emoji / plain text fallback
            return esc_html( $custom_icon );
        }

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
    function sc_iconbox_render_icon_container( $custom_icon, $picked_icon, $extra_class = '' ) {
        $markup = sc_iconbox_render_icon_markup( $custom_icon, $picked_icon );
        if ( $markup === '' ) {
            return '';
        }
        $class = 'icon-box__icon' . ( $extra_class ? ' ' . esc_attr( $extra_class ) : '' );
        return '<span class="' . $class . '" aria-hidden="true">' . $markup . '</span>';
    }
}

/*
|--------------------------------------------------------------------------
| Pre-rendered fragments
|--------------------------------------------------------------------------
*/
$icon_html = $has_icon ? sc_iconbox_render_icon_container( $custom_icon, $picked_icon ) : '';

$title_html = '';
if ( $title !== '' ) {
    $title_html = sprintf(
        '<%1$s class="icon-box__title">%2$s</%1$s>',
        $title_tag,
        wp_kses_post( $title )
    );
}

$content_html = '';
if ( $has_content ) {
    $content_html = '<div class="icon-box__content">' . wp_kses_post( $content ) . '</div>';
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
                <div class="icon-box__divider" role="presentation">
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
            <?php echo $icon_html; ?>
            <?php echo $title_html; ?>
            <?php echo $content_html; ?>
        </div>

    <?php endif; ?>

    <?php echo $close_link; ?>
</div>
