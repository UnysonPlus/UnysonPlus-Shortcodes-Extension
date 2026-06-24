<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Blockquote', 'fw' ),
	'description' => __( 'A styled quote / pullquote with optional author and source — several editorial designs.', 'fw' ),
	'tab'         => __( 'Content Elements', 'fw' ),
	'popup_size'  => 'medium',

	'title_template' => '<em>{{= ( o && o["quote"] ) ? \'"\' + ( o["quote"].length > 60 ? o["quote"].slice(0,60) + "…" : o["quote"] ) + \'"\' : "Blockquote" }}</em>',
);
