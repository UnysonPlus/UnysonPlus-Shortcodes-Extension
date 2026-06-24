<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Steps / Process', 'fw' ),
	'description' => __( 'A numbered steps / process flow — horizontal, vertical timeline, alternating, cards or circles — with icons or numbers and connectors.', 'fw' ),
	'tab'         => __( 'Components', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '
		{{ if ( o && o["steps"] && o["steps"].length ) { }}
			<div style="margin-top:.4rem;color:#555;"><strong>{{= o["steps"].length }}</strong> step(s){{ if ( o["steps"][0] && o["steps"][0].title ) { }} — <em>{{- o["steps"][0].title }}</em>{{ } }} <em style="opacity:.6;">({{- o["design"] || "horizontal" }})</em></div>
		{{ } else { }}
			<em>Steps / Process — add a step</em>
		{{ } }}
	',
);
