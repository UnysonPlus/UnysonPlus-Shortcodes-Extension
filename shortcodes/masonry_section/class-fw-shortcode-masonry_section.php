<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Masonry Section — a section-like shortcode that arranges its columns in a
 * left-to-right CSS-grid masonry (Pinterest-style: items keep source order
 * reading across and pack vertically to fill gaps).
 *
 * Behaves like the built-in `[section]` in the page builder (lives at root,
 * holds columns, edits via the same modal-style options). The only difference
 * is the rendered layout: its inner `.fw-row` becomes a CSS grid and a tiny JS
 * helper (static/js/masonry-section.js) sets each column's `grid-row-end` from
 * its measured height, so columns of unequal height tuck together with no gaps.
 *
 * Mirrors FW_Shortcode_Hero_Section's registration exactly.
 */
class FW_Shortcode_Masonry_Section extends FW_Shortcode {

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
			array( $this, '_filter_add_masonry_section_data' )
		);

		// Register `masonry_section` as section-like EAGERLY (every PHP request),
		// not just on editor render — see the note in FW_Shortcode_Hero_Section.
		add_filter( 'fw_section_like_types', array( $this, '_filter_register_section_like' ) );
	}

	/**
	 * @internal
	 */
	public function _filter_register_section_like( $types ) {
		if ( is_array( $types ) && ! in_array( 'masonry_section', $types, true ) ) {
			$types[] = 'masonry_section';
		}
		return $types;
	}

	/**
	 * @internal
	 */
	public function _filter_add_masonry_section_data( $structure ) {
		$structure['masonry_section'] = $this->get_item_data();
		return $structure;
	}

	public function get_shortcode_config() {
		$config = $this->get_config( 'page_builder' );

		// Use the SVG icon by URL (rendered via <img src>, like Section / the
		// column thumbnails) — reliable regardless of inline-icon support.
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
				'title'          => __( 'Masonry Section', 'fw' ),
				'description'    => __( 'A section that packs its columns into a masonry grid', 'fw' ),
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

		$data['tag'] = 'masonry_section';

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
				'/includes/page-builder-masonry_section-item/class-page-builder-masonry_section-item.php'
			);
		}
	}
}
