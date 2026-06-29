<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Miscellaneous → Maintenance Mode.
 *
 * Serves a 503 splash to visitors (admin + allow-listed roles pass through) via the
 * handler in miscellaneous-handlers.php (template_redirect). Stored theme-scoped under
 * the `misc_maintenance` multi container — the SAME key the theme used, so existing
 * values carry over with no migration.
 *
 * @var array $options Filled with the option schema (loaded via upw_ts_get_options()).
 */

// Allow-list role choices, built from the registered roles (mirrors the theme).
$role_choices = array();
if ( function_exists( 'wp_roles' ) ) {
	foreach ( wp_roles()->get_names() as $role_key => $role_name ) {
		$role_choices[ $role_key ] = translate_user_role( $role_name );
	}
}

$options = array(
	'misc_maintenance' => array(
		'type'          => 'multi',
		'label'         => false,
		'inner-options' => array(
			'maintenance_enabled' => array(
				'label' => __( 'Enable maintenance mode', 'fw' ),
				'desc'  => __( 'Serves a 503 splash page to visitors. Admin pages and allowlisted roles always pass through.', 'fw' ),
				'type'  => 'switch',
				'value' => 'no',
			),
			'maintenance_title' => array(
				'label' => __( 'Title', 'fw' ),
				'type'  => 'text',
				'value' => __( "We'll be right back", 'fw' ),
			),
			'maintenance_message' => array(
				'label'  => __( 'Message', 'fw' ),
				'type'   => 'wp-editor',
				'value'  => __( 'Our site is undergoing scheduled maintenance. Please check back shortly.', 'fw' ),
				'reinit' => true,
			),
			'maintenance_logo' => array(
				'label' => __( 'Logo', 'fw' ),
				'desc'  => __( 'Optional image displayed above the title.', 'fw' ),
				'type'  => 'upload',
				'value' => '',
			),
			'maintenance_allowed_roles' => array(
				'label'      => __( 'Roles that bypass the splash', 'fw' ),
				'desc'       => __( 'Logged-in users with any of these roles see the live site as normal.', 'fw' ),
				'type'       => 'multi-select',
				'value'      => array( 'administrator' ),
				'population' => 'array',
				'choices'    => $role_choices,
			),
		),
	),
);
