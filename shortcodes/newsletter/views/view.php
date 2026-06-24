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
	function sc_nl_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$design   = sc_get( 'design', $atts, 'inline' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'inline'; }

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
		$style_var  = $var( 'accent_color', '--nl-accent' );
		$style_var .= $var( 'field_bg', '--nl-field-bg' );
		$style_var .= $var( 'bg_color', '--nl-bg' );
		$style_var .= $var( 'text_color', '--nl-text' );

		$classes = array(
			'fw-nl',
			'fw-nl--design-' . sanitize_html_class( $design ),
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
		echo '<input class="fw-nl__input fw-nl__input--email" type="email" name="email" required placeholder="' . esc_attr( $email_ph ) . '" autocomplete="email" />';
		echo '<button class="fw-nl__btn" type="submit">' . esc_html( $btn ) . '</button>';
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
