<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Pricing Table — edit-modal options (the saved `atts` schema).
 */

$options = array(

	/* ========================== CONTENT ========================== */
	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group' => array(
				'type'    => 'group',
				'options' => array(
					'title' => array(
						'type'  => 'text',
						'label' => __( 'Heading', 'fw' ),
						'desc'  => __( 'Optional heading above the plans.', 'fw' ),
					),
					'plans' => array(
						'type'          => 'addable-popup',
						'label'         => __( 'Plans', 'fw' ),
						'popup-title'   => __( 'Add / Edit Plan', 'fw' ),
						'desc'          => __( 'Each entry is one pricing plan / column.', 'fw' ),
						'template'      => '{{=plan_title}}',
						'popup-options' => array(
							'plan_title' => array(
								'type'  => 'text',
								'label' => __( 'Plan Name', 'fw' ),
								'value' => __( 'Starter', 'fw' ),
							),
							'icon' => array(
								'type'         => 'icon-v2',
								'label'        => __( 'Icon', 'fw' ),
								'preview_size' => 'small',
								'desc'         => __( 'Optional icon shown above the plan name.', 'fw' ),
							),
							'subtitle' => array(
								'type'  => 'text',
								'label' => __( 'Subtitle', 'fw' ),
								'desc'  => __( 'Small line under the plan name (e.g. "For individuals").', 'fw' ),
							),
							'currency' => array(
								'type'  => 'text',
								'label' => __( 'Currency Symbol', 'fw' ),
								'value' => '$',
							),
							'price' => array(
								'type'  => 'text',
								'label' => __( 'Price', 'fw' ),
								'value' => '29',
								'desc'  => __( 'The amount, e.g. 29 or 0 (Free).', 'fw' ),
							),
							'period' => array(
								'type'  => 'text',
								'label' => __( 'Period', 'fw' ),
								'value' => __( '/mo', 'fw' ),
								'desc'  => __( 'Billing period suffix, e.g. /mo or /year. Leave blank for none.', 'fw' ),
							),
							'features' => array(
								'type'  => 'textarea',
								'label' => __( 'Features', 'fw' ),
								'desc'  => __( 'One feature per line. Start a line with "-" to show it as unavailable (crossed out).', 'fw' ),
								'value' => "10 Projects\n5 GB Storage\nEmail Support\n- Priority Support",
							),
							'featured' => array(
								'type'  => 'switch',
								'label' => __( 'Featured (highlight this plan)', 'fw' ),
								'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
								'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
								'value' => 'no',
							),
							'ribbon' => array(
								'type'  => 'text',
								'label' => __( 'Ribbon / Badge', 'fw' ),
								'desc'  => __( 'Optional corner badge, e.g. "Most Popular".', 'fw' ),
							),
							'button_label' => array(
								'type'  => 'text',
								'label' => __( 'Button Label', 'fw' ),
								'value' => __( 'Choose Plan', 'fw' ),
								'desc'  => __( 'Leave blank to hide the button.', 'fw' ),
							),
							'button_url' => array(
								'type'  => 'text',
								'label' => __( 'Button URL', 'fw' ),
							),
							'button_target' => array(
								'type'  => 'switch',
								'label' => __( 'Open in New Tab', 'fw' ),
								'right-choice' => array( 'value' => '_blank', 'label' => __( 'Yes', 'fw' ) ),
								'left-choice'  => array( 'value' => '_self', 'label' => __( 'No', 'fw' ) ),
								'value' => '_self',
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
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/pricing-table/static/img/design' );
						$choices  = array();
						foreach ( (array) $registry as $key => $meta ) {
							$choices[ $key ] = array(
								'small' => array(
									'src'    => $base . '/' . ( isset( $meta['thumb'] ) ? $meta['thumb'] : $key . '.svg' ),
									'height' => 72,
									'title'  => isset( $meta['label'] ) ? $meta['label'] : $key,
								),
							);
						}
						return array(
							'type'    => 'image-picker',
							'label'   => __( 'Design', 'fw' ),
							'desc'    => __( 'The card style. Hover a thumbnail to see its name.', 'fw' ),
							'value'   => 'classic',
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
						'label'   => __( 'Columns (Desktop)', 'fw' ),
						'value'   => '3',
						'choices' => array( '2' => '2', '3' => '3', '4' => '4', '5' => '5' ),
						'desc'    => __( 'Plans per row on desktop.', 'fw' ),
					),
					'gap' => array(
						'type'    => 'select',
						'label'   => __( 'Gap', 'fw' ),
						'value'   => '4',
						'choices' => function_exists( 'sc_get_gap_select_choices' ) ? sc_get_gap_select_choices( __( 'None', 'fw' ) ) : array( '4' => '4' ),
						'desc'    => __( 'Spacing between plans, from your Spacing → Gap Scale presets.', 'fw' ),
						'help'    => function_exists( 'sc_styling_help_text' ) ? sc_styling_help_text( 'spacing' ) : '',
					),
						'featured_style' => array(
							'type'       => 'multi-select',
							'label'      => __( 'Featured Plan Emphasis', 'fw' ),
							'population' => 'array',
							'value'      => array( 'raise', 'highlight', 'glow', 'badge', 'accent_button' ),
							'choices'    => array(
								'raise'         => __( 'Raise / lift up', 'fw' ),
								'enlarge'       => __( 'Enlarge (scale up)', 'fw' ),
								'highlight'     => __( 'Highlight border', 'fw' ),
								'glow'          => __( 'Glow shadow', 'fw' ),
								'fill'          => __( 'Accent background', 'fw' ),
								'badge'         => __( 'Top badge / banner', 'fw' ),
								'accent_button' => __( 'Accent button', 'fw' ),
								'emphasize'     => __( 'Emphasize plan name', 'fw' ),
							),
							'desc'       => __( 'How the featured plan stands out. Pick any combination; the source-style default is raise + highlight + glow + badge + accent button. Leave empty for no emphasis.', 'fw' ),
						),
					'button_style' => array(
						'type'    => 'select',
						'label'   => __( 'Button Style', 'fw' ),
						'value'   => 'solid',
						'choices' => array(
							'solid'   => __( 'Solid (accent fill)', 'fw' ),
							'outline' => __( 'Outline', 'fw' ),
						),
						'desc'    => __( 'Style of the plan buttons. A featured plan with the "Accent button" emphasis stays solid.', 'fw' ),
					),
					'align' => sc_alignment_field( array(
						'label'   => __( 'Text Alignment', 'fw' ),
						'value'   => 'center',
						'desc'    => __( 'Alignment of the plan content.', 'fw' ),
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
					'accent_color'    => sc_color_field_compact( array( 'label' => __( 'Accent Color', 'fw' ), 'kind' => 'bg', 'desc' => __( 'Featured highlight, price and button background.', 'fw' ) ) ),
					'bg_color'        => sc_color_field_compact( array( 'label' => __( 'Section Background', 'fw' ), 'kind' => 'bg' ) ),
					'card_bg'         => sc_color_field_compact( array( 'label' => __( 'Card Background', 'fw' ), 'kind' => 'bg' ) ),
					'title_color'     => sc_color_field_compact( array( 'label' => __( 'Plan Name Color', 'fw' ) ) ),
					'price_color'     => sc_color_field_compact( array( 'label' => __( 'Price Color', 'fw' ) ) ),
					'text_color'      => sc_color_field_compact( array( 'label' => __( 'Text / Features Color', 'fw' ) ) ),
					'font_size_preset'=> sc_font_size_field(),
				),
			),
			'group_spacings' => array(
				'type'    => 'group',
				'options' => array(
					'spacing' => array(
						'type'  => 'spacing',
						'label' => __( 'Margin & Padding', 'fw' ),
						'desc'  => __( 'All Sides applies to every side at once; any per-side value overrides it.', 'fw' ),
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
