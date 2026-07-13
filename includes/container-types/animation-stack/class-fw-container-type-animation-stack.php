<?php
/**
 * PHP Version: 7.4 or higher
 *
 * "animation-stack" container type — the Animations-tab organizer.
 *
 * The Animation Engine appends one popover image-picker field per module (Entrance, Scroll
 * Motion, Hover, Physics, Parallax, Marquee, Text Effect, Scroll Loop, …) to the Animations
 * tab via the `sc_animation_fields` filter. Rendered flat they stack into an ever-growing wall
 * of always-visible rows. This container wraps them so the tab reads as a short **card stack
 * plus an "Add Animation" inserter** (the WordPress block-inserter pattern):
 *
 *   • Each module field is rendered inside a `.upw-anim-card`; a card is initially HIDDEN when
 *     its picker sits on the "off" value (so an unused effect takes no space).
 *   • A "+ Add Animation" button opens a searchable, category-filtered grid of tiles — one per
 *     module. Clicking a tile reveals that card and opens its popover so the user picks an effect.
 *   • Each visible card carries an inline "remove" that resets its picker back to "off".
 *
 * Zero migration: a container renders/collects its children WITHOUT namespacing (exactly like
 * `group`/`box`/`tab`), so every module keeps saving under its own key (interaction, physics,
 * gsap_motion, …). Nothing about the saved value shape changes — this is purely presentation.
 *
 * "Active"/"off" detection is generic (works for `effect=>none`, `role=>none`, `mode=>off`, …):
 * the picker id is the single key of `$field['picker']`, and the off value is that key's default
 * in `$field['value']`. Per-card metadata (category + icon for the inserter) comes from an
 * optional `$field['anim_meta']` (`['category'=>…, 'icon'=>…]`), with an id→category fallback map.
 */
if ( ! defined( 'FW' ) ) die( 'Forbidden' );

class FW_Container_Type_Animation_Stack extends FW_Container_Type {

	public function get_type() {
		return 'animation-stack';
	}

	protected function _get_defaults() {
		return array();
	}

	/**
	 * Enqueue the inserter CSS/JS. Runs on the builder/settings page during the options
	 * static-enqueue pass (admin_enqueue_scripts context), same as any option type's assets.
	 */
	protected function _enqueue_static( $id, $option, $values, $data ) {
		$ext = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' ) : null;
		if ( ! $ext ) { return; }

		$base = $ext->get_declared_URI( '/includes/container-types/animation-stack/static' );
		$ver  = '1.0.14';

		wp_enqueue_style( 'upw-anim-stack', $base . '/css/animation-stack.css', array(), $ver );
		wp_enqueue_script( 'upw-anim-stack', $base . '/js/animation-stack.js', array( 'jquery' ), $ver, true );
	}

	protected function _render( $containers, $values, $data ) {
		$html = '';

		foreach ( $containers as $cid => $container ) {
			$inner = ( isset( $container['options'] ) && is_array( $container['options'] ) ) ? $container['options'] : array();

			$cards         = '';
			$passthrough   = '';
			$catalog_items = array();
			$catalog_seen  = array();
			$any_active    = false;

			// PASS 1 — classify each card as active/inactive by its saved picker value. Active cards
			// show; inactive ones render the same but carry an `is-hidden` class until "added".
			$active_map = array();
			foreach ( $inner as $fid => $field ) {
				if ( ! is_array( $field ) || ! empty( $field['anim_attach'] ) ) { continue; }
				$is_card = isset( $field['type'] ) && $field['type'] === 'multi-picker' && ! empty( $field['picker'] ) && is_array( $field['picker'] );
				if ( ! $is_card ) { continue; }
				$picker_id = array_key_first( $field['picker'] );
				$off_val   = ( isset( $field['value'][ $picker_id ] ) ) ? $field['value'][ $picker_id ] : 'none';
				$saved     = ( isset( $values[ $fid ][ $picker_id ] ) ) ? $values[ $fid ][ $picker_id ] : $off_val;
				$active_map[ $fid ] = ( $saved !== $off_val && $saved !== '' );
			}

			// Fields that ATTACH INTO a card ( 'anim_attach' => '<card field id>' ) render INSIDE that
			// card's box instead of always-on in passthrough — so they show/hide with the card.
			$attached = array();
			foreach ( $inner as $fid => $field ) {
				if ( is_array( $field ) && ! empty( $field['anim_attach'] ) ) {
					$target = (string) $field['anim_attach'];
					$clean  = $field;
					unset( $clean['anim_attach'] );
					$attached[ $target ] = ( isset( $attached[ $target ] ) ? $attached[ $target ] : '' )
						. fw()->backend->render_options( array( $fid => $clean ), $values, $data );
				}
			}

			foreach ( $inner as $fid => $field ) {
				if ( is_array( $field ) && ! empty( $field['anim_attach'] ) ) {
					continue; // rendered inside its target card above
				}

				$is_card = is_array( $field )
					&& isset( $field['type'] ) && $field['type'] === 'multi-picker'
					&& ! empty( $field['picker'] ) && is_array( $field['picker'] );

				if ( ! $is_card ) {
					// Non-picker entries (the "enable the engine" promo html, headings) render
					// as-is, always visible, outside the card model.
					$passthrough .= fw()->backend->render_options( array( $fid => $field ), $values, $data );
					continue;
				}

				$picker_id = array_key_first( $field['picker'] );
				$off_val   = ( isset( $field['value'] ) && is_array( $field['value'] ) && isset( $field['value'][ $picker_id ] ) )
					? $field['value'][ $picker_id ]
					: 'none';

				$active = ! empty( $active_map[ $fid ] );
				if ( $active ) { $any_active = true; }

				$meta  = ( isset( $field['anim_meta'] ) && is_array( $field['anim_meta'] ) ) ? $field['anim_meta'] : array();
				$cat   = isset( $meta['category'] ) ? (string) $meta['category'] : $this->fallback_category( $fid );
				$label = ( isset( $field['label'] ) && $field['label'] ) ? (string) $field['label'] : $fid;
				// Style / effect names (from the picker choices) so the inserter search can find a
				// module by one of its effects — e.g. "letter jump" → Text Effect, "peel" → Sticky Stack.
				$keywords = $this->picker_keywords( $field, $picker_id );

				// Multi-instance: several slots (interaction, interaction__2, …) share one base id, so
				// they group under a single inserter tile whose card list can grow.
				$is_multi = ! empty( $meta['multi'] );
				$base     = isset( $meta['multi_base'] ) ? (string) $meta['multi_base'] : $fid;

				// Always render the card's options in FULL. The builder's OptionsModal collects values
				// from the fields present when it opens, so a lazily-injected field would never be saved
				// (that reset newly-added animations to "none" on save). The saved-value bloat that used
				// to OOM the builder is fixed at the source instead — in multi-picker::_get_value_from_input,
				// which now stores only the selected choice's sub-values.
				$body = fw()->backend->render_options( array( $fid => $field ), $values, $data )
					. ( isset( $attached[ $fid ] ) ? '<div class="upw-anim-card-extra">' . $attached[ $fid ] . '</div>' : '' );

				$cards .= '<div class="upw-anim-card' . ( $active ? '' : ' is-hidden' ) . '"'
					. ' data-anim-id="' . esc_attr( $base ) . '"'
					. ' data-anim-picker="' . esc_attr( $picker_id ) . '"'
					. ' data-anim-off="' . esc_attr( $off_val ) . '">'
					. '<button type="button" class="upw-anim-card-remove" aria-label="' . esc_attr__( 'Remove animation', 'fw' ) . '" title="' . esc_attr__( 'Remove animation', 'fw' ) . '">&times;</button>'
					. $body
					. '</div>';

				// One catalog tile per base module (slots 2..N don't add their own tile).
				if ( ! isset( $catalog_seen[ $base ] ) ) {
					$catalog_seen[ $base ] = true;
					$catalog_items[] = array(
						'id'       => $base,
						'label'    => $label,
						'cat'      => $cat,
						// A multi-instance tile stays available (never "added") so you can add more;
						// a single tile hides once its one card is active.
						'active'   => ( $is_multi ? false : $active ),
						'keywords' => $keywords,
						'multi'    => $is_multi,
					);
				}
			}

			// Nothing to organize (no picker fields) → just render passthrough untouched.
			if ( empty( $catalog_items ) ) {
				$html .= $passthrough;
				continue;
			}

			$html .= '<div class="upw-anim-stack">'
				. '<div class="upw-anim-empty' . ( $any_active ? ' is-hidden' : '' ) . '">'
				.     '<span class="upw-anim-empty-ico"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 3L5 13h6l-1 8 8-10h-6z"/></svg></span>'
				.     '<span>' . esc_html__( 'No animations yet — click “Add Animation” to bring this element to life.', 'fw' ) . '</span>'
				. '</div>'
				. '<div class="upw-anim-cards">' . $cards . '</div>'
				. $this->render_inserter( $catalog_items )
				. $passthrough
				. '</div>';
		}

		return $html;
	}

	/**
	 * The "+ Add Animation" button + the searchable, category-tabbed tile grid.
	 * Tiles whose card is already active start `is-added` (hidden from the grid via CSS).
	 */
	private function render_inserter( $items ) {
		// Ordered, de-duplicated category list from the items themselves.
		$cats = array();
		foreach ( $items as $it ) {
			if ( ! in_array( $it['cat'], $cats, true ) ) { $cats[] = $it['cat']; }
		}

		// Each group answers a different "when/how does it run?" question — spell that out so the
		// four groups read as a deliberate taxonomy, not an arbitrary split (this is why Entrance
		// shows event triggers while Interaction/Scroll don't). Shown under the tabs, per active tab.
		$cat_desc = array(
			'all'         => __( 'Browse every animation, or filter by how it runs.', 'fw' ),
			'Entrance'    => __( 'Plays once on an event you pick — scroll into view, page load, click or hover.', 'fw' ),
			'Scroll'      => __( 'Driven by scroll position — progress is tied to the scrollbar as the reader scrolls.', 'fw' ),
			'Interaction' => __( 'Responds to the pointer — active while hovering, dragging or pressing, then reverts.', 'fw' ),
			'Ambient'     => __( 'Always running — a continuous, decorative flourish with no trigger.', 'fw' ),
		);
		$desc_attr = function ( $key ) use ( $cat_desc ) {
			return isset( $cat_desc[ $key ] ) ? ' data-desc="' . esc_attr( $cat_desc[ $key ] ) . '"' : '';
		};

		$tabs = '<button type="button" class="upw-anim-tab is-on" data-cat="all"' . $desc_attr( 'all' ) . '>' . esc_html__( 'All', 'fw' ) . '</button>';
		foreach ( $cats as $c ) {
			$tabs .= '<button type="button" class="upw-anim-tab" data-cat="' . esc_attr( $c ) . '"' . $desc_attr( $c ) . '>' . esc_html( $c ) . '</button>';
		}

		$tiles = '';
		foreach ( $items as $it ) {
			$tiles .= '<button type="button" class="upw-anim-tile' . ( $it['active'] ? ' is-added' : '' ) . '"'
				. ' data-target="' . esc_attr( $it['id'] ) . '"'
				. ' data-cat="' . esc_attr( $it['cat'] ) . '"'
				. ( ! empty( $it['multi'] ) ? ' data-multi="1"' : '' )
				. ' data-keywords="' . esc_attr( isset( $it['keywords'] ) ? $it['keywords'] : '' ) . '">'
				. '<span class="upw-anim-tile-ico">' . $this->icon_svg( $it['id'] ) . '</span>'
				. '<span class="upw-anim-tile-lbl">' . esc_html( $it['label'] ) . '</span>'
				. '<span class="upw-anim-tile-cat">' . esc_html( $it['cat'] ) . '</span>'
				. '</button>';
		}

		return '<div class="upw-anim-inserter">'
			. '<button type="button" class="upw-anim-add"><span class="upw-anim-add-plus">+</span> ' . esc_html__( 'Add Animation', 'fw' ) . '</button>'
			. '<div class="upw-anim-catalog" hidden>'
			.     '<input type="search" class="upw-anim-search" placeholder="' . esc_attr__( 'Search effects…', 'fw' ) . '" autocomplete="off">'
			.     '<div class="upw-anim-tabs">' . $tabs . '</div>'
			.     '<div class="upw-anim-cat-desc">' . esc_html( $cat_desc['all'] ) . '</div>'
			.     '<div class="upw-anim-tiles">' . $tiles . '</div>'
			.     '<div class="upw-anim-tiles-empty is-hidden">' . esc_html__( 'All animations added.', 'fw' ) . '</div>'
			. '</div>'
			. '</div>';
	}

	/**
	 * Space-joined, lowercase style / effect names pulled from a field's picker choices, so the
	 * inserter search can match a module by one of its effects (not just the module name). Handles
	 * both image-picker choices (`['label' => …]`) and plain select choices (`key => 'Label'`), and
	 * includes the choice keys themselves (underscores/dashes → spaces) as a loose match.
	 */
	private function picker_keywords( $field, $picker_id ) {
		$choices = ( isset( $field['picker'][ $picker_id ]['choices'] ) && is_array( $field['picker'][ $picker_id ]['choices'] ) )
			? $field['picker'][ $picker_id ]['choices'] : array();

		$out = array();
		foreach ( $choices as $ck => $cv ) {
			if ( is_array( $cv ) && isset( $cv['label'] ) && $cv['label'] ) {
				$out[] = (string) $cv['label'];
			} elseif ( is_string( $cv ) && $cv !== '' ) {
				$out[] = $cv;
			}
			if ( is_string( $ck ) && $ck !== '' ) {
				$out[] = str_replace( array( '_', '-' ), ' ', $ck );
			}
		}

		$out = array_map( 'strtolower', $out );
		$out = array_values( array_unique( array_filter( $out ) ) );
		return implode( ' ', $out );
	}

	/**
	 * Flat, single-colour (blue via currentColor) inline icon per module — matches the plugin's
	 * own line-icon look rather than the OS emoji. Keyed by field id; falls back to a generic
	 * "motion" glyph. Line style, 24×24, stroke = currentColor (the tile sets the blue tone).
	 */
	private function icon_svg( $fid ) {
		$paths = array(
			// Entrance — a sparkle (attention / enters view).
			'animation'   => '<path d="M12 3.5l1.7 5 5 1.7-5 1.7-1.7 5-1.7-5-5-1.7 5-1.7z"/><path d="M18.5 4.5l.7 2 2 .7-2 .7-.7 2-.7-2-2-.7 2-.7z"/>',
			// Scroll Motion — double chevron down (scroll-driven).
			'gsap_motion' => '<path d="M6 8l6 6 6-6"/><path d="M6 13l6 6 6-6"/>',
			// Hover — a pointer cursor.
			'interaction' => '<path d="M6 4l0 14 3.4-3.3 2 4.6 2.4-1-2-4.5 4.6-.1z"/>',
			// Physics — an atom (orbits).
			'physics'     => '<circle cx="12" cy="12" r="2"/><ellipse cx="12" cy="12" rx="9.2" ry="3.7"/><ellipse cx="12" cy="12" rx="9.2" ry="3.7" transform="rotate(60 12 12)"/><ellipse cx="12" cy="12" rx="9.2" ry="3.7" transform="rotate(120 12 12)"/>',
			// Parallax — stacked depth layers.
			'parallax'    => '<path d="M12 3l8.5 4.2L12 11.4 3.5 7.2z"/><path d="M4 12l8 4 8-4"/><path d="M4 16.3l8 4 8-4"/>',
			// Marquee — fast-forward (running motion).
			'marquee'     => '<path d="M4 6.5l7 5.5-7 5.5z"/><path d="M13 6.5l7 5.5-7 5.5z"/>',
			// Text Effect — the letter A.
			'text_effect' => '<path d="M6 20l6-16 6 16"/><path d="M8.4 14h7.2"/>',
			// Scroll Loop — a circular loop arrow.
			'scroll_loop' => '<path d="M20 12a8 8 0 1 1-2.4-5.7"/><path d="M20 4.6V9h-4.4"/>',
			// Sticky Card Stack — a deck of stacked cards.
			'sticky_stack' => '<rect x="6" y="4" width="12" height="5" rx="1.5"/><rect x="4.5" y="10.5" width="15" height="5" rx="1.5"/><rect x="3" y="17" width="18" height="4.5" rx="1.5"/>',
			// Horizontal Scroll — panels moving sideways.
			'horizontal_scroll' => '<rect x="3" y="7" width="6.5" height="10" rx="1.5"/><rect x="10.5" y="7" width="6.5" height="10" rx="1.5"/><path d="M19 8l3 4-3 4"/>',
			// Scroll Reveal — a box unmasking (half-drawn) with a wipe edge.
			'scroll_reveal' => '<rect x="3.5" y="4.5" width="17" height="15" rx="2"/><path d="M12 4.5v15" stroke-dasharray="2 2"/><path d="M3.5 4.5h8.5v15H3.5z" fill="currentColor" stroke="none" opacity="0.28"/>',
			// 3D Flip Card — a card with a flip arc.
			'flip_card' => '<rect x="4.5" y="4" width="15" height="12" rx="2"/><path d="M6.5 19a5.5 2.6 0 0 0 11 0"/><path d="M15.6 17.4l2 1.6-2.2 1.1"/>',
			// Scroll Text Highlight — text lines with a highlighted first line.
			'scroll_text_highlight' => '<rect x="3.4" y="5.8" width="8" height="3.4" rx="1" fill="currentColor" stroke="none" opacity="0.25"/><path d="M4.2 7.5h6.8"/><path d="M4 12h14"/><path d="M4 16.5h9"/>',
			// Scroll Color Shift — a screen whose upper band is tinted (colour morph).
			'scroll_color_shift' => '<rect x="4" y="4" width="16" height="16" rx="2"/><rect x="4.6" y="4.6" width="14.8" height="7" rx="1.6" fill="currentColor" stroke="none" opacity="0.26"/><path d="M4 12h16"/>',
			'_default'    => '<circle cx="12" cy="12" r="8.5"/><path d="M9 12h6M12 9v6"/>',
		);
		$inner = isset( $paths[ $fid ] ) ? $paths[ $fid ] : $paths['_default'];
		return '<svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $inner . '</svg>';
	}

	/**
	 * id → category fallback for module fields that haven't declared `anim_meta` yet.
	 * Four behavioral groups (see anim_meta across the modules): Entrance (one-shot, trigger-
	 * driven), Scroll (progress tied to scroll position), Interaction (responds to the pointer),
	 * Ambient (always-on / decorative flourishes).
	 */
	private function fallback_category( $fid ) {
		$map = array(
			'animation'    => 'Entrance',
			'text_effect'  => 'Entrance',
			'gsap_motion'  => 'Scroll',
			'scroll_loop'  => 'Scroll',
			'parallax'     => 'Scroll',
			'interaction'  => 'Interaction',
			'physics'      => 'Interaction',
			'flip_card'    => 'Interaction',
			'marquee'      => 'Ambient',
			'confetti'     => 'Ambient',
		);
		return isset( $map[ $fid ] ) ? $map[ $fid ] : __( 'Other', 'fw' );
	}
}
