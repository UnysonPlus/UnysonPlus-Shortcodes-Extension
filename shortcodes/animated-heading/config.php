<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Animated Heading', 'fw' ),
	'description' => __( 'A heading where part of the text rotates through several words with a typewriter, fade, slide, flip, zoom or clip animation.', 'fw' ),
	'tab'         => __( 'Content Elements', 'fw' ),
	'popup_size'  => 'medium',

	'title_template' => '<strong>{{= ( o ? ( ( o["before_text"]||"" ) + " [" + ( ( o["words"]||"" ).split( "\\n" )[0] || "words" ) + "] " + ( o["after_text"]||"" ) ) : "Animated Heading" ) }}</strong>',
);
