<?php if (!defined('FW')) {
	die('Forbidden');
}

$bleed_illustration = '<div style="margin:0 0 15px;padding:15px;background:#f9f9f9;border:1px solid #e0e0e0;border-radius:6px;">
<svg viewBox="0 0 480 160" style="width:100%;max-width:480px;height:auto;display:block;margin:0 auto 12px;">
  <rect x="0" y="0" width="480" height="160" rx="4" fill="#1a1a2e" stroke="#444" stroke-width="1"/>
  <rect x="0" y="0" width="280" height="160" rx="0" fill="#22c55e"/>
  <rect x="280" y="0" width="200" height="160" rx="0" fill="#2a2a3e"/>
  <line x1="280" y1="0" x2="280" y2="160" stroke="#fff" stroke-width="1" stroke-dasharray="4,3" opacity="0.5"/>
  <line x1="60" y1="0" x2="60" y2="160" stroke="#fff" stroke-width="1" stroke-dasharray="4,3" opacity="0.3"/>
  <line x1="420" y1="0" x2="420" y2="160" stroke="#fff" stroke-width="1" stroke-dasharray="4,3" opacity="0.3"/>
  <text x="60" y="18" font-size="9" fill="#fff" opacity="0.4" font-family="sans-serif">container</text>
  <rect x="80" y="35" width="160" height="12" rx="2" fill="#fff" opacity="0.9"/>
  <rect x="80" y="55" width="180" height="8" rx="2" fill="#fff" opacity="0.5"/>
  <rect x="80" y="68" width="170" height="8" rx="2" fill="#fff" opacity="0.5"/>
  <rect x="80" y="81" width="150" height="8" rx="2" fill="#fff" opacity="0.5"/>
  <rect x="80" y="94" width="165" height="8" rx="2" fill="#fff" opacity="0.5"/>
  <rect x="80" y="107" width="140" height="8" rx="2" fill="#fff" opacity="0.5"/>
  <text x="140" y="138" font-size="10" fill="#fff" opacity="0.7" font-family="sans-serif" text-anchor="middle">Content</text>
  <rect x="300" y="25" width="80" height="80" rx="4" fill="#fff" opacity="0.15" stroke="#fff" stroke-width="1" opacity="0.3"/>
  <text x="340" y="70" font-size="20" fill="#fff" opacity="0.4" font-family="sans-serif" text-anchor="middle">&#x1f5bc;</text>
  <text x="350" y="138" font-size="10" fill="#fff" opacity="0.7" font-family="sans-serif" text-anchor="middle">Bleed Image</text>
  <text x="25" y="85" font-size="8" fill="#fff" opacity="0.35" font-family="sans-serif" text-anchor="middle" transform="rotate(-90,25,85)">bleeds to edge</text>
  <text x="455" y="85" font-size="8" fill="#fff" opacity="0.35" font-family="sans-serif" text-anchor="middle" transform="rotate(90,455,85)">bleeds to edge</text>
</svg>
<p style="margin:0;font-size:12px;color:#666;line-height:1.5;">
<strong>Bleed Layout</strong> splits the section into two sides. The <strong>content side</strong> (with your chosen background color) extends to the viewport edge, while a <strong>bleed image</strong> fills the opposite side and also extends to the viewport edge. This creates a striking split-screen effect commonly used for featured content sections.
</p>
</div>';

$options = array(
	'tab_layout' => array(
		'title'   => __('Layout', 'fw'),
		'type'    => 'tab',
		'options' => array(
			'is_fullwidth' => array(
				'label' => __('Full Width', 'fw'),
				'type'  => 'switch',
			),
			'background_color' => array(
				'label' => __('Background Color', 'fw'),
				'desc'  => __('Please select the background color', 'fw'),
				'type'  => 'color-picker',
			),
			'background_image' => array(
				'label'   => __('Background Image', 'fw'),
				'desc'    => __('Please select the background image', 'fw'),
				'type'    => 'background-image',
				'choices' => array(),
			),
			'video' => array(
				'label' => __('Background Video', 'fw'),
				'desc'  => __('Insert Video URL to embed this video', 'fw'),
				'type'  => 'text',
			),
		),
	),

	'tab_bleed_layout' => [
		'title'   => __( 'Bleed Layout', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'bleed_illustration' => [
				'type'  => 'html-full',
				'label' => false,
				'html'  => $bleed_illustration,
			],
			'bleed_layout' => [
				'type'    => 'multi-picker',
				'label'   => false,
				'desc'    => false,
				'picker'  => [
					'bleed_enabled' => [
						'type'         => 'switch',
						'label'        => __( 'Enable Bleed Layout', 'fw' ),
						'desc'         => __( 'Split this section into a content area and a full-bleed image', 'fw' ),
						'left-choice'  => [
							'value' => 'no',
							'label' => __( 'No', 'fw' ),
						],
						'right-choice' => [
							'value' => 'yes',
							'label' => __( 'Yes', 'fw' ),
						],
						'value' => 'no',
					],
				],
				'choices' => [
					'yes' => [
						'bleed_bg_color' => [
							'type'  => 'color-picker',
							'label' => __( 'Content Background Color', 'fw' ),
							'desc'  => __( 'Background color for the content side. Bleeds to the viewport edge.', 'fw' ),
							'value' => '',
						],
						'bleed_image' => [
							'type'  => 'upload',
							'label' => __( 'Bleed Image', 'fw' ),
							'desc'  => __( 'Image that fills the opposite side and extends to the viewport edge', 'fw' ),
						],
						'bleed_image_position' => [
							'type'    => 'select',
							'label'   => __( 'Image Position', 'fw' ),
							'desc'    => __( 'How the image is positioned within its area', 'fw' ),
							'choices' => [
								'center'       => __( 'Center', 'fw' ),
								'top'          => __( 'Top', 'fw' ),
								'bottom'       => __( 'Bottom', 'fw' ),
								'left'         => __( 'Left', 'fw' ),
								'right'        => __( 'Right', 'fw' ),
								'left top'     => __( 'Left Top', 'fw' ),
								'right top'    => __( 'Right Top', 'fw' ),
								'left bottom'  => __( 'Left Bottom', 'fw' ),
								'right bottom' => __( 'Right Bottom', 'fw' ),
							],
							'value' => 'center',
						],
						'bleed_image_side' => [
							'type'    => 'select',
							'label'   => __( 'Image Side', 'fw' ),
							'desc'    => __( 'Which side the image appears on', 'fw' ),
							'choices' => [
								'right' => __( 'Right', 'fw' ),
								'left'  => __( 'Left', 'fw' ),
							],
							'value' => 'right',
						],
						'bleed_image_ratio' => [
							'type'    => 'select',
							'label'   => __( 'Image / Content Ratio', 'fw' ),
							'desc'    => __( 'How much space the image takes vs. the content area', 'fw' ),
							'choices' => [
								'1-11' => __( '1/12 Image + 11/12 Content (col-md-1 + col-md-11)', 'fw' ),
								'2-10' => __( '1/6 Image + 5/6 Content (col-md-2 + col-md-10)', 'fw' ),
								'3-9'  => __( '1/4 Image + 3/4 Content (col-md-3 + col-md-9)', 'fw' ),
								'4-8'  => __( '1/3 Image + 2/3 Content (col-md-4 + col-md-8)', 'fw' ),
								'5-7'  => __( '5/12 Image + 7/12 Content (col-md-5 + col-md-7)', 'fw' ),
								'6-6'  => __( '1/2 Image + 1/2 Content (col-md-6 + col-md-6)', 'fw' ),
								'7-5'  => __( '7/12 Image + 5/12 Content (col-md-7 + col-md-5)', 'fw' ),
								'8-4'  => __( '2/3 Image + 1/3 Content (col-md-8 + col-md-4)', 'fw' ),
								'9-3'  => __( '3/4 Image + 1/4 Content (col-md-9 + col-md-3)', 'fw' ),
								'10-2' => __( '5/6 Image + 1/6 Content (col-md-10 + col-md-2)', 'fw' ),
								'11-1' => __( '11/12 Image + 1/12 Content (col-md-11 + col-md-1)', 'fw' ),
							],
							'value' => '5-7',
						],
						'bleed_vertical_align' => [
							'type'    => 'select',
							'label'   => __( 'Content Vertical Alignment', 'fw' ),
							'desc'    => __( 'Align the content vertically within the section', 'fw' ),
							'choices' => [
								'align-items-start'  => __( 'Top', 'fw' ),
								'align-items-center' => __( 'Center', 'fw' ),
								'align-items-end'    => __( 'Bottom', 'fw' ),
							],
							'value' => 'align-items-center',
						],
						'bleed_content_padding' => [
							'type'    => 'select',
							'label'   => __( 'Content Padding', 'fw' ),
							'desc'    => __( 'Vertical padding for the content area', 'fw' ),
							'choices' => [
								'0'    => __( 'None', 'fw' ),
								'2rem' => __( 'Small', 'fw' ),
								'3rem' => __( 'Medium', 'fw' ),
								'5rem' => __( 'Large', 'fw' ),
							],
							'value' => '3rem',
						],
						'bleed_mobile_stacking' => [
							'type'    => 'select',
							'label'   => __( 'Mobile Stacking Order', 'fw' ),
							'desc'    => __( 'Which appears first on mobile', 'fw' ),
							'choices' => [
								'content-first' => __( 'Content First', 'fw' ),
								'image-first'   => __( 'Image First', 'fw' ),
							],
							'value' => 'content-first',
						],
					],
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
				),
			],
		],
	],
);
