<?php if (!defined('FW')) {
	die('Forbidden');
}

$options = array(
    'tab_layout' => array(
        'title'   => __('Layout', 'fw'),
        'type'    => 'tab',
        'options' => array(
            'is_fullwidth' => array(
                'label' => __('Full Width', 'fw'),
                'type'  => 'switch',
            ),
            'background_color' => array(
                'label' => __('Background Color', 'fw'),
                'desc'  => __('Please select the background color', 'fw'),
                'type'  => 'color-picker',
            ),
            'background_image' => array(
                'label'   => __('Background Image', 'fw'),
                'desc'    => __('Please select the background image', 'fw'),
                'type'    => 'background-image',
                'choices' => array(
                    // In future you can set predefined images
                ),
            ),
            'video' => array(
                'label' => __('Background Video', 'fw'),
                'desc'  => __('Insert Video URL to embed this video', 'fw'),
                'type'  => 'text',
            ),
        ),
    ),
	'tab_advanced' => sc_get_advanced_tab(),
);
