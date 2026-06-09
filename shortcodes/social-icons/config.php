<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Social Icons', 'fw' ),
	'description'    => __( 'A row of social profile links', 'fw' ),
	'tab'            => __( 'Header/Footer Elements', 'fw' ),
	'popup_size'     => 'medium',
	'title_template' => '<span class="fw-social-icons">' . __( 'Social Icons', 'fw' ) . '</span>',
);
