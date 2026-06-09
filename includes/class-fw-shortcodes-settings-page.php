<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Admin settings page for the Shortcodes extension.
 *
 * - Lists every discovered shortcode (core + user-installed) with an enable/disable
 *   checkbox. Disabling feeds FW_Extension_Shortcodes::_filter_disabled_from_settings()
 *   which removes the shortcode from registration, the page-builder and the editor.
 * - Installs new shortcodes from a .zip upload or a GitHub URL into the update-safe
 *   uploads directory (FW_Extension_Shortcodes::get_user_shortcodes_dir()).
 * - Deletes user-installed shortcodes (core ones can only be disabled).
 *
 * @internal
 */
class FW_Ext_Shortcodes_Settings_Page {

	const NONCE         = 'fw_ext_shortcodes_settings';
	const PRESETS_NONCE = 'fw_ext_component_presets';
	const OPTION        = 'enabled_shortcodes';
	const PARENT        = 'fw-extensions';
	const PAGE_SLUG     = 'fw-shortcodes';
	const STYLING_SLUG  = 'fw-component-presets';
	const CAPABILITY    = 'manage_options';

	/** @var FW_Extension_Shortcodes */
	private $extension;

	/** @var string|null Hook suffix returned by add_submenu_page() */
	private $hook_suffix = null;

	/** @var string|null Hook suffix for the Component Presets page */
	private $presets_hook_suffix = null;

	public function __construct( FW_Extension_Shortcodes $extension ) {
		$this->extension = $extension;

		// Late priority so the parent (Extensions) menu is already registered.
		add_action( 'admin_menu', array( $this, '_action_admin_menu' ), 100 );
		add_action( 'admin_enqueue_scripts', array( $this, '_action_enqueue' ) );

		add_action( 'wp_ajax_fw_ext_shortcodes_save', array( $this, '_ajax_save' ) );
		add_action( 'wp_ajax_fw_ext_shortcodes_install_zip', array( $this, '_ajax_install_zip' ) );
		add_action( 'wp_ajax_fw_ext_shortcodes_install_github', array( $this, '_ajax_install_github' ) );
		add_action( 'wp_ajax_fw_ext_shortcodes_delete', array( $this, '_ajax_delete' ) );

		// Surface a "Settings" link on the WordPress Shortcodes extension card
		// pointing at this dedicated page (the card has no built-in settings form).
		add_filter( 'fw_ext_manager_settings_url', array( $this, '_filter_card_settings_url' ), 10, 3 );
	}

	/**
	 * @internal
	 */
	public function _filter_card_settings_url( $url, $name, $ext ) {
		if ( in_array( $name, array( 'wp-shortcodes', 'shortcodes' ), true ) ) {
			return self::get_page_url();
		}
		return $url;
	}

	public static function get_page_url() {
		return admin_url( 'admin.php?page=' . self::PAGE_SLUG );
	}

	/**
	 * @internal
	 */
	public function _action_admin_menu() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		$this->hook_suffix = add_submenu_page(
			self::PARENT,
			__( 'Shortcodes', 'fw' ),
			__( 'Shortcodes', 'fw' ),
			self::CAPABILITY,
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);

		// Dedicated entry for the preset editor (Color Presets, Typography,
		// Spacing, Buttons, Borders, Tables). A normal WP settings page that
		// renders the plugin-owned components schema and saves to the
		// theme-independent fw_ext_settings_options:shortcodes store.
		// Hidden in "bare" mode (Page Builder settings → Styling Presets off).
		if ( ! function_exists( 'unysonplus_styling_presets_enabled' ) || unysonplus_styling_presets_enabled() ) {
			$this->presets_hook_suffix = add_submenu_page(
				self::PARENT,
				__( 'Component Presets', 'fw' ),
				__( 'Component Presets', 'fw' ),
				self::CAPABILITY,
				self::STYLING_SLUG,
				array( $this, 'render_presets_page' )
			);
			if ( $this->presets_hook_suffix ) {
				// Handle the save before any output so we can PRG-redirect.
				add_action( 'load-' . $this->presets_hook_suffix, array( $this, '_maybe_save_presets' ) );
			}
		}
	}

	/* ---------------------------------------------------------------------- *
	 * Component Presets page (native WP settings screen)
	 * ---------------------------------------------------------------------- */

	private function presets_options() {
		return function_exists( 'unysonplus_components_settings_options' )
			? unysonplus_components_settings_options()
			: array();
	}

	private function presets_stored_values() {
		$ext = $this->extension->get_name();
		return class_exists( 'FW_WP_Option' )
			? (array) FW_WP_Option::get( 'fw_ext_settings_options:' . $ext, null, array() )
			: array();
	}

	/**
	 * @internal
	 * Save handler (runs on the page's `load-` hook, before output).
	 */
	public function _maybe_save_presets() {
		if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '' ) ) {
			return;
		}
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}
		check_admin_referer( self::PRESETS_NONCE );

		$ext    = $this->extension->get_name();
		$active = isset( $_POST['fw_cp_active_tab'] )
			? preg_replace( '/[^a-z0-9_]/', '', (string) $_POST['fw_cp_active_tab'] )
			: '';

		if ( isset( $_POST['fw_cp_reset_all'] ) ) {
			// Reset every library: drop all preset keys so the getters return the
			// built-in plugin defaults again.
			$this->reset_preset_keys( $this->preset_all_keys() );
			$flag = 'fw-reset';
		} elseif ( isset( $_POST['fw_cp_reset'] ) ) {
			// Reset only the active tab's library.
			$this->reset_preset_keys( $this->preset_tab_keys( $active ) );
			$flag = 'fw-reset';
		} else {
			// Save: merge over whatever is already stored (e.g. enabled_shortcodes)
			// so we only touch the preset keys.
			$values = fw_get_options_values_from_input( $this->presets_options() );
			fw_set_db_ext_settings_option(
				$ext,
				null,
				array_merge( $this->presets_stored_values(), (array) $values )
			);
			$flag = 'fw-saved';
		}

		$url = add_query_arg(
			array( 'page' => self::STYLING_SLUG, $flag => '1' ),
			admin_url( 'admin.php' )
		);
		if ( $active !== '' ) {
			$url .= '#' . $active; // keep the user on the same tab after the redirect
		}
		wp_safe_redirect( $url );
		exit;
	}

	/** Leaf option ids for one tab's library (e.g. tab_spacing → spacing_scale, gap_scale, …). */
	private function preset_tab_keys( $tab_id ) {
		$schema = $this->presets_options();
		if ( $tab_id === '' || ! isset( $schema[ $tab_id ]['options'] ) || ! is_array( $schema[ $tab_id ]['options'] ) ) {
			return array();
		}
		return array_keys( fw_extract_only_options( $schema[ $tab_id ]['options'] ) );
	}

	/** Every preset leaf option id across all tabs. */
	private function preset_all_keys() {
		$keys = array();
		foreach ( $this->presets_options() as $tab ) {
			if ( isset( $tab['options'] ) && is_array( $tab['options'] ) ) {
				$keys = array_merge( $keys, array_keys( fw_extract_only_options( $tab['options'] ) ) );
			}
		}
		return array_values( array_unique( $keys ) );
	}

	/** Remove the given keys from the plugin store so their getters fall back to defaults. */
	private function reset_preset_keys( array $keys ) {
		if ( empty( $keys ) ) {
			return;
		}
		$store = $this->presets_stored_values();
		foreach ( $keys as $k ) {
			unset( $store[ $k ] );
		}
		fw_set_db_ext_settings_option( $this->extension->get_name(), null, $store );
	}

	/**
	 * @internal
	 */
	public function render_presets_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		$schema       = $this->presets_options();  // top-level 'tab' entries, one per library
		$values       = $this->presets_stored_values();
		$first_tab_id = ! empty( $schema ) ? (string) array_key_first( $schema ) : '';
		?>
		<div class="wrap fw-component-presets">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Component Presets', 'fw' ); ?></h1>
			<p class="description fw-component-presets__intro">
				<?php esc_html_e( 'Reusable styles your shortcodes and theme share — Color Presets, Typography, Spacing, Buttons, Borders and Tables. Saved here once and applied site-wide, on any active theme.', 'fw' ); ?>
			</p>

			<?php if ( isset( $_GET['fw-saved'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Component presets saved.', 'fw' ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( isset( $_GET['fw-reset'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Component presets reset to defaults.', 'fw' ); ?></p>
				</div>
			<?php endif; ?>

			<?php /* Native WordPress tabs */ ?>
			<h2 class="nav-tab-wrapper fw-cp-tabs">
				<?php $first = true; foreach ( $schema as $tab_id => $tab ) : ?>
					<a href="#<?php echo esc_attr( $tab_id ); ?>"
					   class="nav-tab<?php echo $first ? ' nav-tab-active' : ''; ?>"
					   data-tab="<?php echo esc_attr( $tab_id ); ?>">
						<?php echo esc_html( isset( $tab['title'] ) ? $tab['title'] : $tab_id ); ?>
					</a>
				<?php $first = false; endforeach; ?>
			</h2>

			<form method="post" action="">
				<?php wp_nonce_field( self::PRESETS_NONCE ); ?>
				<input type="hidden" name="fw_cp_active_tab" class="fw-cp-active-tab" value="<?php echo esc_attr( $first_tab_id ); ?>" />

				<?php $first = true; foreach ( $schema as $tab_id => $tab ) :
					$inner = ( isset( $tab['options'] ) && is_array( $tab['options'] ) ) ? $tab['options'] : array();
					$title = isset( $tab['title'] ) ? $tab['title'] : $tab_id;
					?>
					<div class="fw-cp-panel<?php echo $first ? ' is-active' : ''; ?>" id="panel-<?php echo esc_attr( $tab_id ); ?>">
						<div class="postbox">
							<div class="postbox-header"><h2 class="hndle fw-cp-card-title"><?php echo esc_html( $title ); ?></h2></div>
							<div class="inside">
								<?php
								// Render this library's fields only; all panels are in the DOM
								// (just CSS-hidden) so a Save submits every tab's values. The
								// fields are wrapped in a border-less `group` so the rows read
								// as one cohesive block (no inner separators) — matching the
								// Breadcrumbs settings look. The group is a render-only
								// container (no stored id), so the save handler — which reads
								// the raw presets_options() schema — is unaffected.
								echo fw()->backend->render_options(
									array(
										'group_' . $tab_id => array(
											'type'    => 'group',
											'options' => $inner,
										),
									),
									$values
								);
								?>
							</div>
						</div>
					</div>
				<?php $first = false; endforeach; ?>

				<p class="submit fw-cp-actions">
					<button type="submit" name="fw_cp_save" value="1" class="button button-primary"><?php esc_html_e( 'Save Changes', 'fw' ); ?></button>
					<button type="submit" name="fw_cp_reset" value="1" class="button fw-cp-reset"><?php esc_html_e( 'Reset This Tab to Defaults', 'fw' ); ?></button>
					<button type="submit" name="fw_cp_reset_all" value="1" class="button-link fw-cp-reset-all"><?php esc_html_e( 'Reset all to defaults', 'fw' ); ?></button>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * @internal
	 */
	public function _action_enqueue( $hook ) {
		// Component Presets page: load the Unyson option-editor assets (this
		// registers + enqueues fw-backend-options + each option type's JS/CSS) plus
		// a tiny bit of native-WP polish.
		if ( $hook === $this->presets_hook_suffix ) {
			if ( function_exists( 'unysonplus_components_settings_options' ) ) {
				fw()->backend->enqueue_options_static( unysonplus_components_settings_options() );
			}
			wp_enqueue_style(
				'fw-ext-component-presets',
				fw_min_uri($this->extension->get_uri( '/static/css/component-presets.css' )),
				array(),
				$this->extension->manifest->get( 'version' )
			);
			wp_enqueue_script(
				'fw-ext-component-presets',
				fw_min_uri($this->extension->get_uri( '/static/js/component-presets.js' )),
				array( 'jquery' ),
				$this->extension->manifest->get( 'version' ),
				true
			);
			return;
		}

		if ( $hook !== $this->hook_suffix ) {
			return;
		}

		$version = $this->extension->manifest->get( 'version' );

		wp_enqueue_style(
			'fw-ext-shortcodes-admin-settings',
			fw_min_uri($this->extension->get_uri( '/static/css/admin-settings.css' )),
			array(),
			$version
		);

		wp_enqueue_script(
			'fw-ext-shortcodes-admin-settings',
			fw_min_uri($this->extension->get_uri( '/static/js/admin-settings.js' )),
			array( 'jquery' ),
			$version,
			true
		);

		wp_localize_script(
			'fw-ext-shortcodes-admin-settings',
			'fwScSettings',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( self::NONCE ),
				'i18n'    => array(
					'saving'        => __( 'Saving…', 'fw' ),
					'saved'         => __( 'Settings saved.', 'fw' ),
					'saveError'     => __( 'Could not save settings.', 'fw' ),
					'installing'    => __( 'Installing…', 'fw' ),
					'chooseZip'     => __( 'Please choose a .zip file first.', 'fw' ),
					'enterUrl'      => __( 'Please enter a GitHub URL first.', 'fw' ),
					'confirmDelete' => __( 'Delete this shortcode for good? This removes its files from the server.', 'fw' ),
					'delete'        => __( 'Delete', 'fw' ),
					'genericError'  => __( 'Something went wrong.', 'fw' ),
					'reloadHint'    => __( 'Installed. Reload the page builder to use it.', 'fw' ),
					'enabledCount'  => __( '%1$d of %2$d enabled', 'fw' ),
					'core'          => __( 'Core', 'fw' ),
					'uploaded'      => __( 'Uploaded', 'fw' ),
					'github'        => __( 'GitHub', 'fw' ),
					'zip'           => __( 'Zip', 'fw' ),
				),
			)
		);
	}

	/* ---------------------------------------------------------------------- *
	 * Render
	 * ---------------------------------------------------------------------- */

	/**
	 * @internal
	 */
	public function render_page() {
		$all     = $this->get_sorted_shortcodes();
		$enabled = fw_get_db_ext_settings_option( $this->extension->get_name(), self::OPTION, null );

		$is_enabled = function ( $tag ) use ( $enabled ) {
			return ! is_array( $enabled ) || in_array( $tag, $enabled, true );
		};

		$total       = count( $all );
		$enabled_cnt = 0;
		foreach ( $all as $meta ) {
			if ( $is_enabled( $meta['tag'] ) ) {
				$enabled_cnt++;
			}
		}
		?>
		<div class="wrap fw-sc-settings">
			<h1><?php esc_html_e( 'Shortcodes', 'fw' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Enable or disable the shortcodes available on your site, or add new ones from a .zip file or a GitHub repository.', 'fw' ); ?>
			</p>

			<div class="fw-sc-notice fw-sc-notice-hidden" id="fw-sc-notice"></div>

			<h2 class="fw-sc-section-title"><?php esc_html_e( 'Available shortcodes', 'fw' ); ?></h2>

			<div class="fw-sc-toolbar">
				<input type="search" id="fw-sc-search" class="fw-sc-search"
					placeholder="<?php esc_attr_e( 'Search shortcodes…', 'fw' ); ?>" />
				<div class="fw-sc-toolbar-actions">
					<span class="fw-sc-count" id="fw-sc-count"
						data-template="<?php esc_attr_e( '%1$d of %2$d enabled', 'fw' ); ?>">
						<?php
						echo esc_html( sprintf(
							/* translators: 1: enabled count, 2: total count */
							__( '%1$d of %2$d enabled', 'fw' ),
							$enabled_cnt,
							$total
						) );
						?>
					</span>
					<button type="button" class="button" id="fw-sc-enable-all"><?php esc_html_e( 'Enable all', 'fw' ); ?></button>
					<button type="button" class="button" id="fw-sc-disable-all"><?php esc_html_e( 'Disable all', 'fw' ); ?></button>
					<button type="button" class="button button-primary" id="fw-sc-save"><?php esc_html_e( 'Save changes', 'fw' ); ?></button>
				</div>
			</div>

			<ul class="fw-sc-list" id="fw-sc-list">
				<?php foreach ( $all as $meta ) : ?>
					<?php
					$tag       = $meta['tag'];
					$on        = $is_enabled( $tag );
					$haystack  = strtolower( $meta['title'] . ' ' . $tag );
					$badge_cls = 'fw-sc-badge-' . sanitize_html_class( $meta['source'] );
					?>
					<li class="fw-sc-item" data-search="<?php echo esc_attr( $haystack ); ?>" data-tag="<?php echo esc_attr( $tag ); ?>">
						<label class="fw-sc-item-label">
							<input type="checkbox" class="fw-sc-toggle" value="<?php echo esc_attr( $tag ); ?>" <?php checked( $on ); ?> />
							<span class="fw-sc-icon"><?php echo $this->kses_icon( $meta['icon'] ); // phpcs:ignore ?></span>
							<span class="fw-sc-meta">
								<span class="fw-sc-title"><?php echo esc_html( $meta['title'] ); ?></span>
								<code class="fw-sc-tag">[<?php echo esc_html( $tag ); ?>]</code>
							</span>
						</label>
						<span class="fw-sc-badge <?php echo esc_attr( $badge_cls ); ?>"><?php echo esc_html( $this->source_label( $meta['source'] ) ); ?></span>
						<?php if ( ! empty( $meta['deletable'] ) ) : ?>
							<button type="button" class="button-link fw-sc-delete" data-tag="<?php echo esc_attr( $tag ); ?>" title="<?php esc_attr_e( 'Delete', 'fw' ); ?>">
								<?php esc_html_e( 'Delete', 'fw' ); ?>
							</button>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>

			<p class="fw-sc-empty fw-sc-notice-hidden" id="fw-sc-empty"><?php esc_html_e( 'No shortcodes match your search.', 'fw' ); ?></p>

			<h2 class="fw-sc-section-title"><?php esc_html_e( 'Add a shortcode', 'fw' ); ?></h2>
			<p class="description fw-sc-trust">
				<?php esc_html_e( 'A shortcode is executable PHP. Only install shortcodes from sources you trust — the same level of trust as installing a plugin.', 'fw' ); ?>
			</p>

			<div class="fw-sc-install">
				<div class="fw-sc-card">
					<h3><?php esc_html_e( 'Upload a .zip', 'fw' ); ?></h3>
					<p class="description"><?php esc_html_e( 'A zipped shortcode folder containing config.php and views/view.php.', 'fw' ); ?></p>
					<input type="file" id="fw-sc-zip" accept=".zip" />
					<button type="button" class="button button-primary" id="fw-sc-install-zip"><?php esc_html_e( 'Upload &amp; install', 'fw' ); ?></button>
				</div>

				<div class="fw-sc-card">
					<h3><?php esc_html_e( 'From GitHub', 'fw' ); ?></h3>
					<p class="description"><?php esc_html_e( 'A public repository URL, e.g. https://github.com/owner/repo', 'fw' ); ?></p>
					<input type="url" id="fw-sc-github" class="regular-text" placeholder="https://github.com/owner/repo" />
					<button type="button" class="button button-primary" id="fw-sc-install-github"><?php esc_html_e( 'Download &amp; install', 'fw' ); ?></button>
				</div>
			</div>
		</div>
		<?php
	}

	private function get_sorted_shortcodes() {
		$all = $this->extension->discover_all_shortcodes();
		uasort( $all, function ( $a, $b ) {
			return strcasecmp( $a['title'], $b['title'] );
		} );
		return $all;
	}

	private function source_label( $source ) {
		switch ( $source ) {
			case 'core':
				return __( 'Core', 'fw' );
			case 'github':
				return __( 'GitHub', 'fw' );
			case 'zip':
				return __( 'Zip', 'fw' );
			default:
				return __( 'Uploaded', 'fw' );
		}
	}

	/**
	 * Allow only a safe subset of SVG/img markup for the inline icon.
	 */
	private function kses_icon( $icon ) {
		if ( '' === $icon ) {
			return '';
		}

		$svg_attrs = array(
			'xmlns'       => true,
			'viewbox'     => true,
			'width'       => true,
			'height'      => true,
			'fill'        => true,
			'stroke'      => true,
			'stroke-width' => true,
			'class'       => true,
			'aria-hidden' => true,
			'focusable'   => true,
		);

		$allowed = array(
			'svg'     => $svg_attrs,
			'g'       => array( 'fill' => true, 'stroke' => true, 'transform' => true ),
			'path'    => array( 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'fill-rule' => true, 'clip-rule' => true ),
			'circle'  => array( 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true ),
			'rect'    => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true ),
			'line'    => array( 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true ),
			'polygon' => array( 'points' => true, 'fill' => true, 'stroke' => true ),
			'polyline' => array( 'points' => true, 'fill' => true, 'stroke' => true ),
			'img'     => array( 'src' => true, 'alt' => true, 'width' => true, 'height' => true ),
		);

		return wp_kses( $icon, $allowed );
	}

	/* ---------------------------------------------------------------------- *
	 * AJAX
	 * ---------------------------------------------------------------------- */

	private function guard() {
		check_ajax_referer( self::NONCE, 'nonce' );

		if ( ! current_user_can( self::CAPABILITY ) || ( is_multisite() && ! is_super_admin() ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'fw' ) ) );
		}
	}

	/**
	 * @internal
	 */
	public function _ajax_save() {
		$this->guard();

		$posted = isset( $_POST['tags'] ) ? (array) wp_unslash( $_POST['tags'] ) : array();
		$posted = array_map( 'sanitize_text_field', $posted );

		// Only persist tags that actually exist.
		$valid   = array_keys( $this->extension->discover_all_shortcodes() );
		$enabled = array_values( array_intersect( $valid, $posted ) );

		fw_set_db_ext_settings_option( $this->extension->get_name(), self::OPTION, $enabled );

		wp_send_json_success( array(
			'enabled' => count( $enabled ),
			'total'   => count( $valid ),
		) );
	}

	/**
	 * @internal
	 */
	public function _ajax_install_zip() {
		$this->guard();

		if ( empty( $_FILES['shortcode_zip'] ) || ! empty( $_FILES['shortcode_zip']['error'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No file was uploaded.', 'fw' ) ) );
		}

		$name = isset( $_FILES['shortcode_zip']['name'] ) ? sanitize_file_name( $_FILES['shortcode_zip']['name'] ) : '';
		if ( 'zip' !== strtolower( pathinfo( $name, PATHINFO_EXTENSION ) ) ) {
			wp_send_json_error( array( 'message' => __( 'The file must be a .zip archive.', 'fw' ) ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';

		$upload = wp_handle_upload(
			$_FILES['shortcode_zip'],
			array(
				'test_form' => false,
				'mimes'     => array( 'zip' => 'application/zip' ),
			)
		);

		if ( ! $upload || isset( $upload['error'] ) ) {
			wp_send_json_error( array( 'message' => isset( $upload['error'] ) ? $upload['error'] : __( 'Upload failed.', 'fw' ) ) );
		}

		$result = $this->install_from_zip( $upload['file'], 'zip', null );

		// wp_handle_upload moved the file into uploads; remove the leftover zip.
		@unlink( $upload['file'] );

		$this->respond_install( $result );
	}

	/**
	 * @internal
	 */
	public function _ajax_install_github() {
		$this->guard();

		$url  = isset( $_POST['github_url'] ) ? esc_url_raw( wp_unslash( $_POST['github_url'] ) ) : '';
		$repo = $this->parse_github_repo( $url );

		if ( ! $repo ) {
			wp_send_json_error( array( 'message' => __( 'Enter a valid GitHub repository URL (https://github.com/owner/repo).', 'fw' ) ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';

		$zip_url = $this->resolve_github_zip_url( $repo );
		if ( is_wp_error( $zip_url ) ) {
			wp_send_json_error( array( 'message' => $zip_url->get_error_message() ) );
		}

		$response = wp_remote_get( $zip_url, array( 'timeout' => 300 ) );
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}
		if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not download the repository archive.', 'fw' ) ) );
		}

		$tmp_zip = trailingslashit( get_temp_dir() ) . 'fw-sc-' . wp_generate_password( 10, false ) . '.zip';
		if ( false === file_put_contents( $tmp_zip, wp_remote_retrieve_body( $response ) ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not write the downloaded archive.', 'fw' ) ) );
		}

		$result = $this->install_from_zip( $tmp_zip, 'github', $repo['repo'] );
		@unlink( $tmp_zip );

		$this->respond_install( $result );
	}

	/**
	 * @internal
	 */
	public function _ajax_delete() {
		$this->guard();

		$tag = isset( $_POST['tag'] ) ? sanitize_key( wp_unslash( $_POST['tag'] ) ) : '';
		if ( '' === $tag ) {
			wp_send_json_error( array( 'message' => __( 'Missing shortcode.', 'fw' ) ) );
		}

		$user_dir = FW_Extension_Shortcodes::get_user_shortcodes_dir();
		if ( ! $user_dir ) {
			wp_send_json_error( array( 'message' => __( 'Uploads directory is not available.', 'fw' ) ) );
		}

		$folder = str_replace( '_', '-', $tag );
		$target = wp_normalize_path( $user_dir['path'] . '/' . $folder );

		// Hard guard: the resolved path must live inside the user shortcodes dir.
		$base = wp_normalize_path( trailingslashit( $user_dir['path'] ) );
		if ( strpos( $target . '/', $base ) !== 0 || ! is_dir( $target ) ) {
			wp_send_json_error( array( 'message' => __( 'This shortcode cannot be deleted.', 'fw' ) ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		if ( ! $wp_filesystem->delete( $target, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not remove the shortcode files.', 'fw' ) ) );
		}

		// Drop it from the saved enabled list.
		$enabled = fw_get_db_ext_settings_option( $this->extension->get_name(), self::OPTION, null );
		if ( is_array( $enabled ) ) {
			$enabled = array_values( array_diff( $enabled, array( $tag ) ) );
			fw_set_db_ext_settings_option( $this->extension->get_name(), self::OPTION, $enabled );
		}

		wp_send_json_success( array( 'tag' => $tag ) );
	}

	/* ---------------------------------------------------------------------- *
	 * Install helpers
	 * ---------------------------------------------------------------------- */

	/**
	 * Unzip, validate and move a shortcode folder into the uploads dir.
	 *
	 * @param string      $zip_real_path  Absolute path to the .zip on disk.
	 * @param string      $source         'zip' | 'github'
	 * @param string|null $preferred_name Fallback folder name (e.g. GitHub repo name).
	 * @return array|WP_Error array('tag','title','name') on success.
	 */
	private function install_from_zip( $zip_real_path, $source, $preferred_name ) {
		WP_Filesystem();
		global $wp_filesystem;

		$tmp_dir = trailingslashit( get_temp_dir() ) . 'fw-sc-extract-' . wp_generate_password( 10, false );
		if ( ! wp_mkdir_p( $tmp_dir ) ) {
			return new WP_Error( 'fw_sc', __( 'Could not create a temporary directory.', 'fw' ) );
		}

		$unzip = unzip_file( $zip_real_path, $tmp_dir );
		if ( is_wp_error( $unzip ) ) {
			$this->rmdir( $tmp_dir );
			return $unzip;
		}

		list( $folder_path, $descended ) = $this->locate_shortcode_folder( $tmp_dir );

		if ( ! $folder_path ) {
			$this->rmdir( $tmp_dir );
			return new WP_Error( 'fw_sc', __( 'The archive does not contain a valid shortcode folder (missing config.php).', 'fw' ) );
		}

		// A shortcode must render something.
		if ( ! file_exists( $folder_path . '/views/view.php' ) ) {
			$this->rmdir( $tmp_dir );
			return new WP_Error( 'fw_sc', __( 'The shortcode is missing views/view.php.', 'fw' ) );
		}

		// Make sure config.php is parseable (no fatals) and an array.
		$cfg_vars = fw_get_variables_from_file( $folder_path . '/config.php', array( 'cfg' => null ) );
		if ( empty( $cfg_vars['cfg'] ) || ! is_array( $cfg_vars['cfg'] ) ) {
			$this->rmdir( $tmp_dir );
			return new WP_Error( 'fw_sc', __( 'The shortcode config.php is invalid.', 'fw' ) );
		}

		// Decide the destination folder name (becomes the tag).
		$raw_name = ( $descended || ! $preferred_name ) ? basename( $folder_path ) : $preferred_name;
		$name     = $this->sanitize_folder_name( $raw_name );

		if ( '' === $name ) {
			$this->rmdir( $tmp_dir );
			return new WP_Error( 'fw_sc', __( 'Could not derive a valid name for the shortcode.', 'fw' ) );
		}

		$tag = str_replace( '-', '_', $name );

		// Collision: don't shadow an existing shortcode (core or installed).
		if ( array_key_exists( $tag, $this->extension->discover_all_shortcodes() ) ) {
			$this->rmdir( $tmp_dir );
			return new WP_Error( 'fw_sc', sprintf(
				/* translators: %s: shortcode tag */
				__( 'A shortcode named "%s" already exists.', 'fw' ),
				$tag
			) );
		}

		$user_dir = FW_Extension_Shortcodes::get_user_shortcodes_dir();
		if ( ! $user_dir ) {
			$this->rmdir( $tmp_dir );
			return new WP_Error( 'fw_sc', __( 'Uploads directory is not available.', 'fw' ) );
		}
		wp_mkdir_p( $user_dir['path'] );

		$destination = wp_normalize_path( $user_dir['path'] . '/' . $name );

		$copy = copy_dir( $folder_path, $destination );
		if ( is_wp_error( $copy ) ) {
			$this->rmdir( $tmp_dir );
			return $copy;
		}

		// Record the source for the badge.
		@file_put_contents( $destination . '/.fw-source', $source );

		$this->rmdir( $tmp_dir );

		// New installs are enabled. If the option exists, add the tag explicitly
		// (otherwise our "disabled = all - enabled" filter would hide it).
		$enabled = fw_get_db_ext_settings_option( $this->extension->get_name(), self::OPTION, null );
		if ( is_array( $enabled ) ) {
			$enabled[] = $tag;
			fw_set_db_ext_settings_option(
				$this->extension->get_name(),
				self::OPTION,
				array_values( array_unique( $enabled ) )
			);
		}

		$title = fw_akg( 'page_builder/title', $cfg_vars['cfg'] );

		return array(
			'tag'    => $tag,
			'name'   => $name,
			'source' => $source,
			'title'  => $title ? $title : ucwords( str_replace( array( '_', '-' ), ' ', $tag ) ),
		);
	}

	/**
	 * Find the shortcode folder inside an extracted archive.
	 * Handles both a directly-zipped folder and the GitHub "repo-branch/" wrapper.
	 *
	 * @return array array($path|null, $descended_bool)
	 */
	private function locate_shortcode_folder( $root ) {
		// Case A: the archive root IS the shortcode folder.
		if ( file_exists( $root . '/config.php' ) ) {
			return array( $root, false );
		}

		$top = glob( $root . '/*', GLOB_ONLYDIR );
		if ( empty( $top ) ) {
			return array( null, false );
		}

		// Case B: a single wrapper dir (zipped folder, or GitHub "repo-branch/").
		foreach ( $top as $dir ) {
			// B1: the wrapper itself is the shortcode (files in repo root).
			if ( file_exists( $dir . '/config.php' ) ) {
				return array( $dir, false );
			}
		}

		// C: descend one level — the shortcode lives in a subfolder of the wrapper.
		foreach ( $top as $dir ) {
			$sub = glob( $dir . '/*', GLOB_ONLYDIR );
			if ( empty( $sub ) ) {
				continue;
			}
			foreach ( $sub as $candidate ) {
				if ( file_exists( $candidate . '/config.php' ) ) {
					return array( $candidate, true );
				}
			}
		}

		return array( null, false );
	}

	private function sanitize_folder_name( $name ) {
		$name = strtolower( (string) $name );
		$name = preg_replace( '/\.git$/', '', $name );
		$name = preg_replace( '/[^a-z0-9-]+/', '-', $name );
		$name = trim( $name, '-' );
		return $name;
	}

	/**
	 * Parse owner/repo from a GitHub URL or "owner/repo" string.
	 *
	 * @return array|null array('owner','repo','user_repo')
	 */
	private function parse_github_repo( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url ) {
			return null;
		}

		// Plain "owner/repo".
		if ( preg_match( '#^([\w.-]+)/([\w.-]+)$#', $url, $m ) ) {
			$owner = $m[1];
			$repo  = preg_replace( '/\.git$/', '', $m[2] );
		} else {
			$host = wp_parse_url( $url, PHP_URL_HOST );
			if ( 'github.com' !== strtolower( (string) $host ) ) {
				return null;
			}
			$path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
			$parts = explode( '/', $path );
			if ( count( $parts ) < 2 || '' === $parts[0] || '' === $parts[1] ) {
				return null;
			}
			$owner = $parts[0];
			$repo  = preg_replace( '/\.git$/', '', $parts[1] );
		}

		if ( '' === $owner || '' === $repo ) {
			return null;
		}

		return array(
			'owner'     => $owner,
			'repo'      => $repo,
			'user_repo' => $owner . '/' . $repo,
		);
	}

	/**
	 * Resolve a downloadable .zip URL for a GitHub repo: latest release if any,
	 * otherwise the default-branch archive.
	 *
	 * @return string|WP_Error
	 */
	private function resolve_github_zip_url( $repo ) {
		$api_args = array(
			'timeout' => 25,
			'headers' => array( 'Accept' => 'application/vnd.github+json', 'User-Agent' => 'UnysonPlus' ),
		);

		// Try latest release.
		$release = wp_remote_get( 'https://api.github.com/repos/' . $repo['user_repo'] . '/releases/latest', $api_args );
		if ( ! is_wp_error( $release ) && 200 === (int) wp_remote_retrieve_response_code( $release ) ) {
			$body = json_decode( wp_remote_retrieve_body( $release ), true );
			if ( ! empty( $body['tag_name'] ) ) {
				return 'https://github.com/' . $repo['user_repo'] . '/archive/refs/tags/' . $body['tag_name'] . '.zip';
			}
		}

		// Fall back to the repo's default branch.
		$info = wp_remote_get( 'https://api.github.com/repos/' . $repo['user_repo'], $api_args );
		if ( is_wp_error( $info ) ) {
			return $info;
		}
		$code = (int) wp_remote_retrieve_response_code( $info );
		if ( 404 === $code ) {
			return new WP_Error( 'fw_sc', __( 'Repository not found (it may be private or misspelled).', 'fw' ) );
		}
		if ( 200 !== $code ) {
			$branch = 'main';
		} else {
			$body   = json_decode( wp_remote_retrieve_body( $info ), true );
			$branch = ! empty( $body['default_branch'] ) ? $body['default_branch'] : 'main';
		}

		return 'https://github.com/' . $repo['user_repo'] . '/archive/refs/heads/' . $branch . '.zip';
	}

	private function respond_install( $result ) {
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array(
			'tag'    => $result['tag'],
			'title'  => $result['title'],
			'source' => $result['source'],
			'badge'  => $this->source_label( $result['source'] ),
		) );
	}

	/**
	 * Recursively remove a directory (best effort, real paths).
	 */
	private function rmdir( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;
		if ( $wp_filesystem ) {
			$wp_filesystem->delete( wp_normalize_path( $dir ), true );
		}
	}
}
