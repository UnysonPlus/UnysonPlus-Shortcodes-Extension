<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * @var array $atts
 */

$ruler_type = $atts['style']['ruler_type'];

// Per-element color picks (extracted before any wrapper-class composition).
// sc_extract_styling_atts gives us both preset classes AND custom-hex
// inline-style fragments from the compact picker.
$line_styling = sc_extract_styling_atts( $atts, array( 'line_color' ) );
$icon_styling = sc_extract_styling_atts( $atts, array( 'icon_color' ) );
$text_styling = sc_extract_styling_atts( $atts, array( 'divider_text_color' ) );
$line_extras  = $line_styling['classes'];
$icon_extras  = $icon_styling['classes'];
$text_extras  = $text_styling['classes'];
$line_style   = $line_styling['styles'] ? implode( '; ', $line_styling['styles'] ) : '';
$icon_style   = $icon_styling['styles'] ? implode( '; ', $icon_styling['styles'] ) : '';
$text_style   = $text_styling['styles'] ? implode( '; ', $text_styling['styles'] ) : '';

// Unyson's `group` container (used by 'advanced_settings') flattens to top-level
// keys in the saved $atts — see fw_collect_options() in framework/helpers/general.php.
// Read css_id / css_class / responsive_hide directly. Legacy 'responsive' key is
// honored as a fallback so dividers saved before the rename still hide correctly.
$css_id    = ! empty( $atts['css_id'] )    ? $atts['css_id']    : '';
$css_class = ! empty( $atts['css_class'] ) ? $atts['css_class'] : '';

$hide_keys = array_keys( array_filter( (array) ( $atts['responsive_hide'] ?? [] ) ) );
$legacy    = array_keys( array_filter( (array) ( $atts['responsive']      ?? [] ) ) );
$hide_keys = array_values( array_unique( array_merge( $hide_keys, $legacy ) ) );

$id_attr   = $css_id !== '' ? ' id="' . esc_attr( $css_id ) . '"' : '';

// 1. Build Classes
$classes = [ 'fw-divider' ];

if ( $ruler_type === 'line' ) {
    $line = $atts['style']['line'];
    $classes[] = "divider-{$line['line_design']}";

    if ( $line['content_type'] !== 'none' ) {
        $classes[] = "has-content alignment-{$line['alignment']}";
    }
} else {
    $classes[] = 'divider-space';
}

// Merge responsive-hide classes and custom user class.
// Divider builds its own wrapper attributes instead of going through
// sc_build_wrapper_attr(), so the global filter callback doesn't run here.
if ( ! empty( $hide_keys ) ) {
    $classes = array_merge( $classes, $hide_keys );
}
if ( $css_class !== '' ) $classes[] = esc_attr( $css_class );

// Apply Line Color to the wrapper — divider CSS uses currentColor / the wrapper's
// `color` for border-color in most line designs, so a text-{slug} class on the
// wrapper recolors the line.
if ( ! empty( $line_extras ) ) {
    $classes = array_merge( $classes, $line_extras );
}

// 2. Build Inline Styles
$styles = [];
if ( ! empty( $atts['width'] ) ) $styles[] = "width: {$atts['width']}%; margin-left: auto; margin-right: auto;";
if ( ! empty( $atts['margin_top'] ) ) $styles[] = "margin-top: {$atts['margin_top']}px;";
if ( ! empty( $atts['margin_bottom'] ) ) $styles[] = "margin-bottom: {$atts['margin_bottom']}px;";

if ( $ruler_type === 'space' ) {
    $height = (int) $atts['style']['space']['height'];
    $styles[] = "height: {$height}px;";
}

// Compact-picker custom-hex Line Color → inline style on the wrapper
// (analogous to the preset-class path that appends to $classes above).
if ( $line_style !== '' ) {
    $styles[] = $line_style . ';';
}

$style_output = ! empty( $styles ) ? ' style="' . implode( ' ', $styles ) . '"' : '';
$class_output = ' class="' . implode( ' ', array_unique( $classes ) ) . '"';

// 3. Render
?>

<div<?php echo $id_attr; ?><?php echo $class_output; ?><?php echo $style_output; ?>>
    <?php if ( $ruler_type === 'line' && $line['content_type'] !== 'none' ) : ?>
        <?php
        $inner_classes = array( 'divider-inner' );
        if ( $line['content_type'] === 'text' && ! empty( $text_extras ) ) {
            $inner_classes = array_merge( $inner_classes, $text_extras );
        }
        $icon_classes = array( $line['icon'] ?? '' );
        if ( $line['content_type'] === 'icon' && ! empty( $icon_extras ) ) {
            $icon_classes = array_merge( $icon_classes, $icon_extras );
        }
        $inner_style_attr = ( $line['content_type'] === 'text' && $text_style !== '' )
            ? ' style="' . esc_attr( $text_style ) . '"'
            : '';
        $icon_style_attr = ( $line['content_type'] === 'icon' && $icon_style !== '' )
            ? ' style="' . esc_attr( $icon_style ) . '"'
            : '';
        ?>
        <span class="<?php echo esc_attr( implode( ' ', $inner_classes ) ); ?>"<?php echo $inner_style_attr; ?>>
            <?php if ( $line['content_type'] === 'icon' && ! empty( $line['icon'] ) ) : ?>
                <i class="<?php echo esc_attr( trim( implode( ' ', $icon_classes ) ) ); ?>"<?php echo $icon_style_attr; ?>></i>
            <?php elseif ( $line['content_type'] === 'text' ) : ?>
                <?php echo esc_html( $line['title'] ); ?>
            <?php endif; ?>
        </span>
    <?php endif; ?>
</div>