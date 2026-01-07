<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * @var array $atts
 */
global $wp_embed;

// Wrapper attributes
$atts['base_class']       = 'video';
$atts['unique_id_prefix'] = 'vid-';
$attr = sc_build_wrapper_attr( $atts );

// Reorder classes: base + unique, fixed, user-defined
$css_classes = preg_split('/\s+/', trim($attr['class'] ?? ''));
$css_classes = array_filter($css_classes);
$fixed_classes = ['video-wrapper', 'shortcode-container', 'mx-auto'];
$css_classes = array_diff($css_classes, $fixed_classes);

$ordered = [];
if (!empty($css_classes)) {
    $ordered[] = $css_classes[0] ?? '';
    $ordered[] = $css_classes[1] ?? '';
    $ordered = array_merge($ordered, $fixed_classes, array_slice($css_classes, 2));
}
$attr['class'] = implode(' ', array_filter($ordered));

// Apply max-width and center
$width = is_numeric($atts['width']) && $atts['width'] > 0 ? $atts['width'] : 600;
$attr['style'] = "max-width: {$width}px;";

// Ratio classes
$ratio_class_map = [
    '16x9' => 'ratio ratio-16x9',
    '4x3'  => 'ratio ratio-4x3',
    '1x1'  => 'ratio ratio-1x1',
    '21x9' => 'ratio ratio-21x9',
    '9x16' => 'ratio ratio-9x16',
    '3x4'  => 'ratio ratio-3x4',
];
$ratio_class = $ratio_class_map[$atts['ratio'] ?? '16x9'] ?? 'ratio ratio-16x9';

// Embed
$iframe = $wp_embed->run_shortcode('[embed]' . trim($atts['url']) . '[/embed]');
?>
<div <?php echo fw_attr_to_html($attr); ?>>
    <div class="<?php echo esc_attr($ratio_class); ?>">
        <?php echo do_shortcode($iframe); ?>
    </div>
</div>
