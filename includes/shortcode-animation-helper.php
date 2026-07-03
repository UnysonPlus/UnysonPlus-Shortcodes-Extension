<?php
/**
 * PHP Version: 7.4 or higher
 *
 * Animate.css v4.1.1 integration for every Unysonplus shortcode.
 *
 * Provides:
 *   1. sc_get_animation_fields() — returns the inner fields used as the
 *      `options` of the inline `tab_animation` block in each shortcode's
 *      options.php (mirrors the role of sc_get_advanced_tab() for tab_advanced).
 *   2. A filter on `sc_build_wrapper_attr` that injects animation classes,
 *      data-* attributes and CSS custom properties when a shortcode opts in.
 *   3. Conditional enqueue: animate.min.css + sc-animations.js are loaded only
 *      when at least one shortcode on the rendered page has animations enabled.
 *
 * No view.php files need to be modified — the filter pipes everything through
 * the shared wrapper-attribute builder used by every shortcode today.
 */
if ( ! defined( 'FW' ) ) die( 'Forbidden' );


/**
 * Returns the inner fields for the Animations tab.
 *
 * Use inside each shortcode's options.php:
 *
 *     'tab_animation' => [
 *         'title'   => __( 'Animations', 'fw' ),
 *         'type'    => 'tab',
 *         'options' => sc_get_animation_fields(),
 *     ],
 */
if ( ! function_exists( 'sc_get_animation_fields' ) ) :
function sc_get_animation_fields() {

    // Unyson select optgroup syntax: a numerically-indexed element with
    // 'attr' => ['label' => …] and a nested 'choices' map. See
    // unysonplus-theme/framework-customizations/theme/options/demo.php:152-175
    $effect_choices = [
        [
            'attr'    => [ 'label' => __( 'Attention Seekers', 'fw' ) ],
            'choices' => [
                'animate__bounce'     => __( 'Bounce', 'fw' ),
                'animate__flash'      => __( 'Flash', 'fw' ),
                'animate__pulse'      => __( 'Pulse', 'fw' ),
                'animate__rubberBand' => __( 'Rubber Band', 'fw' ),
                'animate__shakeX'     => __( 'Shake X', 'fw' ),
                'animate__shakeY'     => __( 'Shake Y', 'fw' ),
                'animate__headShake'  => __( 'Head Shake', 'fw' ),
                'animate__swing'      => __( 'Swing', 'fw' ),
                'animate__tada'       => __( 'Tada', 'fw' ),
                'animate__wobble'     => __( 'Wobble', 'fw' ),
                'animate__jello'      => __( 'Jello', 'fw' ),
                'animate__heartBeat'  => __( 'Heart Beat', 'fw' ),
            ],
        ],
        [
            'attr'    => [ 'label' => __( 'Back Entrances', 'fw' ) ],
            'choices' => [
                'animate__backInDown'  => __( 'Back In Down', 'fw' ),
                'animate__backInLeft'  => __( 'Back In Left', 'fw' ),
                'animate__backInRight' => __( 'Back In Right', 'fw' ),
                'animate__backInUp'    => __( 'Back In Up', 'fw' ),
            ],
        ],
        [
            'attr'    => [ 'label' => __( 'Bouncing Entrances', 'fw' ) ],
            'choices' => [
                'animate__bounceIn'      => __( 'Bounce In', 'fw' ),
                'animate__bounceInDown'  => __( 'Bounce In Down', 'fw' ),
                'animate__bounceInLeft'  => __( 'Bounce In Left', 'fw' ),
                'animate__bounceInRight' => __( 'Bounce In Right', 'fw' ),
                'animate__bounceInUp'    => __( 'Bounce In Up', 'fw' ),
            ],
        ],
        [
            'attr'    => [ 'label' => __( 'Fading Entrances', 'fw' ) ],
            'choices' => [
                'animate__fadeIn'            => __( 'Fade In', 'fw' ),
                'animate__fadeInDown'        => __( 'Fade In Down', 'fw' ),
                'animate__fadeInDownBig'     => __( 'Fade In Down Big', 'fw' ),
                'animate__fadeInLeft'        => __( 'Fade In Left', 'fw' ),
                'animate__fadeInLeftBig'     => __( 'Fade In Left Big', 'fw' ),
                'animate__fadeInRight'       => __( 'Fade In Right', 'fw' ),
                'animate__fadeInRightBig'    => __( 'Fade In Right Big', 'fw' ),
                'animate__fadeInUp'          => __( 'Fade In Up', 'fw' ),
                'animate__fadeInUpBig'       => __( 'Fade In Up Big', 'fw' ),
                'animate__fadeInTopLeft'     => __( 'Fade In Top Left', 'fw' ),
                'animate__fadeInTopRight'    => __( 'Fade In Top Right', 'fw' ),
                'animate__fadeInBottomLeft'  => __( 'Fade In Bottom Left', 'fw' ),
                'animate__fadeInBottomRight' => __( 'Fade In Bottom Right', 'fw' ),
            ],
        ],
        [
            'attr'    => [ 'label' => __( 'Flippers', 'fw' ) ],
            'choices' => [
                'animate__flip'    => __( 'Flip', 'fw' ),
                'animate__flipInX' => __( 'Flip In X', 'fw' ),
                'animate__flipInY' => __( 'Flip In Y', 'fw' ),
            ],
        ],
        [
            'attr'    => [ 'label' => __( 'Lightspeed', 'fw' ) ],
            'choices' => [
                'animate__lightSpeedInRight' => __( 'Light Speed In Right', 'fw' ),
                'animate__lightSpeedInLeft'  => __( 'Light Speed In Left', 'fw' ),
            ],
        ],
        [
            'attr'    => [ 'label' => __( 'Rotating Entrances', 'fw' ) ],
            'choices' => [
                'animate__rotateIn'          => __( 'Rotate In', 'fw' ),
                'animate__rotateInDownLeft'  => __( 'Rotate In Down Left', 'fw' ),
                'animate__rotateInDownRight' => __( 'Rotate In Down Right', 'fw' ),
                'animate__rotateInUpLeft'    => __( 'Rotate In Up Left', 'fw' ),
                'animate__rotateInUpRight'   => __( 'Rotate In Up Right', 'fw' ),
            ],
        ],
        [
            'attr'    => [ 'label' => __( 'Sliding Entrances', 'fw' ) ],
            'choices' => [
                'animate__slideInDown'  => __( 'Slide In Down', 'fw' ),
                'animate__slideInLeft'  => __( 'Slide In Left', 'fw' ),
                'animate__slideInRight' => __( 'Slide In Right', 'fw' ),
                'animate__slideInUp'    => __( 'Slide In Up', 'fw' ),
            ],
        ],
        [
            'attr'    => [ 'label' => __( 'Zooming Entrances', 'fw' ) ],
            'choices' => [
                'animate__zoomIn'      => __( 'Zoom In', 'fw' ),
                'animate__zoomInDown'  => __( 'Zoom In Down', 'fw' ),
                'animate__zoomInLeft'  => __( 'Zoom In Left', 'fw' ),
                'animate__zoomInRight' => __( 'Zoom In Right', 'fw' ),
                'animate__zoomInUp'    => __( 'Zoom In Up', 'fw' ),
            ],
        ],
        [
            'attr'    => [ 'label' => __( 'Specials', 'fw' ) ],
            'choices' => [
                'animate__hinge'        => __( 'Hinge', 'fw' ),
                'animate__jackInTheBox' => __( 'Jack In The Box', 'fw' ),
                'animate__rollIn'       => __( 'Roll In', 'fw' ),
            ],
        ],
    ];

    $advanced_settings_fields = [
        'delay' => [
            'type'         => 'number',
            'label'        => __( 'Animation Delay (seconds)', 'fw' ),
            'desc'         => __( 'Seconds before the animation starts after the element enters view. Decimals OK, e.g. 0.5, 1.25, 12.', 'fw' ),
            'value'        => 0,
            'min'          => 0,
            'step'         => 0.1,
            'numeric_type' => 'float',
        ],
        'custom_duration' => [
            'type'         => 'number',
            'label'        => __( 'Custom Duration (seconds)', 'fw' ),
            'desc'         => __( 'Override the animation length in seconds. Leave at 0 to use the Speed Preset.', 'fw' ),
            'value'        => 0,
            'min'          => 0,
            'step'         => 0.1,
            'numeric_type' => 'float',
        ],
        'repeat_count' => [
            'type'         => 'number',
            'label'        => __( 'Repeat Count', 'fw' ),
            'desc'         => __( 'How many times the animation should play. Ignored when "Loop Forever" is on.', 'fw' ),
            'value'        => 1,
            'min'          => 1,
            'step'         => 1,
            'numeric_type' => 'integer',
        ],
        'loop_forever' => [
            'type'         => 'switch',
            'label'        => __( 'Loop Forever', 'fw' ),
            'desc'         => __( 'Run the animation continuously. Best paired with attention seekers like Pulse or Bounce.', 'fw' ),
            'value'        => 'no',
            'left-choice'  => [ 'value' => 'no',  'label' => __( 'No',  'fw' ) ],
            'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
        ],
        'replay_on_scroll' => [
            'type'         => 'switch',
            'label'        => __( 'Replay On Scroll', 'fw' ),
            'desc'         => __( 'Re-trigger the animation every time the element re-enters the viewport.', 'fw' ),
            'value'        => 'no',
            'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
            'right-choice' => [ 'value' => 'yes', 'label' => __( 'On',  'fw' ) ],
        ],
        'easing' => [
            'label'   => __( 'Easing Function', 'fw' ),
            'desc'    => __( 'Override the animation timing function. Leave on Default to use the effect\'s built-in curve.', 'fw' ),
            'type'    => 'select',
            'value'   => '',
            'choices' => [
                ''                                      => __( 'Default', 'fw' ),
                'ease'                                  => __( 'Ease', 'fw' ),
                'ease-in'                               => __( 'Ease In', 'fw' ),
                'ease-out'                              => __( 'Ease Out', 'fw' ),
                'ease-in-out'                           => __( 'Ease In Out', 'fw' ),
                'linear'                                => __( 'Linear', 'fw' ),
                'cubic-bezier(0.25, 0.1, 0.25, 1)'      => __( 'Smooth (cubic-bezier)', 'fw' ),
                'cubic-bezier(0.68, -0.55, 0.27, 1.55)' => __( 'Overshoot (cubic-bezier)', 'fw' ),
            ],
        ],
    ];

    // Speed preset — shown once an effect (≠ None) is chosen.
    $speed_preset_field = [
        'label'   => __( 'Speed Preset', 'fw' ),
        'desc'    => __( 'Quick speed adjustment using Animate.css presets. Leave on Default unless you need a tweak.', 'fw' ),
        'type'    => 'select',
        'value'   => '',
        'choices' => [
            ''                 => __( 'Default (1s)', 'fw' ),
            'animate__slow'    => __( 'Slow (2s)', 'fw' ),
            'animate__slower'  => __( 'Slower (3s)', 'fw' ),
            'animate__fast'    => __( 'Fast (800ms)', 'fw' ),
            'animate__faster'  => __( 'Faster (500ms)', 'fw' ),
        ],
    ];

    // The settings panel revealed once any effect (≠ None) is chosen. Built ONCE and mapped
    // onto every effect key below, so switching effects keeps the same panel — and, like
    // Scroll Motion, each effect remembers its own tweaks (they store under the effect key).
    $reveal_group = [
        'group_animation_basic' => [
            'type'    => 'group',
            'options' => [
                'speed_preset' => $speed_preset_field,
            ],
        ],
        'advanced_tweaks_heading' => [
            'type'  => 'html',
            'label' => false,
            'desc'  => false,
            'html'  => '<h4 style="margin:28px 0 6px;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:#666;">' . esc_html__( 'Advanced Tweaks', 'fw' ) . '</h4>',
        ],
        'group_animation_advanced' => [
            'type'    => 'group',
            'options' => $advanced_settings_fields,
        ],
    ];

    // Reveal choices map the settings panel onto every real effect ("None" reveals nothing =
    // the off state, no separate switch).
    $reveal_choices = [ 'none' => [] ];
    foreach ( $effect_choices as $optgroup ) {
        if ( empty( $optgroup['choices'] ) || ! is_array( $optgroup['choices'] ) ) { continue; }
        foreach ( $optgroup['choices'] as $effect_key => $effect_label ) {
            $reveal_choices[ $effect_key ] = $reveal_group;
        }
    }

    // Picker tiles — an animated-SVG image grid (each effect is a moving box previewing the
    // motion, with its name baked in), shown in a popover. Tiles live in the shortcodes ext
    // static dir; the filename is the effect key without the `animate__` prefix (`none.svg`
    // for the off state). "None" leads the flat grid.
    $sc_ext    = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' ) : null;
    $tile_base = $sc_ext ? $sc_ext->get_declared_URI( '/static/img/entrance-effects' ) : '';
    $tile      = function ( $file, $label ) use ( $tile_base ) {
        return [
            'small' => [ 'src' => $tile_base . '/' . $file . '.svg', 'height' => 96 ],
            'large' => [ 'src' => $tile_base . '/' . $file . '.svg', 'height' => 150 ],
            'label' => $label,
        ];
    };
    $effect_tiles = [ 'none' => $tile( 'none', __( 'None', 'fw' ) ) ];
    foreach ( $effect_choices as $optgroup ) {
        if ( empty( $optgroup['choices'] ) || ! is_array( $optgroup['choices'] ) ) { continue; }
        foreach ( $optgroup['choices'] as $effect_key => $effect_label ) {
            $effect_tiles[ $effect_key ] = $tile( str_replace( 'animate__', '', $effect_key ), $effect_label );
        }
    }

    $fields = [
        'animation' => [
            'type'         => 'multi-picker',
            'popover'      => true,
            // Popover multi-picker: the user-visible label lives on the TOP level; the picker
            // sub-option is label => false (matches the engine's Scroll Motion / Physics pickers).
            'label'        => __( 'Entrance Animation', 'fw' ),
            'desc'         => __( 'Animate the element as it enters view — plus attention-seekers like Pulse and Wobble. Powered by Animate.css.', 'fw' ),
            'help'         => __( 'Entrance Animation (Animate.css): plays a one-shot reveal as the element scrolls into view — fades, slides, zooms, flips and back / bounce / rotate entrances, plus attention-seekers like Pulse, Bounce, Shake and Wobble. After you pick an effect you can set its speed, delay, repeat count, loop and easing. Honours “reduce motion” (shows the element with no animation) and loads Animate.css only on pages that actually use an entrance. Part of core — available with or without the Animation Engine.', 'fw' ),
            'show_borders' => false,
            'value'        => [ 'effect' => 'none' ],
            // Metadata for the Animations-tab organizer (animation-stack container): which
            // inserter category + icon this card shows under.
            'anim_meta'    => [ 'category' => __( 'Entrance', 'fw' ), 'icon' => '&#10024;' ], // ✨
            'picker' => [
                'effect' => [
                    'type'    => 'image-picker',
                    'label'   => false,
                    'value'   => 'none',
                    'choices' => $effect_tiles,
                ],
            ],
            'choices' => $reveal_choices,
        ],
    ];

    // Append the GSAP "Scroll Motion" block (separate engine, separate saved
    // value key `gsap_motion` — no migration of existing `animation` saves).
    /**
     * Discoverability nudge: when the Animation Engine is NOT active, point users to it
     * (it's what adds Scroll Motion / Hover / WebGL). Removed automatically once the
     * engine is on — its own field groups then appear right here instead.
     */
    if ( ! function_exists( 'fw_ext' ) || ! fw_ext( 'animation-engine' ) ) {
        $fields['animation_engine_promo'] = [
            'type'  => 'html',
            'label' => false,
            'desc'  => false,
            'html'  => '<div style="margin-top:16px;padding:12px 14px;border:1px dashed #c3d9f0;border-radius:6px;background:#f4f9ff;color:#3a4a5c;font-size:13px;line-height:1.55;">'
                . '<strong style="color:#2f74e6;">&#10024; ' . esc_html__( 'Want more?', 'fw' ) . '</strong> '
                . esc_html__( 'Add scroll-driven motion, hover interactions and real-time WebGL effects by enabling the', 'fw' )
                . ' <a href="' . esc_url( admin_url( 'admin.php?page=fw-extensions' ) ) . '" target="_blank" rel="noopener" style="font-weight:600;">'
                . esc_html__( 'Animation Engine extension', 'fw' ) . '</a> '
                . esc_html__( 'in the Extension Manager.', 'fw' )
                . '</div>',
        ];
    }

    /**
     * Scroll Motion (GSAP) and Hover Interactions are provided by the Animation Engine
     * extension, which appends its field groups here via this filter — so they appear
     * only when the engine is active. Core ships just the Animate.css Entrance block
     * above, keeping the lightweight path free of GSAP / WebGL / Three.js.
     */
    $fields = apply_filters( 'sc_animation_fields', $fields );

    /**
     * Wrap every module field in the `animation-stack` container — the Animations-tab organizer
     * (card stack + "Add Animation" inserter). A container renders/collects its children WITHOUT
     * namespacing (like group/box/tab), so each module keeps saving under its own key
     * (animation / interaction / physics / gsap_motion / …) — zero value migration. When the
     * container type is registered on fw_container_types_init (below), which fires before any
     * options render.
     */
    return [
        'animation_stack' => [
            'type'    => 'animation-stack',
            'label'   => false,
            'options' => $fields,
        ],
    ];
}
endif;


/**
 * Register the `animation-stack` container type (the Animations-tab organizer). Container types
 * must be registered on the `fw_container_types_init` action.
 */
add_action( 'fw_container_types_init', function () {
    if ( class_exists( 'FW_Container_Type' ) && ! class_exists( 'FW_Container_Type_Animation_Stack' ) ) {
        require_once __DIR__ . '/container-types/animation-stack/class-fw-container-type-animation-stack.php';
        FW_Container_Type::register( 'FW_Container_Type_Animation_Stack' );
    }
} );


/**
 * Marks/queries a per-request flag that says "at least one animated shortcode
 * has rendered on this page". Used to gate the wp_footer enqueue.
 */
if ( ! function_exists( 'sc_animation_flag' ) ) :
function sc_animation_flag( $set = false ) {
    static $used = false;
    if ( $set ) $used = true;
    return $used;
}
endif;


/**
 * Injects animation classes, data attributes and CSS-variable inline styles into
 * the shortcode wrapper. Runs after the responsive-hide filter registered in
 * shortcode-get-option-helpers.php.
 */
add_filter( 'sc_build_wrapper_attr', function ( $attr, $atts ) {
    $anim = ( isset( $atts['animation'] ) && is_array( $atts['animation'] ) ) ? $atts['animation'] : [];

    // The picker IS the effect select now ('none' = off, no separate Enable switch); the
    // per-effect settings live under the chosen effect's key (Scroll Motion / Physics shape).
    $effect = isset( $anim['effect'] ) ? (string) $anim['effect'] : 'none';
    if ( $effect === 'none' || ! preg_match( '/^animate__[a-zA-Z]+$/', $effect ) ) {
        return $attr;
    }

    $settings = ( isset( $anim[ $effect ] ) && is_array( $anim[ $effect ] ) ) ? $anim[ $effect ] : [];

    $speed_preset = isset( $settings['speed_preset'] ) ? (string) $settings['speed_preset'] : '';
    if ( $speed_preset && ! preg_match( '/^animate__[a-zA-Z]+$/', $speed_preset ) ) {
        $speed_preset = '';
    }

    $delay        = (float) ( $settings['delay']           ?? 0 );
    $duration     = (float) ( $settings['custom_duration'] ?? 0 );
    $repeat_count = (int)   ( $settings['repeat_count']    ?? 1 );
    $loop_forever = ! empty( $settings['loop_forever'] )     && $settings['loop_forever']     === 'yes';
    $replay       = ! empty( $settings['replay_on_scroll'] ) && $settings['replay_on_scroll'] === 'yes';
    $easing       = (string) ( $settings['easing'] ?? '' );

    // Build the class list that JS will apply on intersection.
    $anim_classes = [ 'animate__animated' ];
    if ( $speed_preset ) $anim_classes[] = $speed_preset;
    $anim_classes[] = $effect;
    if ( $loop_forever ) $anim_classes[] = 'animate__infinite';

    $anim_classes = array_map( 'sanitize_html_class', $anim_classes );
    $data_anim    = implode( ' ', $anim_classes );

    // Mark the element as "waiting to animate". CSS hides it until JS adds the classes.
    $existing_class = isset( $attr['class'] ) ? trim( (string) $attr['class'] ) : '';
    $attr['class']  = esc_attr( $existing_class === '' ? 'sc-anim-pending' : $existing_class . ' sc-anim-pending' );

    $attr['data-sc-anim'] = esc_attr( $data_anim );
    if ( $replay ) {
        $attr['data-sc-anim-replay'] = '1';
    }

    // CSS custom properties — Animate.css v4 reads these natively.
    $css_vars = [];
    if ( $delay > 0 )                                          $css_vars[] = '--animate-delay: '    . rtrim( rtrim( number_format( $delay, 2, '.', '' ),    '0' ), '.' ) . 's';
    if ( $duration > 0 )                                       $css_vars[] = '--animate-duration: ' . rtrim( rtrim( number_format( $duration, 2, '.', '' ), '0' ), '.' ) . 's';
    if ( ! $loop_forever && $repeat_count > 1 )                $css_vars[] = '--animate-repeat: '   . $repeat_count;
    if ( $easing && preg_match( '/^[a-zA-Z0-9\.,\-\(\)\s]+$/', $easing ) ) {
        $css_vars[] = '--animate-easing: ' . $easing;
    }

    if ( ! empty( $css_vars ) ) {
        $css_string     = implode( '; ', $css_vars ) . ';';
        $existing_style = isset( $attr['style'] ) ? trim( (string) $attr['style'] ) : '';
        $attr['style']  = esc_attr( $existing_style === '' ? $css_string : rtrim( $existing_style, '; ' ) . '; ' . $css_string );
    }

    // Flag the request so wp_footer enqueues the assets.
    sc_animation_flag( true );

    return $attr;
}, 20, 2 );


/**
 * Conditionally enqueue animate.min.css + sc-animations.js at the very start
 * of wp_footer. Only fires if at least one shortcode rendered with animation on.
 */
add_action( 'wp_footer', function () {
    if ( ! sc_animation_flag() ) return;

    $shortcodes_ext = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' ) : null;
    if ( ! $shortcodes_ext ) return;

    $css_uri = fw_min_uri($shortcodes_ext->get_declared_URI( '/static/css/animate.min.css' ));
    $js_uri  = fw_min_uri($shortcodes_ext->get_declared_URI( '/static/js/sc-animations.js' ));

    wp_enqueue_style( 'animate-css', $css_uri, [], '4.1.1' );

    $inline_css = '.sc-anim-pending{visibility:hidden;}'
                . '.sc-anim-pending.animate__animated{visibility:visible;}'
                . '@media (prefers-reduced-motion: reduce){'
                .   '.animate__animated{animation:none !important;}'
                .   '.sc-anim-pending{visibility:visible !important;}'
                . '}';
    wp_add_inline_style( 'animate-css', $inline_css );

    wp_enqueue_script( 'sc-animations', $js_uri, [], '1.0.0', true );
}, 5 );


/**
 * Entrance-picker tile size (admin builder ONLY, entrance-scoped).
 *
 * The Entrance Animation popover uses animated-SVG tiles with the effect name baked in, so they
 * need to render taller than the usual engine popover tiles for the label to read. The core
 * multi-picker CSS caps ALL popover tiles at 72px in the large modal / Theme Settings, which
 * overrides the picker's own per-choice height — that cap stays for the OTHER popovers. So the
 * entrance block owns its tile size here, targeting only its swatches by their `/entrance-effects/`
 * src path (higher specificity than the cap). Printed on admin pages only. Mirrors the pattern the
 * Animation Engine's physics module uses.
 */
add_action( 'admin_head', function () {
    $sel = 'ul.thumbnails.image_picker_selector li .thumbnail img[src*="/entrance-effects/"]';
    echo '<style id="sc-entrance-picker-size">'
        . '.fw-mp-pop ' . $sel . ','
        . '.fw-modal-large .fw-mp-pop ' . $sel . ','
        . '.appearance_page_fw-settings .fw-mp-pop ' . $sel
        . '{height:96px !important;width:auto !important;}'
        . "</style>\n";
} );
