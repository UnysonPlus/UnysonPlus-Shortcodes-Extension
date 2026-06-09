<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Site Logo', 'fw' ),
	'description'    => __( 'Site logo or title (Site Identity, or a custom image)', 'fw' ),
	'tab'            => __( 'Header/Footer Elements', 'fw' ),
	'popup_size'     => 'small',
	'title_template' => '<span class="fw-site-logo">' . __( 'Site Logo', 'fw' ) . '</span>',
);
