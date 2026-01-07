<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Tabs', 'fw' ),
	'description' => __( 'Add some Tabs', 'fw' ),
	'tab'         => __( 'Content Elements', 'fw' ),
	'title_template' => '
		{{ if ( o["tabs"] && o["tabs"].length > 0 ) { }}
			{{ for ( var i = 0; i < o["tabs"].length; i++ ) { }}
				<h3><strong>{{= o["tabs"][i]["tab_title"] }}</strong></h3>
				{{ if ( o["tabs"][i]["tab_content"] ) { }}
					<p>{{= o["tabs"][i]["tab_content"] }}</p>
				{{ } }}
			{{ } }}
		{{ } else { }}
			<em>No tabs added</em>
		{{ } }}
	',
);