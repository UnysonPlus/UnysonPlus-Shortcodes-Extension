<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}
/**
 * @var array $wrapper_atts
 * @var array $atts
 * @var string $content
 * @var string $tag
 */

// Per-element color picks (kept off the wrapper). sc_extract_styling_atts
// gives both preset classes AND compact-picker custom-hex inline styles.
$heading_styling = sc_extract_styling_atts( $atts, array( 'heading_color' ) );
$buttons_styling = sc_extract_styling_atts( $atts, array( 'buttons_color' ) );
$heading_extras  = $heading_styling['classes'];
$buttons_extras  = $buttons_styling['classes'];
$heading_style   = $heading_styling['styles'] ? implode( '; ', $heading_styling['styles'] ) : '';
$buttons_style   = $buttons_styling['styles'] ? implode( '; ', $buttons_styling['styles'] ) : '';

// Background color: route off the wrapper and onto the inner calendar box
// (`#cal-day-box` / `.cal-week-box` / `.cal-month-box`). The class is passed
// to scripts.js via `data-bg-class`; scripts.js applies it in
// `onAfterViewLoad` so it survives template re-renders (prev/next/today).
// Custom-hex picks here are class-only at this layer; scripts.js doesn't
// have a hook to inject inline styles into the dynamically-rendered box,
// so a custom-hex bg silently degrades — preset-class picks work as before.
$bg_styling = sc_extract_styling_atts( $atts, array( 'bg_color' ) );
$bg_extras  = $bg_styling['classes'];
$bg_class   = trim( implode( ' ', $bg_extras ) );

if ( $bg_class !== '' ) {
    $existing_extras     = ( isset( $atts['extra_attrs'] ) && is_array( $atts['extra_attrs'] ) ) ? $atts['extra_attrs'] : array();
    $atts['extra_attrs'] = array_merge(
        $existing_extras,
        array( 'data-bg-class' => $bg_class )
    );
}

$atts['base_class']       = 'calendar';
$atts['unique_id_prefix'] = 'cl-';

// Build attributes for wrapper
$attr = sc_build_wrapper_attr( $atts );

// Merge attributes safely
$attr = array_merge( $attr, $wrapper_atts ?? [] );

// Append our custom classes
$attr['class'] = trim(
    ($attr['class'] ?? '') . ' fw-shortcode-calendar-wrapper shortcode-container'
);

$heading_class = trim( implode( ' ', $heading_extras ) );
// Navigation Buttons Color must be applied to EACH <button>, not the
// .btn-group wrapper. Buttons have their own user-agent / theme `color`
// that doesn't inherit from the parent, so a `text-{slug}` class on
// `.btn-group` is overridden before the cascade reaches the labels.
$btn_class = trim( implode( ' ', $buttons_extras ) );
$heading_style_attr = $heading_style !== '' ? ' style="' . esc_attr( $heading_style ) . '"' : '';
$btn_style_attr     = $buttons_style !== '' ? ' style="' . esc_attr( $buttons_style ) . '"' : '';
?>

<div <?php echo fw_attr_to_html( $attr ); ?>>

    <div class="clearfix"></div>
    <div class="page-header hidden-header">

        <div class="pull-right form-inline">
            <div class="btn-group">
                <button class="<?php echo esc_attr( $btn_class ); ?>"<?php echo $btn_style_attr; ?> data-calendar-nav="prev"><i class="fa fa-angle-left"></i></button>
                <button class="<?php echo esc_attr( $btn_class ); ?>"<?php echo $btn_style_attr; ?> data-calendar-nav="today"><?php echo __( 'Today', 'fw' ) ?></button>
                <button class="<?php echo esc_attr( $btn_class ); ?>"<?php echo $btn_style_attr; ?> data-calendar-nav="next"><i class="fa fa-angle-right"></i></button>
            </div>
        </div>

        <h3<?php echo $heading_class !== '' ? ' class="' . esc_attr( $heading_class ) . '"' : ''; ?><?php echo $heading_style_attr; ?>><!-- Here will be set the title --></h3>

    </div>

    <div class="row">
        <div class="col-xs-12 col-lg-12 col-xl-12 col-sm-12">
            <div class="fw-shortcode-calendar"></div>
        </div>
    </div>

</div>
