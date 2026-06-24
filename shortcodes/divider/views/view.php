<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * @var array $atts
 */

// Default to a line when ruler_type is missing/empty (legacy saves or content
// stored before the multi-picker had a default value). An empty value used to
// fall through to the invisible whitespace branch, so the divider showed nothing.
$ruler_type = ! empty( $atts['style']['ruler_type'] ) ? $atts['style']['ruler_type'] : 'line';

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
    $line = wp_parse_args(
        isset( $atts['style']['line'] ) && is_array( $atts['style']['line'] ) ? $atts['style']['line'] : array(),
        array(
            'line_design'  => 'std',
            'content_type' => 'none',
            'alignment'    => 'center',
            'title'        => '',
            'icon'         => '',
        )
    );
    $classes[] = "divider-{$line['line_design']}";

    if ( $line['content_type'] !== 'none' ) {
        $classes[] = "has-content alignment-{$line['alignment']}";
    }
} elseif ( $ruler_type === 'shape' ) {
    $shape = wp_parse_args(
        isset( $atts['style']['shape'] ) && is_array( $atts['style']['shape'] ) ? $atts['style']['shape'] : array(),
        array(
            'shape_style'  => 'waves',
            'shape_height' => '60',
            'shape_flip_x' => 'no',
            'shape_flip_y' => 'no',
        )
    );
    $classes[] = 'divider-shape';
    $classes[] = 'divider-shape--' . sanitize_html_class( $shape['shape_style'] );
    if ( $shape['shape_flip_x'] === 'yes' ) { $classes[] = 'is-flip-x'; }
    if ( $shape['shape_flip_y'] === 'yes' ) { $classes[] = 'is-flip-y'; }
} else {
    $classes[] = 'divider-space';
}

if ( ! function_exists( 'sc_divider_shape_path' ) ) {
    /** Filled SVG path (viewBox 0 0 1200 120) for each shape style. */
    function sc_divider_shape_path( $style ) {
        switch ( $style ) {
            case 'wave':
                return 'M0,80 C400,20 800,120 1200,60 L1200,120 L0,120 Z';
            case 'curve':
                return 'M0,120 Q600,0 1200,120 L1200,120 L0,120 Z';
            case 'tilt':
                return 'M0,120 L1200,0 L1200,120 L0,120 Z';
            case 'triangle':
                return 'M0,120 L600,0 L1200,120 L0,120 Z';
            case 'zigzag':
                return 'M0,120 L150,40 L300,120 L450,40 L600,120 L750,40 L900,120 L1050,40 L1200,120 L1200,120 L0,120 Z';
            case 'arrow':
                return 'M0,0 L600,90 L1200,0 L1200,120 L0,120 Z';
            case 'waves':
            default:
                return 'M0,64 C150,8 350,120 600,64 C850,8 1050,120 1200,56 L1200,120 L0,120 Z';
        }
    }
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
    <?php elseif ( $ruler_type === 'shape' ) : ?>
        <?php $shape_h = max( 1, (int) $shape['shape_height'] ); ?>
        <svg class="fw-divider__shape-svg" viewBox="0 0 1200 120" preserveAspectRatio="none" style="height:<?php echo esc_attr( $shape_h ); ?>px" aria-hidden="true" focusable="false">
            <path d="<?php echo esc_attr( sc_divider_shape_path( $shape['shape_style'] ) ); ?>" fill="currentColor"></path>
        </svg>
    <?php endif; ?>
</div>