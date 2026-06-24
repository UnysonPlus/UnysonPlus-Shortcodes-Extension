<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Scroll to Top & Progress', 'fw' ),
	'description' => __( 'A back-to-top button and/or a reading-progress bar tied to page scroll. Place once per page.', 'fw' ),
	'tab'         => __( 'Interactive Elements', 'fw' ),
	'popup_size'  => 'medium',

	'title_template' => '
		<div style="margin-top:.4rem;color:#555;">
			{{ if ( !o || o["show_button"] !== "no" ) { }}<span>&#9650; Back-to-top</span>{{ } }}
			{{ if ( o && o["show_progress"] === "yes" ) { }} <span style="opacity:.7;">+ progress bar</span>{{ } }}
		</div>
	',
);
