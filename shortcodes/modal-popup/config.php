<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Modal / Popup', 'fw' ),
	'description' => __( 'A trigger (button, text, icon or image) that opens custom content in a modal — centered card, side drawer or fullscreen.', 'fw' ),
	'tab'         => __( 'Interactive Elements', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '<strong>{{= ( o && o["trigger_label"] ) ? "Modal: " + o["trigger_label"] : "Modal / Popup" }}</strong>',
);
