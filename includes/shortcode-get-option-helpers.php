<?php
/**
 * PHP Version: 7.4 or higher
 */
if (!defined('FW')) die('Forbidden');

/**
 * Returns a reusable "Advanced" tab for shortcodes
 * Includes Unique ID, CSS ID, and CSS Class.
 *
 * @return array
 */
function sc_get_advanced_tab() {
    return [
        'title'   => __('Advanced', 'fw'),
        'type'    => 'tab',
        'options' => [
            'advanced_settings' => [
                'type'    => 'group',
                'options' => [
                    'unique_id' => [
                        'type' => 'unique',
                    ],
                    'css_id' => [
                        'label' => __('CSS ID', 'fw'),
                        'desc'  => __('Useful for anchor links', 'fw'),
                        'type'  => 'text',
                    ],
                    'css_class' => [
                        'label' => __('CSS Class', 'fw'),
                        'desc'  => false,
                        'type'  => 'text',
                    ],
                ]
            ]
        ],
    ];
}
