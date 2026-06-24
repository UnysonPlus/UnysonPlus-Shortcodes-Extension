<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 */

if ( ! function_exists( 'sc_button_kses_label' ) ) {
    /**
     * Sanitize a button label that may contain an inline <svg> icon (or basic
     * inline formatting) without flattening it to escaped source text.
     *
     * Extends post-context kses with the SVG element + presentation attributes
     * needed for a typical pasted icon (Feather / Lucide / Heroicons style),
     * so the icon renders on the front end while scripts / event handlers /
     * disallowed tags are still stripped. A label with no markup is returned
     * effectively unchanged.
     *
     * @param string $label Raw label string from the option value.
     * @return string Sanitized HTML safe to echo.
     */
    function sc_button_kses_label( $label ) {
        $label = (string) $label;

        // Fast path: no tags → behave exactly like the old esc_html().
        if ( strpos( $label, '<' ) === false ) {
            return esc_html( $label );
        }

        $svg_global = array(
            'class'             => true,
            'id'                => true,
            'style'             => true,
            'aria-hidden'       => true,
            'aria-label'        => true,
            'role'              => true,
            'focusable'         => true,
            'fill'              => true,
            'stroke'            => true,
            'stroke-width'      => true,
            'stroke-linecap'    => true,
            'stroke-linejoin'   => true,
            'stroke-dasharray'  => true,
            'stroke-dashoffset' => true,
            'stroke-opacity'    => true,
            'fill-opacity'      => true,
            'fill-rule'         => true,
            'clip-rule'         => true,
            'opacity'           => true,
            'transform'         => true,
        );

        $allowed = array(
            'svg'      => array_merge( $svg_global, array(
                'xmlns'               => true,
                'xmlns:xlink'         => true,
                'viewbox'             => true,
                'width'               => true,
                'height'              => true,
                'preserveaspectratio' => true,
                'version'             => true,
            ) ),
            'g'        => $svg_global,
            'path'     => array_merge( $svg_global, array( 'd' => true ) ),
            'circle'   => array_merge( $svg_global, array( 'cx' => true, 'cy' => true, 'r' => true ) ),
            'ellipse'  => array_merge( $svg_global, array( 'cx' => true, 'cy' => true, 'rx' => true, 'ry' => true ) ),
            'rect'     => array_merge( $svg_global, array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true ) ),
            'line'     => array_merge( $svg_global, array( 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true ) ),
            'polyline' => array_merge( $svg_global, array( 'points' => true ) ),
            'polygon'  => array_merge( $svg_global, array( 'points' => true ) ),
            'use'      => array_merge( $svg_global, array( 'href' => true, 'xlink:href' => true, 'x' => true, 'y' => true, 'width' => true, 'height' => true ) ),
            'defs'     => $svg_global,
            'title'    => $svg_global,
            'desc'     => $svg_global,
            // Basic inline formatting people occasionally put in a label.
            'span'     => array( 'class' => true, 'style' => true ),
            'i'        => array( 'class' => true, 'style' => true ),
            'em'       => array( 'class' => true, 'style' => true ),
            'strong'   => array( 'class' => true, 'style' => true ),
            'b'        => array( 'class' => true, 'style' => true ),
            'small'    => array( 'class' => true, 'style' => true ),
            'br'       => array(),
        );

        return wp_kses( $label, $allowed );
    }
}

// Enqueue ONLY the icon-v2 pack this button's icon needs (Font Awesome / Entypo /
// Linearicons / Typicons / Unycon …), rather than every pack — so a page with one
// Font Awesome glyph doesn't also pull the other pack stylesheets.
if (
    ! empty( $atts['icon'] ) &&
    isset( fw()->backend->option_type( 'icon-v2' )->packs_loader )
) {
    fw()->backend->option_type( 'icon-v2' )->packs_loader->enqueue_pack_for_icon( $atts['icon'] );
}

$atts['base_class']       = 'btn';
// Unique per-element class prefixed `btn-` (e.g. btn-8e730e61) so it reads as part of the button
// namespace. It's a hex id, so it never collides with a `btn-{preset}` style class.
$atts['unique_id_prefix'] = 'btn-';
$atts['extra_attrs']      = [];

// Margin/padding from the `spacing` field auto-applies to the wrapper via the
// sc_apply_styling_classes filter (it flattens $atts['spacing'] into m-*/p-*
// utility classes). Colors + base sizing come from the Style + Size presets.
$attr = sc_build_wrapper_attr( $atts );

$classes = ['btn'];
$style   = !empty($atts['style']) ? $atts['style'] : '';
$size    = !empty($atts['size']) ? $atts['size'] : '';
$state   = !empty($atts['state']) ? $atts['state'] : '';

// Width is a multi-picker: $atts['width'] = [ 'mode' => ''|'w-100'|'custom',
// 'custom' => [ 'custom_width' => {value,unit} ] ]. Back-compat: older saves stored a
// flat string `width` plus a separate `custom_width` att. Also falls back to the
// legacy `block` value for buttons saved before the Width control existed.
$width_raw = isset($atts['width']) ? $atts['width'] : '';
if (is_array($width_raw)) {
    $width_mode = isset($width_raw['mode']) ? (string) $width_raw['mode'] : '';
    $cw_raw     = isset($width_raw['custom']['custom_width']) ? $width_raw['custom']['custom_width'] : '';
} else {
    $width_mode = (string) $width_raw;
    $cw_raw     = isset($atts['custom_width']) ? $atts['custom_width'] : '';
}
if ($width_mode === '' && !empty($atts['block'])) {
    $width_mode = (string) $atts['block'];
}
$block        = ($width_mode === 'w-100') ? 'w-100' : '';
$custom_width = '';
if ($width_mode === 'custom' && !empty($cw_raw) && class_exists('FW_Option_Type_Unit_Input')) {
    $custom_width = FW_Option_Type_Unit_Input::to_string($cw_raw);
}

// Style preset (e.g. btn-primary, btn-outline-primary) carries the colors.
if ($style) {
    $classes[] = $style;
}
if ($size) {
    $classes[] = $size;
}
if ($block) {
    $classes[] = $block;
}
if ($state === 'active') {
    $classes[] = 'active';
}
if ($state === 'disabled') {
    $classes[] = 'disabled';
}

// Hover animation (motion-only .btnfx-* class from hover-fx.css). Value is
// whitelisted by the picker against its choices; guard the shape anyway.
$hover = !empty($atts['hover_animation']) ? (string) $atts['hover_animation'] : '';
if ($hover !== '' && preg_match('/^btnfx-[a-z0-9-]+$/', $hover)) {
    $classes[] = $hover;
}

// Custom width → inline style on the <a>, appended to any existing style.
if ($custom_width !== '') {
    $existing      = isset($attr['style']) ? rtrim($attr['style'], '; ') : '';
    $width_decl    = 'width: ' . $custom_width;
    $attr['style'] = $existing === '' ? $width_decl . ';' : $existing . '; ' . $width_decl . ';';
}

if (!empty($attr['class'])) {
    // $attr['class'] carries the base class ('btn') + the unique id class ('bt-…'); push each
    // token so the array_unique() below collapses the duplicate 'btn' we already seeded.
    foreach (preg_split('/\s+/', $attr['class']) as $cl) {
        if ($cl !== '') { $classes[] = $cl; }
    }
    unset($attr['class']);
}

// Icon inherits the button's text color (currentColor) from the Style preset.
$icon_html = '';
if (!empty($atts['icon']) && is_array($atts['icon'])) {
    $icon_class = !empty($atts['icon']['icon-class']) ? $atts['icon']['icon-class'] : '';
    if (!empty($icon_class) && is_string($icon_class)) {
        $icon_html = '<i class="' . esc_attr($icon_class) . '"></i>';
        // Deterministic hook for the flex centering CSS — more reliable than a
        // :has() selector (which silently no-ops on older browsers).
        $classes[] = 'has-icon';
    }
}

$icon_position = !empty($atts['icon_position']) ? $atts['icon_position'] : 'before';

// The label may contain inline markup — most commonly an inline <svg> icon
// pasted as the label. esc_html() would render that as visible source text on
// the front end (the page-builder canvas shows it fine because the JS
// title_template doesn't escape). Run it through wp_kses with an allowed-tags
// set that permits inline SVG + basic inline formatting so the icon renders,
// while still stripping scripts / event handlers. Plain-text labels pass
// through unchanged.
$label_raw    = isset($atts['label']) ? (string) $atts['label'] : '';
$label_output = sc_button_kses_label($label_raw);

$button_content = '';
if ($icon_html && $icon_position === 'before') {
    $button_content .= $icon_html . ' ';
}
$button_content .= $label_output;
if ($icon_html && $icon_position === 'after') {
    $button_content .= ' ' . $icon_html;
}

$target = !empty($atts['target']) && is_string($atts['target']) ? $atts['target'] : '_self';

$disabled_attr = '';
if ($state === 'disabled') {
    $disabled_attr = ' aria-disabled="true" tabindex="-1"';
}

// Alignment: wrap the (inline-block) button in a text-align div only when a
// non-default alignment is chosen. Moot when Width is Full Width (button spans).
$align       = !empty($atts['alignment']) ? (string) $atts['alignment'] : '';
$align_open  = '';
$align_close = '';
if (in_array($align, array('left', 'center', 'right'), true)) {
    $align_open  = '<div class="sc-btn-align" style="text-align: ' . esc_attr($align) . ';">';
    $align_close = '</div>';
}

// De-duplicate while preserving order — the seeded base class, the wrapper's base class, and a
// style preset can otherwise repeat 'btn' (e.g. "btn btn-primary btn bt-1234").
$classes = array_values(array_unique(array_filter($classes)));
?>
<?php echo $align_open; ?>
<a href="<?php echo esc_url($atts['link']); ?>"
   target="<?php echo esc_attr($target); ?>"
   class="<?php echo esc_attr(implode(' ', $classes)); ?>"
   <?php echo $disabled_attr; ?>
   <?php echo fw_attr_to_html($attr); ?>>
    <?php echo $button_content; ?>
</a>
<?php echo $align_close; ?>
