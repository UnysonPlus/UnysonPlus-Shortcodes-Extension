<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Timeline', 'fw' ),
	'description' => __( 'A sequence of milestones with date, title, text, icon and image — vertical (alternating / left / right) or horizontal.', 'fw' ),
	'tab'         => __( 'Interactive Elements', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '
		{{ if ( o && o["items"] && o["items"].length ) { }}
			<ol style="margin:.4rem 0 0;padding-left:1.1rem;">
				{{ for ( var i = 0; i < Math.min(o["items"].length,5); i++ ) {
					var it = o["items"][i];
				}}
					<li style="margin-bottom:2px;"><strong>{{- it.date || "" }}</strong> {{- it.title || "" }}</li>
				{{ } }}
				{{ if ( o["items"].length > 5 ) { }}<li style="list-style:none;color:#999;">+{{= o["items"].length - 5 }} more</li>{{ } }}
			</ol>
		{{ } else { }}
			<em>No milestones added</em>
		{{ } }}
	',
);
