<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Navigation Menu', 'fw' ),
	'description'    => __( 'Display a WordPress menu (for headers, footers, or anywhere)', 'fw' ),
	'tab'            => __( 'Header/Footer Elements', 'fw' ),
	'popup_size'     => 'medium',
	'title_template' => '<span class="fw-nav-menu">' . __( 'Navigation Menu', 'fw' ) . '</span>',
);
