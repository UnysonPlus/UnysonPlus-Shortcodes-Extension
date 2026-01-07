<?php if (!defined('FW')) die('Forbidden');

$cfg = array();

$cfg['page_builder'] = array(
	'title'         => __('Special Heading', 'fw'),
	'description'   => __('Add a Special Heading', 'fw'),
	'tab'           => __('Content Elements', 'fw'),
	'popup_size'    => 'medium', // can be large, medium or small
	'title_template' => '<{{= o.heading }}>{{= o.title }}</{{= o.heading }}>
						<div>{{= o.subtitle }}</div>',
);