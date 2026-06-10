<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * Bleed Section — a section-like shortcode that splits into a content side and
 * a full-bleed image side (a striking split-screen for featured content). It
 * holds rows/columns like a standard Section; the inner content renders into the
 * content column while the chosen image fills the opposite half to the viewport
 * edge.
 *
 * Modeled on the section-like recipe (see masonry-section). Extracted out of the
 * standard Section, where bleed used to be an embedded "Bleed Layout" tab.
 */
class FW_Shortcode_Bleed_Section extends FW_Shortcode {

	public function _init() {
		add_action(
			'fw_option_type_builder:page-builder:register_items',
			array( $this, '_action_register_builder_item_types' )
		);

		add_filter(
			'fw_ext:shortcodes:collect_shortcodes_data',
			array( $this, '_filter_add_bleed_section_data' )
		);

		// Register as section-like on every PHP request (not just the editor),
		// so root-level restrictions / item correction apply during rendering too.
		add_filter( 'fw_section_like_types', array( $this, '_filter_register_section_like' ) );
	}

	public function _filter_register_section_like( $types ) {
		if ( is_array( $types ) && ! in_array( 'bleed_section', $types, true ) ) {
			$types[] = 'bleed_section';
		}
		return $types;
	}

	public function _filter_add_bleed_section_data( $structure ) {
		$structure['bleed_section'] = $this->get_item_data();
		return $structure;
	}

	public function get_shortcode_config() {
		$config = $this->get_config( 'page_builder' );

		// SVG icon by URL (rendered as <img src>, like the other section thumbnails).
		$icon = $this->locate_URI( '/static/img/page_builder.svg' );
		if ( ! $icon ) {
			$section = fw_ext( 'shortcodes' )->get_shortcode( 'section' );
			if ( $section ) {
				$icon = $section->locate_URI( '/static/img/page_builder.svg' );
			}
		}

		return array_merge(
			array(
				'tab'            => __( 'Layout Elements', 'fw' ),
				'title'          => __( 'Bleed Section', 'fw' ),
				'description'    => __( 'A split section: content on one side, a full-bleed image on the other', 'fw' ),
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

		$data['tag'] = 'bleed_section';

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
				'/includes/page-builder-bleed_section-item/class-page-builder-bleed-section-item.php'
			);
		}
	}
}
