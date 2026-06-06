<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Notification', 'fw' ),
	'description' => __( 'Add a Notification Box', 'fw' ),
	'tab'         => __( 'Content Elements', 'fw' ),
	'title_template' => '<span style="text-transform:uppercase;">{{- o.label_text || o.type }}</span>: {{- o.message }}',
);