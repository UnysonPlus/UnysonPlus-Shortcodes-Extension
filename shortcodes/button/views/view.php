<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 */

$atts['base_class']       = 'btn';
$atts['unique_id_prefix'] = 'bt-';

$atts['extra_attrs'] = [];

$attr = sc_build_wrapper_attr($atts);

$classes   = ['btn'];
$style     = !empty($atts['style']) ? $atts['style'] : '';
$outline   = !empty($atts['outline']) ? $atts['outline'] : '';
$size      = !empty($atts['size']) ? $atts['size'] : '';
$block     = !empty($atts['block']) ? $atts['block'] : '';
$state     = !empty($atts['state']) ? $atts['state'] : '';

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

if (!empty($attr['class'])) {
    $classes[] = $attr['class'];
    unset($attr['class']);
}

$icon_html = '';
if (!empty($atts['icon']) && is_array($atts['icon'])) {
    $icon_class = '';
    if (!empty($atts['icon']['icon-class'])) {
        $icon_class = $atts['icon']['icon-class'];
    }
    if (!empty($icon_class) && is_string($icon_class)) {
        $icon_html = '<i class="' . esc_attr($icon_class) . '"></i>';
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
?>
<a href="<?php echo esc_url($atts['link']); ?>"
   target="<?php echo esc_attr($target); ?>"
   class="<?php echo esc_attr(implode(' ', $classes)); ?>"
   <?php echo $disabled_attr; ?>
   <?php echo fw_attr_to_html($attr); ?>>
    <?php echo $button_content; ?>
</a>
