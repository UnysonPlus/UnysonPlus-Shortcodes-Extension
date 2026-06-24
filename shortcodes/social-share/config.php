<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Social Share', 'fw' ),
	'description' => __( 'Share-to buttons (Facebook, X, LinkedIn, Pinterest, WhatsApp, Telegram, Reddit, Email, Copy link) in several styles and shapes.', 'fw' ),
	'tab'         => __( 'Components', 'fw' ),
	'popup_size'  => 'medium',

	'title_template' => '<strong>{{= ( o && o["networks"] ) ? "Share: " + ( ( o["networks"] || [] ).join(", ") ) : "Social Share" }}</strong>',
);
