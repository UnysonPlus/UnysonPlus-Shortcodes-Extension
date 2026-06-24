<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(

	/* ========================== CONTENT ========================== */
	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group' => array(
				'type'    => 'group',
				'options' => array(
					'poster' => array(
						'type'  => 'upload',
						'label' => __( 'Poster Image', 'fw' ),
						'desc'  => __( 'The image shown before the video plays.', 'fw' ),
					),
					'video_url' => array(
						'type'  => 'text',
						'label' => __( 'Video URL', 'fw' ),
						'desc'  => __( 'A YouTube or Vimeo page URL, or a direct .mp4 / .webm file.', 'fw' ),
						'help'  => __( 'Examples: https://youtu.be/XXXX, https://vimeo.com/123456, https://site.com/clip.mp4', 'fw' ),
					),
					'play_label' => array(
						'type'  => 'text',
						'label' => __( 'Play Label', 'fw' ),
						'desc'  => __( 'Optional text shown beside the play button (e.g. "Watch the film").', 'fw' ),
					),
					'caption' => array(
						'type'  => 'text',
						'label' => __( 'Caption / Accessible Label', 'fw' ),
						'desc'  => __( 'Used as the button\'s screen-reader label.', 'fw' ),
						'value' => __( 'Play video', 'fw' ),
					),
				),
			),
		),
	),

	/* ========================== DESIGN ========================== */
	'tab_design' => array(
		'title'   => __( 'Design', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_design' => array(
				'type'    => 'group',
				'options' => array(
					'design' => call_user_func( function () {
						$registry = require dirname( __FILE__ ) . '/views/parts/registry.php';
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/video-popup/static/img/design' );
						$choices  = array();
						foreach ( (array) $registry as $key => $meta ) {
							$choices[ $key ] = array( 'small' => array(
								'src'    => $base . '/' . ( isset( $meta['thumb'] ) ? $meta['thumb'] : $key . '.svg' ),
								'height' => 64,
								'title'  => isset( $meta['label'] ) ? $meta['label'] : $key,
							) );
						}
						return array(
							'type'    => 'image-picker',
							'label'   => __( 'Play Button Style', 'fw' ),
							'value'   => 'classic',
							'choices' => $choices,
						);
					} ),
				),
			),
			'group_layout' => array(
				'type'    => 'group',
				'options' => array(
					'ratio' => array(
						'type'    => 'select',
						'label'   => __( 'Poster Ratio', 'fw' ),
						'value'   => 'ratio-16-9',
						'choices' => array(
							'original'   => __( 'Original (uncropped)', 'fw' ),
							'ratio-16-9' => __( 'Widescreen 16:9', 'fw' ),
							'ratio-4-3'  => __( 'Landscape 4:3', 'fw' ),
							'ratio-1-1'  => __( 'Square 1:1', 'fw' ),
							'ratio-21-9' => __( 'Cinematic 21:9', 'fw' ),
						),
					),
					'play_size' => array(
						'type'    => 'select',
						'label'   => __( 'Play Button Size', 'fw' ),
						'value'   => 'md',
						'choices' => array( 'sm' => __( 'Small', 'fw' ), 'md' => __( 'Medium', 'fw' ), 'lg' => __( 'Large', 'fw' ) ),
					),
					'rounded' => array(
						'type'    => 'select',
						'label'   => __( 'Corner Radius', 'fw' ),
						'value'   => 'rounded',
						'choices' => array( 'rounded-0' => __( 'Square', 'fw' ), 'rounded' => __( 'Rounded', 'fw' ), 'rounded-lg' => __( 'Large', 'fw' ) ),
					),
				),
			),
			'group_overlay' => array(
				'type'    => 'group',
				'options' => array(
					'overlay' => array(
						'type'  => 'switch',
						'label' => __( 'Darken Poster', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'yes',
					),
					'hover_zoom' => array(
						'type'  => 'switch',
						'label' => __( 'Zoom Poster on Hover', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'yes',
					),
				),
			),
		),
	),

	/* ========================== STYLING ========================== */
	'tab_styling' => array(
		'title'   => __( 'Styling', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_colors' => array(
				'type'    => 'group',
				'options' => array(
					'accent_color' => sc_color_field_compact( array( 'label' => __( 'Play Button Color', 'fw' ), 'kind' => 'bg' ) ),
					'icon_color'   => sc_color_field_compact( array( 'label' => __( 'Play Icon Color', 'fw' ) ) ),
					'overlay_color'=> sc_color_field_compact( array( 'label' => __( 'Overlay Color', 'fw' ), 'kind' => 'bg' ) ),
					'label_color'  => sc_color_field_compact( array( 'label' => __( 'Label Color', 'fw' ) ) ),
					'font_size_preset' => sc_font_size_field(),
				),
			),
			'group_spacings' => array(
				'type'    => 'group',
				'options' => array(
					'spacing' => array(
						'type'  => 'spacing',
						'label' => __( 'Margin & Padding', 'fw' ),
						'help'  => sc_styling_help_text( 'spacing' ),
					),
				),
			),
		),
	),
	'tab_animation' => array(
		'title'   => __( 'Animations', 'fw' ),
		'type'    => 'tab',
		'options' => sc_get_animation_fields(),
	),
	'tab_advanced' => array(
		'title'   => __( 'Advanced', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'advanced_settings' => array(
				'type'    => 'group',
				'options' => sc_get_advanced_tab(),
			),
		),
	),
);
