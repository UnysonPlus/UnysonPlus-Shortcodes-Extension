<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Newsletter', 'fw' ),
	'description' => __( 'An email signup form (AJAX) — wired to your site mail (Mailer extension) and to a hook for Mailchimp / list integrations.', 'fw' ),
	'tab'         => __( 'Interactive Elements', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '<strong>{{= ( o && o["title"] ) ? o["title"] : "Newsletter" }}</strong>',
);
