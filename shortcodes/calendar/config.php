<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Calendar', 'fw' ),
	'description' => __( 'A clean, dependency-free events calendar — a server-rendered month grid with vanilla-JS month navigation, an optional upcoming-events list, and several designs.', 'fw' ),
	'tab'         => __( 'Content Elements', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '
		{{ if ( o && o["events"] && o["events"].length ) { }}
			<div style="margin-top:.4rem;color:#555;"><span style="font-size:15px;">&#128197;</span> {{= o["events"].length }} event(s) <em style="opacity:.6;">({{- o["design"] || "classic" }})</em></div>
		{{ } else { }}
			<em>Calendar — add events</em>
		{{ } }}
	',
);
