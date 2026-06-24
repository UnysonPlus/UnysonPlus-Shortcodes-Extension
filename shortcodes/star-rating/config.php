<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Star Rating', 'fw' ),
	'description' => __( 'A rating display (stars, hearts, circles or bar) with optional label and value/count — supports half ratings.', 'fw' ),
	'tab'         => __( 'Components', 'fw' ),
	'popup_size'  => 'medium',

	'title_template' => '<strong>{{= ( o ? ( ( o["rating"] != null ? o["rating"] : 5 ) + " / " + ( o["max"] || 5 ) + " ★" ) : "Star Rating" ) }}</strong>',
);
