<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * Image Box — edit-modal options (the saved `atts` schema).
 *
 * The Design picker `choices` are built from the design registry
 * (views/parts/registry.php) so the catalog has ONE source of truth: add a
 * registry entry + a thumbnail and it shows up here automatically.
 */

$options = [

    /* ==========================================================
       TAB 1 — CONTENT
       ========================================================== */
    'tab_content' => [
        'title'   => __( 'Content', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_image' => [
                'type'    => 'group',
                'options' => [
                    'image' => [
                        'type'  => 'upload',
                        'label' => __( 'Image', 'fw' ),
                        'desc'  => __( 'Upload a new image or choose one from the media library.', 'fw' ),
                        'help'  => __( 'Pick a source larger than the display size so it stays sharp. Use the Image Crop Ratio (Design tab) to force a consistent shape across several boxes in a row.', 'fw' ),
                    ],
                    'image_alt' => [
                        'type'  => 'text',
                        'label' => __( 'Alt Text Override', 'fw' ),
                        'desc'  => __( 'Optional. Leave blank to use the alt text saved on the image in the media library.', 'fw' ),
                        'help'  => __( 'Describe the image for screen readers and SEO. Leave empty for a purely decorative image so it is skipped by assistive tech.', 'fw' ),
                    ],
                ],
            ],
            'group_text' => [
                'type'    => 'group',
                'options' => [
                    'subtitle' => [
                        'type'  => 'text',
                        'label' => __( 'Eyebrow / Subtitle', 'fw' ),
                        'desc'  => __( 'Small line shown above the title (e.g. a category like "Web Development").', 'fw' ),
                        'help'  => __( 'Optional. Often used for a category, tagline or date. Leave blank to hide it.', 'fw' ),
                    ],
                    'title' => [
                        'type'  => 'text',
                        'label' => __( 'Title', 'fw' ),
                        'help'  => __( 'The main heading for the box, e.g. "Project California". Leave blank to render an image-only box.', 'fw' ),
                    ],
                    'title_tag' => [
                        'type'    => 'select',
                        'label'   => __( 'Title HTML Tag', 'fw' ),
                        'desc'    => __( 'Semantic tag for the title. Pick the heading level that fits the page outline.', 'fw' ),
                        'help'    => __( 'Keep headings in order for SEO/accessibility (an H3 here should sit under an H2 above). Use Span or Paragraph when the title is decorative.', 'fw' ),
                        'value'   => 'h3',
                        'choices' => [
                            'h2'   => __( 'H2', 'fw' ),
                            'h3'   => __( 'H3', 'fw' ),
                            'h4'   => __( 'H4', 'fw' ),
                            'h5'   => __( 'H5', 'fw' ),
                            'h6'   => __( 'H6', 'fw' ),
                            'span' => __( 'Span (decorative, not a heading)', 'fw' ),
                            'p'    => __( 'Paragraph', 'fw' ),
                        ],
                    ],
                    'text' => [
                        'type'  => 'textarea',
                        'label' => __( 'Text', 'fw' ),
                        'desc'  => __( 'Optional description shown below the title.', 'fw' ),
                        'help'  => __( 'Keep it short for grid layouts where boxes sit side by side, so they stay the same height. On hover-overlay designs this is revealed over the image.', 'fw' ),
                    ],
                    'icon' => [
                        'type'         => 'icon-v2',
                        'label'        => __( 'Icon', 'fw' ),
                        'preview_size' => 'small',
                        'modal_size'   => 'medium',
                        'desc'         => __( 'Optional icon. Shown over the image on overlay designs, or above the title on stacked/feature designs.', 'fw' ),
                        'help'         => __( 'Recolour it via Icon Color in the Styling tab. For an emoji or your own SVG, use Custom Icon below instead.', 'fw' ),
                    ],
                    'custom_icon' => [
                        'type'  => 'text',
                        'label' => __( 'Custom Icon (Emoji / SVG)', 'fw' ),
                        'desc'  => __( 'Optional. If filled, overrides the Icon picker above. Accepts an emoji (e.g. 🔗) or inline SVG markup.', 'fw' ),
                        'help'  => __( 'Emoji and pasted SVG colours are fixed, so the Icon Color option will not affect them.', 'fw' ),
                    ],
                ],
            ],
            'group_button' => [
                'type'    => 'group',
                'options' => [
                    'button_style' => [
                        'type'    => 'select',
                        'label'   => __( 'Button / Link Style', 'fw' ),
                        'desc'    => __( 'How the call-to-action under the text is rendered. Choose None to hide it.', 'fw' ),
                        'value'   => 'none',
                        'choices' => [
                            'none'   => __( 'None', 'fw' ),
                            'button' => __( 'Button', 'fw' ),
                            'link'   => __( 'Text link', 'fw' ),
                            'arrow'  => __( 'Arrow link', 'fw' ),
                        ],
                    ],
                    'button_label' => [
                        'type'  => 'text',
                        'label' => __( 'Button Label', 'fw' ),
                        'desc'  => __( 'Text shown on the button / link.', 'fw' ),
                        'help'  => __( 'Use a short action phrase, e.g. "View Project". Ignored when Button / Link Style is None.', 'fw' ),
                        'value' => __( 'Read More', 'fw' ),
                    ],
                ],
            ],
        ],
    ],

    /* ==========================================================
       TAB 2 — DESIGN
       ========================================================== */
    'tab_design' => [
        'title'   => __( 'Design', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_design' => [
                'type'    => 'group',
                'options' => [
                    'design' => call_user_func( function () {
                        $registry = require dirname( __FILE__ ) . '/views/parts/registry.php';
                        $base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/image-box/static/img/design' );
                        $choices  = array();
                        if ( is_array( $registry ) ) {
                            foreach ( $registry as $key => $meta ) {
                                $thumb = isset( $meta['thumb'] ) ? $meta['thumb'] : ( $key . '.svg' );
                                $choices[ $key ] = array(
                                    'small' => array(
                                        'src'    => $base . '/' . $thumb,
                                        'height' => 72,
                                        'title'  => isset( $meta['label'] ) ? $meta['label'] : $key,
                                    ),
                                );
                            }
                        }
                        return array(
                            'type'    => 'image-picker',
                            'label'   => __( 'Design', 'fw' ),
                            'desc'    => __( 'The overall look of the box. Hover-overlay designs reveal the text over the image; caption / card / frame designs keep it visible.', 'fw' ),
                            'help'    => __( 'Pick the layout first, then fine-tune with the Image Crop Ratio, Media Width and Content Alignment below, plus the Hover Effect in the Effects & Link tab.', 'fw' ),
                            'value'   => 'stacked',
                            'choices' => $choices,
                        );
                    } ),
                ],
            ],
            'group_appearance' => [
                'type'    => 'group',
                'options' => [
                    'image_ratio' => [
                        'type'    => 'select',
                        'label'   => __( 'Image Crop Ratio', 'fw' ),
                        'desc'    => __( 'Force the image into a fixed shape (CSS object-fit cover). Use "Original" to keep the uploaded proportions.', 'fw' ),
                        'help'    => __( 'A fixed ratio keeps several boxes in a row perfectly aligned. Portrait (3:4) suits the tall portfolio-tile look.', 'fw' ),
                        'value'   => 'ratio-4-3',
                        'choices' => [
                            'original'    => __( 'Original (uncropped)', 'fw' ),
                            'ratio-1-1'   => __( 'Square 1:1', 'fw' ),
                            'ratio-4-3'   => __( 'Landscape 4:3', 'fw' ),
                            'ratio-3-2'   => __( 'Landscape 3:2', 'fw' ),
                            'ratio-16-9'  => __( 'Widescreen 16:9', 'fw' ),
                            'ratio-3-4'   => __( 'Portrait 3:4', 'fw' ),
                            'ratio-2-3'   => __( 'Portrait 2:3', 'fw' ),
                        ],
                    ],
                    'media_width' => [
                        'type'    => 'select',
                        'label'   => __( 'Media Width (Side designs)', 'fw' ),
                        'desc'    => __( 'For the Side designs only: how much of the row the image occupies. Ignored by other designs.', 'fw' ),
                        'value'   => '50',
                        'choices' => [
                            '33' => __( 'One third (33%)', 'fw' ),
                            '40' => __( 'Two fifths (40%)', 'fw' ),
                            '50' => __( 'Half (50%)', 'fw' ),
                            '60' => __( 'Three fifths (60%)', 'fw' ),
                        ],
                    ],
                    'content_align' => sc_alignment_field( array(
                        'label'   => __( 'Content Alignment', 'fw' ),
                        'inherit' => true,
                        'desc'    => __( 'Horizontal alignment of the eyebrow, title, text and button.', 'fw' ),
                        'help'    => __( 'Centered reads well on overlay and feature designs; Left suits side and caption designs. Leave on Inherit to use each design’s default.', 'fw' ),
                    ) ),
                ],
            ],
        ],
    ],

    /* ==========================================================
       TAB 3 — EFFECTS & LINK
       ========================================================== */
    'tab_effects' => [
        'title'   => __( 'Effects & Link', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_hover' => [
                'type'    => 'group',
                'options' => [
                    'hover_effect' => [
                        'type'    => 'select',
                        'label'   => __( 'Hover Effect', 'fw' ),
                        'desc'    => __( 'A motion / image effect applied on hover. Composes with any Design above.', 'fw' ),
                        'help'    => __( 'Zoom and Shine draw the eye on portfolio grids; Grayscale → Color is a classic team / logo treatment; Lift raises the whole card.', 'fw' ),
                        'value'   => 'zoom-in',
                        'choices' => [
                            'none'           => __( 'None', 'fw' ),
                            'zoom-in'        => __( 'Image zoom in', 'fw' ),
                            'zoom-out'       => __( 'Image zoom out', 'fw' ),
                            'grayscale'      => __( 'Grayscale → Color', 'fw' ),
                            'blur'           => __( 'Image blur', 'fw' ),
                            'shine'          => __( 'Shine sweep', 'fw' ),
                            'lift'           => __( 'Lift card', 'fw' ),
                            'tilt'           => __( '3D tilt', 'fw' ),
                        ],
                    ],
                    'transition_speed' => [
                        'type'    => 'select',
                        'label'   => __( 'Transition Speed', 'fw' ),
                        'value'   => 'normal',
                        'choices' => [
                            'fast'   => __( 'Fast (0.2s)', 'fw' ),
                            'normal' => __( 'Normal (0.4s)', 'fw' ),
                            'slow'   => __( 'Slow (0.7s)', 'fw' ),
                        ],
                    ],
                    'overlay_color' => sc_color_field_compact( array(
                        'label' => __( 'Overlay Color', 'fw' ),
                        'kind'  => 'bg',
                        'desc'  => __( 'Tint over the image for overlay / scrim / caption-bar designs. Defaults to a dark scrim when left empty.', 'fw' ),
                    ) ),
                    'overlay_opacity' => [
                        'type'    => 'select',
                        'label'   => __( 'Overlay Opacity', 'fw' ),
                        'desc'    => __( 'Strength of the overlay tint on hover-overlay / scrim designs.', 'fw' ),
                        'value'   => '60',
                        'choices' => [
                            '0'   => __( '0% (none)', 'fw' ),
                            '25'  => __( '25%', 'fw' ),
                            '40'  => __( '40%', 'fw' ),
                            '60'  => __( '60%', 'fw' ),
                            '75'  => __( '75%', 'fw' ),
                            '90'  => __( '90%', 'fw' ),
                        ],
                    ],
                ],
            ],
            'group_link' => [
                'type'    => 'group',
                'options' => [
                    'link_behavior' => [
                        'type'    => 'select',
                        'label'   => __( 'Link Behavior', 'fw' ),
                        'desc'    => __( 'What happens when the box (or its button) is clicked.', 'fw' ),
                        'help'    => __( 'Lightbox opens the full image in an overlay; Video opens the URL (YouTube / Vimeo / .mp4) in a lightbox player; URL navigates to the link below.', 'fw' ),
                        'value'   => 'none',
                        'choices' => [
                            'none'     => __( 'Not clickable', 'fw' ),
                            'url'      => __( 'Link to URL', 'fw' ),
                            'lightbox' => __( 'Open image in lightbox', 'fw' ),
                            'video'    => __( 'Open video in lightbox', 'fw' ),
                        ],
                    ],
                    'link_url' => [
                        'type'  => 'text',
                        'label' => __( 'Link / Video URL', 'fw' ),
                        'desc'  => __( 'Used when Link Behavior is "Link to URL" or "Open video in lightbox".', 'fw' ),
                        'help'  => __( 'A full URL (https://…). For video, paste a YouTube / Vimeo page URL or a direct .mp4 file. Ignored for the lightbox-image and not-clickable behaviors.', 'fw' ),
                    ],
                    'link_target' => [
                        'type'         => 'switch',
                        'label'        => __( 'Open Link in New Tab', 'fw' ),
                        'desc'         => __( 'Only applies to the "Link to URL" behavior.', 'fw' ),
                        'help'         => __( 'Recommended for links to external sites so visitors keep your page open.', 'fw' ),
                        'right-choice' => [ 'value' => '_blank', 'label' => __( 'Yes', 'fw' ) ],
                        'left-choice'  => [ 'value' => '_self', 'label' => __( 'No', 'fw' ) ],
                        'value'        => '_self',
                    ],
                ],
            ],
        ],
    ],

    /* ==========================================================
       TAB 4 — STYLING
       ========================================================== */
    'tab_styling' => [
        'title'   => __( 'Styling', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_colors' => [
                'type'    => 'group',
                'options' => [
                    'bg_color'         => sc_color_field_compact( array( 'label' => __( 'Background Color', 'fw' ), 'kind' => 'bg' ) ),
                    'font_size_preset' => sc_font_size_field( array(
                        'desc' => __( 'A named size from the framework presets. Customizable in Theme Settings on the official Unyson+ theme.', 'fw' ),
                    ) ),
                    'title_color' => sc_color_field_compact( array(
                        'label' => __( 'Title Color', 'fw' ),
                        'desc'  => __( 'Color applied to the title.', 'fw' ),
                    ) ),
                    'subtitle_color' => sc_color_field_compact( array(
                        'label' => __( 'Eyebrow / Subtitle Color', 'fw' ),
                        'desc'  => __( 'Color applied to the small eyebrow line above the title.', 'fw' ),
                    ) ),
                    'content_color' => sc_color_field_compact( array(
                        'label' => __( 'Text Color', 'fw' ),
                        'desc'  => __( 'Color applied to the body text.', 'fw' ),
                    ) ),
                    'icon_color' => sc_color_field_compact( array(
                        'label' => __( 'Icon Color', 'fw' ),
                        'desc'  => __( 'Color applied to the icon (font icons only).', 'fw' ),
                    ) ),
                    'accent_color' => sc_color_field_compact( array(
                        'label' => __( 'Accent Color', 'fw' ),
                        'kind'  => 'bg',
                        'desc'  => __( 'Used for the button background, arrow link and frame / badge accents.', 'fw' ),
                    ) ),
                ],
            ],
            'group_spacings' => [
                'type'    => 'group',
                'options' => [
                    'spacing' => array(
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
        'title'   => __( 'Advanced', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'advanced_settings' => [
                'type'    => 'group',
                'options' => sc_get_advanced_tab(),
            ],
        ],
    ],
];
