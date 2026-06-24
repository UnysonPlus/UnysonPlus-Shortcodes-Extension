<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Author Box', 'fw' ),
	'description' => __( 'An author / profile box — avatar, name, bio, social links and post count — for the current post author, a chosen user, or fully custom content.', 'fw' ),
	'tab'         => __( 'Components', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '<strong>{{= ( o && o["source"] === "custom" && o["name"] ) ? ( "Author: " + o["name"] ) : "Author Box" }}</strong>',
);
