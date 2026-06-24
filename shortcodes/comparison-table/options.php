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
					'first_col_label' => array(
						'type'  => 'text',
						'label' => __( 'Feature Column Header', 'fw' ),
						'value' => __( 'Features', 'fw' ),
						'desc'  => __( 'The top-left header above the feature names.', 'fw' ),
					),
					'columns' => array(
						'type'          => 'addable-popup',
						'label'         => __( 'Columns (Plans)', 'fw' ),
						'popup-title'   => __( 'Add / Edit Column', 'fw' ),
						'template'      => '{{= name || "Column" }}',
						'desc'          => __( 'Each column is a plan / product shown across the top.', 'fw' ),
						'popup-options' => array(
							'name'    => array( 'type' => 'text', 'label' => __( 'Name', 'fw' ), 'value' => __( 'Plan', 'fw' ) ),
							'price'   => array( 'type' => 'text', 'label' => __( 'Price / Subtitle', 'fw' ), 'desc' => __( 'e.g. "$29 / mo" — shown under the name.', 'fw' ) ),
							'badge'   => array( 'type' => 'text', 'label' => __( 'Badge', 'fw' ), 'desc' => __( 'Optional ribbon, e.g. "Popular".', 'fw' ) ),
							'featured' => array(
								'type'  => 'switch',
								'label' => __( 'Highlight this column', 'fw' ),
								'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
								'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
								'value' => 'no',
							),
							'button_text'   => array( 'type' => 'text', 'label' => __( 'Button Text', 'fw' ) ),
							'button_url'    => array( 'type' => 'text', 'label' => __( 'Button URL', 'fw' ) ),
							'button_target' => array(
								'type'  => 'switch',
								'label' => __( 'Open in New Tab', 'fw' ),
								'right-choice' => array( 'value' => '_blank', 'label' => __( 'Yes', 'fw' ) ),
								'left-choice'  => array( 'value' => '_self',  'label' => __( 'No', 'fw' ) ),
								'value' => '_self',
							),
						),
					),
					'rows' => array(
						'type'          => 'addable-popup',
						'label'         => __( 'Rows (Features)', 'fw' ),
						'popup-title'   => __( 'Add / Edit Row', 'fw' ),
						'template'      => '{{= label || "Row" }}',
						'popup-options' => array(
							'is_heading' => array(
								'type'  => 'switch',
								'label' => __( 'Section Heading row', 'fw' ),
								'desc'  => __( 'Renders the label as a full-width group heading (no cells).', 'fw' ),
								'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
								'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
								'value' => 'no',
							),
							'label'   => array( 'type' => 'text', 'label' => __( 'Feature Label', 'fw' ), 'value' => __( 'Feature', 'fw' ) ),
							'tooltip' => array( 'type' => 'text', 'label' => __( 'Hint', 'fw' ), 'desc' => __( 'Optional small note under the feature label.', 'fw' ) ),
							'values'  => array(
								'type'  => 'textarea',
								'label' => __( 'Cell Values', 'fw' ),
								'desc'  => __( 'ONE line per column, in the same order as the columns above. Use <b>yes</b> for a check, <b>no</b> for a cross, <b>-</b> for a dash, or any text for a literal value (e.g. "Up to 5").', 'fw' ),
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
					'style' => array(
						'type'    => 'select',
						'label'   => __( 'Style', 'fw' ),
						'value'   => 'bordered',
						'choices' => array(
							'bordered' => __( 'Bordered', 'fw' ),
							'striped'  => __( 'Striped rows', 'fw' ),
							'minimal'  => __( 'Minimal', 'fw' ),
						),
					),
					'highlight_featured' => array(
						'type'  => 'switch',
						'label' => __( 'Highlight Featured Column', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'yes',
					),
					'sticky_header' => array(
						'type'  => 'switch',
						'label' => __( 'Sticky Header', 'fw' ),
						'desc'  => __( 'Keep the plan header visible while scrolling the page.', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'no',
					),
					'center_cells' => array(
						'type'  => 'switch',
						'label' => __( 'Center Cell Values', 'fw' ),
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
					'accent_color'    => sc_color_field_compact( array( 'label' => __( 'Accent (checks / featured)', 'fw' ), 'kind' => 'bg' ) ),
					'header_bg'       => sc_color_field_compact( array( 'label' => __( 'Header Background', 'fw' ), 'kind' => 'bg' ) ),
					'header_text'     => sc_color_field_compact( array( 'label' => __( 'Header Text', 'fw' ) ) ),
					'text_color'      => sc_color_field_compact( array( 'label' => __( 'Cell Text', 'fw' ) ) ),
					'border_color'    => sc_color_field_compact( array( 'label' => __( 'Border Color', 'fw' ), 'kind' => 'bg' ) ),
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
