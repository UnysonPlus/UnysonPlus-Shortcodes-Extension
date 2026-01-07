<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}
/**
 * @var array $wrapper_atts
 * @var array $atts
 * @var string $content
 * @var string $tag
 */

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
?>

<div <?php echo fw_attr_to_html( $attr ); ?>>

    <div class="clearfix"></div>
    <div class="page-header hidden-header">

        <div class="pull-right form-inline">
            <div class="btn-group">
                <button data-calendar-nav="prev"><i class="fa fa-angle-left"></i></button>
                <button data-calendar-nav="today"><?php echo __( 'Today', 'fw' ) ?></button>
                <button data-calendar-nav="next"><i class="fa fa-angle-right"></i></button>
            </div>
        </div>

        <h3><!-- Here will be set the title --></h3>

    </div>

    <div class="row">
        <div class="col-xs-12 col-lg-12 col-xl-12 col-sm-12">
            <div class="fw-shortcode-calendar"></div>
        </div>
    </div>

</div>
