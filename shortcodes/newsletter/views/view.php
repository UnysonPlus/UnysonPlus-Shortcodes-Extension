<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** @var array $atts */

if ( ! function_exists( 'sc_get' ) ) {
	function sc_get( $path, $atts, $default = '' ) {
		if ( function_exists( 'fw_akg' ) ) {
			$v = fw_akg( $path, $atts, null );
			if ( $v !== null ) { return $v; }
		}
		return $default;
	}
}

if ( ! function_exists( 'sc_nl_render' ) ) {
	/** Renders the newsletter shortcode, resolving its design and the title, fields, button, and consent text. */
	function sc_nl_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$design   = sc_get( 'design', $atts, 'inline' );
		if ( function_exists( 'fw_sc_design_resolve' ) ) {
			$design = fw_sc_design_resolve( 'newsletter', $atts, 'inline' );
		} elseif ( ! isset( $registry[ $design ] ) ) {
			$design = 'inline';
		}
		// Render-time enqueue of the resolved design's CSS (static/css/design[s]/<key>.css). The
		// per-instance `fw_ext_shortcodes_enqueue_static:newsletter` action is fired from a scan whose
		// shortcode regex is NOT recursive, so on a deeply nested page-builder / Site-Converter tree it
		// only reaches the outer elements — the action never fires for this one and the design CSS
		// silently never loads, collapsing the design to an unstyled block stack. Deduped by handle.
		if ( function_exists( 'fw_sc_design_enqueue_now' ) ) { fw_sc_design_enqueue_now( 'newsletter', $design ); }


		$title = trim( (string) sc_get( 'title', $atts, '' ) );
		$desc  = trim( (string) sc_get( 'description', $atts, '' ) );
		$show_name = sc_get( 'show_name', $atts, 'no' ) === 'yes';
		$name_ph   = trim( (string) sc_get( 'name_placeholder', $atts, __( 'Your name', 'fw' ) ) );
		$email_ph  = trim( (string) sc_get( 'email_placeholder', $atts, __( 'Your email address', 'fw' ) ) );
		$btn       = trim( (string) sc_get( 'button_label', $atts, __( 'Subscribe', 'fw' ) ) );
		$consent   = trim( (string) sc_get( 'consent_text', $atts, '' ) );
		$success   = trim( (string) sc_get( 'success_message', $atts, __( 'Thanks for subscribing!', 'fw' ) ) );
		$error     = trim( (string) sc_get( 'error_message', $atts, __( 'Something went wrong. Please try again.', 'fw' ) ) );
		$list_id   = trim( (string) sc_get( 'list_id', $atts, '' ) );

		$align   = sc_get( 'align', $atts, 'left' );
		$align_cls = function_exists( 'sc_alignment_class' ) ? sc_alignment_class( $align ) : '';
		$rounded = sc_get( 'rounded', $atts, 'rounded' );

		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$style_var  = $var( 'field_bg', '--nl-field-bg' );
		$style_var .= $var( 'bg_color', '--nl-bg' );
		$style_var .= $var( 'text_color', '--nl-text' );

		$classes = array(
			'fw-nl',
			'fw-nl--design-' . sanitize_html_class( $design ),
			'design-' . sanitize_html_class( $design ), // generic scope for skin packs
			'fw-nl--round-' . sanitize_html_class( $rounded ),
		);
		if ( $align_cls ) { $classes[] = $align_cls; }

		$atts['base_class']       = 'newsletter';
		$atts['unique_id_prefix'] = 'nl-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $style_var !== '' ) {
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;
		}

		$nonce   = wp_create_nonce( 'fw_newsletter' );
		$ajax    = admin_url( 'admin-ajax.php' );
		$source  = function_exists( 'get_permalink' ) ? ( get_permalink() ?: '' ) : '';

		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . '>';

		if ( $title !== '' ) { echo '<h3 class="fw-nl__title">' . esc_html( $title ) . '</h3>'; }
		if ( $desc !== '' ) { echo '<div class="fw-nl__desc">' . wp_kses_post( wpautop( $desc ) ) . '</div>'; }

		echo '<form class="fw-nl__form" novalidate'
			. ' data-ajax="' . esc_url( $ajax ) . '"'
			. ' data-nonce="' . esc_attr( $nonce ) . '"'
			. ' data-success="' . esc_attr( $success ) . '"'
			. ' data-error="' . esc_attr( $error ) . '">';

		echo '<div class="fw-nl__fields">';
		if ( $show_name ) {
			echo '<input class="fw-nl__input fw-nl__input--name" type="text" name="name" placeholder="' . esc_attr( $name_ph ) . '" autocomplete="name" />';
		}
		// FIELD ICON — a glyph inside the email field. The input is wrapped so the icon can sit over its left inset
		// (the wrapper is flex:1 like the input it replaces; the input pads past the glyph via CSS).
		$field_icon = sc_get( 'field_icon', $atts, null );
		$icon_html  = '';
		if ( is_array( $field_icon ) && ! empty( $field_icon['type'] ) && 'none' !== $field_icon['type'] && function_exists( 'sc_icon_render' ) ) {
			if ( 'icon-font' === $field_icon['type'] && isset( fw()->backend ) ) { $pt = fw()->backend->option_type( 'icon' ); if ( $pt && isset( $pt->packs_loader ) ) { $pt->packs_loader->enqueue_pack_for_icon( $field_icon ); } }
			$icon_html = (string) sc_icon_render( $field_icon, array( 'class' => 'fw-nl__field-icon', 'aria_hidden' => true ) );
		}
		if ( '' !== $icon_html ) {
			$ic = sc_get( 'field_icon_color', $atts, '' );
			$ic_css = ( is_array( $ic ) && ! empty( $ic['custom'] ) ) ? ' style="--nl-icon:' . esc_attr( preg_replace( '/[^#0-9a-zA-Z(),.%s-]/', '', (string) $ic['custom'] ) ) . '"' : '';
			echo '<span class="fw-nl__field fw-nl__field--icon"' . $ic_css . '>' . $icon_html;
		}
		echo '<input class="fw-nl__input fw-nl__input--email" type="email" name="email" required placeholder="' . esc_attr( $email_ph ) . '" autocomplete="email" />';
		if ( '' !== $icon_html ) { echo '</span>'; }
		// With a Button Preset the submit wears the theme's .btn classes and the preset owns its
		// look; `fw-nl__btn--preset` tells this element's own CSS to stop painting it. The base
		// `fw-nl__btn` class stays on in both cases — the layout rules and the loading-state
		// spinner hang off it, and the JS finds the button by it.
		$btn_preset = trim( (string) sc_get( 'button_preset', $atts, '' ) );
		// The preset value may carry BOTH a colour preset and a size preset ('btn-silk btn-lg') — sanitize per token.
		$btn_cls    = 'fw-nl__btn' . ( '' !== $btn_preset ? ' fw-nl__btn--preset btn ' . implode( ' ', array_filter( array_map( 'sanitize_html_class', preg_split( '/\s+/', $btn_preset ) ) ) ) : '' );
		echo '<button class="' . esc_attr( $btn_cls ) . '" type="submit">' . esc_html( $btn ) . '</button>';
		echo '</div>';

		// Hidden fields: list id, source page, honeypot.
		echo '<input type="hidden" name="list" value="' . esc_attr( $list_id ) . '" />';
		echo '<input type="hidden" name="source" value="' . esc_url( $source ) . '" />';
		echo '<input type="text" name="fw_hp" class="fw-nl__hp" tabindex="-1" autocomplete="off" aria-hidden="true" />';

		if ( $consent !== '' ) {
			echo '<div class="fw-nl__consent">' . wp_kses_post( $consent ) . '</div>';
		}
		echo '<div class="fw-nl__msg" role="status" aria-live="polite"></div>';

		echo '</form>';
		echo '</div>';
		return ob_get_clean();
	}
}

echo sc_nl_render( $atts );
