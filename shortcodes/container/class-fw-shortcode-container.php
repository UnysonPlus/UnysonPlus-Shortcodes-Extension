<?php if (!defined('FW')) die('Forbidden');

/**
 * Container layout element — a second `.fw-container` / `.fw-container-fluid` band that
 * a section can hold alongside its own container (the items-corrector lifts it out so it
 * renders as a SIBLING, not nested). Holds columns, exactly like a section.
 *
 * Mirrors FW_Shortcode_Section's builder-item plumbing (item data, icon, registration).
 */
class FW_Shortcode_Container extends FW_Shortcode
{
	/**
	 * @internal
	 */
	public function _init()
	{
		add_action(
			'fw_option_type_builder:page-builder:register_items',
			array($this, '_action_register_builder_item_types')
		);

		add_filter( 'fw_ext:shortcodes:collect_shortcodes_data', array(
			$this, '_filter_add_container_data'
		) );
	}

	/**
	 * @internal
	 */
	public function _filter_add_container_data( $structure )
	{
		$data['container'] = $this->get_item_data();
		return array_merge( $structure, $data );
	}

	public function get_shortcode_config()
	{
		$config = $this->get_config('page_builder');

		// Use the SVG icon by URL (rendered via <img src>, like the section/column
		// thumbnails) — reliable regardless of the builder's inline-icon support.
		$icon = $this->locate_URI( "/static/img/page_builder.svg" );

		if ( ! $icon ) {
			$icon = $this->locate_URI( "/static/img/page_builder.png" );
		}

		return array_merge(
			array(
				'tab'         => __('Layout Elements', 'fw'),
				'title'       => __('Container', 'fw'),
				'description' => __('Adds a container to a section', 'fw'),
				'title_template' => null,
				'icon' => $icon
			),
			(is_array($config) ? $config : array())
		);
	}

	/**
	 * Data about the container pushed to the frontend builder (options, l10n, …).
	 */
	public function get_item_data()
	{
		$data = array();
		$options = $this->get_options();

		if ($options) {
			fw()->backend->enqueue_options_static($options);
			$data['options'] = $this->transform_options($options);

			$data['default_values'] = fw_get_options_values_from_input(
				$options, array()
			);
		}

		$config = $this->get_shortcode_config();

		if (isset($config['popup_size'])) {
			$data['popup_size'] = $config['popup_size'];
		}

		if (isset($config['popup_header_elements'])) {
			$data['header_elements'] = $config['popup_header_elements'];
		}

		$data['title'] = $config['title'];
		$data['title_template'] = $config['title_template'];

		$data['l10n'] = array(
			'edit'      => __( 'Edit', 'fw' ),
			'duplicate' => __( 'Duplicate', 'fw' ),
			'remove'    => __( 'Remove', 'fw' ),
			'collapse'	=> __( 'Collapse', 'fw' ),
		);

		$data['tag'] = 'container';

		return $data;
	}

	/*
	 * Puts each option into a separate array to keep its order inside the modal dialog.
	 */
	private function transform_options($options)
	{
		$transformed_options = array();
		foreach ($options as $id => $option) {
			if (is_int($id)) {
				$transformed_options[] = $option;
			} else {
				$transformed_options[] = array($id => $option);
			}
		}
		return $transformed_options;
	}

	public function _action_register_builder_item_types() {
		if (fw_ext('page-builder')) {
			require $this->get_declared_path('/includes/page-builder-container-item/class-page-builder-container-item.php');
		}
	}
}
