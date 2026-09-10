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
            'crop' => [
                'type'    => 'group',
                'options' => [
                    'image_ratio' => [
                        'type'    => 'select',
                        'label'   => __( 'Aspect Ratio', 'fw' ),
                        'desc'    => __( 'Crop the image into a fixed ratio. Leave as Original to keep the photo\'s own shape.', 'fw' ),
                        'help'    => __( 'When set, the image is placed in a box of this ratio and cropped to fill it (see Fit + Crop Position). Great for making a row of different-sized photos line up.', 'fw' ),
                        'value'   => '',
                        'choices' => [
                            ''     => __( 'Original (no crop)', 'fw' ),
                            '1/1'  => __( 'Square 1:1', 'fw' ),
                            '4/3'  => __( 'Landscape 4:3', 'fw' ),
                            '3/2'  => __( 'Landscape 3:2', 'fw' ),
                            '16/9' => __( 'Widescreen 16:9', 'fw' ),
                            '3/4'  => __( 'Portrait 3:4', 'fw' ),
                            '2/3'  => __( 'Portrait 2:3', 'fw' ),
                            '9/16' => __( 'Portrait 9:16', 'fw' ),
                        ],
                    ],
                    'image_fit' => [
                        'type'    => 'select',
                        'label'   => __( 'Fit', 'fw' ),
                        'desc'    => __( 'Cover fills the box and crops the overflow; Contain fits the whole image (may letterbox). Only applies when an Aspect Ratio is set.', 'fw' ),
                        'value'   => 'cover',
                        'choices' => [
                            'cover'   => __( 'Cover (crop to fill)', 'fw' ),
                            'contain' => __( 'Contain (fit whole image)', 'fw' ),
                        ],
                    ],
                    'focal_position' => [
                        'type'    => 'select',
                        'label'   => __( 'Crop Position', 'fw' ),
                        'desc'    => __( 'Which part of the image to keep when cropping — like a background-image position. Only applies with Fit = Cover.', 'fw' ),
                        'help'    => __( 'Pick the region you care about (e.g. Top for a face near the top). The crop keeps that area in view and trims the rest.', 'fw' ),
                        'value'   => 'center center',
                        'choices' => [
                            'left top'      => __( 'Top Left', 'fw' ),
                            'center top'    => __( 'Top', 'fw' ),
                            'right top'     => __( 'Top Right', 'fw' ),
                            'left center'   => __( 'Left', 'fw' ),
                            'center center' => __( 'Center', 'fw' ),
                            'right center'  => __( 'Right', 'fw' ),
                            'left bottom'   => __( 'Bottom Left', 'fw' ),
                            'center bottom' => __( 'Bottom', 'fw' ),
                            'right bottom'  => __( 'Bottom Right', 'fw' ),
                        ],
                    ],
                ],
            ],
            'caption_group' => [
                'type'    => 'group',
                'options' => [
                    'caption' => [
                        'type'  => 'text',
                        'label' => __( 'Caption', 'fw' ),
                        'desc'  => __( 'Optional caption shown beneath the image (wraps it in a semantic <figure>). Leave blank for none.', 'fw' ),
                    ],
                ],
            ],
            'loading' => [
                'type'    => 'group',
                'options' => [
                    'fetchpriority' => [
                        'type'    => 'select',
                        'label'   => __( 'Loading Priority', 'fw' ),
                        'desc'    => __( 'Use "High" for above-the-fold / hero images to improve LCP. "Auto" lazy-loads — best for images further down the page.', 'fw' ),
                        'value'   => 'auto',
                        'choices' => [
                            'auto' => __( 'Auto (lazy load)', 'fw' ),
                            'high' => __( 'High (above the fold)', 'fw' ),
                        ],
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
                    'lightbox' => [
                        'type'         => 'switch',
                        'label'        => __( 'Lightbox (click to zoom)', 'fw' ),
                        'desc'         => __( 'Open the full-size image in a lightbox overlay when clicked. Takes precedence over the Image Link above.', 'fw' ),
                        'help'         => __( 'Best for photos you want visitors to inspect. Uses the shared, dependency-free lightbox. When on, the Image Link is ignored.', 'fw' ),
                        'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                        'left-choice'  => [ 'value' => 'no',  'label' => __( 'No', 'fw' ) ],
                        'value'        => 'no',
                    ],
                ],
            ],
        ],
    ],

    'tab_styling' => [
        'title'   => __( 'Styling', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_image_style' => [
                'type'    => 'group',
                'options' => [
                    'image_style' => function_exists( 'sc_image_style_field' )
                        ? sc_image_style_field()
                        : [ 'type' => 'select', 'label' => __( 'Image Style', 'fw' ), 'value' => '', 'choices' => [ '' => __( 'None', 'fw' ) ] ],
                ],
            ],
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
