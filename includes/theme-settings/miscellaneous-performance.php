<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Miscellaneous → Performance Tweaks.
 *
 * WordPress-core tweaks (theme-agnostic). Stored theme-scoped under the
 * `misc_performance` multi container — the SAME key the theme used, so existing
 * values carry over with no migration. Applied by the handler in
 * miscellaneous-handlers.php (on `init` / `wp_default_scripts`).
 *
 * @var array $options Filled with the option schema (loaded via upw_ts_get_options()).
 */

$options = array(
	'misc_performance' => array(
		'type'          => 'multi',
		'label'         => false,
		'inner-options' => array(
			'perf_disable_emojis' => array(
				'label' => __( 'Disable WordPress emojis', 'fw' ),
				'desc'  => __( 'Removes emoji detection script + styles from every page.', 'fw' ),
				'type'  => 'switch',
				'value' => 'no',
			),
			'perf_disable_embeds' => array(
				'label' => __( 'Disable oEmbed discovery', 'fw' ),
				'desc'  => __( 'Removes WP oEmbed JSON/XML discovery links and the legacy wp-embed.js loader.', 'fw' ),
				'type'  => 'switch',
				'value' => 'no',
			),
			'perf_remove_rsd_wlw' => array(
				'label' => __( 'Remove RSD / WLW link tags', 'fw' ),
				'desc'  => __( 'Legacy Windows Live Writer + Really Simple Discovery autodiscovery tags. Safe to remove on most sites.', 'fw' ),
				'type'  => 'switch',
				'value' => 'no',
			),
			'perf_disable_jquery_migrate' => array(
				'label' => __( 'Deregister jquery-migrate', 'fw' ),
				'desc'  => __( 'Drops the legacy compatibility shim. Modern themes / plugins do not need it.', 'fw' ),
				'type'  => 'switch',
				'value' => 'no',
			),
			'perf_remove_version_meta' => array(
				'label' => __( 'Remove WordPress version meta tag', 'fw' ),
				'desc'  => __( 'Hides the &lt;meta name="generator"&gt; tag from front-end source.', 'fw' ),
				'type'  => 'switch',
				'value' => 'no',
			),
			'perf_disable_xmlrpc' => array(
				'label' => __( 'Disable XML-RPC', 'fw' ),
				'desc'  => __( 'Turns off the /xmlrpc.php endpoint. Disable only if no apps depend on it (Jetpack, mobile apps).', 'fw' ),
				'type'  => 'switch',
				'value' => 'no',
			),
		),
	),
);
