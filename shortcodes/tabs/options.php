<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = [
	'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'tabs' => [
				'type'          => 'addable-popup',
				'label'         => __( 'Tabs', 'fw' ),
				'popup-title'   => __( 'Add/Edit Tab', 'fw' ),
				'desc'          => __( 'Create your tabs', 'fw' ),
				'template'      => '{{=tab_title}}',
				'popup-options' => array(
					'tab_title' => array(
						'type'  => 'text',
						'label' => __('Title', 'fw')
					),
					'tab_content' => array(
						'type'  => 'textarea',
						'label' => __('Content', 'fw')
					),
					'is_active' => array(
						'type'  => 'switch',
						'label' => __('Active Tab', 'fw'),
						'value' => 'no',
						'left-choice' => [
							'value' => 'no',
							'label' => __('No', 'fw'),
						],
						'right-choice' => [
							'value' => 'yes',
							'label' => __('Yes', 'fw'),
						],
					),
				),
			],
			'tab_style' => [
				'type'    => 'select',
				'label'   => __('Tab Style', 'fw'),
				'desc'    => __('Choose the style of the tabs', 'fw'),
				'value'   => 'tabs',
				'choices' => [
					'tabs'      => __('Tabs (Default)', 'fw'),
					'pills'     => __('Pills', 'fw'),
					'underline' => __('Underline (v5.2+)', 'fw'),
				],
			],
			'justified' => [
				'type'  => 'switch',
				'label' => __('Justified Tabs', 'fw'),
				'desc'  => __('Stretch tabs to the full width of the container', 'fw'),
				'value' => 'no',
				'left-choice'  => [
					'value' => 'no',
					'label' => __('No', 'fw'),
				],
				'right-choice' => [
					'value' => 'yes',
					'label' => __('Yes', 'fw'),
				],
			],
			'alignment' => [
				'type'    => 'select',
				'label'   => __('Tab Alignment', 'fw'),
				'desc'    => __('Choose alignment of tab navigation', 'fw'),
				'value'   => 'start',
				'choices' => [
					'start'  => __('Start (Default)', 'fw'),
					'center' => __('Center', 'fw'),
					'end'    => __('End', 'fw'),
				],
			],
			'orientation' => [
				'type'    => 'select',
				'label'   => __('Tabs Orientation', 'fw'),
				'desc'    => __('Choose whether tabs are horizontal or vertical', 'fw'),
				'value'   => 'horizontal',
				'choices' => [
					'horizontal' => __('Horizontal', 'fw'),
					'vertical'   => __('Vertical', 'fw'),
				],
			],
			'fade' => [
				'type'  => 'switch',
				'label' => __('Fade Animation', 'fw'),
				'desc'  => __('Enable fade transition between tab content', 'fw'),
				'value' => 'no',
				'left-choice' => [
					'value' => 'no',
					'label' => __('No', 'fw'),
				],
				'right-choice' => [
					'value' => 'yes',
					'label' => __('Yes', 'fw'),
				],
			],
        ],
    ],
	'tab_advanced' => sc_get_advanced_tab(),
];
