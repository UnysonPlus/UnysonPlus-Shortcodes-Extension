<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Animated Heading', 'fw' ),
	'description' => __( 'A self-contained rotating headline (no Animation Engine needed): part of the text cycles through several words with a typewriter, fade, slide, flip, zoom, clip, blur or 3D-rotate animation.', 'fw' ),
	'tab'         => __( 'Content Elements', 'fw' ),
	'popup_size'  => 'medium',

	'title_template' => '<strong>{{= ( o ? ( ( o["before_text"]||"" ) + " [" + ( ( o["words"]||"" ).split( "\\n" )[0] || "words" ) + "] " + ( o["after_text"]||"" ) ) : "Animated Heading" ) }}</strong>',
);
