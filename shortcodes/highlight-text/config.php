<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Highlight Text', 'fw' ),
	'description' => __( 'A short text styled with a typographic effect — marker highlight, gradient fill, underline, outline, glow or a drop-cap.', 'fw' ),
	'tab'         => __( 'Content Elements', 'fw' ),
	'popup_size'  => 'medium',

	'title_template' => '<strong>{{= ( o && o["text"] ) ? o["text"].replace(/<[^>]+>/g,"") : "Highlight Text" }}</strong>',
);
