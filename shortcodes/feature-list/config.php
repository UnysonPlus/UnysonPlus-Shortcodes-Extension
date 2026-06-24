<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Feature List', 'fw' ),
	'description' => __( 'An icon-led list (checklist, cross/mixed, numbered or per-item icons) with optional sub-text and links — one or more columns.', 'fw' ),
	'tab'         => __( 'Components', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '
		{{ if ( o && o["items"] && o["items"].length ) { }}
			<ul style="margin:.4rem 0 0;padding-left:1.1rem;">
				{{ for ( var i = 0; i < Math.min(o["items"].length,5); i++ ) { }}
					<li>{{- o["items"][i].text || "" }}</li>
				{{ } }}
			</ul>
		{{ } else { }}
			<em>No items added</em>
		{{ } }}
	',
);
