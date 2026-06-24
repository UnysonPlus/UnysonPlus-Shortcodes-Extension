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
					'events' => array(
						'type'          => 'addable-popup',
						'label'         => __( 'Events', 'fw' ),
						'popup-title'   => __( 'Add / Edit Event', 'fw' ),
						'desc'          => __( 'Each entry is one event placed on its date in the month grid.', 'fw' ),
						'template'      => '{{= title || "Event" }}{{= date ? " — " + date : "" }}',
						'popup-options' => array(
							'title' => array(
								'type'  => 'text',
								'label' => __( 'Title', 'fw' ),
								'value' => __( 'New event', 'fw' ),
							),
							'date' => array(
								'type'  => 'date-picker',
								'label' => __( 'Date', 'fw' ),
								'desc'  => __( 'The day the event appears on.', 'fw' ),
							),
							'end_date' => array(
								'type'  => 'date-picker',
								'label' => __( 'End Date', 'fw' ),
								'desc'  => __( 'Optional — for a multi-day event, the last day (inclusive).', 'fw' ),
							),
							'time' => array(
								'type'  => 'text',
								'label' => __( 'Time', 'fw' ),
								'desc'  => __( 'Optional display time, e.g. "8:00 AM" or "14:30". Ignored if All Day is on.', 'fw' ),
							),
							'all_day' => array(
								'type'  => 'switch',
								'label' => __( 'All Day', 'fw' ),
								'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
								'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
								'value' => 'no',
							),
							'url' => array(
								'type'  => 'text',
								'label' => __( 'Link URL', 'fw' ),
								'desc'  => __( 'Optional — makes the event clickable.', 'fw' ),
							),
							'color' => array(
								'type'    => 'select',
								'label'   => __( 'Color', 'fw' ),
								'value'   => 'blue',
								'choices' => array(
									'blue'   => __( 'Blue', 'fw' ),
									'green'  => __( 'Green', 'fw' ),
									'amber'  => __( 'Amber', 'fw' ),
									'red'    => __( 'Red', 'fw' ),
									'purple' => __( 'Purple', 'fw' ),
									'teal'   => __( 'Teal', 'fw' ),
								),
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
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/calendar/static/img/design' );
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
			'group_opts' => array(
				'type'    => 'group',
				'options' => array(
					'start_week' => array(
						'type'    => 'select',
						'label'   => __( 'Start Week On', 'fw' ),
						'value'   => 'mon',
						'choices' => array( 'mon' => __( 'Monday', 'fw' ), 'sun' => __( 'Sunday', 'fw' ) ),
					),
					'show_list' => array(
						'type'  => 'switch',
						'label' => __( 'Upcoming Events List', 'fw' ),
						'desc'  => __( 'Show a list of upcoming events beneath the month grid.', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'yes',
					),
					'list_limit' => array(
						'type'  => 'text',
						'label' => __( 'List Length', 'fw' ),
						'desc'  => __( 'Max events to show in the upcoming list.', 'fw' ),
						'value' => '5',
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
					'accent_color' => sc_color_field_compact( array( 'label' => __( 'Accent (today / nav)', 'fw' ), 'kind' => 'bg' ) ),
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
