<?php if (!defined('FW')) die('Forbidden'); ?>

<?php
/**
 * @var array $atts
 */

// Per-element color picks (kept off the wrapper). Applied across all tabs.
// sc_extract_styling_atts gives both preset classes AND compact-picker
// custom-hex inline styles for each per-element field.
$tab_title_styling   = sc_extract_styling_atts( $atts, array( 'tab_title_color' ) );
$tab_content_styling = sc_extract_styling_atts( $atts, array( 'tab_content_color' ) );
$tab_title_extras    = $tab_title_styling['classes'];
$tab_content_extras  = $tab_content_styling['classes'];
$tab_title_style     = $tab_title_styling['styles']   ? implode( '; ', $tab_title_styling['styles'] )   : '';
$tab_content_style   = $tab_content_styling['styles'] ? implode( '; ', $tab_content_styling['styles'] ) : '';
$tab_title_class     = ! empty( $tab_title_extras )   ? ' ' . implode( ' ', $tab_title_extras )   : '';
$tab_content_class   = ! empty( $tab_content_extras ) ? ' ' . implode( ' ', $tab_content_extras ) : '';
$tab_title_style_attr   = $tab_title_style   !== '' ? ' style="' . esc_attr( $tab_title_style ) . '"'   : '';
$tab_content_style_attr = $tab_content_style !== '' ? ' style="' . esc_attr( $tab_content_style ) . '"' : '';

// Always set these before building attributes
$atts['base_class']       = 'tabs';
$atts['unique_id_prefix'] = 'tb-';

// Generate unique tabs ID
$tabs_id = wp_unique_id( 'tabs-' );

// Use CSS ID if provided, otherwise fallback to tabs_id
$atts['css_id'] = !empty($atts['css_id']) ? $atts['css_id'] : $tabs_id;

// Ensure tabs-container class is included
$atts['css_class'] = (!empty($atts['css_class']) ? $atts['css_class'] . ' ' : '') . 'tabs-container';

// Build wrapper attributes
$attr = sc_build_wrapper_attr($atts);

// Get tabs array
$tabs = fw_akg('tabs', $atts, array());

// Settings
$layout        = ( !empty($atts['layout']) && $atts['layout'] === 'media' ) ? 'media' : 'content';
$is_vertical   = !empty($atts['orientation']) && $atts['orientation'] === 'vertical';
$tab_style     = !empty($atts['tab_style']) ? $atts['tab_style'] : 'tabs';
$alignment     = !empty($atts['alignment']) ? $atts['alignment'] : 'start';
$justified     = !empty($atts['justified']) && $atts['justified'] === 'yes';
$fade_enabled  = !empty($atts['fade']) && $atts['fade'] === 'yes';
$media_side    = ( !empty($atts['media_side']) && $atts['media_side'] === 'left' ) ? 'left' : 'right';
$activate_on   = ( !empty($atts['activate_on']) && $atts['activate_on'] === 'hover' ) ? 'hover' : 'click';
$autoplay      = !empty($atts['autoplay']) && $atts['autoplay'] === 'yes';
$interval_ms   = max( 2, min( 12, (int) ( $atts['autoplay_interval'] ?? 5 ) ) ) * 1000;

// Interaction behaviors → data attributes read by the JS.
if ( $activate_on === 'hover' ) { $attr['data-fw-activate'] = 'hover'; }
if ( $autoplay )                { $attr['data-fw-autoplay'] = (string) $interval_ms; }
if ( $layout === 'media' ) {
	$attr['class'] = ( isset($attr['class']) ? $attr['class'] . ' ' : '' ) . 'tabs-container--media tabs-container--media-' . $media_side;
}

// Resolve a tab's Image (upload value) to responsive <img> markup for the media layout.
$sc_tabs_img_html = function ( $val, $alt_fallback = '' ) {
	$url = ''; $id = 0;
	if ( is_array( $val ) ) {
		if ( ! empty( $val['url'] ) )                { $url = $val['url']; }
		elseif ( ! empty( $val['data']['icon'] ) )   { $url = $val['data']['icon']; }
		if ( ! empty( $val['attachment_id'] ) )      { $id = (int) $val['attachment_id']; }
		if ( $url === '' && $id && function_exists('wp_get_attachment_url') ) { $url = wp_get_attachment_url( $id ); }
	} elseif ( is_numeric( $val ) && function_exists('wp_get_attachment_url') ) {
		$id = (int) $val; $url = wp_get_attachment_url( $id );
	}
	if ( $url === '' ) { return ''; }
	$alt = $alt_fallback;
	if ( $id && function_exists('get_post_meta') ) {
		$a = get_post_meta( $id, '_wp_attachment_image_alt', true );
		if ( $a !== '' ) { $alt = $a; }
	}
	if ( $id && function_exists('wp_get_attachment_image') ) {
		return wp_get_attachment_image( $id, 'large', false, array(
			'class' => 'tabs-media__img', 'alt' => $alt, 'decoding' => 'async', 'loading' => 'lazy',
		) );
	}
	return '<img class="tabs-media__img" src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" decoding="async" loading="lazy" />';
};

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

if ($tab_style === 'segmented') {
    $nav_class .= ' nav-segmented';
}

// Alignment (only horizontal)
if (!$is_vertical) {
    $nav_class .= ' justify-content-' . $alignment;
}

// Justified tabs (only horizontal content layout)
if ($justified && !$is_vertical && $layout !== 'media') {
    $nav_class .= ' nav-justified';
}

// Content wrapper class
$content_class = 'tab-content';

// Function to check active tab
$has_active = array_filter($tabs, fn($t) => !empty($t['is_active']) && $t['is_active'] === 'yes');
?>
<div <?php echo fw_attr_to_html($attr); ?>>

    <?php if ($layout === 'media'): ?>
        <?php
        // Media panel: a list of tabs on one side, a switching image on the other.
        // Each tab's pane = its Image + the Content as a caption. Reuses the exact
        // tab-pane switching mechanics (JS activates by target id).
        $list_col = '<div class="fw-col-md-4 tabs-media__list">' . "\n";
        ob_start(); ?>
                <ul class="<?php echo esc_attr($nav_class . ' flex-column'); ?>" id="<?php echo esc_attr($tabs_id); ?>" role="tablist">
                    <?php foreach ($tabs as $key => $tab) :
                        $is_active = !empty($tab['is_active']) && $tab['is_active'] === 'yes';
                        if ($key === 0 && !$has_active) $is_active = true;
                        $tab_id = $tabs_id . '-' . ($key + 1);
                    ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $is_active ? 'active' : ''; echo esc_attr( $tab_title_class ); ?>"<?php echo $tab_title_style_attr; ?>
                                    id="<?php echo esc_attr($tab_id . '-tab'); ?>"
                                    data-fw-toggle="tab"
                                    data-fw-target="#<?php echo esc_attr($tab_id); ?>"
                                    type="button" role="tab"
                                    aria-controls="<?php echo esc_attr($tab_id); ?>"
                                    aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
                                <?php echo esc_html($tab['tab_title']); ?><?php if ( ! empty( $tab['badge'] ) ) : ?><span class="tab-badge"><?php echo esc_html( $tab['badge'] ); ?></span><?php endif; ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
        <?php $list_col = ob_get_clean();

        ob_start(); ?>
                <div class="<?php echo esc_attr($content_class); ?> tabs-media__panel" id="<?php echo esc_attr($tabs_id); ?>-content">
                    <?php foreach ($tabs as $key => $tab) :
                        $is_active = !empty($tab['is_active']) && $tab['is_active'] === 'yes';
                        if ($key === 0 && !$has_active) $is_active = true;
                        $tab_id = $tabs_id . '-' . ($key + 1);
                        $fade_class = $fade_enabled ? 'fade' : '';
                        $active_class = $is_active ? 'show active' : '';
                        $img_html = $sc_tabs_img_html( isset($tab['tab_image']) ? $tab['tab_image'] : '', isset($tab['tab_title']) ? $tab['tab_title'] : '' );
                        $caption  = isset($tab['tab_content']) ? trim( (string) $tab['tab_content'] ) : '';
                    ?>
                        <div class="tab-pane <?php echo $fade_class . ' ' . $active_class; echo esc_attr( $tab_content_class ); ?>"<?php echo $tab_content_style_attr; ?>
                             id="<?php echo esc_attr($tab_id); ?>"
                             role="tabpanel"
                             tabindex="0"
                             aria-labelledby="<?php echo esc_attr($tab_id . '-tab'); ?>">
                            <figure class="tabs-media__figure">
                                <?php echo $img_html; // phpcs:ignore ?>
                                <?php if ($caption !== '') : ?>
                                    <figcaption class="tabs-media__caption"><?php echo do_shortcode( $tab['tab_content'] ); ?></figcaption>
                                <?php endif; ?>
                            </figure>
                        </div>
                    <?php endforeach; ?>
                </div>
        <?php $media_col_inner = ob_get_clean(); ?>
        <div class="fw-row tabs-media__row">
            <?php if ($media_side === 'left') : ?>
                <div class="fw-col-md-8 tabs-media__media"><?php echo $media_col_inner; // phpcs:ignore ?></div>
                <div class="fw-col-md-4 tabs-media__list"><?php echo $list_col; // phpcs:ignore ?></div>
            <?php else : ?>
                <div class="fw-col-md-4 tabs-media__list"><?php echo $list_col; // phpcs:ignore ?></div>
                <div class="fw-col-md-8 tabs-media__media"><?php echo $media_col_inner; // phpcs:ignore ?></div>
            <?php endif; ?>
        </div>
    <?php elseif ($is_vertical): ?>
        <div class="fw-row">
            <div class="fw-col-3">
                <!-- Tab nav -->
                <ul class="<?php echo esc_attr($nav_class . ' flex-column'); ?>" id="<?php echo esc_attr($tabs_id); ?>" role="tablist">
                    <?php foreach ($tabs as $key => $tab) : 
                        $is_active = !empty($tab['is_active']) && $tab['is_active'] === 'yes';
                        if ($key === 0 && !$has_active) $is_active = true;
                        $tab_id = $tabs_id . '-' . ($key + 1);
                    ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $is_active ? 'active' : ''; echo esc_attr( $tab_title_class ); ?>"<?php echo $tab_title_style_attr; ?>
                                    id="<?php echo esc_attr($tab_id . '-tab'); ?>" 
                                    data-fw-toggle="tab"
                                    data-fw-target="#<?php echo esc_attr($tab_id); ?>"
                                    type="button" 
                                    role="tab" 
                                    aria-controls="<?php echo esc_attr($tab_id); ?>" 
                                    aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
                                <?php echo esc_html($tab['tab_title']); ?><?php if ( ! empty( $tab['badge'] ) ) : ?><span class="tab-badge"><?php echo esc_html( $tab['badge'] ); ?></span><?php endif; ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="fw-col-9">
                <!-- Tab content -->
                <div class="<?php echo esc_attr($content_class); ?>" id="<?php echo esc_attr($tabs_id); ?>-content">
                    <?php foreach ($tabs as $key => $tab) : 
                        $is_active = !empty($tab['is_active']) && $tab['is_active'] === 'yes';
                        if ($key === 0 && !$has_active) $is_active = true;
                        $tab_id = $tabs_id . '-' . ($key + 1);
                        $fade_class = $fade_enabled ? 'fade' : '';
                        $active_class = $is_active ? 'show active' : '';
                    ?>
                        <div class="tab-pane <?php echo $fade_class . ' ' . $active_class; echo esc_attr( $tab_content_class ); ?>"<?php echo $tab_content_style_attr; ?>
                             id="<?php echo esc_attr($tab_id); ?>"
                             role="tabpanel"
                             tabindex="0"
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
                    <button class="nav-link <?php echo $is_active ? 'active' : ''; echo esc_attr( $tab_title_class ); ?>"<?php echo $tab_title_style_attr; ?>
                            id="<?php echo esc_attr($tab_id . '-tab'); ?>"
                            data-fw-toggle="tab"
                            data-fw-target="#<?php echo esc_attr($tab_id); ?>"
                            type="button"
                            role="tab"
                            aria-controls="<?php echo esc_attr($tab_id); ?>"
                            aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
                        <?php echo esc_html($tab['tab_title']); ?><?php if ( ! empty( $tab['badge'] ) ) : ?><span class="tab-badge"><?php echo esc_html( $tab['badge'] ); ?></span><?php endif; ?>
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
                <div class="tab-pane <?php echo $fade_class . ' ' . $active_class; echo esc_attr( $tab_content_class ); ?>"<?php echo $tab_content_style_attr; ?>
                     id="<?php echo esc_attr($tab_id); ?>"
                     role="tabpanel"
                     tabindex="0"
                     aria-labelledby="<?php echo esc_attr($tab_id . '-tab'); ?>">
                    <?php echo do_shortcode($tab['tab_content']); ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
