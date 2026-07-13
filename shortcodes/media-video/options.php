<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __( 'Content', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_content' => [
                'type'    => 'group',
                'options' => [

                    /* Source Type — a multi-picker that reveals ONLY the relevant fields:
                       an Embed URL (oEmbed) OR a self-hosted MP4/WebM file. */
                    'source_type' => [
                        'type'   => 'multi-picker',
                        'label'  => false,
                        'desc'   => false,
                        'picker' => [
                            'source' => [
                                'type'    => 'select',
                                'label'   => __( 'Video Source', 'fw' ),
                                'desc'    => __( 'Where the video comes from.', 'fw' ),
                                'help'    => __( 'Embed = paste a public YouTube / Vimeo / … page URL (WordPress oEmbed turns it into a player). Self-hosted = upload or link an MP4 / WebM file you host yourself — the only way to get a muted, looping, autoplaying background / hero clip.', 'fw' ),
                                'choices' => [
                                    'embed'       => __( 'Embed — YouTube / Vimeo / oEmbed URL', 'fw' ),
                                    'self_hosted' => __( 'Self-hosted file — MP4 / WebM', 'fw' ),
                                ],
                                'value'   => 'embed',
                            ],
                        ],
                        'choices' => [

                            /* ---------- EMBED (oEmbed provider) ---------- */
                            'embed' => [
                                'url' => [
                                    'type'            => 'text',
                                    'label'           => __( 'Video URL', 'fw' ),
                                    'desc'            => __( 'Paste a YouTube, Vimeo, or other oEmbed-supported page URL (e.g. https://youtu.be/xxxx).', 'fw' ),
                                    'help'            => __( 'Use the page URL, not the raw .mp4 file or the iframe embed code. For a self-hosted .mp4, switch Video Source to "Self-hosted file".', 'fw' ),
                                    'dynamic_content' => false,
                                ],
                                'youtube_nocookie' => [
                                    'type'         => 'switch',
                                    'label'        => __( 'Privacy Mode (no-cookie)', 'fw' ),
                                    'desc'         => __( 'For YouTube: embed via youtube-nocookie.com so the player sets no tracking cookies until playback.', 'fw' ),
                                    'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
                                    'right-choice' => [ 'value' => 'yes', 'label' => __( 'On',  'fw' ) ],
                                    'value'        => 'no',
                                ],
                                'lazy_facade' => [
                                    'type'         => 'switch',
                                    'label'        => __( 'Lazy-load (click to play)', 'fw' ),
                                    'desc'         => __( 'Show a lightweight poster + play button and only load the heavy provider iframe (and its scripts) when the visitor clicks. Big page-speed / Core Web Vitals win.', 'fw' ),
                                    'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
                                    'right-choice' => [ 'value' => 'yes', 'label' => __( 'On',  'fw' ) ],
                                    'value'        => 'no',
                                ],
                                'poster' => [
                                    'type'        => 'upload',
                                    'label'       => __( 'Poster / Thumbnail (facade)', 'fw' ),
                                    'desc'        => __( 'Optional still shown by the lazy-load facade before the visitor clicks play. Leave empty to use the provider thumbnail where available.', 'fw' ),
                                    'images_only' => true,
                                ],
                            ],

                            /* ---------- SELF-HOSTED (MP4 / WebM <video>) ---------- */
                            'self_hosted' => [
                                'video_file' => [
                                    'type'  => 'upload',
                                    'label' => __( 'Video File (MP4)', 'fw' ),
                                    'desc'  => __( 'Upload or choose an .mp4 from the Media Library. MP4 (H.264) has the widest browser support.', 'fw' ),
                                ],
                                'video_webm' => [
                                    'type'  => 'upload',
                                    'label' => __( 'Video File (WebM, optional)', 'fw' ),
                                    'desc'  => __( 'Optional WebM source for smaller files. The browser picks whichever it can play (WebM first, MP4 fallback).', 'fw' ),
                                ],
                                'video_url' => [
                                    'type'            => 'text',
                                    'label'           => __( 'External File URL (optional)', 'fw' ),
                                    'desc'            => __( 'A direct link to a self-hosted .mp4/.webm on a CDN. Used only when no file is chosen above.', 'fw' ),
                                    'dynamic_content' => false,
                                ],
                                'poster' => [
                                    'type'        => 'upload',
                                    'label'       => __( 'Poster Image', 'fw' ),
                                    'desc'        => __( 'Still frame shown before the video loads / plays. Recommended — improves perceived load and avoids a black box.', 'fw' ),
                                    'images_only' => true,
                                ],
                                'autoplay' => [
                                    'type'         => 'switch',
                                    'label'        => __( 'Autoplay', 'fw' ),
                                    'desc'         => __( 'Play automatically. Browsers only allow autoplay when the video is muted — turn Muted on too.', 'fw' ),
                                    'left-choice'  => [ 'value' => 'no',  'label' => __( 'No',  'fw' ) ],
                                    'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                                    'value'        => 'no',
                                ],
                                'muted' => [
                                    'type'         => 'switch',
                                    'label'        => __( 'Muted', 'fw' ),
                                    'desc'         => __( 'Start with no sound. Required for autoplay to work.', 'fw' ),
                                    'left-choice'  => [ 'value' => 'no',  'label' => __( 'No',  'fw' ) ],
                                    'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                                    'value'        => 'no',
                                ],
                                'loop' => [
                                    'type'         => 'switch',
                                    'label'        => __( 'Loop', 'fw' ),
                                    'desc'         => __( 'Restart when it reaches the end — for seamless background / hero clips.', 'fw' ),
                                    'left-choice'  => [ 'value' => 'no',  'label' => __( 'No',  'fw' ) ],
                                    'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                                    'value'        => 'no',
                                ],
                                'controls' => [
                                    'type'         => 'switch',
                                    'label'        => __( 'Show Controls', 'fw' ),
                                    'desc'         => __( 'Show the native play / seek / volume bar. Turn off for a clean background video.', 'fw' ),
                                    'left-choice'  => [ 'value' => 'no',  'label' => __( 'No',  'fw' ) ],
                                    'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                                    'value'        => 'yes',
                                ],
                                'playsinline' => [
                                    'type'         => 'switch',
                                    'label'        => __( 'Plays Inline (mobile)', 'fw' ),
                                    'desc'         => __( 'On iOS, play inside the page instead of forcing full-screen. Recommended for background / hero video.', 'fw' ),
                                    'left-choice'  => [ 'value' => 'no',  'label' => __( 'No',  'fw' ) ],
                                    'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                                    'value'        => 'yes',
                                ],
                                'preload' => [
                                    'type'    => 'select',
                                    'label'   => __( 'Preload', 'fw' ),
                                    'desc'    => __( 'How much to fetch before play. "Metadata" (dimensions + duration only) is the balanced default; "Auto" pre-buffers; "None" waits for a click.', 'fw' ),
                                    'value'   => 'metadata',
                                    'choices' => [
                                        'metadata' => __( 'Metadata (recommended)', 'fw' ),
                                        'auto'     => __( 'Auto (pre-buffer)', 'fw' ),
                                        'none'     => __( 'None (on demand)', 'fw' ),
                                    ],
                                ],
                                'object_fit' => [
                                    'type'    => 'select',
                                    'label'   => __( 'Fit', 'fw' ),
                                    'desc'    => __( 'Contain = show the whole frame (may letterbox). Cover = fill the box and crop — for full-bleed background video.', 'fw' ),
                                    'value'   => 'contain',
                                    'choices' => [
                                        'contain' => __( 'Contain (fit, letterbox)', 'fw' ),
                                        'cover'   => __( 'Cover (fill, crop)', 'fw' ),
                                    ],
                                ],
                            ],
                        ],
                        'show_borders' => false,
                    ],

                    /* ---------- shared by both sources ---------- */
                    'width' => [
                        'type'  => 'unit-input',
                        'label' => __( 'Video Max Width', 'fw' ),
                        'desc'  => __( 'Maximum width of the video (Ex: 600px, 80%).', 'fw' ),
                        'help'  => __( 'Pick a number and a unit. "px" is a fixed cap; "%" / "vw" are relative to the container / viewport. The video stays centered and the height follows the aspect ratio.', 'fw' ),
                        'value' => [ 'value' => 600, 'unit' => 'px' ],
                        'units' => [ 'px', '%', 'vw', 'rem', 'em' ],
                    ],
                    'ratio' => [
                        'type'    => 'select',
                        'label'   => __( 'Aspect Ratio', 'fw' ),
                        'desc'    => __( 'Choose the aspect ratio for the video. Portrait ratios available too.', 'fw' ),
                        'help'    => __( 'Match the ratio to the source video to avoid letterboxing. Use 16:9 for most modern videos, or a Portrait ratio for vertical clips.', 'fw' ),
                        'value'   => '16x9',
                        'choices' => [
                            '16x9' => 'Landscape 16:9 (Widescreen)',
                            '4x3'  => 'Landscape 4:3 (Standard)',
                            '1x1'  => 'Square 1:1',
                            '21x9' => 'Landscape 21:9 (Ultra Wide)',
                            '9x16' => 'Portrait 9:16 (Widescreen)',
                            '3x4'  => 'Portrait 3:4 (Standard)',
                        ],
                    ],
                ], // group_content options
            ], // group_content
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
