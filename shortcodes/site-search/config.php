<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Search', 'fw' ),
	'description'    => __( 'A site search form (inline, or an expanding icon)', 'fw' ),
	'tab'            => __( 'Header/Footer Elements', 'fw' ),
	'popup_size'     => 'small',
	'title_template' => '<span class="fw-site-search">' . __( 'Search', 'fw' ) . '</span>',
);
