<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Theme Settings — Export / Import (plugin-owned, works on any theme).
 *
 * Adds a self-contained "Export / Import" control to the Theme Settings → Miscellaneous
 * tab so a site's *design* can travel as a portable .json file. Operates on the
 * framework store (fw_get_db_settings_option / fw_set_db_settings_option, the single
 * fw_theme_settings_options:{id} option), so it is theme-independent. Ported from the
 * UnysonPlus theme; functions are function_exists-guarded so the plugin and an older
 * theme copy can't fatally re-declare during an upgrade.
 *
 * DESIGN-ONLY: operational/site-specific keys are excluded on export AND ignored on
 * import (defense in depth). Media is stripped on export (source-site attachment refs).
 */

if ( ! is_admin() ) {
	return; // admin-only feature
}

if ( ! function_exists( 'unysonplus_settings_io_exclude_keys' ) ) :
	function unysonplus_settings_io_exclude_keys() {
		return (array) apply_filters( 'unysonplus_settings_io_exclude_keys', array(
			'misc_analytics',       // GA4 / GTM / Meta Pixel / Clarity ids
			'misc_performance',     // per-site performance toggles
			'misc_maintenance',     // maintenance-mode content
			'misc_404',             // 404 page selection
			'misc_custom_scripts',  // custom head/body/footer scripts (tracking, exec)
		) );
	}
endif;

if ( ! function_exists( 'unysonplus_settings_io_strip_media' ) ) :
	function unysonplus_settings_io_strip_media( $value ) {
		if ( is_array( $value ) ) {
			if ( array_key_exists( 'attachment_id', $value ) ) {
				return array();
			}
			foreach ( $value as $k => $v ) {
				$value[ $k ] = unysonplus_settings_io_strip_media( $v );
			}
		}
		return $value;
	}
endif;

if ( ! function_exists( 'unysonplus_settings_io_can' ) ) :
	function unysonplus_settings_io_can() {
		return current_user_can( 'manage_options' );
	}
endif;

if ( ! function_exists( 'unysonplus_settings_io_page_slug' ) ) :
	function unysonplus_settings_io_page_slug() {
		if ( function_exists( 'fw' ) && fw()->backend && method_exists( fw()->backend, '_get_settings_page_slug' ) ) {
			return (string) fw()->backend->_get_settings_page_slug();
		}
		return 'fw-settings';
	}
endif;

if ( ! function_exists( 'unysonplus_settings_io_page_url' ) ) :
	function unysonplus_settings_io_page_url() {
		return admin_url( 'themes.php?page=' . unysonplus_settings_io_page_slug() );
	}
endif;

if ( ! function_exists( 'unysonplus_settings_io_theme_meta' ) ) :
	function unysonplus_settings_io_theme_meta() {
		if ( function_exists( 'fw' ) && fw()->theme && fw()->theme->manifest ) {
			return array( (string) fw()->theme->manifest->get_id(), (string) fw()->theme->manifest->get_version() );
		}
		return array( '', '' );
	}
endif;

if ( ! function_exists( 'unysonplus_settings_io_redirect' ) ) :
	function unysonplus_settings_io_redirect( $code ) {
		wp_safe_redirect( add_query_arg( 'unysonplus_io', rawurlencode( $code ), unysonplus_settings_io_page_url() ) );
		exit;
	}
endif;

/* ----- Export (admin-post.php?action=unysonplus_export_theme_settings) ----- */
if ( ! function_exists( 'unysonplus_settings_io_export' ) ) :
	function unysonplus_settings_io_export() {
		if ( ! unysonplus_settings_io_can() ) {
			wp_die( esc_html__( 'You are not allowed to export theme settings.', 'fw' ) );
		}
		check_admin_referer( 'unysonplus_export_theme_settings' );

		if ( ! function_exists( 'fw_get_db_settings_option' ) ) {
			wp_die( esc_html__( 'The Unyson framework is not active.', 'fw' ) );
		}

		$values = fw_get_db_settings_option();
		$values = is_array( $values ) ? $values : array();

		foreach ( unysonplus_settings_io_exclude_keys() as $k ) {
			unset( $values[ $k ] );
		}
		$values = unysonplus_settings_io_strip_media( $values );

		list( $theme_id, $theme_version ) = unysonplus_settings_io_theme_meta();

		$envelope = array(
			'_fw_settings_export' => array(
				'format_version' => 1,
				'scope'          => 'design',
				'theme_id'       => $theme_id,
				'theme_version'  => $theme_version,
				'exported_at'    => time(),
				'excluded'       => array_values( unysonplus_settings_io_exclude_keys() ),
				'media_stripped' => true,
			),
			'values' => $values,
		);

		$slug     = sanitize_title( $theme_id ? $theme_id : 'theme' );
		$filename = $slug . '-settings-design-' . gmdate( 'Ymd-His' ) . '.json';

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		echo wp_json_encode( $envelope, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		exit;
	}
endif;
add_action( 'admin_post_unysonplus_export_theme_settings', 'unysonplus_settings_io_export' );

/* ----- Import (admin-post.php?action=unysonplus_import_theme_settings) ----- */
if ( ! function_exists( 'unysonplus_settings_io_import' ) ) :
	function unysonplus_settings_io_import() {
		if ( ! unysonplus_settings_io_can() ) {
			wp_die( esc_html__( 'You are not allowed to import theme settings.', 'fw' ) );
		}
		check_admin_referer( 'unysonplus_import_theme_settings' );

		if ( ! function_exists( 'fw_get_db_settings_option' ) || ! function_exists( 'fw_set_db_settings_option' ) ) {
			wp_die( esc_html__( 'The Unyson framework is not active.', 'fw' ) );
		}

		if (
			empty( $_FILES['settings_file'] ) ||
			! isset( $_FILES['settings_file']['error'] ) ||
			$_FILES['settings_file']['error'] !== UPLOAD_ERR_OK
		) {
			unysonplus_settings_io_redirect( 'err_no_file' );
		}

		if ( (int) $_FILES['settings_file']['size'] > 5 * 1024 * 1024 ) {
			unysonplus_settings_io_redirect( 'err_too_large' );
		}

		$tmp = isset( $_FILES['settings_file']['tmp_name'] ) ? $_FILES['settings_file']['tmp_name'] : '';
		if ( empty( $tmp ) || ! is_uploaded_file( $tmp ) ) {
			unysonplus_settings_io_redirect( 'err_no_file' );
		}

		$raw  = file_get_contents( $tmp );
		$data = ( is_string( $raw ) && '' !== $raw ) ? json_decode( $raw, true ) : null;

		if (
			! is_array( $data ) ||
			empty( $data['_fw_settings_export'] ) || ! is_array( $data['_fw_settings_export'] ) ||
			! isset( $data['values'] ) || ! is_array( $data['values'] )
		) {
			unysonplus_settings_io_redirect( 'err_invalid' );
		}

		$env      = $data['_fw_settings_export'];
		$incoming = $data['values'];

		foreach ( unysonplus_settings_io_exclude_keys() as $k ) {
			unset( $incoming[ $k ] );
		}

		if ( empty( $incoming ) ) {
			unysonplus_settings_io_redirect( 'err_empty' );
		}

		$current = fw_get_db_settings_option();
		$current = is_array( $current ) ? $current : array();

		$merged = $current;
		foreach ( $incoming as $k => $v ) {
			$merged[ $k ] = $v;
		}

		fw_set_db_settings_option( null, $merged );

		do_action( 'fw_settings_form_saved', $current, $merged );

		list( $theme_id ) = unysonplus_settings_io_theme_meta();
		$cross_theme = ! empty( $env['theme_id'] ) && $theme_id && $env['theme_id'] !== $theme_id;

		unysonplus_settings_io_redirect( $cross_theme ? 'imported_warning' : 'imported' );
	}
endif;
add_action( 'admin_post_unysonplus_import_theme_settings', 'unysonplus_settings_io_import' );

/* ----- Result notice after an import redirect ----- */
if ( ! function_exists( 'unysonplus_settings_io_result_notice' ) ) :
	function unysonplus_settings_io_result_notice() {
		if ( ! unysonplus_settings_io_can() ) {
			return;
		}
		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== unysonplus_settings_io_page_slug() ) {
			return;
		}
		if ( ! isset( $_GET['unysonplus_io'] ) ) {
			return;
		}

		$map = array(
			'imported'         => array( 'success', __( 'Theme settings imported — the design has been applied.', 'fw' ) ),
			'imported_warning' => array( 'warning', __( 'Imported, but the file came from a different theme; only recognized design settings were applied.', 'fw' ) ),
			'err_no_file'      => array( 'error',   __( 'No file was uploaded, or the upload failed.', 'fw' ) ),
			'err_too_large'    => array( 'error',   __( 'That file is too large (maximum 5 MB).', 'fw' ) ),
			'err_invalid'      => array( 'error',   __( 'That file is not a valid Unyson+ theme-settings export.', 'fw' ) ),
			'err_empty'        => array( 'error',   __( 'No importable design settings were found in that file.', 'fw' ) ),
		);

		$code = sanitize_key( wp_unslash( $_GET['unysonplus_io'] ) );
		if ( isset( $map[ $code ] ) ) {
			printf(
				'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
				esc_attr( $map[ $code ][0] ),
				esc_html( $map[ $code ][1] )
			);
		}
	}
endif;
add_action( 'admin_notices', 'unysonplus_settings_io_result_notice' );

/* ----- The Export / Import control (html-full field in the Misc tab) ----- */
if ( ! function_exists( 'unysonplus_settings_io_misc_field_html' ) ) :
	function unysonplus_settings_io_misc_field_html() {
		$export_url   = wp_nonce_url(
			admin_url( 'admin-post.php?action=unysonplus_export_theme_settings' ),
			'unysonplus_export_theme_settings'
		);
		$import_nonce = wp_create_nonce( 'unysonplus_import_theme_settings' );
		$post_url     = admin_url( 'admin-post.php' );

		ob_start();
		?>
		<div class="unysonplus-io">
			<p style="max-width:70ch;color:#50575e;margin-top:0;">
				<?php esc_html_e( 'Save this site\'s design (colors, typography, header, footer, spacing, custom CSS) to a portable .json file, or apply a design file to this site. Operational settings (analytics, performance, maintenance, 404, custom scripts) and uploaded images are not included.', 'fw' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( $export_url ); ?>" class="button button-secondary" style="display:inline-flex;align-items:center;gap:6px;">
					<span class="dashicons dashicons-download"></span>
					<?php esc_html_e( 'Export design', 'fw' ); ?>
				</a>
			</p>
			<p style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
				<input type="file" id="unysonplus-io-file" accept="application/json,.json" />
				<button type="button" id="unysonplus-io-import" class="button button-primary" style="display:inline-flex;align-items:center;gap:6px;">
					<span class="dashicons dashicons-upload"></span>
					<?php esc_html_e( 'Import design', 'fw' ); ?>
				</button>
			</p>
		</div>
		<script>
		(function () {
			var btn  = document.getElementById('unysonplus-io-import');
			var file = document.getElementById('unysonplus-io-file');
			if (!btn || !file) { return; }
			btn.addEventListener('click', function () {
				if (!file.files || !file.files.length) {
					window.alert(<?php echo wp_json_encode( __( 'Please choose a .json design file to import first.', 'fw' ) ); ?>);
					return;
				}
				var form = document.createElement('form');
				form.method        = 'post';
				form.enctype       = 'multipart/form-data';
				form.action        = <?php echo wp_json_encode( $post_url ); ?>;
				form.style.display = 'none';

				var a = document.createElement('input');
				a.type = 'hidden'; a.name = 'action'; a.value = 'unysonplus_import_theme_settings';
				form.appendChild(a);

				var n = document.createElement('input');
				n.type = 'hidden'; n.name = '_wpnonce'; n.value = <?php echo wp_json_encode( $import_nonce ); ?>;
				form.appendChild(n);

				file.name = 'settings_file';
				form.appendChild(file);

				document.body.appendChild(form);
				form.submit();
			});
		})();
		</script>
		<?php
		return ob_get_clean();
	}
endif;
