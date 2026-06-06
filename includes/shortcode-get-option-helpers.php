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
                'custom_css' => [
                    'label'      => __( 'Custom CSS', 'fw' ),
                    'desc'       => __( 'Scoped to THIS element. Use the keyword "selector" for the element itself, e.g. "selector { padding: 80px 0; }" or "selector .title { font-size: 48px; }". Travels with the element when you export/import a template, and renders on every page the element appears on — no need to edit Theme Settings or page CSS.', 'fw' ),
                    'type'       => 'code-editor',
                    'value'      => '',
                    // `mode` is read at the top level by the code-editor option
                    // type (NOT under `properties`); without it the editor
                    // defaults to htmlmixed and raw CSS isn't syntax-highlighted.
                    'mode'       => 'css',
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
        'group_custom_attrs' => [
            'type'    => 'group',
            'options' => [
                'custom_attrs' => [
                    'label'           => __( 'Custom HTML Attributes', 'fw' ),
                    'type'            => 'addable-box',
                    'value'           => [],
                    'box-options'     => [
                        'name'  => [
                            'label' => __( 'Name', 'fw' ),
                            'type'  => 'text',
                            'value' => '',
                            'desc'  => __( 'e.g. aria-label, data-id, role', 'fw' ),
                        ],
                        'value' => [
                            'label' => __( 'Value', 'fw' ),
                            'type'  => 'text',
                            'value' => '',
                        ],
                    ],
                    'add-button-text' => __( 'Add attribute', 'fw' ),
                    'template'        => '{{- name }}="{{- value }}"',
                    'desc'            => __( 'Add extra HTML attributes to this shortcode\'s wrapper. Allowed: data-*, aria-*, role, title, tabindex, lang, target, rel. Other attribute names are silently ignored for safety.', 'fw' ),
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

/**
 * Apply user-defined custom HTML attributes from the Advanced tab's
 * Custom HTML Attributes field to every shortcode wrapper. Whitelist-based —
 * only data-*, aria-*, plus a small list of safe exact names are passed
 * through. Anything else (style, class, id, on* handlers, etc.) is silently
 * dropped to keep the admin-input surface from becoming an XSS vector.
 */
add_filter( 'sc_build_wrapper_attr', function ( $attr, $atts ) {
    if ( empty( $atts['custom_attrs'] ) || ! is_array( $atts['custom_attrs'] ) ) {
        return $attr;
    }

    static $exact_allow = array( 'role', 'title', 'tabindex', 'lang', 'target', 'rel' );

    foreach ( $atts['custom_attrs'] as $row ) {
        if ( empty( $row['name'] ) ) { continue; }
        $name  = strtolower( trim( (string) $row['name'] ) );
        $value = isset( $row['value'] ) ? (string) $row['value'] : '';

        $is_data_or_aria = preg_match( '/^(data|aria)-[a-z0-9_-]+$/', $name );
        $is_exact_allow  = in_array( $name, $exact_allow, true );

        if ( ! $is_data_or_aria && ! $is_exact_allow ) { continue; }

        $attr[ $name ] = esc_attr( $value );
    }

    return $attr;
}, 15, 2 );
