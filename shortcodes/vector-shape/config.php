<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Vector Shape', 'fw' ),
	'description' => __( 'Draw a shape (pen, rectangles, ellipses, polygons, stars, combine and cut) and keep it as clean inline SVG', 'fw' ),
	'tab'         => __( 'Media Elements', 'fw' ),
	'popup_size'  => 'medium',
);
