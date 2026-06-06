<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 */

// Enqueue the icon-v2 pack CSS on the front end when an icon is used. Without
// this only globally-loaded packs (Font Awesome, Dashicons) render; Linecons /
// Entypo / Linearicons / Typicons / Unycon have no global handle, so their
// stylesheet must be enqueued here (same pattern as the icon / icon-box views).
if (
    ! empty( $atts['icon'] ) &&
    isset( fw()->backend->option_type( 'icon-v2' )->packs_loader )
) {
    fw()->backend->option_type( 'icon-v2' )->packs_loader->enqueue_frontend_css();
}

$atts['base_class']       = 'btn';
$atts['unique_id_prefix'] = 'bt-';
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
    $classes[] = $attr['class'];
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

$button_content = '';
if ($icon_html && $icon_position === 'before') {
    $button_content .= $icon_html . ' ';
}
$button_content .= esc_html($atts['label']);
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
