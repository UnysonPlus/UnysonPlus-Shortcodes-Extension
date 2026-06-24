<?php if (!defined('FW')) die('Forbidden');

/**
 * Flexbox — a self-contained, nestable flex container that renders a chosen
 * semantic HTML tag. Its children sit side-by-side (or stacked) via CSS flexbox
 * on the container itself, so it never depends on the Section/Row/Column grouping
 * and never touches those elements. A new page-builder item type ('flexbox').
 *
 * Lives in the "Structure" palette tab and is exposed as one tile per HTML tag
 * (div / main / article / header / footer / aside / nav) — see get_thumbnails_data().
 */
$cfg = array(
	'page_builder' => array(
		'tab'            => __('Structure', 'fw'),
		'title'          => __('Flexbox', 'fw'),
		'description'    => __('A nestable flexbox container with a semantic HTML tag. Lay children out in a row or column; give each child a width to build side-by-side layouts.', 'fw'),
		'type'           => 'flexbox', // WARNING: must match Page_Builder_Flexbox_Item::get_type()
		'popup_size'     => 'medium',
		'title_template' => '{{= ( o.html_tag && o.html_tag !== "div" ? o.html_tag : "Flexbox" ) }}',
	)
);
