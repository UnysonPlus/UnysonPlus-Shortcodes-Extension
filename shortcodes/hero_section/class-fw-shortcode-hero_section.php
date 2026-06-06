<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Hero Section — a section-like shortcode with a parallax-image background.
 *
 * Behaves identically to the built-in `[section]` in the page builder
 * (lives at root, holds rows, edits via the same modal-style options).
 * The only difference is the rendered HTML: it adds a parallax-aware
 * background container and exposes a few hero-specific options
 * (parallax strength, overlay color, min-height).
 *
 * Registration of the page-builder item type happens lazily on
 * `fw_option_type_builder:page-builder:register_items`, mirroring how
 * `FW_Shortcode_Section` registers `Page_Builder_Section_Item`.
 */
class FW_Shortcode_Hero_Section extends FW_Shortcode {

	/**
	 * @internal
	 */
	public function _init() {
		add_action(
			'fw_option_type_builder:page-builder:register_items',
			array( $this, '_action_register_builder_item_types' )
		);

		add_filter(
			'fw_ext:shortcodes:collect_shortcodes_data',
			array( $this, '_filter_add_hero_section_data' )
		);

		// Register `hero_section` as section-like EAGERLY via the
		// `fw_section_like_types` filter. The static
		// `FW_Section_Like_Registry::register()` call inside
		// `Page_Builder_Hero_Section_Item::_init()` only runs when the
		// page-builder option-type's `register_items` action fires — and
		// that action only fires during the editor admin page render, NOT
		// during admin-ajax requests. Without this filter, every
		// section-like ajax check (template save / import, future hooks)
		// would see hero_section as "not section-like" on ajax. The filter
		// approach hooks at shortcode-init time so the registration is
		// present in every PHP request the shortcode loads in.
		add_filter( 'fw_section_like_types', array( $this, '_filter_register_section_like' ) );
	}

	/**
	 * @internal
	 */
	public function _filter_register_section_like( $types ) {
		if ( is_array( $types ) && ! in_array( 'hero_section', $types, true ) ) {
			$types[] = 'hero_section';
		}
		return $types;
	}

	/**
	 * @internal
	 */
	public function _filter_add_hero_section_data( $structure ) {
		$structure['hero_section'] = $this->get_item_data();
		return $structure;
	}

	public function get_shortcode_config() {
		$config = $this->get_config( 'page_builder' );

		$icon = $this->locate_path( '/static/img/page_builder.svg' );
		if ( ! $icon ) {
			// Fall back to the built-in section's icon — both render the same
			// thumbnail look in the Layout Elements tab.
			$section = fw_ext( 'shortcodes' )->get_shortcode( 'section' );
			if ( $section ) {
				$icon = $section->locate_path( '/static/img/page_builder.svg' );
			}
		}

		if ( $icon && file_exists( $icon ) ) {
			$icon = file_get_contents( $icon );
		}

		return array_merge(
			array(
				'tab'            => __( 'Layout Elements', 'fw' ),
				'title'          => __( 'Hero Section', 'fw' ),
				'description'    => __( 'A section with a parallax background', 'fw' ),
				'title_template' => null,
				'icon'           => $icon,
			),
			( is_array( $config ) ? $config : array() )
		);
	}

	public function get_item_data() {
		$data    = array();
		$options = $this->get_options();

		if ( $options ) {
			fw()->backend->enqueue_options_static( $options );
			$data['options']        = $this->transform_options( $options );
			$data['default_values'] = fw_get_options_values_from_input( $options, array() );
		}

		$config = $this->get_shortcode_config();

		if ( isset( $config['popup_size'] ) ) {
			$data['popup_size'] = $config['popup_size'];
		}
		if ( isset( $config['popup_header_elements'] ) ) {
			$data['header_elements'] = $config['popup_header_elements'];
		}

		$data['title']          = $config['title'];
		$data['title_template'] = $config['title_template'];

		$data['l10n'] = array(
			'edit'      => __( 'Edit', 'fw' ),
			'duplicate' => __( 'Duplicate', 'fw' ),
			'remove'    => __( 'Remove', 'fw' ),
			'collapse'  => __( 'Collapse', 'fw' ),
		);

		$data['tag'] = 'hero_section';

		return $data;
	}

	private function transform_options( $options ) {
		$transformed = array();
		foreach ( $options as $id => $option ) {
			if ( is_int( $id ) ) {
				$transformed[] = $option;
			} else {
				$transformed[] = array( $id => $option );
			}
		}
		return $transformed;
	}

	public function _action_register_builder_item_types() {
		if ( fw_ext( 'page-builder' ) ) {
			require $this->get_declared_path(
				'/includes/page-builder-hero_section-item/class-page-builder-hero-section-item.php'
			);
		}
	}
}
