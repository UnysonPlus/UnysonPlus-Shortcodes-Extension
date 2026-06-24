<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Audio Player', 'fw' ),
	'description' => __( 'A custom audio player for self-hosted or remote tracks — single track or playlist, with cover art, in several designs.', 'fw' ),
	'tab'         => __( 'Media Elements', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '
		{{ if ( o && o["tracks"] && o["tracks"].length ) { }}
			<div style="margin-top:.4rem;color:#555;"><span style="font-size:16px;">&#9835;</span> {{= o["tracks"].length }} track(s){{ if ( o["tracks"][0] && o["tracks"][0].title ) { }} — <em>{{- o["tracks"][0].title }}</em>{{ } }}</div>
		{{ } else { }}
			<em>Audio Player — add a track</em>
		{{ } }}
	',
);
