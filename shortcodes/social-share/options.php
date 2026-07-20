<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$networks = require dirname( __FILE__ ) . '/views/parts/networks.php';
$net_choices = array();
foreach ( (array) $networks as $key => $n ) {
	$net_choices[ $key ] = $n['label'];
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
					'networks' => array(
						'type'       => 'multi-select',
						'label'      => __( 'Networks', 'fw' ),
						'population' => 'array',
						'choices'    => $net_choices,
						'value'      => array( 'facebook', 'twitter', 'linkedin', 'pinterest', 'email', 'copy' ),
						'desc'       => __( 'Pick and order the share buttons.', 'fw' ),
					),
					'share_source' => array(
						'type'    => 'select',
						'label'   => __( 'Share URL', 'fw' ),
						'value'   => 'current',
						'choices' => array(
							'current' => __( 'This page', 'fw' ),
							'custom'  => __( 'Custom URL', 'fw' ),
						),
					),
					'custom_url' => array(
						'type'  => 'text',
						'label' => __( 'Custom URL', 'fw' ),
						'desc'  => __( 'Used when Share URL is "Custom URL".', 'fw' ),
					),
					'share_text' => array(
						'type'  => 'text',
						'label' => __( 'Share Text', 'fw' ),
						'desc'  => __( 'Title / text used by networks that support it (X, WhatsApp, email …). Blank = this page\'s title.', 'fw' ),
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
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/social-share/static/img/design' );
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
							'label'   => __( 'Button Style', 'fw' ),
							'value'   => 'brand',
							'choices' => $choices,
						);
					} ),
				),
			),
			'group_layout' => array(
				'type'    => 'group',
				'options' => array(
					'shape' => array(
						'type'    => 'select',
						'label'   => __( 'Shape', 'fw' ),
						'value'   => 'circle',
						'choices' => array(
							'circle'  => __( 'Circle', 'fw' ),
							'rounded' => __( 'Rounded', 'fw' ),
							'square'  => __( 'Square', 'fw' ),
						),
						'desc'    => __( 'Ignored by the "Minimal" style.', 'fw' ),
					),
					'size' => array(
						'type'    => 'select',
						'label'   => __( 'Size', 'fw' ),
						'value'   => 'md',
						'choices' => array( 'sm' => __( 'Small', 'fw' ), 'md' => __( 'Medium', 'fw' ), 'lg' => __( 'Large', 'fw' ) ),
					),
					'show_label' => array(
						'type'  => 'switch',
						'label' => __( 'Show Network Name', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'no',
						'desc'  => __( 'Always on for the Minimal style.', 'fw' ),
					),
					'layout' => array(
						'type'    => 'select',
						'label'   => __( 'Layout', 'fw' ),
						'value'   => 'inline',
						'choices' => array(
							'inline'  => __( 'Inline (wrap)', 'fw' ),
							'stacked' => __( 'Stacked (full width)', 'fw' ),
						),
					),
					'align' => sc_alignment_field( array(
						'label' => __( 'Alignment', 'fw' ),
						'value' => 'left',
					) ),
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
					'custom_color' => sc_color_field_compact( array( 'label' => __( 'Override Button Color', 'fw' ), 'kind' => 'bg', 'desc' => __( 'Use one color for every button instead of brand colors.', 'fw' ) ) ),
					'icon_color'   => sc_color_field_compact( array( 'label' => __( 'Icon / Label Color', 'fw' ), 'desc' => __( 'For the Minimal / Outline styles.', 'fw' ) ) ),
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
