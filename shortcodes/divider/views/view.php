<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * @var array $atts
 */

$ruler_type = $atts['style']['ruler_type'];

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

// 2. Build Inline Styles
$styles = [];
if ( ! empty( $atts['width'] ) ) $styles[] = "width: {$atts['width']}%; margin-left: auto; margin-right: auto;";
if ( ! empty( $atts['margin_top'] ) ) $styles[] = "margin-top: {$atts['margin_top']}px;";
if ( ! empty( $atts['margin_bottom'] ) ) $styles[] = "margin-bottom: {$atts['margin_bottom']}px;";

if ( $ruler_type === 'space' ) {
    $height = (int) $atts['style']['space']['height'];
    $styles[] = "height: {$height}px;";
}

$style_output = ! empty( $styles ) ? ' style="' . implode( ' ', $styles ) . '"' : '';
$class_output = ' class="' . implode( ' ', array_unique( $classes ) ) . '"';

// 3. Render
?>

<div<?php echo $id_attr; ?><?php echo $class_output; ?><?php echo $style_output; ?>>
    <?php if ( $ruler_type === 'line' && $line['content_type'] !== 'none' ) : ?>
        <span class="divider-inner">
            <?php if ( $line['content_type'] === 'icon' && ! empty( $line['icon'] ) ) : ?>
                <i class="<?php echo $line['icon']; ?>"></i>
            <?php elseif ( $line['content_type'] === 'text' ) : ?>
                <?php echo esc_html( $line['title'] ); ?>
            <?php endif; ?>
        </span>
    <?php endif; ?>
</div>