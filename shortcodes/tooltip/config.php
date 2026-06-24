<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Tooltip', 'fw' ),
	'description' => __( 'A trigger (text, icon or button) that reveals a tooltip on hover, focus or click — four positions and themes.', 'fw' ),
	'tab'         => __( 'Interactive Elements', 'fw' ),
	'popup_size'  => 'medium',

	'title_template' => '<strong>{{= ( o && o["trigger_text"] ) ? o["trigger_text"] : "Tooltip" }}</strong>',
);
