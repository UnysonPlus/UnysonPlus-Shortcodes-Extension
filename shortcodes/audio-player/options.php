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
					'tracks' => array(
						'type'          => 'addable-popup',
						'label'         => __( 'Tracks', 'fw' ),
						'popup-title'   => __( 'Add / Edit Track', 'fw' ),
						'desc'          => __( 'One track = one audio file. Add several for a playlist.', 'fw' ),
						'template'      => '{{= title || "Track" }}',
						'popup-options' => array(
							'audio' => array(
								'type'  => 'upload',
								'label' => __( 'Audio File', 'fw' ),
								'desc'  => __( 'Upload or pick an audio file (mp3, m4a, ogg, wav).', 'fw' ),
								'files_ext' => array( 'mp3', 'm4a', 'ogg', 'wav', 'aac' ),
							),
							'audio_url' => array(
								'type'  => 'text',
								'label' => __( 'Audio URL (fallback)', 'fw' ),
								'desc'  => __( 'Used if no file is chosen above — a direct link to an audio file.', 'fw' ),
							),
							'title' => array(
								'type'  => 'text',
								'label' => __( 'Title', 'fw' ),
								'value' => __( 'Untitled', 'fw' ),
							),
							'artist' => array(
								'type'  => 'text',
								'label' => __( 'Artist / Subtitle', 'fw' ),
							),
							'cover' => array(
								'type'  => 'upload',
								'label' => __( 'Cover Image', 'fw' ),
								'desc'  => __( 'Optional artwork (used by Card / Playlist designs).', 'fw' ),
							),
						),
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
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/audio-player/static/img/design' );
						$choices  = array();
						foreach ( (array) $registry as $key => $meta ) {
							$choices[ $key ] = array( 'small' => array(
								'src'    => $base . '/' . ( isset( $meta['thumb'] ) ? $meta['thumb'] : $key . '.svg' ),
								'height' => 56,
								'title'  => isset( $meta['label'] ) ? $meta['label'] : $key,
							) );
						}
						return array(
							'type'    => 'image-picker',
							'label'   => __( 'Design', 'fw' ),
							'value'   => 'classic',
							'choices' => $choices,
						);
					} ),
				),
			),
			'group_behavior' => array(
				'type'    => 'group',
				'options' => array(
					'autoplay' => array(
						'type'  => 'switch',
						'label' => __( 'Autoplay', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'no',
						'help'  => __( 'Most browsers block autoplay with sound until the visitor interacts with the page.', 'fw' ),
					),
					'loop' => array(
						'type'  => 'switch',
						'label' => __( 'Loop', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'no',
						'desc'  => __( 'Repeat the track (or restart the playlist after the last track).', 'fw' ),
					),
					'show_volume' => array(
						'type'  => 'switch',
						'label' => __( 'Volume Control', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'yes',
					),
					'show_download' => array(
						'type'  => 'switch',
						'label' => __( 'Download Button', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'no',
					),
					'rounded' => array(
						'type'    => 'select',
						'label'   => __( 'Corner Radius', 'fw' ),
						'value'   => 'rounded',
						'choices' => array( 'rounded-0' => __( 'Square', 'fw' ), 'rounded' => __( 'Rounded', 'fw' ), 'rounded-lg' => __( 'Large', 'fw' ) ),
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
					'accent_color' => sc_color_field_compact( array( 'label' => __( 'Accent (controls / progress)', 'fw' ), 'kind' => 'bg' ) ),
					'bg_color'     => sc_color_field_compact( array( 'label' => __( 'Player Background', 'fw' ), 'kind' => 'bg' ) ),
					'text_color'   => sc_color_field_compact( array( 'label' => __( 'Text Color', 'fw' ) ) ),
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
