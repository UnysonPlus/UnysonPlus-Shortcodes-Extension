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
 *   3. On-demand enqueue: the shared base + ONLY the used effects' Animate.css
 *      partials (static/css/animate/) + sc-animations.js are loaded, and only when
 *      at least one shortcode on the rendered page has animations enabled.
 *
 * No view.php files need to be modified — the filter pipes everything through
 * the shared wrapper-attribute builder used by every shortcode today.
 */
if ( ! defined( 'FW' ) ) die( 'Forbidden' );

// Reusable easing picker (anime.js-style set) — sc_easing_field() / sc_easing_css().
require_once __DIR__ . '/shortcode-easing-helper.php';


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
        // Easing — the `easing-picker` type renders only a LIGHT trigger here (thumbnail + name),
        // so it is safe inside this panel even though it is duplicated onto all ~56 effect reveals;
        // the 41-tile grid is a single SHARED palette built client-side (see the option type). Lives
        // right where the old easing select did — in the popover's Advanced Tweaks. Resolved to a
        // CSS animation-timing-function at render via sc_easing_css().
        'easing' => [
            'type'  => 'easing-picker',
            'label' => __( 'Easing', 'fw' ),
            'desc'  => __( 'Timing curve for this entrance — ~40 anime.js-style easings (Spring / Elastic / Bounce / Back / Steps …). Default keeps the effect\'s built-in curve. Spring / Elastic / Bounce need a 2023+ browser.', 'fw' ),
            'value' => 'default',
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
    // Trigger — decouples WHEN the effect plays from the effect itself (the "unified trigger"
    // control, mirroring Confetti). MULTI-SELECT: toggle any combination of tiles, so one animation
    // can fire on several events — e.g. reveal on "Scroll into view" AND replay on "Click". The
    // entrance triggers (view / load) hide the element until first play; the interaction triggers
    // (click / hover) leave it visible and replay on each event. If view + load are both on, load
    // wins (it fires immediately). Value is an ARRAY of the selected keys; a legacy scalar 'view'
    // save is tolerated (the image-picker renders it as nothing-selected and the reader falls back
    // to 'view'). Tiles live in static/img/triggers/<key>.svg.
    $trig_ext  = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' ) : null;
    $trig_base = $trig_ext ? $trig_ext->get_declared_URI( '/static/img/triggers' ) : '';
    $trig_tile = function ( $key, $label ) use ( $trig_base ) {
        return [
            'small' => [ 'src' => $trig_base . '/' . $key . '.svg', 'height' => 30, 'title' => $label ],
            'label' => $label,
        ];
    };
    $trigger_field = [
        'label'      => __( 'Trigger', 'fw' ),
        'desc'       => __( 'When the animation plays — pick one or more. “Scroll into view” and “Page load” are entrances (the element is hidden until it plays); “Click” and “Hover” keep it visible and replay on each event. Combine them, e.g. reveal on scroll and replay on click.', 'fw' ),
        'type'       => 'image-picker',
        'multiple'   => true,
        'show_label' => false,
        'value'      => [ 'view' ],
        // Labels are hidden by default and reveal as a tooltip on hover (see animation-stack.css),
        // so the tiles stay a uniform icon-only row while keeping descriptive names.
        'choices'    => [
            'view'  => $trig_tile( 'view',  __( 'Scroll into view', 'fw' ) ),
            'load'  => $trig_tile( 'load',  __( 'Page load', 'fw' ) ),
            'click' => $trig_tile( 'click', __( 'Click', 'fw' ) ),
            'hover' => $trig_tile( 'hover', __( 'Hover', 'fw' ) ),
        ],
    ];

    $reveal_group = [
        'group_animation_basic' => [
            'type'    => 'group',
            'options' => [
                'trigger'      => $trigger_field,
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

    // The picker itself reveals NOTHING per-effect. Mapping the identical $reveal_group onto all
    // ~56 effect keys made the multi-picker render that panel 56 times (~7MB of options HTML, even
    // with the lazy data-options-template path). Instead the settings render ONCE as a shared
    // `animation_settings` panel attached inside the card (see below). 'none' = the off state.
    $reveal_choices = [ 'none' => [] ];

    // Picker tiles — an animated-SVG image grid (each effect is a moving box previewing the
    // motion, with its name baked in), shown in a popover. Tiles live in the shortcodes ext
    // static dir; the filename is the effect key without the `animate__` prefix (`none.svg`
    // for the off state). "None" leads the flat grid.
    $sc_ext    = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' ) : null;
    $tile_base = $sc_ext ? $sc_ext->get_declared_URI( '/static/img/entrance-effects' ) : '';
    $tile      = function ( $file, $label ) use ( $tile_base ) {
        return [
            'small' => [ 'src' => $tile_base . '/' . $file . '.svg', 'height' => 86 ],
            'large' => [ 'src' => $tile_base . '/' . $file . '.svg', 'height' => 190 ],
            'label' => $label,
        ];
    };
    // Grouped tiles — each category becomes an image-picker GROUP (rendered as an <optgroup>, which
    // the picker turns into a category header). No "None" tile: to remove an entrance the user just
    // deletes the card (×). The off state (value 'none') simply selects nothing; the trigger shows
    // the "None" placeholder. The search box + headers come from the image-picker's `search` option.
    $effect_tiles = [];
    foreach ( $effect_choices as $gi => $optgroup ) {
        if ( empty( $optgroup['choices'] ) || ! is_array( $optgroup['choices'] ) ) { continue; }
        $glabel      = isset( $optgroup['attr']['label'] ) ? $optgroup['attr']['label'] : '';
        $group_tiles = [];
        foreach ( $optgroup['choices'] as $effect_key => $effect_label ) {
            $group_tiles[ $effect_key ] = $tile( str_replace( 'animate__', '', $effect_key ), $effect_label );
        }
        $effect_tiles[ 'grp_' . $gi ] = [ 'label' => $glabel, 'choices' => $group_tiles ];
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
            // Popover trigger text when nothing is picked (there is no "None" tile — value 'none'
            // selects nothing; delete the card to remove the entrance).
            'placeholder'  => __( 'None', 'fw' ),
            // Metadata for the Animations-tab organizer (animation-stack container): which
            // inserter category + icon this card shows under.
            'anim_meta'    => [ 'category' => __( 'Entrance', 'fw' ), 'icon' => '&#10024;' ], // ✨
            'picker' => [
                'effect' => [
                    'type'    => 'image-picker',
                    'label'   => false,
                    'value'   => 'none',
                    'search'  => __( 'Search entrance effects…', 'fw' ),
                    'layout'  => 'tabs',
                    'choices' => $effect_tiles,
                ],
            ],
            'choices' => $reveal_choices,
        ],
        // The Entrance settings (Trigger / Speed / Advanced Tweaks) rendered ONCE — a single shared
        // panel, NOT duplicated per effect. `anim_attach => 'animation'` makes the animation-stack
        // container render it INSIDE the Entrance card (below the effect picker), so it shows/hides
        // with the card. Value is a `multi`, so it saves under its own fixed key `animation_settings`
        // (no per-effect key). This is the whole point of the dedup: ~125KB instead of ~7MB. The
        // trade-off is that the tweaks are shared across effects (no per-effect memory). The wrapper
        // reader falls back to the legacy per-effect location for pre-dedup saves.
        'animation_settings' => [
            'type'          => 'multi',
            'label'         => false,
            'desc'          => false,
            'anim_attach'   => 'animation',
            'inner-options' => $reveal_group,
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
     * Multi-instance expansion. A module that sets `anim_meta['multi'] => true` can be added to an
     * element MORE THAN ONCE (e.g. Hover = Lift + Ripple, Text Effect = Slide + Rainbow + Neon).
     * We pre-declare up to N slots per such module — the base key plus `<key>__2 … __N` — so each
     * instance saves under its own key (the fields must exist in the declared options for their
     * values to persist; the container just shows/hides them as cards). Every slot is a deep copy
     * tagged with its base id + index so the container groups them under one inserter tile.
     *
     * Slots are capped at 2 (was 4): each pre-declared slot renders its FULL picker eagerly even
     * when inactive, so a 43-choice module like Hover cost ~0.7MB PER slot (~2.75MB for 4). Two
     * instances covers the realistic case (e.g. Hover Lift + Ripple) at half the modal weight.
     */
    $fields = sc_expand_multi_animation_fields( $fields, 2 );

    /**
     * Wrap every module field in the `animation-stack` container — the Animations-tab organizer
     * (card stack + "Add Animation" inserter). A container renders/collects its children WITHOUT
     * namespacing (like group/box/tab), so each module keeps saving under its own key
     * (animation / interaction / physics / gsap_motion / …) — zero value migration. The container
     * type is registered on fw_container_types_init (below), which fires before any options render.
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
 * Expand `anim_meta['multi']` module fields into up to $max instance slots (base + `<key>__2..__N`).
 * Each field (base and slot) is tagged `anim_meta['multi_base']` (the base key) and
 * `anim_meta['multi_index']` (1..N) so the container can group slots under one inserter tile and
 * reveal the next empty one on "Add". Single-instance fields pass through untouched, order kept.
 */
if ( ! function_exists( 'sc_expand_multi_animation_fields' ) ) :
function sc_expand_multi_animation_fields( $fields, $max = 4 ) {
    if ( ! is_array( $fields ) ) {
        return $fields;
    }
    $out = [];
    foreach ( $fields as $key => $field ) {
        $is_multi = is_array( $field ) && ! empty( $field['anim_meta']['multi'] );
        if ( ! $is_multi ) {
            $out[ $key ] = $field;
            continue;
        }
        for ( $i = 1; $i <= max( 1, (int) $max ); $i++ ) {
            $slot_key = ( $i === 1 ) ? $key : $key . '__' . $i;
            $slot     = $field; // deep value-copy (arrays copy by value in PHP)
            $slot['anim_meta']['multi_base']  = $key;
            $slot['anim_meta']['multi_index'] = $i;
            $slot['anim_meta']['multi_max']   = (int) $max;
            $out[ $slot_key ] = $slot;
        }
    }
    return $out;
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
 * On-demand asset registry for entrance animations — records which Animate.css
 * effect classes actually rendered this request, so wp_footer enqueues ONLY those
 * effects' CSS partials (+ the shared base) instead of the whole 72 KB bundle.
 * Pass an 'animate__<name>' class to record it; call with no arg to read the set.
 */
if ( ! function_exists( 'sc_animation_use' ) ) :
function sc_animation_use( $effect = null ) {
    static $used = [];
    if ( $effect !== null && preg_match( '/^animate__[a-zA-Z]+$/', (string) $effect ) ) {
        $used[ $effect ] = true;
    }
    return array_keys( $used );
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

    // Settings now live in the shared `animation_settings` panel (one panel for all effects).
    // Fall back to the legacy per-effect location ( animation[<effect>] ) for pre-dedup saves so
    // existing elements keep animating with their saved tweaks until re-saved.
    $settings = ( isset( $atts['animation_settings'] ) && is_array( $atts['animation_settings'] ) ) ? $atts['animation_settings'] : [];
    if ( empty( $settings ) && isset( $anim[ $effect ] ) && is_array( $anim[ $effect ] ) ) {
        $settings = $anim[ $effect ];
    }

    $speed_preset = isset( $settings['speed_preset'] ) ? (string) $settings['speed_preset'] : '';
    if ( $speed_preset && ! preg_match( '/^animate__[a-zA-Z]+$/', $speed_preset ) ) {
        $speed_preset = '';
    }

    $delay        = (float) ( $settings['delay']           ?? 0 );
    $duration     = (float) ( $settings['custom_duration'] ?? 0 );
    $repeat_count = (int)   ( $settings['repeat_count']    ?? 1 );
    $loop_forever = ! empty( $settings['loop_forever'] )     && $settings['loop_forever']     === 'yes';
    $replay       = ! empty( $settings['replay_on_scroll'] ) && $settings['replay_on_scroll'] === 'yes';
    // Easing lives back in the per-effect panel (an easing-picker); fall back to the brief
    // sibling-field location used in 1.10.95–1.10.97 saves. 'default'/'' = no override.
    $easing       = (string) ( $settings['easing'] ?? ( $atts['animation_easing'] ?? '' ) );
    if ( $easing === 'default' ) { $easing = ''; }

    // Build the class list that JS will apply on intersection.
    $anim_classes = [ 'animate__animated' ];
    if ( $speed_preset ) $anim_classes[] = $speed_preset;
    $anim_classes[] = $effect;
    if ( $loop_forever ) $anim_classes[] = 'animate__infinite';

    $anim_classes = array_map( 'sanitize_html_class', $anim_classes );
    $data_anim    = implode( ' ', $anim_classes );

    // Trigger(s): a MULTI-SELECT of view | load | click | hover (value is an array). Tolerate the
    // legacy scalar save (a single string) and an empty/absent value → default to 'view'. For
    // click/hover the element must stay visible and interactive, so it is NOT hidden until play;
    // view/load keep the hide-until-play. If ANY of view/load is on, the element hides until play.
    $raw_trigger = isset( $settings['trigger'] ) ? $settings['trigger'] : array( 'view' );
    $triggers    = is_array( $raw_trigger ) ? $raw_trigger : ( $raw_trigger === '' ? array() : array( (string) $raw_trigger ) );
    $triggers    = array_values( array_intersect( array_map( 'strval', $triggers ), array( 'view', 'load', 'click', 'hover' ) ) );
    if ( empty( $triggers ) ) { $triggers = array( 'view' ); }
    $hide_until_play = in_array( 'view', $triggers, true ) || in_array( 'load', $triggers, true );

    if ( $hide_until_play ) {
        // Mark the element as "waiting to animate". CSS hides it until JS adds the classes.
        $existing_class = isset( $attr['class'] ) ? trim( (string) $attr['class'] ) : '';
        $attr['class']  = esc_attr( $existing_class === '' ? 'sc-anim-pending' : $existing_class . ' sc-anim-pending' );
    }

    $attr['data-sc-anim'] = esc_attr( $data_anim );
    // Emit the space-joined trigger list. Omit for the plain default (['view']) to keep markup lean
    // and backward-compatible (sc-animations.js treats a missing attr as 'view').
    if ( $triggers !== array( 'view' ) ) {
        $attr['data-sc-anim-trigger'] = esc_attr( implode( ' ', $triggers ) );
    }
    // Replay-on-scroll applies to the view trigger; click/hover naturally re-fire per event.
    if ( $replay && in_array( 'view', $triggers, true ) ) {
        $attr['data-sc-anim-replay'] = '1';
    }

    // CSS custom properties — Animate.css v4 reads these natively.
    $css_vars = [];
    if ( $delay > 0 )                                          $css_vars[] = '--animate-delay: '    . rtrim( rtrim( number_format( $delay, 2, '.', '' ),    '0' ), '.' ) . 's';
    if ( $duration > 0 )                                       $css_vars[] = '--animate-duration: ' . rtrim( rtrim( number_format( $duration, 2, '.', '' ), '0' ), '.' ) . 's';
    if ( ! $loop_forever && $repeat_count > 1 )                $css_vars[] = '--animate-repeat: '   . $repeat_count;

    // Easing — resolve the picker KEY to a CSS timing-function and apply it DIRECTLY (Animate.css
    // has no --animate-easing var, so setting one was a no-op; an inline animation-timing-function
    // actually takes effect — for the segments a keyframe doesn't override). Legacy raw-CSS values
    // pass through sc_easing_css() unchanged. `linear()`/`%` are allowed for the sampled curves.
    $easing_css = function_exists( 'sc_easing_css' ) ? sc_easing_css( $easing ) : '';
    if ( $easing_css !== '' && preg_match( '/^[a-zA-Z0-9\.,\-\(\)\s%]+$/', $easing_css ) ) {
        $css_vars[] = 'animation-timing-function: ' . $easing_css;
        $css_vars[] = '-webkit-animation-timing-function: ' . $easing_css;
        $css_vars[] = '--animate-easing: ' . $easing_css; // harmless; kept for any theme that reads it
    }

    if ( ! empty( $css_vars ) ) {
        $css_string     = implode( '; ', $css_vars ) . ';';
        $existing_style = isset( $attr['style'] ) ? trim( (string) $attr['style'] ) : '';
        $attr['style']  = esc_attr( $existing_style === '' ? $css_string : rtrim( $existing_style, '; ' ) . '; ' . $css_string );
    }

    // Flag the request + record the effect so wp_footer enqueues ONLY this effect's
    // CSS partial (plus the shared base), not the whole Animate.css bundle.
    sc_animation_flag( true );
    sc_animation_use( $effect );

    return $attr;
}, 20, 2 );


/**
 * On-demand enqueue at the start of wp_footer. Instead of the whole 72 KB
 * Animate.css bundle, ship the shared base (~3 KB) plus ONLY the CSS partials for
 * the effects that actually rendered on this page — so one entrance animation costs
 * ~3.5 KB, not 72 KB. Per-effect files live in static/css/animate/effects/<name>.min.css
 * (base in static/css/animate/base.min.css); split from the upstream bundle. The
 * generic IntersectionObserver runtime (sc-animations.js) is tiny + shared, loaded
 * whenever any effect is used. Only fires if a shortcode rendered with animation on.
 */
add_action( 'wp_footer', function () {
    if ( ! sc_animation_flag() ) return;

    $shortcodes_ext = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' ) : null;
    if ( ! $shortcodes_ext ) return;

    $base_uri = $shortcodes_ext->get_declared_URI( '/static/css/animate' );
    $base_dir = $shortcodes_ext->get_declared_path( '/static/css/animate' );
    $ver      = fw_ext( 'shortcodes' )->manifest->get_version();
    $fmt      = function ( $rel ) use ( $base_dir, $ver ) {
        $abs = $base_dir . $rel;
        return file_exists( $abs ) ? $ver . '.' . filemtime( $abs ) : $ver;
    };

    // Shared base (CSS vars, .animate__animated, speed / delay / repeat / infinite utilities,
    // reduced-motion guard). Carries the .sc-anim-pending visibility helpers as inline CSS.
    wp_enqueue_style( 'animate-css-base', $base_uri . '/base.min.css', [], $fmt( '/base.min.css' ) );
    $inline_css = '.sc-anim-pending{visibility:hidden;}'
                . '.sc-anim-pending.animate__animated{visibility:visible;}'
                . '@media (prefers-reduced-motion: reduce){'
                .   '.animate__animated{animation:none !important;}'
                .   '.sc-anim-pending{visibility:visible !important;}'
                . '}';
    wp_add_inline_style( 'animate-css-base', $inline_css );

    // One partial per used effect (only the effects the page actually rendered).
    foreach ( sc_animation_use() as $effect ) {
        $name = substr( $effect, strlen( 'animate__' ) ); // e.g. animate__fadeInUp → fadeInUp
        $rel  = '/effects/' . $name . '.min.css';
        if ( ! file_exists( $base_dir . $rel ) ) continue;
        wp_enqueue_style( 'animate-css-' . $name, $base_uri . $rel, [ 'animate-css-base' ], $fmt( $rel ) );
    }

    $js_uri = fw_min_uri( $shortcodes_ext->get_declared_URI( '/static/js/sc-animations.js' ) );
    wp_enqueue_script( 'sc-animations', $js_uri, [], '1.0.0', true );
}, 5 );
