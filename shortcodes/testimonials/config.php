<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Testimonials', 'fw' ),
	'description' => __( 'Add some Testimonials', 'fw' ),
	'tab'         => __( 'Content Elements', 'fw' ),
	'title_template' => '
		{{ if ( o["testimonials"] && o["testimonials"].length > 0 ) { }}
			{{ for ( var i = 0; i < o["testimonials"].length; i++ ) { }}
			 	{{ if ( o["testimonials"][i]["title"] ) { }}
					<h3><strong>{{= o["testimonials"][i]["title"] }}</strong></h3>
				{{ } }}
				{{ if ( o["testimonials"][i]["content"] ) { }}
					<p>"{{= o["testimonials"][i]["content"] }}"</p>
					{{ if ( o["testimonials"][i]["author_name"] ) { }}
						<p>- {{= o["testimonials"][i]["author_name"] }}</p>
						<br />
					{{ } }}
				{{ } }}
			{{ } }}
		{{ } else { }}
			<em>No testimonials added</em>
		{{ } }}
	',
);