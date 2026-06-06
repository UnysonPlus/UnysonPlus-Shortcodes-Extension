<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array(
	'page_builder' => array(
		'tab'            => __( 'Layout Elements', 'fw' ),
		'title'          => __( 'Masonry Section', 'fw' ),
		'description'    => __( 'A section that packs its columns into a masonry grid', 'fw' ),
		'type'           => 'masonry_section', // WARNING: must match get_type() returned by the item class
		'popup_size'     => 'medium',
		'title_template' => '{{= o.css_id || "Masonry Section" }}',
	),
);
