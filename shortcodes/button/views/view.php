<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 */

$atts['base_class']       = 'btn';
$atts['unique_id_prefix'] = 'bt-';

// Build attributes for wrapper (adds id, custom class, etc. from advanced tab)
$attr = sc_build_wrapper_attr($atts);

// Collect button classes
$classes   = ['btn'];
$style     = !empty($atts['style']) ? $atts['style'] : '';
$outline   = !empty($atts['outline']) ? $atts['outline'] : '';
$size      = !empty($atts['size']) ? $atts['size'] : '';
$block     = !empty($atts['block']) ? $atts['block'] : '';
$state     = !empty($atts['state']) ? $atts['state'] : '';
$custom    = !empty($atts['css_class']) ? $atts['css_class'] : '';

if ($style && !$outline) {
    $classes[] = $style;
}
if ($outline) {
    $classes[] = $outline;
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
if ($custom) {
    $classes[] = $custom;
}

// Icon handling
$icon_html = '';
if (!empty($atts['icon'])) {
    // Some Unyson icon pickers return arrays
    if (is_array($atts['icon']) && isset($atts['icon']['icon-class'])) {
        $icon_class = $atts['icon']['icon-class'];
    } elseif (is_array($atts['icon']) && isset($atts['icon']['value'])) {
        $icon_class = $atts['icon']['value'];
    } else {
        $icon_class = $atts['icon'];
    }

    if (!empty($icon_class)) {
        $icon_html = '<i class="' . esc_attr($icon_class) . '"></i>';
    }
}

// Button content
$button_content = '';
if ($icon_html && $atts['icon_position'] === 'before') {
    $button_content .= $icon_html . ' ';
}
$button_content .= esc_html($atts['label']);
if ($icon_html && $atts['icon_position'] === 'after') {
    $button_content .= ' ' . $icon_html;
}

// Disabled button handling
$disabled_attr = '';
if ($state === 'disabled') {
    $disabled_attr = ' aria-disabled="true" tabindex="-1"';
}
?>
<a href="<?php echo esc_url($atts['link']); ?>"
   target="<?php echo esc_attr($atts['target']); ?>"
   class="<?php echo esc_attr(implode(' ', $classes)); ?>"
   <?php echo $disabled_attr; ?>
   <?php echo fw_attr_to_html($attr); ?>>
    <?php echo $button_content; ?>
</a>
