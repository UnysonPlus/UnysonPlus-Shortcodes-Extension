<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array(
	'page_builder' => array(
		'tab'            => __( 'Layout Elements', 'fw' ),
		'title'          => __( 'Hero Section', 'fw' ),
		'description'    => __( 'Section with a parallax background image', 'fw' ),
		'type'           => 'hero_section', // WARNING: must match get_type() returned by the item class
		'popup_size'     => 'medium',
		'title_template' => '{{= o.css_id || "Hero Section" }}',
	),
);
