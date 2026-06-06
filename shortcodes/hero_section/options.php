<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(
	'tab_layout' => array(
		'title'   => __( 'Layout', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'is_fullwidth' => array(
				'label' => __( 'Full Width', 'fw' ),
				'help'  => __( 'On: the hero background spans the full viewport width (typical for a landing hero). Off: it is constrained to the site container width.', 'fw' ),
				'type'  => 'switch',
				'value' => 'yes',
			),
			'min_height' => array(
				'label'   => __( 'Minimum Height', 'fw' ),
				'desc'    => __( 'Vertical space the hero occupies', 'fw' ),
				'help'    => __( 'Measured in vh (percent of the screen height). "100vh — Full Viewport" makes the hero fill the screen on load; the hero grows taller if content needs more room.', 'fw' ),
				'type'    => 'select',
				'choices' => array(
					'40vh'  => __( '40vh — Compact', 'fw' ),
					'60vh'  => __( '60vh — Medium', 'fw' ),
					'80vh'  => __( '80vh — Tall', 'fw' ),
					'100vh' => __( '100vh — Full Viewport', 'fw' ),
				),
				'value' => '60vh',
			),
			'content_vertical_align' => array(
				'label'   => __( 'Content Vertical Alignment', 'fw' ),
				'help'    => __( 'Positions the heading/text block within the hero\'s minimum height. "Center" is the usual choice; "Bottom" suits a caption sitting above a fold-line CTA.', 'fw' ),
				'type'    => 'select',
				'choices' => array(
					'flex-start' => __( 'Top', 'fw' ),
					'center'     => __( 'Center', 'fw' ),
					'flex-end'   => __( 'Bottom', 'fw' ),
				),
				'value' => 'center',
			),
		),
	),

	'tab_background' => array(
		'title'   => __( 'Background', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'background_image' => array(
				'label' => __( 'Background Image', 'fw' ),
				'desc'  => __( 'The image used for the parallax effect', 'fw' ),
				'help'  => __( 'Choose a wide, high-resolution image — it is scaled to cover and shifts on scroll, so edges get cropped. If left empty, the Fallback Background Color is shown instead.', 'fw' ),
				'type'  => 'upload',
			),
			'parallax_strength' => array(
				'label'   => __( 'Parallax Strength', 'fw' ),
				'desc'    => __( 'How much the background moves relative to scroll (0 = static, 1 = full speed)', 'fw' ),
				'help'    => __( 'Values around 0.3–0.5 give a subtle, tasteful drift; closer to 1 can feel jarring. Set to 0 to disable the parallax and pin the image in place.', 'fw' ),
				'type'    => 'slider',
				'value'   => 0.4,
				'properties' => array(
					'min'  => 0,
					'max'  => 1,
					'step' => 0.05,
				),
			),
			'overlay_color' => array(
				'label' => __( 'Overlay Color', 'fw' ),
				'desc'  => __( 'Color drawn on top of the background image (use transparency)', 'fw' ),
				'help'  => __( 'A semi-transparent dark overlay (e.g. rgba(0,0,0,0.35)) keeps light text legible over busy images. Set full transparency to show the image untinted.', 'fw' ),
				'type'  => 'color-picker',
				'value' => 'rgba(0,0,0,0.35)',
			),
			'background_color' => array(
				'label' => __( 'Fallback Background Color', 'fw' ),
				'desc'  => __( 'Shown when no image is set', 'fw' ),
				'help'  => __( 'Acts as a safety net: it also fills the area while the Background Image loads, so pick a color close to the image\'s dominant tone to avoid a flash.', 'fw' ),
				'type'  => 'color-picker',
				'value' => '',
			),
		),
	),

	'tab_advanced' => array(
		'title'   => __( 'Advanced', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'advanced_settings' => array(
				'type'    => 'group',
				'options' => array_merge(
					function_exists( 'sc_get_advanced_tab' ) ? sc_get_advanced_tab() : array()
				),
			),
		),
	),
);
