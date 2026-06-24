<?php if (!defined('FW')) die('Forbidden');

$cfg = array(
	'page_builder' => array(
		'tab'         => __('Layout Elements', 'fw'),
		'title'       => __('Container', 'fw'),
		'description' => __('Add a second container to a section (Boxed or Full-width). Holds columns.', 'fw'),
		'type'        => 'container', // WARNING: Do not edit this
		'popup_size'  => 'small', // can be large, medium or small
		'title_template' => '{{= o.is_fullwidth ? "Container (Full-width)" : "Container" }}',
	)
);
