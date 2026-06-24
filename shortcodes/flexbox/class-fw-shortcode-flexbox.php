<?php if (!defined('FW')) die('Forbidden');

/**
 * Flexbox — a self-contained, nestable flex container that renders a chosen
 * semantic HTML tag. A new page-builder item type ('flexbox'); does NOT reuse or
 * modify Section/Row/Column. Mirrors FW_Shortcode_Container's builder-item
 * plumbing (item data, icon, registration).
 */
class FW_Shortcode_Flexbox extends FW_Shortcode
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
			$this, '_filter_add_flexbox_data'
		) );
	}

	/**
	 * @internal
	 */
	public function _filter_add_flexbox_data( $structure )
	{
		$data['flexbox'] = $this->get_item_data();
		return array_merge( $structure, $data );
	}

	public function get_shortcode_config()
	{
		$config = $this->get_config('page_builder');

		$icon = $this->locate_URI( "/static/img/page_builder.svg" );
		if ( ! $icon ) {
			$icon = $this->locate_URI( "/static/img/page_builder.png" );
		}

		return array_merge(
			array(
				'tab'            => __('Structure', 'fw'),
				'title'          => __('Flexbox', 'fw'),
				'description'    => __('A nestable flexbox container with a semantic HTML tag.', 'fw'),
				'title_template' => null,
				'icon'           => $icon,
			),
			(is_array($config) ? $config : array())
		);
	}

	/**
	 * Data about the flexbox pushed to the frontend builder (options, l10n, …).
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

		$data['tag'] = 'flexbox';

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
			require $this->get_declared_path('/includes/page-builder-flexbox-item/class-page-builder-flexbox-item.php');
		}
	}
}
