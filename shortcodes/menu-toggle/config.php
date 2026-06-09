<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Menu Toggle', 'fw' ),
	'description'    => __( 'A hamburger button that opens an off-canvas drawer', 'fw' ),
	'tab'            => __( 'Header/Footer Elements', 'fw' ),
	'popup_size'     => 'small',
	'title_template' => '<span class="fw-menu-toggle">' . __( 'Menu Toggle', 'fw' ) . '</span>',
);
