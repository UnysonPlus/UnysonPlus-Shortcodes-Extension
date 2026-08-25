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
			<div style="margin-top:.4rem;color:#555;"><strong>{{= o["steps"].length }}</strong> step(s) <em style="opacity:.6;">({{- o["design"] || "horizontal" }})</em></div>
			{{ for ( var i = 0; i < o["steps"].length; i++ ) { var st = o["steps"][i]; if ( st ) { }}
				{{ if ( st["title"] ) { }}
					<h4 style="margin:.5rem 0 .15rem;"><strong>{{ if ( st["number"] ) { }}{{- st["number"] }}. {{ } }}{{= st["title"] }}</strong></h4>
				{{ } }}
				{{ if ( st["content"] ) { }}
					<div style="color:#666;">{{= st["content"] }}</div>
				{{ } }}
			{{ } } }}
		{{ } else { }}
			<em>Steps / Process — add a step</em>
		{{ } }}
	',
);
