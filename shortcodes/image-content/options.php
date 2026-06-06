<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

$options = [
        'tab_content' => [
                'title'   => __( 'Content', 'fw' ),
                'type'    => 'tab',
                'options' => [
                        'group_content' => [
                                'type'    => 'group',
                                'options' => [
                                        'image' => [
                                                'type'  => 'upload',
                                                'label' => __( 'Image', 'fw' ),
                                                'desc'  => __( 'Upload or choose an image from the media library', 'fw' ),
                                                'help'  => __( 'Use a high-resolution image at least as wide as its column; the theme scales it down crisply but cannot add detail when scaling up.', 'fw' ),
                                        ],
                                        'content' => [
                                                'type'          => 'wp-editor',
                                                'reinit'        => true,
                                                'label'         => __( 'Content', 'fw' ),
                                                'desc'          => __( 'Enter the text content to display alongside the image', 'fw' ),
                                                'help'          => __( 'Accepts shortcodes, so you can nest headings or buttons here to build a full hero-style block beside the image.', 'fw' ),
                                                'tinymce'       => true,
                                                'size'          => 'large',
                                                'editor_height' => 300,
                                                'shortcodes'    => true,
                                                'wpautop'       => true,
                                                'value'         => '',
                                        ],
                                        'image_alt' => [
                                                'type'  => 'text',
                                                'label' => __( 'Image Alt Text', 'fw' ),
                                                'desc'  => __( 'Leave empty to use the alt text from the media library', 'fw' ),
                                                'help'  => __( 'Describes the image for screen readers and search engines. Set this when the image carries meaning; leave blank for purely decorative images.', 'fw' ),
                                                'value' => '',
                                        ],
                                        'image_link_group' => [
                                                'type'    => 'group',
                                                'options' => [
                                                        'image_link' => [
                                                                'type'  => 'text',
                                                                'label' => __( 'Image Link', 'fw' ),
                                                                'desc'  => __( 'Optional URL to link the image to', 'fw' ),
                                                                'help'  => __( 'Enter a full URL including https://. Leave empty to keep the image non-clickable.', 'fw' ),
                                                                'value' => '',
                                                        ],
                                                        'image_link_target' => [
                                                                'type'         => 'switch',
                                                                'label'        => __( 'Open Link in New Window', 'fw' ),
                                                                'help'         => __( 'Only applies when an Image Link is set. Turn on for links to external sites so visitors do not leave your page.', 'fw' ),
                                                                'right-choice' => [
                                                                        'value' => '_blank',
                                                                        'label' => __( 'Yes', 'fw' ),
                                                                ],
                                                                'left-choice'  => [
                                                                        'value' => '_self',
                                                                        'label' => __( 'No', 'fw' ),
                                                                ],
                                                        ],
                                                ],
                                        ],
                                ],
                        ],
                ],
        ],

        'tab_layout' => [
                'title'   => __( 'Layout', 'fw' ),
                'type'    => 'tab',
                'options' => [
                        'group_layout' => [
                                'type'    => 'group',
                                'options' => [
                                        'layout' => [
                                                'type'    => 'select',
                                                'label'   => __( 'Layout', 'fw' ),
                                                'desc'    => __( 'Choose the position of the image relative to the content', 'fw' ),
                                                'help'    => __( 'Alternate the layout between stacked Image-Content blocks down a page for a zig-zag rhythm that keeps long sections engaging.', 'fw' ),
                                                'choices' => [
                                                        'image-left'  => __( 'Image Left / Content Right', 'fw' ),
                                                        'image-right' => __( 'Image Right / Content Left', 'fw' ),
                                                ],
                                                'value' => 'image-left',
                                        ],
                                        'column_ratio' => call_user_func( function() {
                                                $img_uri = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/image-content/static/img' );
                                                $ratios  = [
                                                        '1-11' => '1/12 + 11/12',
                                                        '2-10' => '2/12 + 10/12',
                                                        '3-9'  => '3/12 + 9/12',
                                                        '4-8'  => '4/12 + 8/12',
                                                        '5-7'  => '5/12 + 7/12',
                                                        '6-6'  => '6/12 + 6/12',
                                                        '7-5'  => '7/12 + 5/12',
                                                        '8-4'  => '8/12 + 4/12',
                                                        '9-3'  => '9/12 + 3/12',
                                                        '10-2' => '10/12 + 2/12',
                                                        '11-1' => '11/12 + 1/12',
                                                ];
                                                $choices = [];
                                                foreach ( $ratios as $key => $label ) {
                                                        $choices[ $key ] = [
                                                                'small' => [
                                                                        'src'    => $img_uri . '/ratio-' . $key . '.svg',
                                                                        'height' => 40,
                                                                        'title'  => $label,
                                                                ],
                                                        ];
                                                }
                                                return [
                                                        'type'    => 'image-picker',
                                                        'label'   => __( 'Column Ratio', 'fw' ),
                                                        'desc'    => __( 'Set the width ratio between the image and content columns', 'fw' ),
                                                        'help'    => __( 'The first number is the image column, the second is the content column. Lean toward the content side when there is a lot of text to read.', 'fw' ),
                                                        'choices' => $choices,
                                                        'value'   => '4-8',
                                                ];
                                        } ),
                                        'vertical_align' => [
                                                'type'    => 'select',
                                                'label'   => __( 'Vertical Alignment', 'fw' ),
                                                'desc'    => __( 'Align the image and content vertically within the row', 'fw' ),
                                                'help'    => __( 'Matters most when the image and the text are different heights. Center usually looks balanced; Top aligns both columns to the same baseline.', 'fw' ),
                                                'choices' => [
                                                        'align-items-start'  => __( 'Top', 'fw' ),
                                                        'align-items-center' => __( 'Center', 'fw' ),
                                                        'align-items-end'    => __( 'Bottom', 'fw' ),
                                                ],
                                                'value' => 'align-items-center',
                                        ],
                                        'gap' => [
                                                'type'    => 'select',
                                                'label'   => __( 'Gap', 'fw' ),
                                                'desc'    => __( 'Space between the image and content columns', 'fw' ),
                                                'help'    => __( 'Larger gaps give the block more breathing room; choose None when you want the image and text to read as a single tight unit.', 'fw' ),
                                                'choices' => [
                                                        'g-0' => __( 'None', 'fw' ),
                                                        'g-3' => __( 'Small', 'fw' ),
                                                        'g-4' => __( 'Medium', 'fw' ),
                                                        'g-5' => __( 'Large', 'fw' ),
                                                ],
                                                'value' => 'g-4',
                                        ],
                                        'mobile_order' => [
                                                'type'    => 'select',
                                                'label'   => __( 'Mobile Stacking Order', 'fw' ),
                                                'desc'    => __( 'Which column appears first when stacked on mobile', 'fw' ),
                                                'help'    => __( 'On narrow screens the columns stack vertically. Choose Content First when the heading should be read before the image on phones.', 'fw' ),
                                                'choices' => [
                                                        'image-first'   => __( 'Image First', 'fw' ),
                                                        'content-first' => __( 'Content First', 'fw' ),
                                                ],
                                                'value' => 'image-first',
                                        ],
                                ],
                        ],
                ],
        ],

        'tab_styling' => [
                'title'   => __( 'Styling', 'fw' ),
                'type'    => 'tab',
                'options' => [
                        'group_options' => [
                                'type'    => 'group',
                                'options' => [
                                        'image_fit' => [
                                                'type'    => 'select',
                                                'label'   => __( 'Image Fit', 'fw' ),
                                                'desc'    => __( 'How the image fills its column', 'fw' ),
                                                'help'    => __( 'Cover crops the image to completely fill the column (good for matching heights); Contain shows the whole image, which may leave empty space.', 'fw' ),
                                                'choices' => [
                                                        'contain' => __( 'Contain (show full image)', 'fw' ),
                                                        'cover'   => __( 'Cover (fill column)', 'fw' ),
                                                ],
                                                'value' => 'contain',
                                        ],
                                        'image_radius' => [
                                                'type'    => 'select',
                                                'label'   => __( 'Image Border Radius', 'fw' ),
                                                'help'    => __( 'Rounds the image corners. Circle works best with a square image and Cover fit, otherwise the result will be an oval.', 'fw' ),
                                                'choices' => [
                                                        'rounded-0'      => __( 'None', 'fw' ),
                                                        'rounded-2'      => __( 'Small', 'fw' ),
                                                        'rounded-3'      => __( 'Medium', 'fw' ),
                                                        'rounded-4'      => __( 'Large', 'fw' ),
                                                        'rounded-circle' => __( 'Circle', 'fw' ),
                                                ],
                                                'value' => 'rounded-0',
                                        ],
                                        'image_shadow' => [
                                                'type'    => 'select',
                                                'label'   => __( 'Image Shadow', 'fw' ),
                                                'help'    => __( 'Adds a drop shadow to lift the image off the page. Keep it subtle (Small) on light backgrounds so it does not look heavy.', 'fw' ),
                                                'choices' => [
                                                        ''          => __( 'None', 'fw' ),
                                                        'shadow-sm' => __( 'Small', 'fw' ),
                                                        'shadow'    => __( 'Medium', 'fw' ),
                                                        'shadow-lg' => __( 'Large', 'fw' ),
                                                ],
                                                'value' => '',
                                        ],
                                ],
                        ],
                        'group_colors' => [
                                'type'    => 'group',
                                'options' => [
                                        'content_color' => sc_color_field_compact( array(
                                                'label' => __( 'Content Color', 'fw' ),
                                                'desc'  => __( 'Color preset applied to the body content text.', 'fw' ),
                                        ) ),
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
                'title'   => __( 'Advanced', 'fw' ),
                'type'    => 'tab',
                'options' => [
                        'advanced_settings' => [
                                'type'    => 'group',
                                'options' => array_merge(
                                        sc_get_advanced_tab(),
                                ),
                        ],
                ],
        ],
];
