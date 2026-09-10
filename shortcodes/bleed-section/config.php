<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

$cfg = array(
	'page_builder' => array(
		'tab'            => __( 'Layout Elements', 'fw' ),
		'title'          => __( 'Bleed Section', 'fw' ),
		'description'    => __( 'A split section: content on one side, a full-bleed image on the other', 'fw' ),
		'type'           => 'bleed_section',
		'popup_size'     => 'large',
		'title_template' => '{{= o.css_id || "Bleed Section" }}',
	),
);
