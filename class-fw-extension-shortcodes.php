<?php if (!defined('FW')) die('Forbidden');

class FW_Extension_Shortcodes extends FW_Extension
{
	/**
	 * @var FW_Shortcode[]
	 */
	private $shortcodes;

	/**
	 * @var FW_Ext_Shortcodes_Attr_Coder[]
	 */
	private $coders = array();

	/**
	 * Gets a certain shortcode by a given tag
	 *
	 * @param string $tag The shortcode tag
	 * @return FW_Shortcode|null
	 */
	public function get_shortcode($tag)
	{
		$this->load_shortcodes();
		return isset($this->shortcodes[$tag]) ? $this->shortcodes[$tag] : null;
	}

	/**
	 * Gets all shortcodes
	 *
	 * @return FW_Shortcode[]
	 */
	public function get_shortcodes()
	{
		$this->load_shortcodes();
		return $this->shortcodes;
	}

	/**
	 * @internal
	 */
	protected function _init()
	{
		add_action('fw_extensions_init', array($this, '_action_fw_extensions_init'));
		add_action('init', array($this, '_action_init'),
			11 // register shortcodes later than other plugins (there were some problems with the `column` shortcode)
		);

		/**
		 * We need aggressive only for wp-editor, at least for now.
		 * https://github.com/ThemeFuse/Unyson/issues/1807#issuecomment-235243578
		 */
		add_action(
			'wp_enqueue_editor',
			array($this, '_action_editor_shortcodes')
		);

		// Process the shortcodes and enqueue their assets in page <head>
		add_action(
			'wp_enqueue_scripts',
			array($this, '_action_enqueue_shortcodes_static_in_frontend_head'),
			/**
			 * Enqueue later than theme styles
			 * https://github.com/ThemeFuse/Theme-Includes/blob/b1467714c8a3125f077f1251f01ba6d6ca38640f/init.php#L41
			 * to be able to wp_add_inline_style('theme-style-handle', ...) in 'fw_ext_shortcodes_enqueue_static:{name}' action
			 * http://manual.unyson.io/en/latest/extension/shortcodes/index.html#enqueue-shortcode-dynamic-css-in-page-head
			 * in case the shortcode doesn't have a style, needed in step 3.
			 */
			30
		);

		if( is_admin() && defined('DOING_AJAX') && DOING_AJAX ) {
			add_filter( 'fw_ext:shortcodes:collect_shortcodes_data', array(
				$this, 'add_simple_shortcodes_data_to_filter'
			) );
		}

		add_action(
			'admin_enqueue_scripts',
			array($this, 'enqueue_admin_scripts')
		);

		add_action(
			'wp_ajax_fw_ext_wp_shortcodes_data',
			array($this, 'send_wp_shortcodes_data')
		);

		// Resolve Dynamic Content {{tokens}} in shortcode/page-builder atts at render time.
		require_once dirname( __FILE__ ) . '/includes/dynamic-content-resolver.php';

		// Feed the disable filter from the saved settings (Shortcodes settings page).
		add_filter(
			'fw_ext_shortcodes_disable_shortcodes',
			array( $this, '_filter_disabled_from_settings' )
		);

		// Admin settings page: enable/disable list + zip/GitHub install.
		if ( is_admin() ) {
			require_once dirname( __FILE__ ) . '/includes/class-fw-shortcodes-settings-page.php';
			new FW_Ext_Shortcodes_Settings_Page( $this );
		}
	}

	/**
	 * Directory (outside the plugin tree) where user-installed shortcodes live.
	 * Survives plugin updates. Scanned by _FW_Shortcodes_Loader::load_core_shortcodes().
	 *
	 * @return array|null array('path' => ..., 'uri' => ...) or null if uploads unavailable.
	 */
	public static function get_user_shortcodes_dir()
	{
		$upload = wp_upload_dir();

		if ( ! empty( $upload['error'] ) ) {
			return null;
		}

		return array(
			'path' => wp_normalize_path( $upload['basedir'] . '/unysonplus-shortcodes' ),
			'uri'  => $upload['baseurl'] . '/unysonplus-shortcodes',
		);
	}

	/**
	 * Discover ALL shortcodes (core + user-installed) WITHOUT applying the disable
	 * filter. Used by the settings page (to show disabled ones too) and by the
	 * disable filter itself (to compute disabled = all - enabled).
	 *
	 * Lightweight: globs folders and reads config.php, never instantiates a shortcode.
	 *
	 * @return array tag => array('tag','title','source','icon','deletable','dir')
	 */
	public function discover_all_shortcodes()
	{
		$result = array();

		// Core (bundled) shortcodes.
		$core_path = $this->get_path( '/shortcodes' );
		$core_uri  = $this->get_uri( '/shortcodes' );
		foreach ( $this->_scan_shortcode_folder( $core_path, $core_uri, 'core', false ) as $tag => $meta ) {
			$result[ $tag ] = $meta;
		}

		// User-installed shortcodes (uploads dir).
		$user_dir = self::get_user_shortcodes_dir();
		if ( $user_dir && file_exists( $user_dir['path'] ) ) {
			foreach ( $this->_scan_shortcode_folder( $user_dir['path'], $user_dir['uri'], 'uploaded', true ) as $tag => $meta ) {
				// A core tag of the same name wins (matches loader behaviour).
				if ( ! isset( $result[ $tag ] ) ) {
					$result[ $tag ] = $meta;
				}
			}
		}

		return $result;
	}

	/**
	 * @internal
	 * @param string $path
	 * @param string $uri
	 * @param string $default_source 'core' | 'uploaded'
	 * @param bool   $deletable
	 * @return array tag => meta
	 */
	private function _scan_shortcode_folder( $path, $uri, $default_source, $deletable )
	{
		$out = array();

		if ( ! file_exists( $path ) ) {
			return $out;
		}

		$dirs = glob( $path . '/*', GLOB_ONLYDIR );
		if ( empty( $dirs ) ) {
			return $out;
		}

		foreach ( $dirs as $dir ) {
			$folder = strtolower( basename( $dir ) );
			$tag    = str_replace( '-', '_', $folder );

			// A valid shortcode folder must at least declare config.php.
			if ( ! file_exists( $dir . '/config.php' ) ) {
				continue;
			}

			$title = $this->_humanize_tag( $tag );
			$vars  = fw_get_variables_from_file( $dir . '/config.php', array( 'cfg' => null ) );
			if ( ! empty( $vars['cfg'] ) ) {
				$cfg_title = fw_akg( 'page_builder/title', $vars['cfg'] );
				if ( $cfg_title ) {
					$title = $cfg_title;
				}
			}

			$source = $default_source;
			if ( $deletable && file_exists( $dir . '/.fw-source' ) ) {
				$marker = trim( (string) @file_get_contents( $dir . '/.fw-source' ) );
				if ( in_array( $marker, array( 'zip', 'github', 'uploaded' ), true ) ) {
					$source = $marker;
				}
			}

			$out[ $tag ] = array(
				'tag'       => $tag,
				'title'     => $title,
				'source'    => $source,
				// Only inline SVG for bundled (trusted) shortcodes; for user-installed
				// ones reference the file via <img> so a malicious SVG can't inject script.
				'icon'      => $this->_locate_folder_icon( $dir, $uri . '/' . $folder, ! $deletable ),
				'deletable' => $deletable,
				'dir'       => $folder,
			);
		}

		return $out;
	}

	/**
	 * Inline SVG (or <img> for PNG) for the shortcode's page-builder icon, by folder.
	 * Mirrors _locate_shortcode_icon() but without needing a FW_Shortcode instance.
	 *
	 * @internal
	 */
	private function _locate_folder_icon( $dir, $uri, $allow_inline_svg = true )
	{
		if ( file_exists( $dir . '/static/img/page_builder.svg' ) ) {
			if ( $allow_inline_svg ) {
				return trim( (string) @file_get_contents( $dir . '/static/img/page_builder.svg' ) );
			}
			return '<img src="' . esc_url( $uri . '/static/img/page_builder.svg' ) . '" alt="" />';
		}

		if ( file_exists( $dir . '/static/img/page_builder.png' ) ) {
			return '<img src="' . esc_url( $uri . '/static/img/page_builder.png' ) . '" alt="" />';
		}

		return '';
	}

	/**
	 * @internal
	 */
	private function _humanize_tag( $tag )
	{
		return ucwords( str_replace( array( '_', '-' ), ' ', $tag ) );
	}

	/**
	 * Build the disabled-tags list from the saved "enabled_shortcodes" setting.
	 * We store the ENABLED set, so anything discovered but not in it is disabled.
	 * If the option was never saved (null) nothing is disabled (all on by default).
	 *
	 * @internal
	 * @param array $disabled
	 * @return array
	 */
	public function _filter_disabled_from_settings( $disabled )
	{
		// Read RAW (not via fw_get_db_ext_settings_option). This runs on every
		// request; the latter would load + process this extension's full settings
		// schema (the preset libraries) just to read one key — firing
		// fw_option_types_init too early (before the page-builder shortcodes
		// register their custom option types, which broke the Table editor) and
		// dropping this non-schema key during option processing.
		$enabled = class_exists( 'FW_WP_Option' )
			? FW_WP_Option::get( 'fw_ext_settings_options:' . $this->get_name(), 'enabled_shortcodes', null )
			: null;

		if ( ! is_array( $enabled ) ) {
			return $disabled; // never configured -> keep everything enabled
		}

		$all            = array_keys( $this->discover_all_shortcodes() );
		$newly_disabled = array_diff( $all, $enabled );

		return array_values( array_unique( array_merge( (array) $disabled, $newly_disabled ) ) );
	}

	/**
	 * @since 1.3.21
	 */
	public function collect_shortcodes_data() {
		$structure = array();
		$structure = apply_filters( 'fw_ext:shortcodes:collect_shortcodes_data', $structure );
		return $structure;
	}

	public function enqueue_admin_scripts() {
		if (! is_admin()) return;

		wp_register_script(
			'fw-ext-shortcodes-editor-integration',
			fw_ext('shortcodes')->get_uri('/static/js/aggressive-coder.js'),
			array('fw'),
			fw_ext('shortcodes')->manifest->get('version'),
			true
		);

		wp_enqueue_script(
			'fw-ext-shortcodes-load-shortcodes-data',
			fw_ext('shortcodes')->get_uri('/static/js/load-shortcodes-data.js'),
			array('fw'),
			fw_ext('shortcodes')->manifest->get('version'),
			true
		);
	}

	/**
	 * @since 1.3.19
	 */
	public function send_wp_shortcodes_data() {
		wp_send_json_success(
			$this->collect_shortcodes_data()
		);
	}

	/**
	 * @since 1.3.19
	 */
	public function build_shortcodes_list() {
		$shortcodes = array_values( fw_ext('shortcodes')->get_shortcodes() );

		$shortcodes = array_map(
			array($this, '_parse_single_shortcode'),
			$shortcodes
		);

		return $shortcodes;
	}

	/**
	 * @since 1.3.19
	 */
	public function _parse_single_shortcode( $shortcode ) {
		$result = array();

		$icon = $this->_locate_shortcode_icon($shortcode);

		if ($icon) {
			$result['icon'] = $icon;
		}

		$result['options'] = $shortcode->get_options();
		$result['config'] = $shortcode->get_config();
		$result['tag'] = $shortcode->get_tag();

		if ($result['options']) {
			$result['default_values'] = fw_get_options_values_from_input(
				$result['options'],
				array()
			);
		}

		$title = $shortcode->get_config('page_builder/title');
		$result['title'] = $title ? $title : $result['tag'];

		return $result;
	}

	/**
	 * @internal
	 */
	public function _action_fw_extensions_init()
	{
		$this->load_shortcodes();
	}

	public function _action_editor_shortcodes()
	{
		wp_enqueue_script('fw-ext-shortcodes-editor-integration');
	}

	public function _action_init() {
		$this->register_shortcodes();
	}

	public function load_shortcodes()
	{
		static $is_loading = false; // prevent recursion

		if ($is_loading) {
			trigger_error('Recursive shortcodes load', E_USER_WARNING);
			return;
		}

		if ($this->shortcodes) {
			return;
		}

		$is_loading = true;

		$disabled_shortcodes = apply_filters('fw_ext_shortcodes_disable_shortcodes', array());
		$this->shortcodes    = _FW_Shortcodes_Loader::load(array(
			'disabled_shortcodes' => $disabled_shortcodes
		));

		$is_loading = false;
	}

	private function register_shortcodes()
	{
		foreach ($this->shortcodes as $tag => $instance) {
			add_shortcode($tag, array($instance, 'render'));
		}
	}

	/**
	 * Make sure to enqueue shortcodes static in <head> (not in <body>)
	 * @internal
	 */
	public function _action_enqueue_shortcodes_static_in_frontend_head()
	{
		do_action('fw:ext:shortcodes:enqueue_custom_content');

		/** @var WP_Post $post */
		global $post;

		if (!$post) {
			return;
		}

		/**
		* @since 1.3.26
		*/
		do_action(
			'fw:ext:shortcodes:enqueue_shortcodes_static:before',
			$post->post_content
		);
		
		$this->enqueue_shortcodes_static($post->post_content);
		
		/**
		* @since 1.3.26
		*/
		do_action(
			'fw:ext:shortcodes:enqueue_shortcodes_static:after',
			$post->post_content
		);
	}

	/**
	 * @see fw_ext_shortcodes_enqueue_shortcodes_static()
	 * @param string $content
	 */
	public function enqueue_shortcodes_static( $content ) {
		preg_replace_callback( '/'. get_shortcode_regex() .'/s', array( $this, 'enqueue_shortcode_static'), $content );
	}

	private function enqueue_shortcode_static( $shortcode ) {
		/**
		 * Remember the enqueued shortcodes and prevent enqueue static multiple times.
		 * There is no sense to call enqueue_static() multiple times
		 * because there is no dynamic data passed to it.
		 */
		static $enqueued_shortcodes = array();

		// allow [[foo]] syntax for escaping a tag
		if ( $shortcode[1] == '[' && $shortcode[6] == ']' ) {
			return;
		}

		$tag = $shortcode[2];

		if ( ! is_null( $this->get_shortcode( $tag ) ) ) {
			if (!isset($enqueued_shortcodes[$tag])) {
				$this->get_shortcode($tag)->_enqueue_static();
				$enqueued_shortcodes[$tag] = true;
			}

			/** @var WP_Post $post */
			global $post;

			/**
			 * @since 1.3.26
			 */
			do_action('fw_ext_shortcodes_enqueue_static_before', array(
				'tag' => $tag,
				'raw_shortcode' => $shortcode,
				'atts_string' => $shortcode[3],
				'post' => $post
			));

			do_action('fw_ext_shortcodes_enqueue_static:'. $tag, array(
				/**
				 * Transform to array:
				 * $attr = shortcode_parse_atts( $data['atts_string'] );
				 *
				 * By default it's not transformed, but sent as raw string,
				 * to prevent useless computation for every shortcode,
				 * because this action may be used very rare and only for a specific shortcode.
				 */
				'atts_string' => $shortcode[3],
				'post' => $post,
			));

			$this->enqueue_shortcodes_static($shortcode[5]); // inner shortcodes

			/**
			 * @since 1.3.18
			 */
			do_action(
				'fw_ext_shortcodes:after_shortcode_enqueue_static',
				$shortcode
			);
		}
	}

	/**
	 * @param string $coder_id
	 * @return null|FW_Ext_Shortcodes_Attr_Coder|FW_Ext_Shortcodes_Attr_Coder[]
	 */
	public function get_attr_coder($coder_id = null)
	{
		if (empty($this->coders)) {
			if (!class_exists('FW_Ext_Shortcodes_Attr_Coder')) {
				require_once dirname(__FILE__) . '/includes/coder/interface-fw-ext-shortcodes-attr-coder.php';
			}

			if (!class_exists('FW_Ext_Shortcodes_Attr_Coder_JSON')) {
				require_once dirname(__FILE__) . '/includes/coder/class-fw-ext-shortcodes-attr-coder-json.php';
			}

			if (!class_exists('FW_Ext_Shortcodes_Attr_Coder_Aggressive')) {
				require_once dirname(__FILE__) . '/includes/coder/class-fw-ext-shortcodes-attr-coder-aggressive.php';
			}

			$coder_json = new FW_Ext_Shortcodes_Attr_Coder_JSON();
			$this->coders[ $coder_json->get_id() ] = $coder_json;

			$coder_aggressive = new FW_Ext_Shortcodes_Attr_Coder_Aggressive();
			$this->coders[ $coder_aggressive->get_id() ] = $coder_aggressive;

			if (!class_exists('FW_Ext_Shortcodes_Attr_Coder_Post_Meta')) {
				require_once dirname(__FILE__) . '/includes/coder/class-fw-ext-shortcodes-attr-coder-post-meta.php';
			}
			$coder_post_meta = new FW_Ext_Shortcodes_Attr_Coder_Post_Meta();
			$this->coders[ $coder_post_meta->get_id() ] = $coder_post_meta;

			foreach (apply_filters('fw_ext_shortcodes_coders', array()) as $coder) {
				if (!($coder instanceof FW_Ext_Shortcodes_Attr_Coder)) {
					trigger_error(get_class($coder) .' must implement FW_Ext_Shortcodes_Attr_Coder', E_USER_WARNING);
					continue;
				}

				if (isset($this->coders[ $coder->get_id() ])) {
					trigger_error('Coder id='. $coder->get_id() .' is already defined', E_USER_WARNING);
					continue;
				}

				$this->coders[ $coder->get_id() ] = $coder;
			}
		}

		if (is_null($coder_id)) {
			return $this->coders;
		} else {
			if (isset($this->coders[$coder_id])) {
				return $this->coders[$coder_id];
			} else {
				return null;
			}
		}
	}

	/**
	 * @since 1.3.21
	 */
	public function get_builder_data()
	{
		try {
			return FW_Cache::get($cache_key = 'fw:ext:shortcodes:builder-data');
		} catch (FW_Cache_Not_Found_Exception $e) {
			$builder_data = array();

			foreach ($this->get_shortcodes() as $tag => $shortcode) {
				if ($item_data = $this->get_shortcode_builder_data($tag)) {
					$builder_data[$tag] = $item_data;
				}
			}

			FW_Cache::set($cache_key, $builder_data);

			return $builder_data;
		}
	}

	/**
	 * @since 1.3.21
	 * @param string $tag
	 * @return array|null
	 */
	public function get_shortcode_builder_data($tag) {
		try {
			return FW_Cache::get(
				/** the same cache key as @see get_builder_data() */
				$cache_key = 'fw:ext:shortcodes:builder-data/'. $tag
			);
		} catch (FW_Cache_Not_Found_Exception $e) {
			if (!(
				($shortcode = $this->get_shortcode($tag))
				&&
				($config = $shortcode->get_config('page_builder'))
				&&
				($config = array_merge(array('type' => 'simple'), $config))
				&&
				$config['type'] === 'simple' // check if the shortcode type is valid
			)) {
				return;
			}

			if (!isset($config['tab'])) {
				trigger_error(
					sprintf(__("No Page Builder tab specified for shortcode: %s", 'fw'), $tag),
					E_USER_WARNING
				);
			}

			$item_data = array_merge(
				array(
					'tab'            => '~',
					'title'          => $tag,
					'tag'            => $tag,
					'description'    => '',
					'localize'       => array(
						'edit'      => __( 'Edit', 'fw' ),
						'remove'    => __( 'Remove', 'fw' ),
						'duplicate' => __( 'Duplicate', 'fw' ),
					),
					'icon'           => null,
					'title_template' => null,
					'popup_size'     => 'small'
				),
				apply_filters( 'fw_ext:shortcodes:config_shortcode', $config, $tag )
			);

			if (
				!isset($item_data['icon'])
				&&
				($icon = $this->_locate_shortcode_icon($shortcode))
			) {
				$item_data['icon'] = $icon;
			}

			// if the shortcode has options we store them and then they are passed to the modal
			if ($options = $shortcode->get_options()) {
				$item_data['options'] = $this->transform_options($options);
				$item_data['default_values'] = fw_get_options_values_from_input(
					$options, array()
				);
			}

			FW_Cache::set($cache_key, $item_data);

			return $item_data;
		}
	}

	public function _locate_shortcode_icon($shortcode) {
		$maybe_svg = $shortcode->locate_path('/static/img/page_builder.svg');

		if (! $maybe_svg) {
			$maybe_png = $shortcode->locate_URI('/static/img/page_builder.png');
			return $maybe_png;
		}

		/**
		 * Put SVG inline, do not wrap it into an <img> tag.
		 */
		return trim(fw_render_view($maybe_svg));
	}

	public function add_simple_shortcodes_data_to_filter( $structure ) {
		return array_merge( $structure, $this->get_builder_data() );
	}

	/*
	 * Puts each option into a separate array
	 * to keep it's order inside the modal dialog
	 */
	private function transform_options($options)
	{
		$new_options = array();
		foreach ($options as $id => $option) {
			if (is_int($id)) {
				/**
				 * this happens when in options array are loaded external options using fw()->theme->get_options()
				 * and the array looks like this
				 * array(
				 *    'hello' => array('type' => 'text'), // this has string key
				 *    array('hi' => array('type' => 'text')) // this has int key
				 * )
				 */
				$new_options[] = $option;
			} else {
				$new_options[] = array($id => $option);
			}
		}
		return $new_options;
	}
}
