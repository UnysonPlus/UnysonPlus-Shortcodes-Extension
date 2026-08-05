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
					'plans' => array(
						'type'          => 'addable-popup',
						'label'         => __( 'Plans', 'fw' ),
						'popup-title'   => __( 'Add / Edit Plan', 'fw' ),
						'desc'          => __( 'Each entry is one pricing plan / column.', 'fw' ),
						'template'      => '{{=plan_title}}',
						// Grouped plan editor: each section is a `group` (Unyson groups flatten their values,
						// so every field keeps its original id at the plan-object level — saved plans and
						// the `{{=plan_title}}` template are unaffected by the regrouping).
						'popup-options' => array(
							'group_plan' => array(
								'type'    => 'group',
								'options' => array(
									'plan_title' => array(
										'type'  => 'text',
										'label' => __( 'Plan Name', 'fw' ),
										'value' => __( 'Starter', 'fw' ),
									),
									'icon' => array(
										'type'         => 'icon',
										'label'        => __( 'Icon', 'fw' ),
										'preview_size' => 'small',
										'desc'         => __( 'Optional icon shown above the plan name.', 'fw' ),
									),
									'subtitle' => array(
										'type'  => 'text',
										'label' => __( 'Subtitle', 'fw' ),
										'desc'  => __( 'Small line under the plan name (e.g. "For individuals").', 'fw' ),
									),
								),
							),
							'group_pricing' => array(
								'type'    => 'group',
								'options' => array(
									'currency' => array(
										'type'  => 'text',
										'label' => __( 'Currency Symbol', 'fw' ),
										'value' => '$',
										'desc'  => __( 'Used for both the monthly and yearly price.', 'fw' ),
									),
									// Price / Period / Original Price each carry BOTH the monthly and yearly figure
									// on one row (multi-inline). The Yearly input is only used when the Billing
									// Toggle (Content tab) is on; a blank Yearly falls back to the Monthly value.
									'price' => array(
										'type'             => 'multi-inline',
										'equal'            => true,
										'label'            => __( 'Price', 'fw' ),
										'desc'             => __( 'The amount, e.g. 29 or 0 (Free). Fill Yearly to charge a different price under "Bill Yearly".', 'fw' ),
										'value'            => array( 'monthly' => '29', 'yearly' => '' ),
										'fw_multi_options' => array(
											'monthly' => array( 'type' => 'text', 'title' => __( 'Monthly', 'fw' ) ),
											'yearly'  => array( 'type' => 'text', 'title' => __( 'Yearly', 'fw' ) ),
										),
									),
									'period' => array(
										'type'             => 'multi-inline',
										'equal'            => true,
										'label'            => __( 'Period', 'fw' ),
										'desc'             => __( 'Period suffix, e.g. /mo and /yr. Leave blank for none.', 'fw' ),
										'value'            => array( 'monthly' => __( '/mo', 'fw' ), 'yearly' => __( '/yr', 'fw' ) ),
										'fw_multi_options' => array(
											'monthly' => array( 'type' => 'text', 'title' => __( 'Monthly', 'fw' ) ),
											'yearly'  => array( 'type' => 'text', 'title' => __( 'Yearly', 'fw' ) ),
										),
									),
									'original_price' => array(
										'type'             => 'multi-inline',
										'equal'            => true,
										'label'            => __( 'Original Price (struck-out)', 'fw' ),
										'desc'             => __( 'Optional "was" price crossed out above the price, e.g. $9.99. Renders verbatim. Blank = none.', 'fw' ),
										'value'            => array( 'monthly' => '', 'yearly' => '' ),
										'fw_multi_options' => array(
											'monthly' => array( 'type' => 'text', 'title' => __( 'Monthly', 'fw' ) ),
											'yearly'  => array( 'type' => 'text', 'title' => __( 'Yearly', 'fw' ) ),
										),
									),
								),
							),
							'group_features' => array(
								'type'    => 'group',
								'options' => array(
									'features' => array(
										'type'  => 'textarea',
										'label' => __( 'Features', 'fw' ),
										'desc'  => __( 'One feature per line. Start a line with "-" to show it as unavailable (crossed out).', 'fw' ),
										'value' => "10 Projects\n5 GB Storage\nEmail Support\n- Priority Support",
									),
								),
							),
							'group_highlight' => array(
								'type'    => 'group',
								'options' => array(
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
								),
							),
							'group_button' => array(
								'type'    => 'group',
								'options' => array(
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
			'group_billing' => array(
				'type'    => 'group',
				'options' => array(
					'billing_toggle' => array(
						'type'         => 'switch',
						'label'        => __( 'Monthly / Yearly Toggle', 'fw' ),
						'desc'         => __( 'Show a billing-period switch above the plans that swaps each plan\'s monthly price for its Yearly Price. Enter the yearly figures per plan above.', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value'        => 'no',
					),
					'billing_default' => array(
						'type'    => 'select',
						'label'   => __( 'Default Period', 'fw' ),
						'value'   => 'monthly',
						'choices' => array(
							'monthly' => __( 'Monthly', 'fw' ),
							'yearly'  => __( 'Yearly', 'fw' ),
						),
						'desc'    => __( 'Which price is shown before the visitor toggles.', 'fw' ),
					),
					'billing_monthly_label' => array(
						'type'  => 'text',
						'label' => __( 'Monthly Label', 'fw' ),
						'value' => __( 'Bill Monthly', 'fw' ),
					),
					'billing_yearly_label' => array(
						'type'  => 'text',
						'label' => __( 'Yearly Label', 'fw' ),
						'value' => __( 'Bill Yearly', 'fw' ),
					),
					'billing_note' => array(
						'type'  => 'text',
						'label' => __( 'Savings Note', 'fw' ),
						'desc'  => __( 'Optional small note beside the toggle, e.g. "Save 20%". Blank = none.', 'fw' ),
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
						// Built-in skins PLUS installed skin packs (disabled ones hidden),
						// via the pluggable-designs layer; local registry as fallback.
						if ( function_exists( 'fw_sc_design_picker_choices' ) ) {
							$choices = fw_sc_design_picker_choices( 'pricing_table' );
						} else {
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
					'button_preset' => array(
						'label'        => __( 'Button Preset', 'fw' ),
						'type'         => 'button-style-picker',
						'choices'      => function_exists( 'sc_get_button_style_choices' ) ? sc_get_button_style_choices() : array(),
						'value'        => '',
						'allow_none'   => true,
						'preview_text' => __( 'Select', 'fw' ),
						'desc'         => __( 'Apply a themed Button Preset (Theme Settings → General → Buttons) to every plan button. Leave as None to use the accent-coloured button. When a preset is set it owns the button look, so the "Accent button" featured emphasis no longer applies.', 'fw' ),
						'help'         => function_exists( 'sc_styling_help_text' ) ? sc_styling_help_text( 'button_style' ) : '',
					),
					'align' => sc_alignment_field( array(
						'label'   => __( 'Text Alignment', 'fw' ),
						'value'   => 'center',
						'desc'    => __( 'Alignment of the plan content.', 'fw' ),
					) ),
					'product_schema' => array(
						'type'         => 'switch',
						'label'        => __( 'Product Schema (JSON-LD)', 'fw' ),
						'desc'         => __( 'Output Product + Offer structured data (one per plan) so pricing is machine-readable to search engines and AI agents.', 'fw' ),
						'help'         => __( 'Emits a Product with an Offer (price + currency) for each plan. The currency symbol is mapped to an ISO code (default USD).', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value'        => 'no',
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
					'box_style'       => sc_card_box_style_field( array( 'desc' => __( 'Apply a Box Preset to each plan card. Manage presets in Theme Settings → Components → Box Presets.', 'fw' ) ) ),
					'icon_badge_preset' => sc_icon_badge_preset_field( array( 'desc' => __( 'Apply a reusable Icon Badge — a shaped tile (fill, border, corners, shadow) with its own icon colour + size — to EVERY plan\'s icon. Manage presets in Theme Settings → Components → Icon Badges.', 'fw' ) ) ),
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
