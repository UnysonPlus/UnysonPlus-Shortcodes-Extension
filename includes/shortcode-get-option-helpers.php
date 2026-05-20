<?php
/**
 * PHP Version: 7.4 or higher
 */
if (!defined('FW')) die('Forbidden');

/**
 * Returns a reusable "Advanced" tab for shortcodes.
 * Includes Unique ID, CSS ID, and CSS Class.
 *
 * @return array
 */
if ( ! function_exists( 'sc_get_advanced_tab' ) ) :
function sc_get_advanced_tab() {
    return [
        // 'unique' is Unyson's auto-generated, immutable ID — renders as a
        // hidden input, no label/desc shown to the user.
        'unique_id' => [
            'type' => 'unique',
        ],
        'group_css' => [
            'type'    => 'group',
            'options' => [
                'css_id' => [
                    'label' => __( 'CSS ID', 'fw' ),
                    'desc'  => __( 'Useful for anchor links', 'fw' ),
                    'type'  => 'text',
                ],
                'css_class' => [
                    'label' => __( 'CSS Class', 'fw' ),
                    'desc'  => false,
                    'type'  => 'text',
                ],
            ],
        ],
        'group_responsive' => [
            'type'    => 'group',
            'options' => [
                'responsive_hide' => [
                    'type'    => 'checkboxes',
                    'label'   => __( 'Hide on', 'fw' ),
                    'desc'    => __( 'Hide this element on the selected device viewports.', 'fw' ),
                    'choices' => [
                        'hide-xs' => __( 'Mobile (< 768px)', 'fw' ),
                        'hide-sm' => __( 'Tablet (768 – 991px)', 'fw' ),
                        'hide-md' => __( 'Desktop (≥ 992px)', 'fw' ),
                    ],
                ],
            ],
        ],
    ];
}
endif;

/**
 * Append responsive-hide classes to every shortcode wrapper that runs through
 * sc_build_wrapper_attr(). Reads the user's selections from $atts['responsive_hide']
 * (set by the field added above) and merges hide-xs/hide-sm/hide-md into the
 * wrapper's class attribute. The matching CSS lives in builder/static/css/frontend-grid.css.
 */
add_filter( 'sc_build_wrapper_attr', function ( $attr, $atts ) {
    if ( empty( $atts['responsive_hide'] ) || ! is_array( $atts['responsive_hide'] ) ) {
        return $attr;
    }

    $selected = array_filter( $atts['responsive_hide'] );
    if ( empty( $selected ) ) {
        return $attr;
    }

    $extra = implode( ' ', array_map( 'sanitize_html_class', array_keys( $selected ) ) );

    $existing = isset( $attr['class'] ) ? trim( (string) $attr['class'] ) : '';
    $attr['class'] = esc_attr( $existing === '' ? $extra : $existing . ' ' . $extra );

    return $attr;
}, 10, 2 );
