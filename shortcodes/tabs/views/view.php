<?php if (!defined('FW')) die('Forbidden'); ?>

<?php
/**
 * @var array $atts
 */

// Always set these before building attributes
$atts['base_class']       = 'tabs';
$atts['unique_id_prefix'] = 'tb-';

// Generate unique tabs ID
$tabs_id = uniqid('tabs-');

// Use CSS ID if provided, otherwise fallback to tabs_id
$atts['css_id'] = !empty($atts['css_id']) ? $atts['css_id'] : $tabs_id;

// Ensure tabs-container class is included
$atts['css_class'] = (!empty($atts['css_class']) ? $atts['css_class'] . ' ' : '') . 'tabs-container';

// Build wrapper attributes
$attr = sc_build_wrapper_attr($atts);

// Get tabs array
$tabs = fw_akg('tabs', $atts, array());

// Settings
$is_vertical   = !empty($atts['orientation']) && $atts['orientation'] === 'vertical';
$tab_style     = !empty($atts['tab_style']) ? $atts['tab_style'] : 'tabs';
$alignment     = !empty($atts['alignment']) ? $atts['alignment'] : 'start';
$justified     = !empty($atts['justified']) && $atts['justified'] === 'yes';
$fade_enabled  = !empty($atts['fade']) && $atts['fade'] === 'yes';

// Base nav class
$nav_class = 'nav';

// Apply style
if ($tab_style === 'tabs') {
    $nav_class .= ' nav-tabs';
} elseif ($tab_style === 'pills') {
    $nav_class .= ' nav-pills';
} elseif ($tab_style === 'underline') {
    $nav_class .= ' nav-underline';
}

// Alignment (only horizontal)
if (!$is_vertical) {
    $nav_class .= ' justify-content-' . $alignment;
}

// Justified tabs (only horizontal)
if ($justified && !$is_vertical) {
    $nav_class .= ' nav-justified';
}

// Content wrapper class
$content_class = 'tab-content';

// Function to check active tab
$has_active = array_filter($tabs, fn($t) => !empty($t['is_active']) && $t['is_active'] === 'yes');
?>
<div <?php echo fw_attr_to_html($attr); ?>>

    <?php if ($is_vertical): ?>
        <div class="row">
            <div class="col-3">
                <!-- Tab nav -->
                <ul class="<?php echo esc_attr($nav_class . ' flex-column'); ?>" id="<?php echo esc_attr($tabs_id); ?>" role="tablist">
                    <?php foreach ($tabs as $key => $tab) : 
                        $is_active = !empty($tab['is_active']) && $tab['is_active'] === 'yes';
                        if ($key === 0 && !$has_active) $is_active = true;
                        $tab_id = $tabs_id . '-' . ($key + 1);
                    ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $is_active ? 'active' : ''; ?>" 
                                    id="<?php echo esc_attr($tab_id . '-tab'); ?>" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#<?php echo esc_attr($tab_id); ?>" 
                                    type="button" 
                                    role="tab" 
                                    aria-controls="<?php echo esc_attr($tab_id); ?>" 
                                    aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
                                <?php echo esc_html($tab['tab_title']); ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-9">
                <!-- Tab content -->
                <div class="<?php echo esc_attr($content_class); ?>" id="<?php echo esc_attr($tabs_id); ?>-content">
                    <?php foreach ($tabs as $key => $tab) : 
                        $is_active = !empty($tab['is_active']) && $tab['is_active'] === 'yes';
                        if ($key === 0 && !$has_active) $is_active = true;
                        $tab_id = $tabs_id . '-' . ($key + 1);
                        $fade_class = $fade_enabled ? 'fade' : '';
                        $active_class = $is_active ? 'show active' : '';
                    ?>
                        <div class="tab-pane <?php echo $fade_class . ' ' . $active_class; ?>" 
                             id="<?php echo esc_attr($tab_id); ?>" 
                             role="tabpanel" 
                             aria-labelledby="<?php echo esc_attr($tab_id . '-tab'); ?>">
                            <?php echo do_shortcode($tab['tab_content']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Horizontal layout -->
        <ul class="<?php echo esc_attr($nav_class); ?>" id="<?php echo esc_attr($tabs_id); ?>" role="tablist">
            <?php foreach ($tabs as $key => $tab) : 
                $is_active = !empty($tab['is_active']) && $tab['is_active'] === 'yes';
                if ($key === 0 && !$has_active) $is_active = true;
                $tab_id = $tabs_id . '-' . ($key + 1);
            ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $is_active ? 'active' : ''; ?>" 
                            id="<?php echo esc_attr($tab_id . '-tab'); ?>" 
                            data-bs-toggle="tab" 
                            data-bs-target="#<?php echo esc_attr($tab_id); ?>" 
                            type="button" 
                            role="tab" 
                            aria-controls="<?php echo esc_attr($tab_id); ?>" 
                            aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
                        <?php echo esc_html($tab['tab_title']); ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Tab content -->
        <div class="<?php echo esc_attr($content_class); ?>" id="<?php echo esc_attr($tabs_id); ?>-content">
            <?php foreach ($tabs as $key => $tab) : 
                $is_active = !empty($tab['is_active']) && $tab['is_active'] === 'yes';
                if ($key === 0 && !$has_active) $is_active = true;
                $tab_id = $tabs_id . '-' . ($key + 1);
                $fade_class = $fade_enabled ? 'fade' : '';
                $active_class = $is_active ? 'show active' : '';
            ?>
                <div class="tab-pane <?php echo $fade_class . ' ' . $active_class; ?>" 
                     id="<?php echo esc_attr($tab_id); ?>" 
                     role="tabpanel" 
                     aria-labelledby="<?php echo esc_attr($tab_id . '-tab'); ?>">
                    <?php echo do_shortcode($tab['tab_content']); ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
