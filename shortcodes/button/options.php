<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'label'  => [
                'label' => __('Button Label', 'fw'),
                'desc'  => __('This is the text that appears on your button', 'fw'),
                'type'  => 'text',
                'value' => 'Submit'
            ],
            'link'   => [
                'label' => __('Button Link', 'fw'),
                'desc'  => __('Where should your button link to', 'fw'),
                'type'  => 'text',
                'value' => '#'
            ],
            'target' => [
                'type'  => 'switch',
                'label'   => __('Open Link in New Window', 'fw'),
                'desc'    => __('Select here if you want to open the linked page in a new window', 'fw'),
                'right-choice' => [
                    'value' => '_blank',
                    'label' => __('Yes', 'fw'),
                ],
                'left-choice' => [
                    'value' => '_self',
                    'label' => __('No', 'fw'),
                ],
            ],
            'icon' => [
                'label' => __('Button Icon', 'fw'),
                // 'desc'  => __('Optional icon class (e.g. bi bi-star)', 'fw'),
                'type'  => 'icon-v2',
                'preview_size' => 'medium',
                'modal_size' => 'medium',
            ],
            'icon_position' => [
                'label'   => __('Icon Position', 'fw'),
                'type'    => 'select',
                'choices' => [
                    'before' => __('Before Label', 'fw'),
                    'after'  => __('After Label', 'fw'),
                ],
                'value' => 'before'
            ],
        ],
    ],

    'tab_styling' => [
        'title'   => __('Styling', 'fw'),
        'type'    => 'tab',
        'options' => [
            'style' => [
                'label'   => __('Button Style', 'fw'),
                'desc'    => __('Choose a style for your button', 'fw'),
                'type'    => 'select',
                'choices' => [
                    'btn-primary'   => __('Primary (Main)', 'fw'),
                    'btn-secondary' => __('Secondary (Accent)', 'fw'),
                    'btn-dark'      => __('Neutral (Gray)', 'fw'),
                    'btn-success'   => __('Green (Success)', 'fw'),
                    'btn-danger'    => __('Red (Danger)', 'fw'),
                    'btn-warning'   => __('Yellow (Warning)', 'fw'),
                    'btn-info'      => __('Teal (Info)', 'fw'),
                    'btn-light'     => __('Light (White)', 'fw'),
                    'btn-black'     => __('Dark (Black)', 'fw'), // Bootstrap doesn’t have btn-black by default, maps to custom class
                    'btn-link'      => __('Link (Minimal)', 'fw'),
                ]
            ],
           'outline' => [
                'label'   => __('Outline Style', 'fw'),
                'desc'    => __('Use outline version of the button', 'fw'),
                'type'    => 'select',
                'choices' => [
                    ''                    => __('No Outline', 'fw'),
                    'btn-outline-primary'   => __('Outline Primary (Main)', 'fw'),
                    'btn-outline-secondary' => __('Outline Secondary (Accent)', 'fw'),
                    'btn-outline-dark'      => __('Outline Neutral (Gray)', 'fw'),
                    'btn-outline-success'   => __('Outline Green (Success)', 'fw'),
                    'btn-outline-danger'    => __('Outline Red (Danger)', 'fw'),
                    'btn-outline-warning'   => __('Outline Yellow (Warning)', 'fw'),
                    'btn-outline-info'      => __('Outline Teal (Info)', 'fw'),
                    'btn-outline-light'     => __('Outline Light (White)', 'fw'),
                    'btn-outline-dark'      => __('Outline Dark (Black)', 'fw'), // same as Gray unless you define a custom .btn-black
                    'btn-outline-link'      => __('Outline Link (Minimal)', 'fw'), // not in Bootstrap, optional
                ]
            ],
            'size' => [
                'label'   => __('Button Size', 'fw'),
                'type'    => 'select',
                'choices' => [
                    ''       => __('Normal', 'fw'),
                    'btn-sm' => __('Small', 'fw'),
                    'btn-lg' => __('Large', 'fw'),
                ]
            ],
            'block' => [
                'type'  => 'switch',
                'label' => __('Full Width', 'fw'),
                'desc'  => __('Make the button full width (block level)', 'fw'),
                'right-choice' => [
                    'value' => 'w-100',
                    'label' => __('Yes', 'fw'),
                ],
                'left-choice' => [
                    'value' => '',
                    'label' => __('No', 'fw'),
                ],
            ],
            'state' => [
                'label'   => __('Button State', 'fw'),
                'type'    => 'select',
                'choices' => [
                    ''         => __('Normal', 'fw'),
                    'active'   => __('Active', 'fw'),
                    'disabled' => __('Disabled', 'fw'),
                ]
            ],
        ],
    ],

    'tab_advanced' => sc_get_advanced_tab(),
];
