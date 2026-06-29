<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Miscellaneous → Analytics & Tracking.
 *
 * Stored theme-scoped under the `misc_analytics` multi container — the SAME key the
 * theme used, so existing values carry over with no migration. Emitted by the handlers
 * in miscellaneous-handlers.php.
 *
 * @var array $options Filled with the option schema (loaded via upw_ts_get_options()).
 */

$options = array(
	'misc_analytics' => array(
		'type'          => 'multi',
		'label'         => false,
		'inner-options' => array(
			'analytics_ga4_id' => array(
				'label' => __( 'Google Analytics 4 Measurement ID', 'fw' ),
				'desc'  => __( 'Format: G-XXXXXXXXXX. Leave empty to disable.', 'fw' ),
				'type'  => 'text',
				'value' => '',
			),
			'analytics_gtm_id' => array(
				'label' => __( 'Google Tag Manager Container ID', 'fw' ),
				'desc'  => __( 'Format: GTM-XXXXXXX. Emits both the &lt;head&gt; script and &lt;noscript&gt; iframe.', 'fw' ),
				'type'  => 'text',
				'value' => '',
			),
			'analytics_meta_pixel_id' => array(
				'label' => __( 'Meta (Facebook) Pixel ID', 'fw' ),
				'desc'  => __( 'Numeric ID, e.g. 1234567890123456.', 'fw' ),
				'type'  => 'text',
				'value' => '',
			),
			'analytics_clarity_id' => array(
				'label' => __( 'Microsoft Clarity Project ID', 'fw' ),
				'desc'  => __( '10-character alphanumeric ID from clarity.microsoft.com.', 'fw' ),
				'type'  => 'text',
				'value' => '',
			),
		),
	),
);
