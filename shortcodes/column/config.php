<?php if (!defined('FW')) die('Forbidden');

/**
 * The Column's page-builder items (the 1/1 … 12/12 width tiles) are registered
 * via Page_Builder_Column_Item / get_thumbnails_data(), not from this config.
 * This file only needs to set the edit-modal size, which
 * FW_Shortcode_Column::get_item_data() reads from get_config('page_builder').
 */
$cfg = array(
	'page_builder' => array(
		// Not 'simple' — the column is registered via Page_Builder_Column_Item, not
		// the generic "simple element" path. This (like section's 'type' => 'section')
		// makes get_shortcode_builder_data() bail early, so no duplicate element is
		// registered and no "No Page Builder tab specified" warning is raised.
		'type'       => 'column',
		'popup_size' => 'medium', // can be large, medium or small
	),
);
