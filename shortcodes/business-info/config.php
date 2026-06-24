<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Business Info', 'fw' ),
	'description' => __( 'Opening hours (with a live Open/Closed status) plus contact details — address, phone, email and links — in several layouts.', 'fw' ),
	'tab'         => __( 'Components', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '<strong>{{= ( o && o["biz_name"] ) ? o["biz_name"] : "Business Info" }}</strong>',
);
