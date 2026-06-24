<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$bi_days = array(
	'mon' => __( 'Monday', 'fw' ), 'tue' => __( 'Tuesday', 'fw' ), 'wed' => __( 'Wednesday', 'fw' ),
	'thu' => __( 'Thursday', 'fw' ), 'fri' => __( 'Friday', 'fw' ), 'sat' => __( 'Saturday', 'fw' ), 'sun' => __( 'Sunday', 'fw' ),
);

$options = array(

	/* ========================== CONTENT ========================== */
	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_hours' => array(
				'type'    => 'group',
				'options' => array(
					'biz_name' => array(
						'type'  => 'text',
						'label' => __( 'Business Name', 'fw' ),
						'desc'  => __( 'Optional heading.', 'fw' ),
					),
					'hours' => array(
						'type'          => 'addable-popup',
						'label'         => __( 'Opening Hours', 'fw' ),
						'popup-title'   => __( 'Add / Edit Day', 'fw' ),
						'template'      => '{{= day }}',
						'value'         => array(
							array( 'day' => 'mon', 'closed' => 'no', 'open' => '09:00', 'close' => '17:00' ),
							array( 'day' => 'tue', 'closed' => 'no', 'open' => '09:00', 'close' => '17:00' ),
							array( 'day' => 'wed', 'closed' => 'no', 'open' => '09:00', 'close' => '17:00' ),
							array( 'day' => 'thu', 'closed' => 'no', 'open' => '09:00', 'close' => '17:00' ),
							array( 'day' => 'fri', 'closed' => 'no', 'open' => '09:00', 'close' => '17:00' ),
							array( 'day' => 'sat', 'closed' => 'no', 'open' => '10:00', 'close' => '14:00' ),
							array( 'day' => 'sun', 'closed' => 'yes', 'open' => '', 'close' => '' ),
						),
						'popup-options' => array(
							'day' => array(
								'type'    => 'select',
								'label'   => __( 'Day', 'fw' ),
								'value'   => 'mon',
								'choices' => $bi_days,
							),
							'closed' => array(
								'type'  => 'switch',
								'label' => __( 'Closed', 'fw' ),
								'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
								'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
								'value' => 'no',
							),
							'open' => array(
								'type'  => 'text',
								'label' => __( 'Opens (HH:MM, 24h)', 'fw' ),
								'value' => '09:00',
							),
							'close' => array(
								'type'  => 'text',
								'label' => __( 'Closes (HH:MM, 24h)', 'fw' ),
								'value' => '17:00',
							),
							'note' => array(
								'type'  => 'text',
								'label' => __( 'Note', 'fw' ),
								'desc'  => __( 'Optional, e.g. "By appointment".', 'fw' ),
							),
						),
					),
					'show_status' => array(
						'type'  => 'switch',
						'label' => __( 'Show Open / Closed Status', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'yes',
						'desc'  => __( 'Computed live from the site timezone and today\'s hours.', 'fw' ),
					),
					'time_format' => array(
						'type'    => 'select',
						'label'   => __( 'Time Format', 'fw' ),
						'value'   => '12',
						'choices' => array( '12' => __( '12-hour (5:00 PM)', 'fw' ), '24' => __( '24-hour (17:00)', 'fw' ) ),
					),
				),
			),
			'group_contact' => array(
				'type'    => 'group',
				'options' => array(
					'address' => array( 'type' => 'textarea', 'label' => __( 'Address', 'fw' ) ),
					'phone'   => array( 'type' => 'text', 'label' => __( 'Phone', 'fw' ) ),
					'email'   => array( 'type' => 'text', 'label' => __( 'Email', 'fw' ) ),
					'website' => array( 'type' => 'text', 'label' => __( 'Website URL', 'fw' ) ),
					'map_link'=> array( 'type' => 'text', 'label' => __( 'Map / Directions URL', 'fw' ) ),
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
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/business-info/static/img/design' );
						$choices  = array();
						foreach ( (array) $registry as $key => $meta ) {
							$choices[ $key ] = array( 'small' => array(
								'src'    => $base . '/' . ( isset( $meta['thumb'] ) ? $meta['thumb'] : $key . '.svg' ),
								'height' => 60,
								'title'  => isset( $meta['label'] ) ? $meta['label'] : $key,
							) );
						}
						return array(
							'type'    => 'image-picker',
							'label'   => __( 'Layout', 'fw' ),
							'value'   => 'card',
							'choices' => $choices,
						);
					} ),
				),
			),
			'group_options' => array(
				'type'    => 'group',
				'options' => array(
					'highlight_today' => array(
						'type'  => 'switch',
						'label' => __( 'Highlight Today', 'fw' ),
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
					'accent_color' => sc_color_field_compact( array( 'label' => __( 'Accent', 'fw' ), 'kind' => 'bg' ) ),
					'card_bg'      => sc_color_field_compact( array( 'label' => __( 'Card Background', 'fw' ), 'kind' => 'bg' ) ),
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
