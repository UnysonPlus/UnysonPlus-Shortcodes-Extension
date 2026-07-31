<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * Element Designs — local catalog (uploads) + import-from-JSON + remote browse.
 *
 * Forked from the Template-Library installer (framework/extensions/template-library/
 * includes/installer.php) and the icon-v3 pack installer's upload path. Designs are
 * per-shortcode option-value bundles, NOT builder trees, so they group by SHORTCODE
 * (not kind) and are applied via the element modal — never fed to the builder's
 * predefined-templates filters.
 *
 * A design ENVELOPE (v1):
 *   { "unysonplus_design":1, "shortcode":"image_box", "name":"Glass Card",
 *     "thumb":"data:image/webp;base64,…"(import-only), "atts":{ …option values… } }
 *
 * Installed layout (uploads, routed through fw_upw_uploads_dir; a DEDICATED
 * `designs/` root — NOT under `shortcodes/`, which the shortcode loader scans):
 *   unysonplus/designs/<shortcode>/<slug>/design.json  the envelope (thumb stripped)
 *   unysonplus/designs/<shortcode>/<slug>/thumb.<ext>  the decoded data-URI thumbnail
 *   unysonplus/designs/<shortcode>/<slug>/meta.json    { id, shortcode, name, thumb(url), source, installed }
 * PHP design PACKS live alongside as <shortcode>/<key>/ (manifest.json + view.php + …).
 *
 * The heavy data-URI thumbnail is decoded to a file on import so the builder only
 * ever localizes small thumb URLs, not base64.
 */

/* -----------------------------------------------------------------------------
 * Paths + enabled shortcodes
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'fw_design_lib_dir' ) ) :
	/**
	 * Absolute uploads path of a shortcode's designs folder: unysonplus/designs/<shortcode>.
	 *
	 * NOTE: designs live under the dedicated `designs/` root, NOT under `shortcodes/`.
	 * The shortcodes install root (uploads/unysonplus/shortcodes/) is scanned one level
	 * deep by the shortcode loader, so per-shortcode design containers nested there were
	 * mis-detected as bogus config-less "shortcodes" ([undefined] cards). A separate
	 * `designs/` root avoids that collision entirely.
	 */
	function fw_design_lib_dir( $shortcode ) {
		$shortcode = sanitize_key( $shortcode );
		$paths     = fw_upw_uploads_dir( 'designs' ); // uploads/unysonplus/designs
		return apply_filters( 'fw_design_lib_dir', trailingslashit( $paths['path'] ) . $shortcode, $shortcode );
	}
endif;

if ( ! function_exists( 'fw_design_lib_url' ) ) :
	/** Public URL of a shortcode's designs folder (for thumbnails). */
	function fw_design_lib_url( $shortcode ) {
		$shortcode = sanitize_key( $shortcode );
		$paths     = fw_upw_uploads_dir( 'designs' );
		return apply_filters( 'fw_design_lib_url', trailingslashit( $paths['url'] ) . $shortcode, $shortcode );
	}
endif;

if ( ! function_exists( 'fw_design_lib_enabled' ) ) :
	/** Shortcodes allowed to carry designs (mirrors the modal enabled-list). */
	function fw_design_lib_enabled() {
		if ( function_exists( 'sc_design_enabled_shortcodes' ) ) {
			return (array) sc_design_enabled_shortcodes();
		}
		return apply_filters( 'unysonplus_design_enabled_shortcodes', array( 'image_box' ) );
	}
endif;

/* -----------------------------------------------------------------------------
 * Internal helpers
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'fw_design_lib__slugify' ) ) :
	/** name → url-safe slug (lowercase, non-alnum → '-'). */
	function fw_design_lib__slugify( $name ) {
		$slug = sanitize_title( (string) $name );
		return $slug !== '' ? $slug : 'design';
	}
endif;

if ( ! function_exists( 'fw_design_lib__unique_slug' ) ) :
	/** Ensure the slug is free under <root>/<shortcode>/; append -2, -3… on clash. */
	function fw_design_lib__unique_slug( $shortcode, $slug ) {
		$dir  = fw_design_lib_dir( $shortcode );
		$base = $slug; $n = 2;
		while ( is_dir( trailingslashit( $dir ) . $slug ) ) { $slug = $base . '-' . $n; $n++; }
		return $slug;
	}
endif;

if ( ! function_exists( 'fw_design_lib__rrmdir' ) ) :
	/** Recursively delete a directory (temp + uninstall). */
	function fw_design_lib__rrmdir( $dir ) {
		if ( ! is_dir( $dir ) ) { return; }
		foreach ( (array) glob( trailingslashit( $dir ) . '*' ) as $item ) {
			is_dir( $item ) ? fw_design_lib__rrmdir( $item ) : @unlink( $item );
		}
		@rmdir( $dir );
	}
endif;

if ( ! function_exists( 'fw_design_lib__decode_thumb' ) ) :
	/**
	 * Decode a `data:image/<type>;base64,…` thumbnail into $dir. Raster types only
	 * (webp/png/jpg/gif) — no SVG (defence in depth). Returns the written filename
	 * (e.g. 'thumb.webp') or '' when absent/invalid.
	 */
	function fw_design_lib__decode_thumb( $data_uri, $dir ) {
		if ( ! is_string( $data_uri ) || $data_uri === '' ) { return ''; }
		if ( ! preg_match( '#^data:image/(webp|png|jpe?g|gif);base64,(.+)$#is', trim( $data_uri ), $m ) ) { return ''; }
		$ext  = strtolower( $m[1] ) === 'jpeg' ? 'jpg' : strtolower( $m[1] );
		$blob = base64_decode( $m[2], true );
		if ( $blob === false || strlen( $blob ) === 0 || strlen( $blob ) > 512 * 1024 ) { return ''; } // cap 512KB
		$file = 'thumb.' . $ext;
		return ( false !== file_put_contents( trailingslashit( $dir ) . $file, $blob ) ) ? $file : '';
	}
endif;

if ( ! function_exists( 'fw_design_lib_validate_envelope' ) ) :
	/**
	 * Validate a design envelope: correct marker, an enabled shortcode, a name, and
	 * an atts object. Values-only — no markup/JS is ever accepted here.
	 *
	 * @return true|WP_Error
	 */
	function fw_design_lib_validate_envelope( $env ) {
		if ( ! is_array( $env ) || ! isset( $env['unysonplus_design'] ) ) {
			return new WP_Error( 'bad_envelope', __( 'The file is not an Unyson+ design (.json).', 'fw' ) );
		}
		$shortcode = isset( $env['shortcode'] ) ? sanitize_key( $env['shortcode'] ) : '';
		if ( $shortcode === '' || ! in_array( $shortcode, fw_design_lib_enabled(), true ) ) {
			return new WP_Error( 'bad_shortcode', __( 'This design targets an element that does not accept designs.', 'fw' ) );
		}
		if ( ! isset( $env['atts'] ) || ! is_array( $env['atts'] ) ) {
			return new WP_Error( 'no_atts', __( 'The design has no option values.', 'fw' ) );
		}
		return true;
	}
endif;

if ( ! function_exists( 'fw_design_lib__fetch_json' ) ) :
	/** GET a URL and json_decode it. Returns array|WP_Error. */
	function fw_design_lib__fetch_json( $url ) {
		$res = wp_remote_get( $url, array( 'timeout' => 30 ) );
		if ( is_wp_error( $res ) ) { return $res; }
		if ( (int) wp_remote_retrieve_response_code( $res ) !== 200 ) {
			return new WP_Error( 'http_error', sprintf( __( 'Download failed (%s).', 'fw' ), wp_remote_retrieve_response_code( $res ) ) );
		}
		$json = json_decode( wp_remote_retrieve_body( $res ), true );
		if ( ! is_array( $json ) ) { return new WP_Error( 'bad_json', __( 'Downloaded file was not valid JSON.', 'fw' ) ); }
		return $json;
	}
endif;

/* -----------------------------------------------------------------------------
 * Write (import from JSON envelope) — the shared install core
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'fw_design_lib_store_envelope' ) ) :
	/**
	 * Persist a validated envelope into the catalog. Atomic (temp dir → rename).
	 * Decodes the data-URI thumb to a file and strips it from the stored design.json.
	 *
	 * @param array  $env    Design envelope (already validated).
	 * @param string $source 'upload' | 'library' (provenance in meta).
	 * @return array|WP_Error  { shortcode, slug } on success.
	 */
	function fw_design_lib_store_envelope( $env, $source = 'upload' ) {
		$check = fw_design_lib_validate_envelope( $env );
		if ( is_wp_error( $check ) ) { return $check; }

		$shortcode = sanitize_key( $env['shortcode'] );
		$name      = isset( $env['name'] ) && $env['name'] !== '' ? (string) $env['name'] : ucwords( str_replace( '_', ' ', $shortcode ) . ' design' );
		$slug      = fw_design_lib__unique_slug( $shortcode, fw_design_lib__slugify( $name ) );

		$root = fw_design_lib_dir( $shortcode );
		if ( ! wp_mkdir_p( $root ) ) {
			return new WP_Error( 'mkdir_failed', __( 'Could not create the designs folder.', 'fw' ) );
		}
		$dest = trailingslashit( $root ) . $slug;
		$tmp  = $dest . '.tmp-' . wp_generate_password( 6, false );
		if ( ! wp_mkdir_p( $tmp ) ) {
			return new WP_Error( 'mkdir_failed', __( 'Could not create a temp folder.', 'fw' ) );
		}

		// Decode thumbnail (if any) to a file; strip the data-URI from what we store.
		$thumb_file = isset( $env['thumb'] ) ? fw_design_lib__decode_thumb( $env['thumb'], $tmp ) : '';
		$stored     = $env;
		unset( $stored['thumb'] );

		$thumb_url = ( $thumb_file !== '' )
			? trailingslashit( fw_design_lib_url( $shortcode ) ) . $slug . '/' . $thumb_file
			: '';

		$meta = array(
			'id'        => $slug,
			'shortcode' => $shortcode,
			'name'      => $name,
			'thumb'     => $thumb_url,
			'source'    => ( $source === 'library' ? 'library' : 'upload' ),
			'installed' => gmdate( 'c' ),
		);

		$ok =
			   ( false !== file_put_contents( $tmp . '/design.json', wp_json_encode( $stored ) ) )
			&& ( false !== file_put_contents( $tmp . '/meta.json', wp_json_encode( $meta ) ) );

		if ( ! $ok ) {
			fw_design_lib__rrmdir( $tmp );
			return new WP_Error( 'write_failed', __( 'Could not write the design files.', 'fw' ) );
		}
		if ( is_dir( $dest ) ) { fw_design_lib__rrmdir( $dest ); }
		if ( ! @rename( $tmp, $dest ) ) {
			fw_design_lib__rrmdir( $tmp );
			return new WP_Error( 'install_failed', __( 'Could not finalize the design install.', 'fw' ) );
		}
		return array( 'shortcode' => $shortcode, 'slug' => $slug );
	}
endif;

if ( ! function_exists( 'fw_design_lib_install_from_json' ) ) :
	/**
	 * Import a user-supplied design JSON (string or decoded array) into the catalog.
	 *
	 * @param string|array $json
	 * @return array|WP_Error  { shortcode, slug }
	 */
	function fw_design_lib_install_from_json( $json ) {
		$env = is_array( $json ) ? $json : json_decode( (string) $json, true );
		if ( ! is_array( $env ) ) {
			return new WP_Error( 'bad_json', __( 'That file is not valid JSON.', 'fw' ) );
		}
		return fw_design_lib_store_envelope( $env, 'upload' );
	}
endif;

/* -----------------------------------------------------------------------------
 * Read (local scan — no network)
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'fw_design_lib_installed_items' ) ) :
	/**
	 * Flat list of installed designs (optionally for one shortcode), each:
	 *   { id, shortcode, name, thumb(url), source, atts }
	 * `atts` is loaded from design.json so the builder can apply offline.
	 *
	 * @param string|null $shortcode Filter to one shortcode, or null for all.
	 * @return array
	 */
	function fw_design_lib_installed_items( $shortcode = null ) {
		// unysonplus/<shortcode>/designs/<slug>/. When no shortcode is given, scan the
		// enabled shortcodes' folders (a design for a now-disabled element won't list).
		$scopes = ( $shortcode !== null )
			? array( sanitize_key( $shortcode ) )
			: array_map( 'sanitize_key', (array) fw_design_lib_enabled() );

		$items = array();
		foreach ( array_unique( $scopes ) as $sc ) {
			$sc_dir = fw_design_lib_dir( $sc );
			if ( ! is_dir( $sc_dir ) ) { continue; }
			foreach ( (array) glob( trailingslashit( $sc_dir ) . '*', GLOB_ONLYDIR ) as $dir ) {
				$design = @json_decode( @file_get_contents( $dir . '/design.json' ), true );
				$meta   = @json_decode( @file_get_contents( $dir . '/meta.json' ), true );
				if ( ! is_array( $design ) || ! is_array( $meta ) ) { continue; }
				$items[] = array(
					'id'        => isset( $meta['id'] ) ? (string) $meta['id'] : basename( $dir ),
					'shortcode' => $sc,
					'name'      => isset( $meta['name'] ) ? (string) $meta['name'] : basename( $dir ),
					'thumb'     => isset( $meta['thumb'] ) ? (string) $meta['thumb'] : '',
					'source'    => isset( $meta['source'] ) ? (string) $meta['source'] : 'upload',
					'atts'      => isset( $design['atts'] ) && is_array( $design['atts'] ) ? $design['atts'] : array(),
				);
			}
		}
		usort( $items, function ( $a, $b ) { return strcasecmp( $a['name'], $b['name'] ); } );
		return $items;
	}
endif;

if ( ! function_exists( 'fw_design_lib_uninstall' ) ) :
	/** Remove an installed design. @return true|WP_Error */
	function fw_design_lib_uninstall( $shortcode, $slug ) {
		$shortcode = sanitize_key( $shortcode );
		$slug      = sanitize_key( $slug );
		$dir       = trailingslashit( fw_design_lib_dir( $shortcode ) ) . $slug;
		if ( $shortcode === '' || $slug === '' || ! is_dir( $dir ) ) {
			return new WP_Error( 'not_installed', __( 'That design is not installed.', 'fw' ) );
		}
		fw_design_lib__rrmdir( $dir );
		return true;
	}
endif;

if ( ! function_exists( 'fw_design_lib_update_meta' ) ) :
	/**
	 * Edit an installed design's name and/or scoped CSS (atts.custom_css). Values-only;
	 * used by the Theme Settings manager. @return true|WP_Error
	 */
	function fw_design_lib_update_meta( $shortcode, $slug, $fields ) {
		$shortcode = sanitize_key( $shortcode );
		$slug      = sanitize_key( $slug );
		$dir       = trailingslashit( fw_design_lib_dir( $shortcode ) ) . $slug;
		if ( ! is_dir( $dir ) ) { return new WP_Error( 'not_installed', __( 'That design is not installed.', 'fw' ) ); }

		$design = @json_decode( @file_get_contents( $dir . '/design.json' ), true );
		$meta   = @json_decode( @file_get_contents( $dir . '/meta.json' ), true );
		if ( ! is_array( $design ) || ! is_array( $meta ) ) { return new WP_Error( 'corrupt', __( 'Design files are unreadable.', 'fw' ) ); }

		if ( isset( $fields['name'] ) && $fields['name'] !== '' ) {
			$design['name'] = (string) $fields['name'];
			$meta['name']   = (string) $fields['name'];
		}
		if ( isset( $fields['custom_css'] ) ) {
			if ( ! isset( $design['atts'] ) || ! is_array( $design['atts'] ) ) { $design['atts'] = array(); }
			// Scrub with the shared per-element CSS scrubber (no markup/@import/js).
			$css = (string) $fields['custom_css'];
			$design['atts']['custom_css'] = function_exists( 'unysonplus_scrub_element_css' ) ? unysonplus_scrub_element_css( $css ) : $css;
		}
		$ok =
			   ( false !== file_put_contents( $dir . '/design.json', wp_json_encode( $design ) ) )
			&& ( false !== file_put_contents( $dir . '/meta.json', wp_json_encode( $meta ) ) );
		return $ok ? true : new WP_Error( 'write_failed', __( 'Could not save the design.', 'fw' ) );
	}
endif;

/* -----------------------------------------------------------------------------
 * Remote catalog (Browse Library) — designs/catalog.json in UnysonPlus-Library
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'fw_design_lib_catalog_url' ) ) :
	function fw_design_lib_catalog_url() {
		return apply_filters(
			'fw_design_lib_catalog_url',
			'https://raw.githubusercontent.com/UnysonPlus/UnysonPlus-Library/master/designs/catalog.json'
		);
	}
endif;

if ( ! function_exists( 'fw_design_lib_catalog' ) ) :
	/**
	 * Remote catalog, transient-cached (12h ok / 5min fail). Normalized to
	 * { version, base_url, designs:{ "<shortcode>/<slug>" => {shortcode,slug,name,category,thumb,description} } }.
	 */
	function fw_design_lib_catalog( $force = false ) {
		$key = 'fw_design_lib_catalog';
		if ( ! $force ) {
			$cached = get_transient( $key );
			if ( is_array( $cached ) ) { return $cached; }
		}
		$empty = array( 'version' => 0, 'base_url' => '', 'designs' => array() );

		$res = wp_remote_get( fw_design_lib_catalog_url(), array( 'timeout' => 15 ) );
		if ( is_wp_error( $res ) || (int) wp_remote_retrieve_response_code( $res ) !== 200 ) {
			set_transient( $key, $empty, 5 * MINUTE_IN_SECONDS ); return $empty;
		}
		$json = json_decode( wp_remote_retrieve_body( $res ), true );
		if ( ! is_array( $json ) || empty( $json['designs'] ) || ! is_array( $json['designs'] ) ) {
			set_transient( $key, $empty, 5 * MINUTE_IN_SECONDS ); return $empty;
		}
		$base    = isset( $json['base_url'] ) ? trailingslashit( (string) $json['base_url'] ) : '';
		$enabled = fw_design_lib_enabled();
		$out     = array( 'version' => isset( $json['version'] ) ? (int) $json['version'] : 1, 'base_url' => $base, 'designs' => array() );

		foreach ( $json['designs'] as $d ) {
			if ( ! is_array( $d ) ) { continue; }
			$sc   = isset( $d['shortcode'] ) ? sanitize_key( $d['shortcode'] ) : '';
			$slug = isset( $d['slug'] ) ? sanitize_key( $d['slug'] ) : '';
			if ( $sc === '' || $slug === '' || ! in_array( $sc, $enabled, true ) ) { continue; }
			$thumb = ( ! empty( $d['thumb'] ) && $base !== '' ) ? esc_url_raw( $base . ltrim( (string) $d['thumb'], '/' ) ) : '';
			$out['designs'][ $sc . '/' . $slug ] = array(
				'shortcode'   => $sc,
				'slug'        => $slug,
				'name'        => isset( $d['name'] ) ? (string) $d['name'] : ucwords( str_replace( '-', ' ', $slug ) ),
				'category'    => isset( $d['category'] ) ? (string) $d['category'] : '',
				'description' => isset( $d['description'] ) ? (string) $d['description'] : '',
				'thumb'       => $thumb,
			);
		}
		set_transient( $key, $out, 12 * HOUR_IN_SECONDS );
		return $out;
	}
endif;

if ( ! function_exists( 'fw_design_lib_install' ) ) :
	/**
	 * Download + install one catalog design ( <base>/<shortcode>/<slug>/design.json ).
	 * @return array|WP_Error { shortcode, slug }
	 */
	function fw_design_lib_install( $shortcode, $slug ) {
		$shortcode = sanitize_key( $shortcode );
		$slug      = sanitize_key( $slug );
		$catalog   = fw_design_lib_catalog();
		$key       = $shortcode . '/' . $slug;
		if ( empty( $catalog['designs'][ $key ] ) ) {
			return new WP_Error( 'not_in_catalog', __( 'That design is not in the catalog.', 'fw' ) );
		}
		if ( $catalog['base_url'] === '' ) {
			return new WP_Error( 'no_base_url', __( 'Catalog is missing a base URL.', 'fw' ) );
		}
		$env = fw_design_lib__fetch_json( $catalog['base_url'] . $shortcode . '/' . $slug . '/design.json' );
		if ( is_wp_error( $env ) ) { return $env; }
		return fw_design_lib_store_envelope( $env, 'library' );
	}
endif;

/* -----------------------------------------------------------------------------
 * AJAX (admin only, capability + nonce gated)
 * -------------------------------------------------------------------------- */

add_action( 'wp_ajax_fw_design_lib_manage', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'fw' ) ), 403 );
	}
	check_ajax_referer( 'fw_design_lib_manage', 'nonce' );

	$action = isset( $_POST['design_action'] ) ? sanitize_key( $_POST['design_action'] ) : '';

	if ( $action === 'import' ) {
		// Raw JSON string in $_POST['json'] (unslashed; it's file content, not HTML).
		$json = isset( $_POST['json'] ) ? wp_unslash( $_POST['json'] ) : '';
		$r    = fw_design_lib_install_from_json( $json );
	} elseif ( $action === 'delete' ) {
		$r = fw_design_lib_uninstall(
			isset( $_POST['shortcode'] ) ? $_POST['shortcode'] : '',
			isset( $_POST['slug'] ) ? $_POST['slug'] : ''
		);
	} elseif ( $action === 'update' ) {
		$r = fw_design_lib_update_meta(
			isset( $_POST['shortcode'] ) ? $_POST['shortcode'] : '',
			isset( $_POST['slug'] ) ? $_POST['slug'] : '',
			array(
				'name'       => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : null,
				'custom_css' => isset( $_POST['custom_css'] ) ? wp_unslash( $_POST['custom_css'] ) : null,
			)
		);
	} elseif ( $action === 'install' ) {
		$r = fw_design_lib_install(
			isset( $_POST['shortcode'] ) ? $_POST['shortcode'] : '',
			isset( $_POST['slug'] ) ? $_POST['slug'] : ''
		);
	} elseif ( $action === 'refresh' ) {
		fw_design_lib_catalog( true );
		$r = true;
	} else {
		$r = new WP_Error( 'bad_action', __( 'Unknown action.', 'fw' ) );
	}

	if ( is_wp_error( $r ) ) {
		wp_send_json_error( array( 'message' => $r->get_error_message() ) );
	}
	wp_send_json_success( array( 'items' => fw_design_lib_installed_items() ) );
} );
