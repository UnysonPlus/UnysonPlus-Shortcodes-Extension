<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

$options = array(
	'tab_layout' => array(
		'title'   => __( 'Layout', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_bleed' => array(
				'type'    => 'group',
				'options' => array(
					'bleed_image' => array(
						'type'        => 'upload',
						'label'       => __( 'Bleed Image', 'fw' ),
						'desc'        => __( 'Image that fills one side and extends to the viewport edge.', 'fw' ),
						'help'        => __( 'Use a high-resolution image — it is cropped to fill its half (cover), so detail near the edges may be trimmed depending on the Image / Content Ratio. Served responsively (srcset) when picked from the Media Library.', 'fw' ),
						'images_only' => true,
					),
					'bleed_image_alt' => array(
						'type'  => 'text',
						'label' => __( 'Image Alt Text', 'fw' ),
						'desc'  => __( 'Describe the image for screen readers / SEO. Leave blank to use the alt text saved on the image in the Media Library; leave empty only for a purely decorative image.', 'fw' ),
					),
					'bleed_image_side' => array(
						'type'    => 'select',
						'label'   => __( 'Image Side', 'fw' ),
						'desc'    => __( 'Which side the image appears on (desktop). On mobile the halves stack per the Mobile Stacking Order.', 'fw' ),
						'value'   => 'right',
						'choices' => array(
							'right' => __( 'Right', 'fw' ),
							'left'  => __( 'Left', 'fw' ),
						),
					),
					'bleed_image_ratio' => array(
						'type'    => 'select',
						'label'   => __( 'Image / Content Ratio', 'fw' ),
						'desc'    => __( 'How much width the image takes vs. the content. Each pair sums to 12 columns.', 'fw' ),
						'value'   => '5-7',
						'choices' => array(
							'1-11' => __( '1/12 Image + 11/12 Content', 'fw' ),
							'2-10' => __( '1/6 Image + 5/6 Content', 'fw' ),
							'3-9'  => __( '1/4 Image + 3/4 Content', 'fw' ),
							'4-8'  => __( '1/3 Image + 2/3 Content', 'fw' ),
							'5-7'  => __( '5/12 Image + 7/12 Content', 'fw' ),
							'6-6'  => __( '1/2 Image + 1/2 Content', 'fw' ),
							'7-5'  => __( '7/12 Image + 5/12 Content', 'fw' ),
							'8-4'  => __( '2/3 Image + 1/3 Content', 'fw' ),
							'9-3'  => __( '3/4 Image + 1/4 Content', 'fw' ),
							'10-2' => __( '5/6 Image + 1/6 Content', 'fw' ),
							'11-1' => __( '11/12 Image + 1/12 Content', 'fw' ),
						),
					),
					'bleed_image_position' => array(
						'type'    => 'select',
						'label'   => __( 'Image Position', 'fw' ),
						'desc'    => __( 'Which part of the image stays visible when it is cropped to fill.', 'fw' ),
						'value'   => 'center',
						'choices' => array(
							'center'       => __( 'Center', 'fw' ),
							'top'          => __( 'Top', 'fw' ),
							'bottom'       => __( 'Bottom', 'fw' ),
							'left'         => __( 'Left', 'fw' ),
							'right'        => __( 'Right', 'fw' ),
							'left top'     => __( 'Left Top', 'fw' ),
							'right top'    => __( 'Right Top', 'fw' ),
							'left bottom'  => __( 'Left Bottom', 'fw' ),
							'right bottom' => __( 'Right Bottom', 'fw' ),
						),
					),
					'bleed_image_lazy' => array(
						'type'         => 'switch',
						'label'        => __( 'Lazy-load Image', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value'        => 'no',
						'help'         => __( 'Off (default) loads the image immediately — best when the Bleed Section is near the top of the page (better LCP). Turn on for a section lower down the page.', 'fw' ),
					),
					'bleed_mobile_stacking' => array(
						'type'    => 'select',
						'label'   => __( 'Mobile Stacking Order', 'fw' ),
						'desc'    => __( 'Which half appears first when the section stacks on mobile.', 'fw' ),
						'value'   => 'content-first',
						'choices' => array(
							'content-first' => __( 'Content First', 'fw' ),
							'image-first'   => __( 'Image First', 'fw' ),
						),
					),
					'bleed_min_height' => array(
						'type'    => 'select',
						'label'   => __( 'Minimum Height', 'fw' ),
						'desc'    => __( 'Force a minimum section height so the split reads as a hero even when the content is short. The Content Vertical Align then positions the content within it.', 'fw' ),
						'value'   => 'none',
						'choices' => array(
							'none' => __( 'Auto (fit content)', 'fw' ),
							'sm'   => __( 'Small (40vh)', 'fw' ),
							'md'   => __( 'Medium (60vh)', 'fw' ),
							'lg'   => __( 'Large (80vh)', 'fw' ),
							'full' => __( 'Fullscreen (100vh)', 'fw' ),
						),
					),
					'is_fullwidth' => array(
						'type'  => 'switch',
						'label' => __( 'Full Width Content', 'fw' ),
						'help'  => __( 'On: the content side uses the full-width container. Off: the standard site container width. The image always bleeds to the viewport edge.', 'fw' ),
					),
				),
			),
		),
	),

	'tab_styling' => array(
		'title'   => __( 'Styling', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_styling' => array(
				'type'    => 'group',
				'options' => array(
					'background' => array(
						'type'  => 'background-pro',
						'label' => __( 'Content Background', 'fw' ),
						'desc'  => __( 'Background for the CONTENT side (color, gradient and/or image). It bleeds to the viewport edge behind the content. The image side is the Bleed Image on the Layout tab.', 'fw' ),
					),
					'bleed_overlay_color' => sc_color_field_compact( array(
						'label' => __( 'Image Overlay', 'fw' ),
						'kind'  => 'bg',
						'desc'  => __( 'A tint laid over the bleed image — e.g. to darken it or wash it toward a brand color. Pair with the opacity below.', 'fw' ),
					) ),
					'bleed_overlay_opacity' => array(
						'type'       => 'slider',
						'label'      => __( 'Overlay Opacity', 'fw' ),
						'desc'       => __( '0 = no overlay, 100 = fully opaque. Only used when an Image Overlay color is set.', 'fw' ),
						'value'      => 0,
						'properties' => array( 'min' => 0, 'max' => 100, 'step' => 5 ),
					),
					'bleed_vertical_align' => array(
						'type'    => 'select',
						'label'   => __( 'Content Vertical Align', 'fw' ),
						'desc'    => __( 'Vertical alignment of the content within the section.', 'fw' ),
						'value'   => 'align-items-center',
						'choices' => array(
							'align-items-start'  => __( 'Top', 'fw' ),
							'align-items-center' => __( 'Center', 'fw' ),
							'align-items-end'    => __( 'Bottom', 'fw' ),
						),
					),
					'bleed_content_padding' => array(
						'type'    => 'select',
						'label'   => __( 'Content Padding', 'fw' ),
						'desc'    => __( 'Vertical padding above and below the content.', 'fw' ),
						'value'   => '3rem',
						'choices' => array(
							'0'    => __( 'None', 'fw' ),
							'2rem' => __( 'Small', 'fw' ),
							'3rem' => __( 'Medium', 'fw' ),
							'5rem' => __( 'Large', 'fw' ),
						),
					),
				),
			),
		),
	),
);

if ( function_exists( 'sc_get_animation_fields' ) ) {
	$options['tab_animation'] = array(
		'title'   => __( 'Animations', 'fw' ),
		'type'    => 'tab',
		'options' => sc_get_animation_fields(),
	);
}

if ( function_exists( 'sc_get_advanced_tab' ) ) {
	$options['tab_advanced'] = array(
		'title'   => __( 'Advanced', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'advanced_settings' => array(
				'type'    => 'group',
				'options' => sc_get_advanced_tab(),
			),
		),
	);
}
