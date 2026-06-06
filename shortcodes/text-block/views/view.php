<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 */

$atts['base_class']       = 'text-block';
$atts['unique_id_prefix'] = 'tb-';

// To add extra wrapper attributes (data-*, aria-*, target, rel, etc.),
// populate $atts['extra_attrs'] before calling sc_build_wrapper_attr().
// Inline color/font styling is handled by the Styling tab — pick from
// dropdowns there instead of writing inline style here.

// Build attributes for wrapper
$attr = sc_build_wrapper_attr( $atts );

// Determine if wrapper is needed
$should_wrap = function_exists( 'sc_needs_wrapper' )
    ? sc_needs_wrapper( $atts )
    : ( ! empty( $atts['css_id'] ) || ! empty( $atts['css_class'] ) );

?>

<?php if ( ! empty( $atts['text'] ) ) : ?>
    <?php if ( $should_wrap ) : ?>
        <div <?php echo fw_attr_to_html( $attr ); ?>>
            <?php echo do_shortcode( $atts['text'] ); ?>
        </div>
    <?php else : ?>
        <?php echo do_shortcode( $atts['text'] ); ?>
    <?php endif; ?>
<?php endif; ?>
