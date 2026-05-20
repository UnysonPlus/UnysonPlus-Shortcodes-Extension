<?php if (!defined('FW')) die('Forbidden');

$cfg = array(
	'page_builder' => array(
		'tab'         => __('Layout Elements', 'fw'),
		'title'       => __('Section', 'fw'),
		'description' => __('Add a Section', 'fw'),
		'type'        => 'section', // WARNING: Do not edit this
		'popup_size'  => 'medium', // can be large, medium or small
		'title_template' => '{{= o.css_id || "Section" }}',
	)
);