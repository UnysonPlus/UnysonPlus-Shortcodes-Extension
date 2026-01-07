<?php if (!defined('FW')) { die('Forbidden'); }

$options = array(

	/*
    // --- Layout Tab ---
    'tab_layout' => array(
        'title'   => __('Layout', 'fw'),
        'type'    => 'tab',
        'options' => array(
            
            // Column Width
            'column_width' => array(
                'type'    => 'select',
                'label'   => __('Column Width', 'fw'),
                'desc'    => __('Set the width of the column at each breakpoint.', 'fw'),
                'value'   => '',
                'choices' => array(
                    ''  => __('Auto', 'fw'),
                    'col-1' => '1/12',
                    'col-2' => '2/12',
                    'col-3' => '3/12',
                    'col-4' => '4/12',
                    'col-5' => '5/12',
                    'col-6' => '6/12',
                    'col-7' => '7/12',
                    'col-8' => '8/12',
                    'col-9' => '9/12',
                    'col-10'=> '10/12',
                    'col-11'=> '11/12',
                    'col-12'=> '12/12',
                )
            ),

            // Offset
            'offset' => array(
                'type' => 'text',
                'label' => __('Offset (e.g., offset-md-2)', 'fw'),
                'desc'  => __('Add offset classes manually if needed.', 'fw'),
            ),

            // Order
            'order' => array(
                'type' => 'text',
                'label' => __('Order (e.g., order-lg-1)', 'fw'),
                'desc'  => __('Add order classes manually if needed.', 'fw'),
            ),

            // Content Alignment
            'alignment' => array(
                'type'    => 'select',
                'label'   => __('Content Alignment', 'fw'),
                'desc'    => __('Align content horizontally in the column.', 'fw'),
                'value'   => '',
                'choices' => array(
                    '' => __('None', 'fw'),
                    'text-start' => __('Start', 'fw'),
                    'text-center' => __('Center', 'fw'),
                    'text-end' => __('End', 'fw'),
                ),
            ),

            // Vertical Alignment
            'vertical_align' => array(
                'type'    => 'select',
                'label'   => __('Vertical Alignment', 'fw'),
                'desc'    => __('Align items vertically using flex utilities.', 'fw'),
                'value'   => '',
                'choices' => array(
                    '' => __('None', 'fw'),
                    'align-items-start' => __('Top', 'fw'),
                    'align-items-center' => __('Center', 'fw'),
                    'align-items-end' => __('Bottom', 'fw'),
                    'align-items-stretch' => __('Stretch', 'fw'),
                ),
            ),

            // Height
            'height' => array(
                'type'         => 'switch',
                'label'        => __('Height', 'fw'),
                'desc'         => __('Set the height of the column.', 'fw'),
                'value'        => '',
                'left-choice'  => array('value'=>'', 'label'=>__('Auto', 'fw')),
                'right-choice' => array('value'=>'h-100', 'label'=>__('Full', 'fw')),
            ),

        ),
    ),

    // --- Spacing Tab ---
    'tab_spacing' => array(
        'title'   => __('Spacing', 'fw'),
        'type'    => 'tab',
        'options' => array(
            'spacing' => sc_option_spacing(array('all'=>array('value'=>'py-2')))
        ),
    ),

    // --- Display / Visibility Tab ---
    'tab_display' => array(
        'title'   => __('Display', 'fw'),
        'type'    => 'tab',
        'options' => array(
            'display' => array(
                'type'    => 'multi-picker',
                'label'   => false,
                'desc'    => false,
                'value'   => array('selected'=>'d-none','d-none'=>''),
                'picker'  => array(
                    'selected' => array(
                        'type'    => 'select',
                        'label'   => __('Display', 'fw'),
                        'choices' => array(
                            '' => __('Default', 'fw'),
                            'd-none' => __('None', 'fw'),
                            'd-block' => __('Block', 'fw'),
                            'd-inline' => __('Inline', 'fw'),
                            'd-inline-block' => __('Inline Block', 'fw'),
                            'd-flex' => __('Flex', 'fw'),
                        ),
                    ),
                ),
                'choices' => array(
                    'd-none' => array(),
                    'd-block' => array(),
                    'd-flex' => array(),
                ),
            ),
            'visibility' => sc_option_visibility(),
        ),
    ),

    // --- Background Tab ---
    'tab_background' => array(
        'title'   => __('Background', 'fw'),
        'type'    => 'tab',
        'options' => array(
            'bg' => sc_option_color_select('Background', 'bg'),
        ),
    ),

    // --- Border Tab ---
    'tab_border' => array(
        'title'   => __('Border', 'fw'),
        'type'    => 'tab',
        'options' => array(
            'border' => array(
                'type' => 'multi',
                'label'=> false,
                'value'=> array(),
                'desc' => false,
                'inner-options' => array(
                    'side'   => sc_option_box_border('Border Sides'),
                    'color'  => sc_option_color_select('Border', 'border'),
                    'width'  => array(
                        'type'  => 'short-text',
                        'label' => __('Width (px)', 'fw'),
                        'value' => '',
                        'desc'  => __('Set border width in pixels.', 'fw'),
                    ),
                    'radius' => sc_option_box_border_radius('Border Radius'),
                ),
            ),
        ),
    ),

    // --- Text Tab ---
    'tab_text' => array(
        'title'   => __('Text', 'fw'),
        'type'    => 'tab',
        'options' => array(
            'text_color' => sc_option_color_select('Text Color', 'text'),
            'font_weight' => array(
                'type' => 'select',
                'label'=> __('Font Weight', 'fw'),
                'value'=> '',
                'choices'=> array(
                    '' => __('Default', 'fw'),
                    'fw-light'=>__('Light','fw'),
                    'fw-normal'=>__('Normal','fw'),
                    'fw-bold'=>__('Bold','fw'),
                ),
            ),
            'font_style' => array(
                'type' => 'select',
                'label'=> __('Font Style', 'fw'),
                'value'=> '',
                'choices'=> array(
                    '' => __('Default','fw'),
                    'fst-italic'=>__('Italic','fw'),
                    'fst-normal'=>__('Normal','fw'),
                ),
            ),
        ),
    ),

    // --- Effects Tab ---
    'tab_effects' => array(
        'title'   => __('Effects', 'fw'),
        'type'    => 'tab',
        'options' => array(
            'shadow' => array(
                'type'  => 'select',
                'label' => __('Box Shadow', 'fw'),
                'value' => '',
                'choices'=> array(
                    '' => __('None','fw'),
                    'shadow-sm'=>__('Small','fw'),
                    'shadow'=>__('Medium','fw'),
                    'shadow-lg'=>__('Large','fw'),
                ),
            ),
            'opacity' => array(
                'type'=>'select',
                'label'=>__('Opacity','fw'),
                'value'=>'',
                'choices'=>array(
                    ''=>'Default',
                    'opacity-25'=>'25%',
                    'opacity-50'=>'50%',
                    'opacity-75'=>'75%',
                    'opacity-100'=>'100%',
                ),
            ),
        ),
    ),

    // --- Position Tab ---
    'tab_position' => array(
        'title'   => __('Position', 'fw'),
        'type'    => 'tab',
        'options' => array(
            'position' => array(
                'type'=>'select',
                'label'=>__('Position','fw'),
                'value'=>'',
                'choices'=>array(
                    ''=>'Default',
                    'position-static'=>'Static',
                    'position-relative'=>'Relative',
                    'position-absolute'=>'Absolute',
                    'position-fixed'=>'Fixed',
                    'position-sticky'=>'Sticky',
                ),
            ),
        ),
    ),
	*/

    'tab_advanced' => sc_get_advanced_tab(),

);
