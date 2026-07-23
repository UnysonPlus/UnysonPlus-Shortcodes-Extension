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
                        'type'          => 'wp-editor',
                        'label'         => __( 'Text', 'fw' ),
                        'desc'          => __( 'Optional description shown below the title.', 'fw' ),
                        'help'          => __( 'Supports rich text (bold, links, lists). Keep it short for grid layouts where boxes sit side by side, so they stay the same height. On hover-overlay designs this is revealed over the image.', 'fw' ),
                        'size'          => 'large',
                        'reinit'        => true,
                        'tinymce'       => true,
                        'editor_height' => 180,
                        'wpautop'       => true,
                        'value'         => '',
                    ],
                    'icon' => [
                        'type'         => 'icon-v2',
                        'label'        => __( 'Icon', 'fw' ),
                        'preview_size' => 'small',
                        'modal_size'   => 'medium',
                        'desc'         => __( 'Optional icon — a font icon, a Lucide SVG, an emoji, or your own pasted/uploaded SVG, all from the one picker. Shown over the image on overlay designs, or above the title on stacked/feature designs.', 'fw' ),
                        'help'         => __( 'Font icons and currentColor SVGs recolour via Icon Color in the Styling tab; emoji colours are fixed.', 'fw' ),
                    ],
                    // Retired — the picker above now covers emoji + SVG. Kept as a
                    // hidden option so pre-existing values are preserved on re-save
                    // and still render (the picked icon takes precedence when set).
                    'custom_icon' => [
                        'type'  => 'hidden',
                        'label' => false,
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
                    'design_settings' => call_user_func( function () {
                        $registry = require dirname( __FILE__ ) . '/views/parts/registry.php';
                        $families = ( is_array( $registry ) && isset( $registry['families'] ) ) ? $registry['families'] : array();
                        $base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/image-box/static/img/design' );

                        // Family tiles (the popover image-picker).
                        $family_choices = array();
                        foreach ( $families as $fkey => $fmeta ) {
                            $thumb = isset( $fmeta['thumb'] ) ? $fmeta['thumb'] : ( $fkey . '.svg' );
                            $family_choices[ $fkey ] = array(
                                'small' => array(
                                    'src'    => $base . '/' . $thumb,
                                    'height' => 72,
                                    'title'  => isset( $fmeta['label'] ) ? $fmeta['label'] : $fkey,
                                ),
                                'label' => isset( $fmeta['label'] ) ? $fmeta['label'] : $fkey,
                            );
                        }

                        // Shared overlay colour/opacity fragments (Overlay family).
                        $ov_color = function_exists( 'sc_color_field_compact' )
                            ? sc_color_field_compact( array(
                                'label' => __( 'Overlay Colour', 'fw' ),
                                'kind'  => 'bg',
                                'desc'  => __( 'Tint over the image. Defaults to a dark scrim when empty.', 'fw' ),
                            ) )
                            : array( 'type' => 'color-picker', 'label' => __( 'Overlay Colour', 'fw' ) );

                        return array(
                            'type'         => 'multi-picker',
                            'label'        => __( 'Design', 'fw' ),
                            'desc'         => __( 'Pick a layout family — its variations appear in the panel.', 'fw' ),
                            'help'         => __( 'Choose the family first, then fine-tune its variations here. Universal controls (Image Crop Ratio, Content Alignment, Hover Effect, Link) stay in their own sections.', 'fw' ),
                            'popover'      => true,
                            'show_borders' => false,
                            'value'        => array( 'family' => 'stacked' ),
                            'picker'       => array(
                                'family' => array(
                                    'type'    => 'image-picker',
                                    'label'   => false,
                                    'desc'    => __( 'Hover a tile to preview it.', 'fw' ),
                                    'value'   => 'stacked',
                                    'choices' => $family_choices,
                                ),
                            ),
                            'choices'      => array(

                                'stacked' => array(
                                    'stacking' => call_user_func( function () {
                                        $sbase = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/image-box/static/img/stacking' );
                                        $items = array(
                                            'img-title-text' => __( 'Image, Title, Text', 'fw' ),
                                            'title-img-text' => __( 'Title, Image, Text', 'fw' ),
                                            'title-text-img' => __( 'Title, Text, Image', 'fw' ),
                                            'text-img-title' => __( 'Text, Image, Title', 'fw' ),
                                        );
                                        $ch = array();
                                        foreach ( $items as $k => $lbl ) {
                                            $ch[ $k ] = array(
                                                'small' => array( 'src' => $sbase . '/' . $k . '.svg', 'height' => 60, 'title' => $lbl ),
                                                'label' => $lbl,
                                            );
                                        }
                                        return array(
                                            'type'    => 'image-picker',
                                            'label'   => __( 'Stacking Order', 'fw' ),
                                            'desc'    => __( 'The vertical order of the image, heading and text.', 'fw' ),
                                            'value'   => 'img-title-text',
                                            'choices' => $ch,
                                        );
                                    } ),
                                ),

                                'side' => array(
                                    'image_side' => array(
                                        'type'    => 'select',
                                        'label'   => __( 'Image Side', 'fw' ),
                                        'value'   => 'left',
                                        'choices' => array(
                                            'left'  => __( 'Left', 'fw' ),
                                            'right' => __( 'Right', 'fw' ),
                                        ),
                                    ),
                                    'panel' => array(
                                        'type'         => 'switch',
                                        'label'        => __( 'Colour Panel', 'fw' ),
                                        'desc'         => __( 'Fill the content half with the Accent colour (equal-height split).', 'fw' ),
                                        'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
                                        'left-choice'  => array( 'value' => 'no', 'label' => __( 'No', 'fw' ) ),
                                        'value'        => 'no',
                                    ),
                                    'media_width' => array(
                                        'type'    => 'select',
                                        'label'   => __( 'Media Width', 'fw' ),
                                        'desc'    => __( 'How much of the row the image occupies.', 'fw' ),
                                        'value'   => '50',
                                        'choices' => array(
                                            '33' => __( 'One third (33%)', 'fw' ),
                                            '40' => __( 'Two fifths (40%)', 'fw' ),
                                            '50' => __( 'Half (50%)', 'fw' ),
                                            '60' => __( 'Three fifths (60%)', 'fw' ),
                                        ),
                                    ),
                                ),

                                'overlay' => array(
                                    'reveal' => array(
                                        'type'    => 'select',
                                        'label'   => __( 'Reveal', 'fw' ),
                                        'desc'    => __( 'How the text sits on the image.', 'fw' ),
                                        'value'   => 'scrim',
                                        'choices' => array(
                                            'scrim'   => __( 'Gradient scrim (always visible)', 'fw' ),
                                            'cover'   => __( 'Editorial cover (title at top)', 'fw' ),
                                            'overlap' => __( 'Overlapping panel (magazine)', 'fw' ),
                                            'bar'     => __( 'Solid caption bar', 'fw' ),
                                            'fade'    => __( 'Fade in on hover', 'fw' ),
                                            'slide'   => __( 'Slide up on hover', 'fw' ),
                                            'center'  => __( 'Centered on hover', 'fw' ),
                                            'frame'   => __( 'Frame draw on hover', 'fw' ),
                                        ),
                                    ),
                                    'overlay_color'   => $ov_color,
                                    'overlay_opacity' => array(
                                        'type'    => 'select',
                                        'label'   => __( 'Overlay Opacity', 'fw' ),
                                        'value'   => '60',
                                        'choices' => array(
                                            '0'  => __( '0% (none)', 'fw' ),
                                            '25' => __( '25%', 'fw' ),
                                            '40' => __( '40%', 'fw' ),
                                            '60' => __( '60%', 'fw' ),
                                            '75' => __( '75%', 'fw' ),
                                            '90' => __( '90%', 'fw' ),
                                        ),
                                    ),
                                ),

                                'card' => array(
                                    'style' => array(
                                        'type'    => 'select',
                                        'label'   => __( 'Card Style', 'fw' ),
                                        'value'   => 'card',
                                        'choices' => array(
                                            'card'          => __( 'Bordered card', 'fw' ),
                                            'caption-below' => __( 'Clean caption strip', 'fw' ),
                                        ),
                                    ),
                                ),

                                'frame' => array(
                                    'style' => array(
                                        'type'    => 'select',
                                        'label'   => __( 'Frame Style', 'fw' ),
                                        'value'   => 'polaroid',
                                        'choices' => array(
                                            'polaroid'    => __( 'Polaroid', 'fw' ),
                                            'postcard'    => __( 'Postcard', 'fw' ),
                                            'badge'       => __( 'Bordered badge', 'fw' ),
                                            'photo-stack' => __( 'Photo stack', 'fw' ),
                                        ),
                                    ),
                                ),
                            ),
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
                    'content_align' => sc_alignment_field( array(
                        'label'   => __( 'Content Alignment', 'fw' ),
                        'inherit' => true,
                        'desc'    => __( 'Horizontal alignment of the eyebrow, title, text and button.', 'fw' ),
                        'help'    => __( 'Centered reads well on overlay and feature designs; Left suits side and caption designs. Leave on Inherit to use each design’s default.', 'fw' ),
                    ) ),
                    'image_size' => array(
                        'type'    => 'short-select',
                        'label'   => __( 'Image Size', 'fw' ),
                        'desc'    => __( 'How large the image renders. Applies to the image-top families (Stacked, Card, Frame); the Side family uses Media Width instead.', 'fw' ),
                        'help'    => __( 'Small / X-Small centre the image — handy for a logo or avatar with a shape Mask below.', 'fw' ),
                        'value'   => 'full',
                        'choices' => array(
                            'full'   => __( 'Full', 'fw' ),
                            'large'  => __( 'Large (75%)', 'fw' ),
                            'medium' => __( 'Medium (55%)', 'fw' ),
                            'small'  => __( 'Small (35%)', 'fw' ),
                            'xsmall' => __( 'X-Small (140px)', 'fw' ),
                        ),
                    ),
                    // Image Mask option removed — image masking is now handled by the shared
                    // Image Style preset (Styling → Image Style; Theme Settings → Components →
                    // Image Styles), which carries the same shape library. Existing boxes with a
                    // saved `image_mask` still RENDER via the view + `.imgbox--mask-*` CSS (back-compat).
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
                    'box_style'        => sc_card_box_style_field(),
                    'image_style'      => function_exists( 'sc_image_style_field' )
                        ? sc_image_style_field()
                        : [ 'type' => 'select', 'label' => __( 'Image Style', 'fw' ), 'value' => '', 'choices' => [ '' => __( 'None', 'fw' ) ] ],
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
