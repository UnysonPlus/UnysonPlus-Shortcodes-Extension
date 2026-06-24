<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Comparison Table', 'fw' ),
	'description' => __( 'A feature comparison matrix — plans/products across the top, feature rows down the side, with checks, crosses, text values, a highlighted column and section headings.', 'fw' ),
	'tab'         => __( 'Components', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '
		{{ if ( o && o["columns"] && o["columns"].length ) { }}
			<div style="margin-top:.4rem;color:#555;"><strong>{{= o["columns"].length }}</strong> column(s) &times; <strong>{{= ( o["rows"] && o["rows"].length ) || 0 }}</strong> row(s)</div>
		{{ } else { }}
			<em>Comparison Table — add columns &amp; rows</em>
		{{ } }}
	',
);
