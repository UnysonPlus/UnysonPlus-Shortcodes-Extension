<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'image' => [
                'label' => __('Image', 'fw'),
                'desc'  => __('Either upload a new, or choose an existing image from your media library', 'fw'),
                'help'  => __('Use a square headshot for the most consistent look across multiple team members in a row.', 'fw'),
                'type'  => 'upload',
            ],
            'name' => [
                'label' => __('Name', 'fw'),
                'desc'  => __('Name of the person', 'fw'),
                'help'  => __('Shown as the main heading, e.g. "Jane Doe". This is the most prominent text in the card.', 'fw'),
                'type'  => 'text',
                'value' => '',
            ],
            'job' => [
                'label' => __('Job Title', 'fw'),
                'desc'  => __('Job title of the person.', 'fw'),
                'help'  => __('The role shown under the name, e.g. "Marketing Director". Keep it short so it fits on one line.', 'fw'),
                'type'  => 'text',
                'value' => '',
            ],
            'desc' => [
                'label' => __('Description', 'fw'),
                'desc'  => __('Enter a few words that describe the person', 'fw'),
                'help'  => __('A short bio or specialty line. Keep lengths similar across members so cards line up evenly.', 'fw'),
                'type'  => 'textarea',
                'value' => '',
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
                    'text_color'       => sc_color_field_compact( array( 'label' => __( 'Text Color', 'fw' ),       'kind' => 'text' ) ),
                    'bg_color'         => sc_color_field_compact( array( 'label' => __( 'Background Color', 'fw' ), 'kind' => 'bg' ) ),
                    'font_size_preset' => sc_font_size_field( array(
                        'desc' => __( 'A named size from the framework presets. Customizable in Theme Settings on the official Unyson+ theme.', 'fw' ),
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
        'title'   => __('Advanced', 'fw'),
        'type'    => 'tab',
        'options' => [
            'advanced_settings' => [
                'type'    => 'group',
                'options' => array_merge(
                    sc_get_advanced_tab(),
                    []
                ),
            ],
        ],
    ],
];

// CARD DESIGNER — the same Card Rows system as [wc_products]: arrange the member's
// slots (image, name, job title, description) into rows with inline/stacked layout +
// alignment, with a live schematic preview. The default rows reproduce the classic
// stacked look, so existing team members render unchanged. Inserted right after the
// Content tab. Guarded so the shortcode still works if the shared helper isn't loaded.
if ( function_exists( 'sc_card_rows_field' ) && function_exists( 'sc_card_preview_mount_html' ) ) {
	$options_card_tab = array(
		'tab_card' => array(
			'title'   => __( 'Card', 'fw' ),
			'type'    => 'tab',
			'options' => array(
				'group_card' => array(
					'type'    => 'group',
					'options' => array(
						'card_preview' => array(
							'type'  => 'html-full',
							'label' => false,
							'html'  => sc_card_preview_mount_html(),
						),
						'card_rows' => sc_card_rows_field( array(
							'label' => __( 'Card Rows', 'fw' ),
							'desc'  => __( 'The card layout for this team member. Add / drag rows; in each row pick & order the slots (image, name, job title, description) and set inline / stacked + alignment. A slot appears only when it\'s in a row and has content — remove a slot to hide it.', 'fw' ),
							'slots' => array(
								'media' => __( 'Image', 'fw' ),
								'name'  => __( 'Name', 'fw' ),
								'job'   => __( 'Job Title', 'fw' ),
								'desc'  => __( 'Description', 'fw' ),
							),
							'value' => array(
								array( 'slots' => array( 'media' ),       'direction' => 'stack', 'justify' => 'start', 'align' => 'center' ),
								array( 'slots' => array( 'name', 'job' ), 'direction' => 'stack', 'justify' => 'start', 'align' => 'center' ),
								array( 'slots' => array( 'desc' ),        'direction' => 'stack', 'justify' => 'start', 'align' => 'center' ),
							),
						) ),
						// The card SKIN — a reusable Box Preset (border, corners, shadow, fill + hover)
						// applied to the whole card — and the Image Style preset applied to the photo.
						// Uses the default "Box Style" label to match every other element (a Box
						// Preset is a general container skin, not a card-specific thing).
						'box_style'   => sc_card_box_style_field(),
						'image_style' => function_exists( 'sc_image_style_field' )
							? sc_image_style_field( array(
								'desc' => __( 'Apply a reusable Image Style (crop, corners, mask, filter, scrim) to the member photo. Manage presets in Theme Settings → Components → Image Styles.', 'fw' ),
							) )
							: array( 'type' => 'text', 'label' => __( 'Image Style', 'fw' ), 'value' => '', 'desc' => __( 'Image Styles are unavailable (styling helper not loaded).', 'fw' ) ),
					),
				),
			),
		),
	);
	// Splice the Card tab in right after Content.
	$pos     = array_search( 'tab_content', array_keys( $options ), true );
	$pos     = ( $pos === false ) ? 0 : $pos + 1;
	$options = array_slice( $options, 0, $pos, true ) + $options_card_tab + array_slice( $options, $pos, null, true );
}
