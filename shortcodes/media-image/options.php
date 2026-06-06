<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'image' => [
                'type'  => 'upload',
                'label' => __('Choose Image', 'fw'),
                'desc'  => __('Either upload a new, or choose an existing image from your media library', 'fw'),
                'help'  => __('Pick a source file larger than the Width/Height below so it stays sharp; the display size is controlled by those fields, not the original dimensions.', 'fw'),
            ],
            'size' => [
                'type'    => 'group',
                'options' => [
                    'width' => [
                        'type'  => 'unit-input',
                        'label' => __('Width', 'fw'),
                        'desc'  => __('Set image width (Ex: 300px, 100%)', 'fw'),
                        'help'  => __('Pick a number and a unit. When BOTH Width and Height are in "px" the source image is cropped to that exact size; other units (%, vw, …) just scale the display. To keep the aspect ratio, set Width and leave Height empty.', 'fw'),
                        'value' => [ 'value' => 300, 'unit' => 'px' ],
                        'units' => [ 'px', '%', 'vw', 'rem', 'em' ],
                    ],
                    'height' => [
                        'type'  => 'unit-input',
                        'label' => __('Height', 'fw'),
                        'desc'  => __('Set image height (Ex: 200px)', 'fw'),
                        'help'  => __('Pick a number and a unit, or leave the number blank to let the height follow the width. Setting both Width and Height (in px) crops the source to that exact size, which can stretch the image if the ratio differs.', 'fw'),
                        'value' => [ 'value' => 200, 'unit' => 'px' ],
                        'units' => [ 'px', '%', 'vh', 'rem', 'em' ],
                    ],
                ],
            ],
            'image-link-group' => [
                'type'    => 'group',
                'options' => [
                    'link' => [
                        'type'  => 'text',
                        'label' => __('Image Link', 'fw'),
                        'desc'  => __('Where should your image link to?', 'fw'),
                        'help'  => __('Enter a full URL (e.g. https://example.com/page) to make the image clickable. Leave blank for a plain, non-linking image.', 'fw'),
                    ],
                    'target' => [
                        'type'         => 'switch',
                        'label'        => __('Open Link in New Window', 'fw'),
                        'desc'         => __('Select here if you want to open the linked page in a new window', 'fw'),
                        'help'         => __('Recommended for links to external sites so visitors keep your page open. Has no effect unless an Image Link is set above.', 'fw'),
                        'right-choice' => [
                            'value' => '_blank',
                            'label' => __('Yes', 'fw'),
                        ],
                        'left-choice'  => [
                            'value' => '_self',
                            'label' => __('No', 'fw'),
                        ],
                    ],
                ],
            ],
        ],
    ],

    'tab_styling' => [
        'title'   => __( 'Styling', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_colors' => [
                'type'    => 'group',
                'options' => [
                    'bg_color' => sc_color_field_compact( array( 'label' => __( 'Background Color', 'fw' ), 'kind' => 'bg' ) ),
                ],
            ],
            'group_spacings' => [
                'type'    => 'group',
                'options' => [
                    'spacing'  => array(
                        'type'  => 'spacing',
                        'label' => __( 'Margin & Padding', 'fw' ),
                        'desc'  => __( 'All Sides applies to every side at once; any per-side value (Top, Right, Bottom, Left) overrides it for that direction.', 'fw' ),
                        'help'  => sc_styling_help_text( 'spacing' ),
                    ),
                ],
            ],
        ],
    ],
    'tab_animation' => [
        'title'   => __( 'Animations', 'fw' ),
        'type'    => 'tab',
        'options' => sc_get_animation_fields(),
    ],
    'tab_advanced' => [
        'title'   => __('Advanced', 'fw'),
        'type'    => 'tab',
        'options' => [
            'advanced_settings' => [
                'type'    => 'group',
                'options' => array_merge(
                    sc_get_advanced_tab(),
                    [
                        /* 'title_extra' => [
                            'type'  => 'text',
                            'label' => __('Some Title', 'fw'),
                            'desc'  => __('Write some heading title content', 'fw'),
                        ],
                        'title_extra_2' => [
                            'type'  => 'text',
                            'label' => __('Some Title2', 'fw'),
                            'desc'  => __('Write some heading title content', 'fw'),
                        ],*/
                    ]
                ),
            ],
        ],
    ],
];
