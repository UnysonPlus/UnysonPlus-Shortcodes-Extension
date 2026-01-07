<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'group' => [
                'type'    => 'group',
                'options' => [
                    'title' => [
                        'label' => __('Title', 'fw'),
                        'desc'  => __('Section heading displayed above the testimonials list.', 'fw'),
                        'help'  => __('Plain heading text shown before the testimonial items.', 'fw'),
                        'type'  => 'text',
                    ],
                    'testimonials' => [
                        'label'         => __('Testimonials', 'fw'),
                        'popup-title'   => __('Add/Edit Testimonial', 'fw'),
                        'desc'          => __('Manage testimonial entries.', 'fw'),
                        'help'          => __('Each item becomes one testimonial card / slide.', 'fw'),
                        'type'          => 'addable-popup',
                        'template'      => '{{=author_name}}',
                        'popup-options' => [
                            'content' => [
                                'label' => __('Quote', 'fw'),
                                'desc'  => __('Main testimonial text.', 'fw'),
                                'help'  => __('Core content shown inside the blockquote element.', 'fw'),
                                'type'  => 'textarea',
                            ],
                            'author_avatar' => [
                                'label' => __('Image', 'fw'),
                                'desc'  => __('Upload or choose an image.', 'fw'),
                                'help'  => __('Avatar shown using Bootstrap responsive image utilities.', 'fw'),
                                'type'  => 'upload',
                            ],
                            'author_name' => [
                                'label' => __('Name', 'fw'),
                                'desc'  => __('Person’s name.', 'fw'),
                                'help'  => __('Displayed as bold author line beneath the quote.', 'fw'),
                                'type'  => 'text',
                            ],
                            'author_job' => [
                                'label' => __('Position', 'fw'),
                                'desc'  => __('Job title or role.', 'fw'),
                                'help'  => __('Optional metadata line (small, muted).', 'fw'),
                                'type'  => 'text',
                            ],
                            'site_name' => [
                                'label' => __('Website Name', 'fw'),
                                'desc'  => __('Shown as link text if URL set.', 'fw'),
                                'help'  => __('Anchor text for the source / company link.', 'fw'),
                                'type'  => 'text',
                            ],
                            'site_url' => [
                                'label' => __('Website Link', 'fw'),
                                'desc'  => __('External site URL.', 'fw'),
                                'help'  => __('Adds rel="nofollow" and target="_blank".', 'fw'),
                                'type'  => 'text',
                            ],
                            'rating' => [
                                'label' => __('Rating', 'fw'),
                                'desc'  => __('0–5 in 0.5 steps.', 'fw'),
                                'help'  => __('Renders 5 Rating stars.', 'fw'),
                                'type'  => 'slider',
                                'value' => 5,
                                'properties' => [
                                    'min'  => 0,
                                    'max'  => 5,
                                    'step' => .5,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'tab_layout' => [
        'title'   => __('Layout', 'fw'),
        'type'    => 'tab',
        'options' => [
            'group' => [
                'type'    => 'group',
                'options' => [
                    'layout_type' => [
                        'type'         => 'multi-picker',
                        'label'        => false,
                        'desc'         => false,
                        'help'         => __('Choose overall presentation: Bootstrap carousel, responsive grid, or a single centered item.', 'fw'),
                        'picker'       => [
                            'layout_choice' => [
                                'label'   => __('Layout Type', 'fw'),
                                'type'    => 'select',
                                'choices' => [
                                    'carousel' => __('Carousel', 'fw'),
                                    'grid'     => __('Grid', 'fw'),
                                    'single'   => __('Single', 'fw'),
                                ],
                                'desc' => __('Select the structural layout.', 'fw'),
                                'help' => __('Carousel uses Bootstrap 5 .carousel; Grid uses row + row-cols-* utilities; Single shows the first item only.', 'fw'),
                            ]
                        ],
                        'choices'      => [
                            'grid'  => [
                                'grid_columns' => [
                                    'label' => __('Grid Columns (Desktop)', 'fw'),
                                    'type'  => 'select',
                                    'value' => 'row-cols-3',
                                    'choices' => [
                                        'row-cols-1' => '1',
                                        'row-cols-2' => '2',
                                        'row-cols-3' => '3',
                                        'row-cols-4' => '4',
                                    ],
                                    'desc' => __('Columns per row ≥ md.', 'fw'),
                                    'help' => __('Applies Bootstrap row-cols-* utility to control automatic column count.', 'fw'),
                                ],
                            ]
                        ],
                        'show_borders' => false,
                    ],
                    'gutter' => [
                        'label' => __('Gutter Size', 'fw'),
                        'type'  => 'select',
                        'choices' => [
                            ''    => __('Default', 'fw'),
                            'g-0' => 'g-0',
                            'g-1' => 'g-1',
                            'g-2' => 'g-2',
                            'g-3' => 'g-3',
                            'g-4' => 'g-4',
                            'g-5' => 'g-5',
                        ],
                        'desc' => __('Spacing between columns.', 'fw'),
                        'help' => __('Bootstrap g-* utilities apply horizontal & vertical column gutters.', 'fw'),
                    ],
                    'text_align' => [
                        'label' => __('Text Align', 'fw'),
                        'type'  => 'select',
                        'choices' => [
                            ''            => __('Left', 'fw'),
                            'text-center' => __('Center', 'fw'),
                            'text-end'    => __('Right', 'fw'),
                        ],
                        'desc' => __('Alignment of text content.', 'fw'),
                        'help' => __('Uses Bootstrap text alignment utilities (text-center, text-end).', 'fw'),
                    ],
                    'container_type' => [
                        'label' => __('Container', 'fw'),
                        'type'  => 'select',
                        'value' => 'container',
                        'choices' => [
                            ''                => __('None', 'fw'),
                            'container'       => __('Container', 'fw'),
                            'container-fluid' => __('Fluid', 'fw'),
                        ],
                        'desc' => __('Outer width wrapper.', 'fw'),
                        'help' => __('Bootstrap layout container for responsive fixed width or full-width (container-fluid).', 'fw'),
                    ],
                    'items_per_slide' => [
                        'label' => __('Items per Slide (Carousel)', 'fw'),
                        'type'  => 'select',
                        'value' => '1',
                        'choices' => [
                            '1' => '1',
                            '2' => '2',
                            '3' => '3',
                        ],
                        'desc' => __('Cards shown simultaneously in each slide.', 'fw'),
                        'help' => __('Determines how many testimonial cards are grouped per carousel slide.', 'fw'),
                    ],
                ],
            ],
        ],
    ],

    'tab_style' => [
        'title'   => __('Style', 'fw'),
        'type'    => 'tab',
        'options' => [
            'group' => [
                'type'    => 'group',
                'options' => [
                    'card_style' => [
                        'label' => __('Card Style', 'fw'),
                        'type'  => 'select',
                        'choices' => [
                            ''                               => __('Plain', 'fw'),
                            'card card-body'                 => __('Card', 'fw'),
                            'card card-body border'          => __('Card Bordered', 'fw'),
                            'card card-body shadow'          => __('Card Shadow', 'fw'),
                            'card card-body bg-light'        => __('Card Light', 'fw'),
                            'card card-body bg-dark text-light' => __('Card Dark', 'fw'),
                        ],
                        'desc' => __('Visual container style.', 'fw'),
                        'help' => __('Applies Bootstrap card utility classes plus optional border, shadow, or background contextual class.', 'fw'),
                    ],
                    'avatar_position' => [
                        'label' => __('Avatar Position', 'fw'),
                        'type'  => 'select',
                        'value' => 'top',
                        'choices' => [
                            'top'   => __('Top (Above Content)', 'fw'),
                            'left'  => __('Left of Content', 'fw'),
                            'right' => __('Right of Content', 'fw'),
                            'none'  => __('Hide Avatar', 'fw'),
                        ],
                        'desc' => __('Placement of avatar relative to text.', 'fw'),
                        'help' => __('Controls flex / column layout for avatar vs quote (adds helper classes).', 'fw'),
                    ],
                    'avatar_shape' => [
                        'label' => __('Avatar Shape', 'fw'),
                        'type'  => 'select',
                        'value' => 'rounded-circle',
                        'choices' => [
                            'rounded-circle' => __('Circle', 'fw'),
                            'rounded'        => __('Rounded', 'fw'),
                            'rounded-0'      => __('Square', 'fw'),
                        ],
                        'desc' => __('Corner radius style.', 'fw'),
                        'help' => __('Uses Bootstrap radius utilities (rounded, rounded-circle, rounded-0).', 'fw'),
                    ],
                    'avatar_size' => [
                        'label' => __('Avatar Size', 'fw'),
                        'type'  => 'select',
                        'value' => 'avatar-md',
                        'choices' => [
                            'avatar-sm' => __('Small', 'fw'),
                            'avatar-md' => __('Medium', 'fw'),
                            'avatar-lg' => __('Large', 'fw'),
                        ],
                        'desc' => __('Predefined size class.', 'fw'),
                        'help' => __('Custom classes you define in CSS to set width/height (e.g. 64/96/128px).', 'fw'),
                    ],
                    'show_rating' => [
                        'label' => __('Show Rating Stars', 'fw'),
                        'type'  => 'switch',
                        'right-choice' => ['value'=>'yes','label'=>__('Yes','fw')],
                        'left-choice'  => ['value'=>'no','label'=>__('No','fw')],
                        'value'=>'yes',
                        'desc' => __('Toggle star display.', 'fw'),
                        'help' => __('If enabled and rating value present, renders Font Awesome stars (solid / half / regular).', 'fw'),
                    ],
                ],
            ],
        ],
    ],

    'tab_carousel' => [
        'title'   => __('Carousel', 'fw'),
        'type'    => 'tab',
        'options' => [
            'group' => [
                'type'    => 'group',
                'options' => [
                    'carousel_autoplay' => [
                        'label' => __('Autoplay', 'fw'),
                        'type'  => 'switch',
                        'right-choice' => ['value'=>'yes','label'=>__('Yes','fw')],
                        'left-choice'  => ['value'=>'no','label'=>__('No','fw')],
                        'value'=>'yes',
                        'desc' => __('Auto cycle slides.', 'fw'),
                        'help' => __('Controls data-bs-ride to trigger automatic sliding.', 'fw'),
                    ],
                    'carousel_interval' => [
                        'label' => __('Autoplay Interval (ms)', 'fw'),
                        'type'  => 'text',
                        'value' => '5000',
                        'desc'  => __('Delay between auto slides.', 'fw'),
                        'help'  => __('Maps to data-bs-interval (milliseconds).', 'fw'),
                    ],
                    'carousel_pause_hover' => [
                        'label' => __('Pause on Hover', 'fw'),
                        'type'  => 'switch',
                        'right-choice' => ['value'=>'yes','label'=>__('Yes','fw')],
                        'left-choice'  => ['value'=>'no','label'=>__('No','fw')],
                        'value'=>'yes',
                        'desc' => __('Stop cycling while hovered.', 'fw'),
                        'help' => __('Maps to data-bs-pause="hover" behavior.', 'fw'),
                    ],
                    'carousel_controls' => [
                        'label' => __('Show Prev/Next', 'fw'),
                        'type'  => 'switch',
                        'right-choice' => ['value'=>'yes','label'=>__('Yes','fw')],
                        'left-choice'  => ['value'=>'no','label'=>__('No','fw')],
                        'value'=>'yes',
                        'desc' => __('Display navigation arrows.', 'fw'),
                        'help' => __('Adds Bootstrap .carousel-control-prev / -next buttons.', 'fw'),
                    ],
                    'carousel_indicators' => [
                        'label' => __('Show Indicators', 'fw'),
                        'type'  => 'switch',
                        'right-choice' => ['value'=>'yes','label'=>__('Yes','fw')],
                        'left-choice'  => ['value'=>'no','label'=>__('No','fw')],
                        'value'=>'yes',
                        'desc' => __('Display slide markers.', 'fw'),
                        'help' => __('Adds Bootstrap .carousel-indicators button list.', 'fw'),
                    ],
                    'carousel_indicator_style' => [
                        'label' => __('Indicator Style', 'fw'),
                        'type'  => 'select',
                        'value' => 'dots',
                        'choices' => [
                            'none'  => __('None', 'fw'),
                            'dots'  => __('Dots', 'fw'),
                            'lines' => __('Lines', 'fw'),
                        ],
                        'desc' => __('Indicator appearance.', 'fw'),
                        'help' => __('Dots (default), line bars, or none (no custom styling; use with indicators on).', 'fw'),
                    ],
                    'carousel_wrap' => [
                        'label' => __('Wrap Around (Loop)', 'fw'),
                        'type'  => 'switch',
                        'right-choice' => ['value'=>'yes','label'=>__('Yes','fw')],
                        'left-choice'  => ['value'=>'no','label'=>__('No','fw')],
                        'value'=>'yes',
                        'desc' => __('Loop from last to first.', 'fw'),
                        'help' => __('Maps to data-bs-wrap controlling cyclic behavior.', 'fw'),
                    ],
                ],
            ],
        ],
    ],

    'tab_advanced' => sc_get_advanced_tab(),
];
