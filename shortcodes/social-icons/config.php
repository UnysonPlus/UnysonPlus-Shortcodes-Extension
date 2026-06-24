<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Social Icons', 'fw' ),
	'description'    => __( 'A row of social profile links', 'fw' ),
	// General-purpose element (footers, contact/about pages, …) — lives with the
	// other reusable Components, NOT gated behind the Theme Builder, unlike the
	// header chrome (menu toggle / nav / search / logo).
	'tab'            => __( 'Components', 'fw' ),
	'popup_size'     => 'medium',
	'title_template' => '<span class="fw-social-icons">' . __( 'Social Icons', 'fw' ) . '</span>',
);
