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
					'logos' => array(
						'type'          => 'addable-popup',
						'label'         => __( 'Logos', 'fw' ),
						'popup-title'   => __( 'Add / Edit Logo', 'fw' ),
						'template'      => '{{= name || "Logo" }}',
						'popup-options' => array(
							'image' => array(
								'type'  => 'upload',
								'label' => __( 'Logo Image', 'fw' ),
								'desc'  => __( 'A raster/vector logo file. For a monochrome mark, prefer the SVG Markup field below — it renders inline (crisp, recolourable, no SVG-upload plugin needed).', 'fw' ),
							),
							'svg' => array(
								'type'  => 'textarea',
								'label' => __( 'SVG Markup (inline)', 'fw' ),
								'desc'  => __( 'Paste raw &lt;svg&gt;…&lt;/svg&gt; markup to render the logo INLINE instead of uploading an image (sanitised on output). Use fill="currentColor" so the Logo Color on the Styling tab applies. Takes priority over Logo Image.', 'fw' ),
							),
							'name' => array(
								'type'  => 'text',
								'label' => __( 'Name (alt / label)', 'fw' ),
								'desc'  => __( 'Alt text — and the visible label when "Show Names" is on (Design tab).', 'fw' ),
							),
							'no_label' => array(
								'type'  => 'switch',
								'label' => __( 'Hide Name Label', 'fw' ),
								'desc'  => __( 'When Show Names is on, suppress THIS logo text label — e.g. a wordmark logo whose mark already reads as the name.', 'fw' ),
								'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
								'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
								'value' => 'no',
							),
							'link_url' => array(
								'type'  => 'text',
								'label' => __( 'Link URL', 'fw' ),
							),
							'link_target' => array(
								'type'  => 'switch',
								'label' => __( 'Open in New Tab', 'fw' ),
								'right-choice' => array( 'value' => '_blank', 'label' => __( 'Yes', 'fw' ) ),
								'left-choice'  => array( 'value' => '_self', 'label' => __( 'No', 'fw' ) ),
								'value' => '_blank',
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
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/logo-grid/static/img/design' );
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
							'label'   => __( 'Layout', 'fw' ),
							'value'   => 'grid',
							'choices' => $choices,
						);
					} ),
				),
			),
			'group_layout' => array(
				'type'    => 'group',
				'options' => array(
					'columns' => array(
						'type'    => 'select',
						'label'   => __( 'Columns / Per View', 'fw' ),
						'value'   => '4',
						'choices' => array( '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ),
						'desc'    => __( 'Columns (grid) or visible logos (carousel/marquee height scales with logo height).', 'fw' ),
					),
					'gap' => array(
						'type'    => 'select',
						'label'   => __( 'Gap', 'fw' ),
						'value'   => '4',
						'choices' => function_exists( 'sc_get_gap_select_choices' ) ? sc_get_gap_select_choices( __( 'None', 'fw' ) ) : array( '4' => '4' ),
						'help'    => function_exists( 'sc_styling_help_text' ) ? sc_styling_help_text( 'spacing' ) : '',
					),
					'logo_height' => array(
						'type'  => 'slider',
						'label' => __( 'Logo Height (px)', 'fw' ),
						'value' => 48,
						'properties' => array( 'min' => 24, 'max' => 120, 'step' => 2 ),
					),
					'grayscale' => array(
						'type'  => 'switch',
						'label' => __( 'Grayscale → Color on Hover', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'yes',
					),
					'show_labels' => array(
						'type'  => 'switch',
						'label' => __( 'Show Names', 'fw' ),
						'desc'  => __( 'Render each logo Name as a visible text label beside the mark (a "trusted by [logo] Brand" strip).', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'no',
					),
				),
			),
			'group_motion' => array(
				'type'    => 'group',
				'options' => array(
					'autoplay' => array(
						'type'  => 'switch',
						'label' => __( 'Carousel Autoplay', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'yes',
					),
					'speed' => array(
						'type'    => 'select',
						'label'   => __( 'Marquee / Autoplay Speed', 'fw' ),
						'value'   => 'normal',
						'choices' => array( 'slow' => __( 'Slow', 'fw' ), 'normal' => __( 'Normal', 'fw' ), 'fast' => __( 'Fast', 'fw' ) ),
					),
					'direction' => array(
						'type'    => 'select',
						'label'   => __( 'Marquee Direction', 'fw' ),
						'value'   => 'left',
						'choices' => array( 'left' => __( 'Right → Left', 'fw' ), 'right' => __( 'Left → Right', 'fw' ) ),
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
					'text_color' => sc_color_field_compact( array( 'label' => __( 'Logo / Label Color', 'fw' ), 'kind' => 'text', 'desc' => __( 'Colors the name labels and any inline SVG marks that use fill="currentColor".', 'fw' ) ) ),
					'box_bg'     => sc_color_field_compact( array( 'label' => __( 'Box Background (Boxed)', 'fw' ), 'kind' => 'bg' ) ),
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
