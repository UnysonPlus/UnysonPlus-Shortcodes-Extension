<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 */

$atts['base_class']         = 'text-block';
$atts['unique_id_prefix']   = 'tb-';

// Build extra attributes for the wrapper
$atts['extra_attrs'] = [
    // Commented out examples of additional attributes you might want to include
    
    // Link behavior
    // 'target' => ! empty( $atts['target'] ) ? esc_attr( $atts['target'] ) : '',
    // 'rel'    => ! empty( $atts['rel'] ) ? esc_attr( $atts['rel'] ) : '',

    // Inline styles
    //'style'  => ! empty( $atts['bg_color'] ) ? 'background-color:' . esc_attr( $atts['bg_color'] ) . ';' : '',

    // Data attributes
    // 'data-type'   => 'text-block',
    // 'data-id'   => ! empty( $atts['unique_id'] ) ? esc_attr( $atts['unique_id'] ) : '',

    // ARIA attributes
    //'aria-label'  => ! empty( $atts['aria_label'] ) ? esc_attr( $atts['aria_label'] ) : '',
    //'aria-hidden' => ! empty( $atts['aria_hidden'] ) ? esc_attr( $atts['aria_hidden'] ) : '',
];

// Build attributes for wrapper
$attr = sc_build_wrapper_attr( $atts );

// Determine if wrapper is needed
$needs_wrapper = ! empty( $atts['css_id'] ) || ! empty( $atts['css_class'] );

?>

<?php if ( ! empty( $atts['text'] ) ) : ?>
    <?php if ( $needs_wrapper ) : ?>
        <div <?php echo fw_attr_to_html( $attr ); ?>>
            <?php echo do_shortcode( $atts['text'] ); ?>
        </div>
    <?php else : ?>
        <?php echo do_shortcode( $atts['text'] ); ?>
    <?php endif; ?>
<?php endif; ?>