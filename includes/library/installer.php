<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Shortcodes Library — catalog fetch + per-shortcode install/uninstall + AJAX.
 *
 * Mirrors the Template Library installer, but a shortcode is CODE: the per-item payload is
 * a zip (slug folder at root) verified against the catalog's sha256 and extracted into the
 * active theme's framework-customizations shortcodes tree, where the loader auto-registers it.
 */

/* -------------------------------------------------------------------------- *
 * Config
 * -------------------------------------------------------------------------- */

if ( ! defined( 'UPW_SC_LIB_CATALOG_TTL' ) )  { define( 'UPW_SC_LIB_CATALOG_TTL', 12 * HOUR_IN_SECONDS ); }
if ( ! defined( 'UPW_SC_LIB_CATALOG_FAIL_TTL' ) ) { define( 'UPW_SC_LIB_CATALOG_FAIL_TTL', 5 * MINUTE_IN_SECONDS ); }

/** Remote catalog URL (filterable so a dev can point at a local copy for testing). */
function upw_sc_lib_catalog_url() {
	return apply_filters(
		'upw_sc_lib_catalog_url',
		'https://raw.githubusercontent.com/UnysonPlus/UnysonPlus-Library/master/shortcodes/catalog.json'
	);
}

/** Install target: the ACTIVE theme's shortcodes customization tree (loader auto-registers here). */
function upw_sc_lib_install_dir() {
	$dir = get_stylesheet_directory() . '/framework-customizations/extensions/shortcodes/shortcodes';
	return apply_filters( 'upw_sc_lib_install_dir', $dir );
}

/* -------------------------------------------------------------------------- *
 * Catalog
 * -------------------------------------------------------------------------- */

/** Bundled fallback catalog shipped beside this installer, so the gallery works offline. */
function upw_sc_lib_bundled_catalog() {
	$file = dirname( __FILE__ ) . '/catalog.json';
	if ( ! is_readable( $file ) ) { return array( 'shortcodes' => array() ); }
	$data = json_decode( (string) file_get_contents( $file ), true );
	return is_array( $data ) ? upw_sc_lib_normalize_catalog( $data ) : array( 'shortcodes' => array() );
}

/** Normalize a raw catalog into { version, base_url, shortcodes:{ slug => {...} } }. */
function upw_sc_lib_normalize_catalog( $raw ) {
	$out = array(
		'version'    => isset( $raw['version'] ) ? (int) $raw['version'] : 1,
		'base_url'   => isset( $raw['base_url'] ) ? esc_url_raw( (string) $raw['base_url'] ) : '',
		'shortcodes' => array(),
	);
	$items = ( isset( $raw['shortcodes'] ) && is_array( $raw['shortcodes'] ) ) ? $raw['shortcodes'] : array();
	foreach ( $items as $key => $it ) {
		if ( ! is_array( $it ) ) { continue; }
		$slug = sanitize_key( isset( $it['slug'] ) ? $it['slug'] : $key );
		if ( '' === $slug ) { continue; }
		$out['shortcodes'][ $slug ] = array(
			'slug'        => $slug,
			'title'       => isset( $it['title'] ) ? (string) $it['title'] : $slug,
			'description' => isset( $it['description'] ) ? (string) $it['description'] : '',
			'category'    => isset( $it['category'] ) && '' !== $it['category'] ? (string) $it['category'] : __( 'Uncategorized', 'fw' ),
			'version'     => isset( $it['version'] ) ? (string) $it['version'] : '1.0.0',
			'min_core'    => isset( $it['min_core'] ) ? (string) $it['min_core'] : '',
			'requires'    => ( isset( $it['requires'] ) && is_array( $it['requires'] ) ) ? array_map( 'strval', $it['requires'] ) : array(),
			'author'      => isset( $it['author'] ) ? (string) $it['author'] : '',
			'thumb'       => isset( $it['thumb'] ) ? (string) $it['thumb'] : '',
			'payload'     => isset( $it['payload'] ) ? (string) $it['payload'] : '',
			'sha256'      => isset( $it['sha256'] ) ? strtolower( preg_replace( '/[^a-f0-9]/i', '', (string) $it['sha256'] ) ) : '',
		);
	}
	return $out;
}

/** Resolve a catalog-relative path (thumb / payload) against the catalog base_url. */
function upw_sc_lib_resolve_url( $catalog, $rel ) {
	$rel = (string) $rel;
	if ( '' === $rel ) { return ''; }
	if ( preg_match( '#^https?://#i', $rel ) ) { return $rel; }
	$base = isset( $catalog['base_url'] ) ? (string) $catalog['base_url'] : '';
	return $base ? trailingslashit( $base ) . ltrim( $rel, '/' ) : '';
}

/**
 * The gallery catalog: remote fetch (12h transient) with the bundled catalog as fallback.
 * Adds `_catalog_ok` = whether the remote (not just the fallback) was reachable.
 */
function upw_sc_lib_catalog( $force = false ) {
	$key = 'upw_sc_lib_catalog';
	if ( ! $force ) {
		$cached = get_transient( $key );
		if ( is_array( $cached ) ) { return $cached; }
	}

	$res  = wp_remote_get( upw_sc_lib_catalog_url(), array( 'timeout' => 15 ) );
	$ok   = ! is_wp_error( $res ) && 200 === (int) wp_remote_retrieve_response_code( $res );
	$data = $ok ? json_decode( (string) wp_remote_retrieve_body( $res ), true ) : null;

	if ( is_array( $data ) ) {
		$catalog = upw_sc_lib_normalize_catalog( $data );
		$catalog['_catalog_ok'] = true;
		set_transient( $key, $catalog, UPW_SC_LIB_CATALOG_TTL );
		return $catalog;
	}

	// Remote unreachable / malformed → bundled fallback, cached briefly so we retry soon.
	$catalog = upw_sc_lib_bundled_catalog();
	$catalog['_catalog_ok'] = false;
	set_transient( $key, $catalog, UPW_SC_LIB_CATALOG_FAIL_TTL );
	return $catalog;
}

/* -------------------------------------------------------------------------- *
 * Installed state
 * -------------------------------------------------------------------------- */

/** Slugs currently installed in the theme's shortcodes customization tree (folder + config.php). */
function upw_sc_lib_installed_slugs() {
	$dir = upw_sc_lib_install_dir();
	if ( ! is_dir( $dir ) ) { return array(); }
	$out = array();
	foreach ( (array) glob( $dir . '/*', GLOB_ONLYDIR ) as $d ) {
		if ( is_file( $d . '/config.php' ) ) { $out[] = sanitize_key( basename( $d ) ); }
	}
	return $out;
}

/** Merged gallery items, each tagged with state: installed | available. */
function upw_sc_lib_items() {
	$catalog   = upw_sc_lib_catalog();
	$installed = upw_sc_lib_installed_slugs();
	$items     = array();
	foreach ( $catalog['shortcodes'] as $slug => $it ) {
		$it['state']     = in_array( $slug, $installed, true ) ? 'installed' : 'available';
		$it['thumb_url'] = upw_sc_lib_resolve_url( $catalog, $it['thumb'] );
		unset( $it['payload'], $it['sha256'] ); // not needed client-side
		$items[] = $it;
	}
	usort( $items, function ( $a, $b ) { return strcasecmp( $a['title'], $b['title'] ); } );
	return $items;
}

/* -------------------------------------------------------------------------- *
 * Install / uninstall
 * -------------------------------------------------------------------------- */

/**
 * Download + install ONE shortcode by slug: fetch its zip, verify sha256, extract into the
 * theme's shortcodes customization tree (atomic). Returns true or WP_Error.
 */
function upw_sc_lib_install( $slug ) {
	$slug    = sanitize_key( $slug );
	$catalog = upw_sc_lib_catalog();
	if ( '' === $slug || ! isset( $catalog['shortcodes'][ $slug ] ) ) {
		return new WP_Error( 'unknown', __( 'Unknown shortcode.', 'fw' ) );
	}
	$entry = $catalog['shortcodes'][ $slug ];
	$url   = upw_sc_lib_resolve_url( $catalog, $entry['payload'] );
	if ( '' === $url ) { return new WP_Error( 'no_payload', __( 'No download available for this shortcode.', 'fw' ) ); }

	require_once ABSPATH . 'wp-admin/includes/file.php';

	$tmp = download_url( $url, 60 );
	if ( is_wp_error( $tmp ) ) { return $tmp; }

	// Integrity: the payload must match the catalog's sha256 (when present).
	if ( '' !== $entry['sha256'] ) {
		$got = strtolower( (string) hash_file( 'sha256', $tmp ) );
		if ( $got !== $entry['sha256'] ) {
			@unlink( $tmp );
			return new WP_Error( 'checksum', __( 'Download failed an integrity check (checksum mismatch).', 'fw' ) );
		}
	}

	// Extract into a staging dir; the zip carries `<slug>/` at its root.
	WP_Filesystem();
	$staging = trailingslashit( get_temp_dir() ) . 'upw-sclib-' . wp_generate_password( 8, false );
	$unzip   = unzip_file( $tmp, $staging );
	@unlink( $tmp );
	if ( is_wp_error( $unzip ) ) { $GLOBALS['wp_filesystem']->delete( $staging, true ); return $unzip; }

	$src = $staging . '/' . $slug;
	if ( ! is_dir( $src ) || ! is_file( $src . '/config.php' ) ) {
		$GLOBALS['wp_filesystem']->delete( $staging, true );
		return new WP_Error( 'bad_payload', __( 'The downloaded package is not a valid shortcode.', 'fw' ) );
	}

	$install_dir = upw_sc_lib_install_dir();
	if ( ! wp_mkdir_p( $install_dir ) ) {
		$GLOBALS['wp_filesystem']->delete( $staging, true );
		return new WP_Error( 'mkdir', __( 'Could not create the shortcodes folder in your theme.', 'fw' ) );
	}
	$dest = $install_dir . '/' . $slug;
	if ( is_dir( $dest ) ) { $GLOBALS['wp_filesystem']->delete( $dest, true ); } // replace on update

	$moved = $GLOBALS['wp_filesystem']->move( $src, $dest, true );
	if ( ! $moved ) {
		// move() can fail across volumes — fall back to a recursive copy.
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$copied = copy_dir( $src, $dest );
		if ( is_wp_error( $copied ) ) { $GLOBALS['wp_filesystem']->delete( $staging, true ); return $copied; }
	}
	$GLOBALS['wp_filesystem']->delete( $staging, true );
	return true;
}

/** Remove ONE installed shortcode's folder (guarded to the install dir + a catalog slug). */
function upw_sc_lib_uninstall( $slug ) {
	$slug = sanitize_key( $slug );
	if ( '' === $slug ) { return new WP_Error( 'unknown', __( 'Unknown shortcode.', 'fw' ) ); }
	$dir = upw_sc_lib_install_dir() . '/' . $slug;
	if ( ! is_dir( $dir ) ) { return true; } // already gone
	// Safety: never delete outside the install dir.
	$real_base = wp_normalize_path( realpath( upw_sc_lib_install_dir() ) );
	$real_dir  = wp_normalize_path( realpath( $dir ) );
	if ( '' === $real_base || 0 !== strpos( $real_dir, $real_base . '/' ) ) {
		return new WP_Error( 'path', __( 'Refused to remove a folder outside the shortcodes directory.', 'fw' ) );
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();
	$GLOBALS['wp_filesystem']->delete( $dir, true );
	return ! is_dir( $dir ) ? true : new WP_Error( 'delete', __( 'Could not remove the shortcode folder.', 'fw' ) );
}

/* -------------------------------------------------------------------------- *
 * AJAX + payload
 * -------------------------------------------------------------------------- */

/** Data localized to the gallery JS. */
function upw_sc_lib_installer_payload() {
	$catalog = upw_sc_lib_catalog();
	return array(
		'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
		'nonce'     => wp_create_nonce( 'upw_sc_lib_manage' ),
		'items'     => upw_sc_lib_items(),
		'installed' => upw_sc_lib_installed_slugs(),
		'catalogOk' => ! empty( $catalog['_catalog_ok'] ),
		'i18n'      => array(
			'install'    => __( 'Install', 'fw' ),
			'installing' => __( 'Installing…', 'fw' ),
			'installed'  => __( 'Installed', 'fw' ),
			'remove'     => __( 'Remove', 'fw' ),
			'removing'   => __( 'Removing…', 'fw' ),
			'failed'     => __( 'Something went wrong. Please try again.', 'fw' ),
			'offline'    => __( 'Showing the built-in list — the online catalog is unreachable right now.', 'fw' ),
			'reload'     => __( 'Installed! Reload the Page Builder to use it.', 'fw' ),
			'allCats'    => __( 'All categories', 'fw' ),
		),
	);
}

add_action( 'wp_ajax_upw_sc_lib_manage', 'upw_sc_lib_ajax_manage' );
function upw_sc_lib_ajax_manage() {
	if ( ! current_user_can( 'manage_options' ) ) { wp_send_json_error( array( 'message' => __( 'Not allowed.', 'fw' ) ), 403 ); }
	check_ajax_referer( 'upw_sc_lib_manage', 'nonce' );

	$action = isset( $_POST['sc_action'] ) ? sanitize_key( wp_unslash( $_POST['sc_action'] ) ) : '';
	$slug   = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';

	if ( 'install' === $action ) {
		$r = upw_sc_lib_install( $slug );
	} elseif ( 'uninstall' === $action ) {
		$r = upw_sc_lib_uninstall( $slug );
	} elseif ( 'refresh' === $action ) {
		upw_sc_lib_catalog( true );
		$r = true;
	} else {
		wp_send_json_error( array( 'message' => __( 'Unknown action.', 'fw' ) ), 400 );
	}

	if ( is_wp_error( $r ) ) { wp_send_json_error( array( 'message' => $r->get_error_message() ) ); }

	$catalog = upw_sc_lib_catalog();
	wp_send_json_success( array(
		'items'     => upw_sc_lib_items(),
		'installed' => upw_sc_lib_installed_slugs(),
		'catalogOk' => ! empty( $catalog['_catalog_ok'] ),
	) );
}
