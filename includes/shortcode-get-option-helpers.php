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
    // Offset + z-index sub-fields the Position picker reveals ONLY for a POSITIONED value
    // (relative / absolute / fixed / sticky). z-index and the offsets have no effect on
    // static, so Default / Static reveal nothing. Shared across the four positioned choices.
    $pos_fields = [
        // One compact inline row of four unit inputs (Top / Right / Bottom / Left), like the
        // spacing control. Each side picks a unit (px / % / em / rem / vh / vw) or "auto".
        'pos_offsets'    => [
            'type'  => 'position-box',
            'label' => __( 'Offset', 'fw' ),
            'desc'  => __( 'Nudge the element from its anchor edges — Top / Right / Bottom / Left. Choose a unit per side, or "auto" to let the browser decide.', 'fw' ),
        ],
        'element_zindex' => [ 'type' => 'number', 'label' => __( 'Z-Index', 'fw' ), 'desc' => __( 'Stacking order — a higher value sits on top of lower ones. Integer.', 'fw' ), 'value' => '', 'step' => 1 ],
    ];
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
        // Position + Z-Index for ANY element (Elementor-style) — placed after the CSS classes.
        // A multi-picker so the Offset row + Z-Index appear ONLY for a positioned value; Default /
        // Static reveal nothing (offsets + stacking have no effect there). Applied to the wrapper
        // by sc_build_wrapper_attr() via sc_position_style().
        'group_position' => [
            'type'    => 'group',
            'options' => [
                'element_position' => [
                    'type'         => 'multi-picker',
                    'label'        => false,
                    'desc'         => false,
                    'value'        => [ 'position' => 'default' ],
                    'picker'       => [
                        'position' => [
                            'type'    => 'select',
                            'label'   => __( 'Position', 'fw' ),
                            'desc'    => __( 'Take this element out of the normal flow. Relative nudges it from its own spot; Absolute / Fixed / Sticky position it against a positioned ancestor or the viewport. The Offset row + Z-Index appear once you pick a positioned value.', 'fw' ),
                            'help'    => __( 'Absolute needs a positioned ancestor — set the containing Section or Column to Relative for that. Z-Index and the offsets have no effect on Default / Static.', 'fw' ),
                            'choices' => [
                                'default'  => __( 'Default', 'fw' ),
                                'static'   => __( 'Static', 'fw' ),
                                'relative' => __( 'Relative', 'fw' ),
                                'absolute' => __( 'Absolute', 'fw' ),
                                'fixed'    => __( 'Fixed', 'fw' ),
                                'sticky'   => __( 'Sticky', 'fw' ),
                            ],
                        ],
                    ],
                    'choices'      => [
                        'relative' => $pos_fields,
                        'absolute' => $pos_fields,
                        'fixed'    => $pos_fields,
                        'sticky'   => $pos_fields,
                    ],
                    'show_borders' => false,
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
        'group_display_conditions' => [
            'type'    => 'group',
            'options' => [
                'dc_logged' => [
                    'type'    => 'select',
                    'label'   => __( 'Visibility', 'fw' ),
                    'desc'    => __( 'Display Conditions — show this element to everyone, or only to logged-in / logged-out visitors.', 'fw' ),
                    'value'   => 'all',
                    'choices' => [
                        'all'        => __( 'Everyone', 'fw' ),
                        'logged_in'  => __( 'Logged-in users only', 'fw' ),
                        'logged_out' => __( 'Logged-out visitors only', 'fw' ),
                    ],
                ],
                'dc_roles' => [
                    'type'    => 'checkboxes',
                    'label'   => __( 'Restrict to roles', 'fw' ),
                    'desc'    => __( 'If any roles are checked, only logged-in users with one of those roles see this element. Leave empty for no role restriction.', 'fw' ),
                    'value'   => [],
                    'choices' => function_exists( 'wp_roles' ) ? wp_roles()->get_names() : [],
                ],
                'dc_start' => [
                    'type'            => 'datetime-picker',
                    'label'           => __( 'Show from', 'fw' ),
                    'desc'            => __( 'Optional. Hide this element until this date / time (site timezone).', 'fw' ),
                    'value'           => '',
                    'dynamic_content' => false,
                ],
                'dc_end' => [
                    'type'            => 'datetime-picker',
                    'label'           => __( 'Show until', 'fw' ),
                    'desc'            => __( 'Optional. Hide this element after this date / time (site timezone).', 'fw' ),
                    'value'           => '',
                    'dynamic_content' => false,
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

/**
 * Display Conditions — per-element visibility gate (the Theme Builder "show this
 * element when…" feature). Mirrors Divi's render-then-strip model: the element
 * renders normally, then its output is discarded if its conditions don't pass.
 *
 * Gate is intentionally FAIL-OPEN: any uncertainty (non-builder shortcode, decode
 * error, exception) returns the original output, so a bug here can never blank a
 * page. A cheap raw pre-check skips the (heavier) atts decode for the ~99% of
 * elements that set no condition. Extend the verdict via `fw_sc_display_conditions`.
 */
if ( ! function_exists( 'sc_eval_display_conditions' ) ) :
function sc_eval_display_conditions( $atts ) {
    $logged = isset( $atts['dc_logged'] ) ? (string) $atts['dc_logged'] : 'all';

    // Normalize checkboxes (list of slugs OR { slug => true }) to a slug list.
    $roles = ( isset( $atts['dc_roles'] ) && is_array( $atts['dc_roles'] ) ) ? array_filter( $atts['dc_roles'] ) : array();
    if ( $roles && array_keys( $roles ) !== range( 0, count( $roles ) - 1 ) ) {
        $roles = array_keys( $roles );
    }
    $roles = array_values( array_map( 'strval', $roles ) );

    $logged_in = is_user_logged_in();
    $show      = true;

    if ( $logged === 'logged_in' && ! $logged_in ) {
        $show = false;
    } elseif ( $logged === 'logged_out' && $logged_in ) {
        $show = false;
    } elseif ( ! empty( $roles ) ) {
        if ( ! $logged_in ) {
            $show = false;
        } else {
            $user = wp_get_current_user();
            if ( ! array_intersect( $roles, (array) $user->roles ) ) {
                $show = false;
            }
        }
    }

    // Schedule — show only within [dc_start, dc_end]. Each bound is a datetime-picker
    // string (Y/m/d H:i) interpreted in the SITE timezone; comparison is on absolute
    // timestamps so it is correct regardless of the server's PHP timezone.
    if ( $show ) {
        $tz    = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( 'UTC' );
        $now   = time();
        $to_ts = function ( $raw ) use ( $tz ) {
            $raw = trim( (string) $raw );
            if ( $raw === '' ) {
                return null;
            }
            $dt = DateTimeImmutable::createFromFormat( 'Y/m/d H:i', $raw, $tz );
            if ( ! $dt ) {
                try { $dt = new DateTimeImmutable( $raw, $tz ); } catch ( \Exception $e ) { $dt = false; }
            }
            return $dt ? $dt->getTimestamp() : null;
        };
        $start = isset( $atts['dc_start'] ) ? $to_ts( $atts['dc_start'] ) : null;
        $end   = isset( $atts['dc_end'] ) ? $to_ts( $atts['dc_end'] ) : null;
        if ( $start !== null && $now < $start ) {
            $show = false;
        }
        if ( $end !== null && $now > $end ) {
            $show = false;
        }
    }

    return (bool) apply_filters( 'fw_sc_display_conditions', $show, $atts );
}
endif;

if ( ! function_exists( '_fw_sc_display_conditions_gate' ) ) :
function _fw_sc_display_conditions_gate( $output, $tag, $attr ) {
    // Only builder-authored elements carry these atts; plain shortcodes pass through.
    if ( ! is_array( $attr ) || ! isset( $attr['_made_with_builder'] ) ) {
        return $output;
    }

    // Cheap pre-check on the raw (still-encoded) atts: bail before decoding when
    // nothing restricts visibility. dc_logged is a plain scalar; an empty dc_roles
    // encodes to "[]".
    $logged_raw = isset( $attr['dc_logged'] ) ? html_entity_decode( (string) $attr['dc_logged'], ENT_QUOTES ) : 'all';
    $roles_raw  = isset( $attr['dc_roles'] ) ? html_entity_decode( (string) $attr['dc_roles'], ENT_QUOTES ) : '';
    $start_raw  = isset( $attr['dc_start'] ) ? trim( (string) $attr['dc_start'] ) : '';
    $end_raw    = isset( $attr['dc_end'] ) ? trim( (string) $attr['dc_end'] ) : '';
    $has_roles  = ( $roles_raw !== '' && $roles_raw !== '[]' && $roles_raw !== 'null' && $roles_raw !== '{}' );
    $has_sched  = ( $start_raw !== '' || $end_raw !== '' );
    if ( ( $logged_raw === 'all' || $logged_raw === '' ) && ! $has_roles && ! $has_sched ) {
        return $output;
    }

    // A restriction exists — decode + evaluate. FAIL-OPEN on any error.
    try {
        if ( ! function_exists( 'fw_ext_shortcodes_decode_attr' ) ) {
            return $output;
        }
        global $post;
        $decoded = fw_ext_shortcodes_decode_attr( $attr, (string) $tag, $post ? (int) $post->ID : 0 );
        if ( is_wp_error( $decoded ) || ! is_array( $decoded ) ) {
            return $output;
        }
        return sc_eval_display_conditions( $decoded ) ? $output : '';
    } catch ( \Throwable $e ) {
        return $output;
    }
}
endif;
add_filter( 'do_shortcode_tag', '_fw_sc_display_conditions_gate', 10, 3 );
