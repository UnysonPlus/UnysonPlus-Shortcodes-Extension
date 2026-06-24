<?php if (!defined('FW')) die('Forbidden');

class FW_Ext_Builder_Templates_Component_Section extends FW_Ext_Builder_Templates_Component
{
	public function get_type()
	{
		return 'section';
	}

	public function get_title()
	{
		return __('Sections', 'fw');
	}

	public function _render($data)
	{
		if ($data['builder_type'] !== 'page-builder') {
			return;
		}

		if (version_compare(fw_ext('builder')->manifest->get_version(), '1.1.14', '<')) {
			// some important changes were added in Builder v1.1.14
			return;
		}

		$templates = $this->get_templates($data['builder_type']);

		{
			$this->fake_created_value = 0;

			$templates = array_map( // make this to keep elements order after applying uasort()
				array($this, 'array_map_add_fake_created_key'),
				$templates
			);
		}

		uasort($templates, array($this, 'sort_templates'));

		$html = '';

		foreach ($templates as $template_id => $template) {
			if (isset($template['type']) && $template['type'] === 'predefined') {
				$delete_btn = '';
			} else {
				$delete_btn = '<a href="#" onclick="return false;" data-delete-template="'. fw_htmlspecialchars($template_id) .'"'
				              . ' class="template-delete dashicons fw-x" title="'. esc_attr__('Delete', 'fw') .'"></a>';
			}

			$export_btn = '<a href="#" onclick="return false;" data-export-template="'. fw_htmlspecialchars($template_id) .'"'
			              . ' class="template-export dashicons dashicons-download" title="'. esc_attr__('Export', 'fw') .'"></a>';

			$html .=
				'<li>'
					. $delete_btn
					. $export_btn
					. '<a href="#" onclick="return false;" data-load-template="'. fw_htmlspecialchars($template_id) .'"'
						. ' class="template-title">'
						. fw_htmlspecialchars($template['title'])
					. '</a>'
				. '</li>';
		}

		// Append Global Templates (synced) — published `snippet` posts tagged as
		// section-kind globals. Clicking one inserts a [global_section] REFERENCE
		// (handled in scripts.js), not a copy, so editing the snippet syncs every
		// page. Marked "(Global)" with a violet dot to read apart from local copies.
		$html .= $this->get_global_templates_html();

		if (empty($html)) {
			$html = '<div class="fw-text-muted">'. __('No Templates Saved', 'fw') .'</div>';
		} else {
			$html =
				'<p class="fw-text-muted load-template-title">'. __('Load Template', 'fw') .':</p>'
				. '<ul class="std">'. $html .'</ul>';
		}

		$html =
			'<div class="save-template-wrapper import-only">'
				. '<a href="#" onclick="return false;" class="import-template button button-secondary">'
					. __('Import Section…', 'fw')
				. '</a>'
				. '<input type="file" class="template-import-file fw-hidden" accept="application/json,.json" />'
			. '</div>'
			. $html;

		return $html;
	}

	/**
	 * `<li>` entries for Section-kind Global Templates (published `snippet` posts
	 * tagged `_fw_global_kind = section`). Each carries `data-load-global-section`
	 * = the snippet ID; scripts.js inserts a [global_section] reference (synced),
	 * not a content copy. Returns '' when snippets is inactive or none exist.
	 */
	private function get_global_templates_html()
	{
		if ( ! fw_ext( 'snippets' ) ) {
			return '';
		}

		$snippets = get_posts( array(
			'post_type'        => 'snippet',
			'post_status'      => 'publish',
			'numberposts'      => -1,
			'orderby'          => 'title',
			'order'            => 'ASC',
			'suppress_filters' => false,
			'meta_key'         => '_fw_global_kind',
			'meta_value'       => 'section',
		) );

		if ( empty( $snippets ) ) {
			return '';
		}

		$html = '';

		foreach ( $snippets as $snippet ) {
			$title = $snippet->post_title !== '' ? $snippet->post_title : ( '#' . $snippet->ID );

			$html .=
				'<li class="template-global">'
					. '<a href="#" onclick="return false;" data-delete-global="'. (int) $snippet->ID .'"'
						. ' class="template-delete dashicons fw-x" title="'. esc_attr__( 'Delete', 'fw' ) .'"></a>'
					. '<a href="#" onclick="return false;" data-load-global-section="'. (int) $snippet->ID .'"'
						. ' class="template-title">'
						. fw_htmlspecialchars( $title )
						. ' <span class="template-global-tag">'. esc_html__( '(Global)', 'fw' ) .'</span>'
					. '</a>'
				. '</li>';
		}

		return $html;
	}

	public function _enqueue($data)
	{
		if ($data['builder_type'] !== 'page-builder') {
			return;
		}

		if (version_compare(fw_ext('builder')->manifest->get_version(), '1.1.14', '<')) {
			// some important changes were added in Builder v1.1.14
			return;
		}

		$uri = fw_ext('shortcodes')->get_uri('/shortcodes/'. $this->get_type() .'/includes/template-component');
		$version = fw_ext('shortcodes')->manifest->get_version();

		wp_enqueue_style(
			'fw-option-builder-templates-'. $this->get_type(),
			$uri .'/styles.css',
			array('fw-option-builder-templates'),
			$version
		);

		wp_enqueue_script(
			'fw-option-builder-templates-'. $this->get_type(),
			$uri .'/scripts.js',
			array('fw-option-builder-templates'),
			$version,
			true
		);

		wp_localize_script(
			'fw-option-builder-templates-'. $this->get_type(),
			'_fw_option_type_builder_templates_'. $this->get_type(),
			array(
				// Global Templates are stored as `snippet` posts, so the switch only
				// makes sense (and is only shown) when the snippets extension is active.
				'globalTemplatesEnabled'     => (bool) fw_ext('snippets'),
				'globalTemplatesNonce'       => wp_create_nonce('fw_builder_global_template_save'),
				'globalTemplatesDeleteNonce' => wp_create_nonce('fw_global_template_delete'),
				'l10n' => array(
					'template_name'         => __('Template Name', 'fw'),
					'save_template'         => __('Save Section', 'fw'),
					'save_template_tooltip' => __('Save as Template', 'fw'),
					'import_failed'         => __('Failed to import template', 'fw'),
					'import_not_json'       => __('That file is not a valid JSON file', 'fw'),
					'import_no_file'        => __('Please choose a file to import', 'fw'),
					'save_as_global_label'  => __('Save as Global Template', 'fw'),
					'save_as_global_desc'   => __('Store this section as a reusable, synced Global Template. Drop it on any page from Templates → Sections (Global) — editing it updates every page that uses it.', 'fw'),
					'global_save_failed'    => __('Failed to save global template', 'fw'),
					'global_saved'          => __('Saved as Global Template. Insert it from Templates → Sections (Global).', 'fw'),
					'global_delete_confirm' => __('Move this Global Template to Trash? Pages using it will stop showing it until restored.', 'fw'),
					'global_delete_failed'  => __('Failed to delete global template', 'fw'),
				),
			)
		);
	}

	public function _init()
	{
		add_action('wp_ajax_fw_builder_templates_'. $this->get_type() .'_load',   array($this, '_action_ajax_load_template'));
		add_action('wp_ajax_fw_builder_templates_'. $this->get_type() .'_save',   array($this, '_action_ajax_save_template'));
		add_action('wp_ajax_fw_builder_templates_'. $this->get_type() .'_delete', array($this, '_action_ajax_delete_template'));
		add_action('wp_ajax_fw_builder_templates_'. $this->get_type() .'_export', array($this, '_action_ajax_export_template'));
		add_action('wp_ajax_fw_builder_templates_'. $this->get_type() .'_import', array($this, '_action_ajax_import_template'));
	}

	private function get_templates($builder_type)
	{
		return $this->get_db_templates($builder_type) + $this->get_predefined_templates($builder_type);
	}

	/**
	 * @internal
	 */
	public function _action_ajax_load_template()
	{
		if (!current_user_can('edit_posts')) {
			wp_send_json_error();
		}

		$builder_type = (string)FW_Request::POST('builder_type');

		if (!$this->builder_type_is_valid($builder_type)) {
			wp_send_json_error();
		}

		$templates = $this->get_templates($builder_type);

		$template_id = (string)FW_Request::POST('template_id');

		if (!isset($templates[$template_id])) {
			wp_send_json_error();
		}

		wp_send_json_success(array(
			'json' => $templates[$template_id]['json']
		));
	}

	/**
	 * @internal
	 */
	public function _action_ajax_save_template()
	{
		if (!current_user_can('edit_posts')) {
			wp_send_json_error();
		}

		$builder_type = (string)FW_Request::POST('builder_type');

		if (!$this->builder_type_is_valid($builder_type)) {
			wp_send_json_error();
		}

		$template = array(
			'title' => trim((string)FW_Request::POST('template_name')),
			'json' => trim((string)FW_Request::POST($this->get_type() .'_json')),
			'created' => time(),
		);

		if (
			empty($template['json'])
			||
			($decoded_json = json_decode($template['json'], true)) === null
			||
			!isset($decoded_json['type'])
			||
			!$this->is_acceptable_inner_type($decoded_json['type'])
		) {
			wp_send_json_error();
		}

		unset($decoded_json);

		if (empty($template['title'])) {
			$template['title'] = __('No Title', 'fw');
		}

		$template_id = md5($template['json']);

		update_option(
			$this->get_wp_option_prefix($builder_type) . $template_id,
			$template,
			false
		);

		/**
		 * Remove from old storage (to prevent array key merge with old value on get)
		 */
		{
			$old_templates = fw_get_db_extension_data('builder', 'templates:'. $this->get_type() .'/'. $builder_type, array());

			unset($old_templates[$template_id]);

			fw_set_db_extension_data('builder', 'templates:'. $this->get_type() .'/'. $builder_type, $old_templates);

			unset($old_templates);
		}

		wp_send_json_success();
	}

	/**
	 * @internal
	 */
	public function _action_ajax_delete_template()
	{
		if (!current_user_can('edit_posts')) {
			wp_send_json_error();
		}

		$builder_type = (string)FW_Request::POST('builder_type');

		if (!$this->builder_type_is_valid($builder_type)) {
			wp_send_json_error();
		}

		$template_id = (string)FW_Request::POST('template_id');

		delete_option($this->get_wp_option_prefix($builder_type) . $template_id);

		/**
		 * Remove from old storage (to prevent array key merge with old value on get)
		 */
		{
			$old_templates = fw_get_db_extension_data('builder', 'templates:'. $this->get_type() .'/'. $builder_type, array());

			unset($old_templates[$template_id]);

			fw_set_db_extension_data('builder', 'templates:'. $this->get_type() .'/'. $builder_type, $old_templates);

			unset($old_templates);
		}

		wp_send_json_success();
	}

	/**
	 * Wrap a saved section template in the portable export envelope. See
	 * the Full component's matching handler for the rationale; the only
	 * differences here are the `kind` field (`section`) and the absence
	 * of `check_ajax_referer` (matches this component's existing
	 * save / load / delete handlers — they do not use a nonce).
	 *
	 * @internal
	 */
	public function _action_ajax_export_template()
	{
		if (!current_user_can('edit_posts')) {
			wp_send_json_error();
		}

		$builder_type = (string)FW_Request::POST('builder_type');

		if (!$this->builder_type_is_valid($builder_type)) {
			wp_send_json_error();
		}

		$templates = $this->get_templates($builder_type);

		$template_id = (string)FW_Request::POST('template_id');

		if (!isset($templates[$template_id])) {
			wp_send_json_error();
		}

		$template = $templates[$template_id];

		$envelope = array(
			'_fw_template_export' => array(
				'format_version' => 2, // v2: per-element Custom CSS travels inside `json`
				'kind'           => $this->get_type(),
				'builder_type'   => $builder_type,
				'plugin_version' => fw()->manifest->get_version(),
				'exported_at'    => time(),
			),
			'title'   => isset($template['title']) ? (string)$template['title'] : '',
			'json'    => (string)$template['json'],
			'created' => isset($template['created']) && is_numeric($template['created']) ? (int)$template['created'] : time(),
		);

		$slug = sanitize_title($envelope['title']);
		if (empty($slug)) {
			$slug = 'template';
		}

		wp_send_json_success(array(
			'filename' => $slug .'-'. $this->get_type() .'-'. substr($template_id, 0, 8) .'.json',
			'content'  => $envelope,
		));
	}

	/**
	 * Section import — like the Full importer plus one extra check: the
	 * decoded inner `json` must carry `type === 'section'`, matching the
	 * existing `_action_ajax_save_template()` validation (sections refuse
	 * to save a fragment whose outer type doesn't match).
	 *
	 * @internal
	 */
	public function _action_ajax_import_template()
	{
		if (!current_user_can('edit_posts')) {
			wp_send_json_error();
		}

		$builder_type = (string)FW_Request::POST('builder_type');

		if (!$this->builder_type_is_valid($builder_type)) {
			wp_send_json_error();
		}

		if (
			empty($_FILES['template_file'])
			|| !isset($_FILES['template_file']['error'])
			|| $_FILES['template_file']['error'] !== UPLOAD_ERR_OK
		) {
			wp_send_json_error(array('message' => __('No file uploaded', 'fw')));
		}

		if ($_FILES['template_file']['size'] > 5 * 1024 * 1024) {
			wp_send_json_error(array('message' => __('File is too large', 'fw')));
		}

		$tmp = isset($_FILES['template_file']['tmp_name'])
			? sanitize_text_field((string)$_FILES['template_file']['tmp_name'])
			: '';

		if (empty($tmp) || !is_uploaded_file($tmp)) {
			wp_send_json_error(array('message' => __('Upload failed', 'fw')));
		}

		$contents = @file_get_contents($tmp);

		if ($contents === false || $contents === '') {
			wp_send_json_error(array('message' => __('Could not read uploaded file', 'fw')));
		}

		$data = json_decode($contents, true);

		if (!is_array($data)) {
			wp_send_json_error(array('message' => __('That file is not a valid JSON file', 'fw')));
		}

		if (
			!isset($data['_fw_template_export'])
			|| !is_array($data['_fw_template_export'])
		) {
			wp_send_json_error(array('message' => __('Not an Unyson+ template file', 'fw')));
		}

		$envelope = $data['_fw_template_export'];

		if (!isset($envelope['kind']) || $envelope['kind'] !== $this->get_type()) {
			wp_send_json_error(array('message' => sprintf(
				/* translators: 1: envelope kind found in the file, 2: kind expected by this importer */
				__('This is a %1$s template — open the %2$s list to import it.', 'fw'),
				isset($envelope['kind']) ? (string)$envelope['kind'] : __('unknown', 'fw'),
				$this->get_type()
			)));
		}

		if (
			!isset($envelope['builder_type'])
			|| $envelope['builder_type'] !== $builder_type
		) {
			wp_send_json_error(array('message' => __('Template was exported from a different builder type', 'fw')));
		}

		if (!isset($data['json']) || !is_string($data['json'])) {
			wp_send_json_error(array('message' => __('Template file is missing the body', 'fw')));
		}

		$template_json = trim((string)$data['json']);

		$decoded_json = json_decode($template_json, true);

		if (empty($template_json) || $decoded_json === null) {
			wp_send_json_error(array('message' => __('Template content is not valid JSON', 'fw')));
		}

		// Per-component check: the inner JSON's type must be accepted by
		// the Sections list. Accepts the canonical `section` type AND any
		// custom section-like variant registered via
		// `FW_Section_Like_Registry` (e.g. `hero_section`) — same as the
		// save handler above.
		if (!isset($decoded_json['type']) || !$this->is_acceptable_inner_type($decoded_json['type'])) {
			wp_send_json_error(array('message' => __('Template content does not match the expected type', 'fw')));
		}

		unset($decoded_json);

		$template = array(
			'title'   => isset($data['title']) ? trim((string)$data['title']) : '',
			'json'    => $template_json,
			'created' => isset($data['created']) && is_numeric($data['created']) ? (int)$data['created'] : time(),
		);

		if (empty($template['title'])) {
			$template['title'] = __('Imported Template', 'fw');
		}

		$template_id = md5($template['json']);

		update_option(
			$this->get_wp_option_prefix($builder_type) . $template_id,
			$template,
			false
		);

		wp_send_json_success(array(
			'id'    => $template_id,
			'title' => $template['title'],
		));
	}

	/**
	 * @param $builder_type
	 * @return mixed|null
	 *
	 * Note: Templates can be very big and saving them in a single wp option can throw mysql error on update query
	 */
	protected function get_db_templates($builder_type)
	{
		$templates = array();

		/**
		 * Note: 'prefix + name' max length should be 64
		 */
		$option_prefix = $this->get_wp_option_prefix($builder_type); // + md5 (length=32)

		/**
		 * @var WPDB $wpdb
		 */
		global $wpdb;

		foreach ((array)$wpdb->get_results($wpdb->prepare(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
			$wpdb->esc_like( $option_prefix ) .'%'
		), ARRAY_A) as $row) {
			$templates[
				// extract (suffix) md5 used as id
			preg_replace('/^'. preg_quote($option_prefix, '/') .'/', '', $row['option_name'])
			] = get_option($row['option_name']);
		}

		$templates +=
			/**
			 * Append old templates
			 * This can't be removed because a lot of installations already use this
			 */
			fw_get_db_extension_data('builder', 'templates:'. $this->get_type() .'/'. $builder_type, array());

		return $templates;
	}

	private $fake_created_value;

	private function array_map_add_fake_created_key($el)
	{
		if (!isset($el['created'])) {
			/**
			 * Before 1.1.14 templates were appended
			 * After 1.1.14 templates are prepended
			 * So reverse old templates to be in the same order as the new ones
			 */
			$el['created'] = (++$this->fake_created_value);
		}

		return $el;
	}

	private function sort_templates($a, $b)
	{
		$at = isset($a['created']) ? $a['created'] : 0;
		$bt = isset($b['created']) ? $b['created'] : 0;

		if ($at == $bt) {
			return 0;
		}

		return ($at > $bt) ? -1 : 1;
	}

	private function get_wp_option_prefix($builder_type)
	{
		return 'fw:bt:s:'. $builder_type .':';
	}

	/**
	 * Return true if the given shortcode `type` is acceptable for storage
	 * in the Sections template list. Accepts the canonical `section` type
	 * and any custom section-like variant registered via the
	 * `fw_section_like_types` filter (e.g. `hero_section`).
	 *
	 * We DON'T rely solely on `FW_Section_Like_Registry::is_section_like()`
	 * here, because that registry is populated lazily at page-builder
	 * `register_items` time — which runs during the editor render but NOT
	 * during admin-ajax requests like this one. Custom section-like
	 * shortcodes that register themselves on `register_items` would be
	 * absent from the registry's static array on ajax. The
	 * `fw_section_like_types` filter, on the other hand, is hooked at
	 * shortcode `_init()` time and IS present on ajax — so we apply that
	 * filter directly with `'section'` as the always-present default. The
	 * registry's `is_section_like()` ends up consulting the same filter
	 * under the hood, so we get equivalent behavior in both contexts.
	 */
	private function is_acceptable_inner_type($type)
	{
		if (!is_string($type) || $type === '') {
			return false;
		}

		$types = apply_filters('fw_section_like_types', array('section'));

		return is_array($types) && in_array($type, $types, true);
	}
}
