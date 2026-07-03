<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Announcement Pill', 'fw' ),
	'description' => __( 'A compact pill / badge — an optional sub-tag, a message, optional leading + trailing icons and an optional link. Great for "what\'s new" hero chips, status badges and eyebrow labels.', 'fw' ),
	'tab'         => __( 'Content Elements', 'fw' ),
	'popup_size'  => 'small',

	// Canvas preview: the "New" sub-tag (when set) followed by the message.
	'title_template' =>
		'<span style="display:inline-flex;align-items:center;gap:6px;">' .
		'{{ if ( o.tag_text ) { }}<strong style="text-transform:uppercase;font-size:10px;letter-spacing:.4px;background:#1a8f74;color:#fff;padding:1px 7px;border-radius:999px;">{{- o.tag_text }}</strong>{{ } }}' .
		'<span>{{- o.message || "Announcement Pill" }}</span></span>',
);
