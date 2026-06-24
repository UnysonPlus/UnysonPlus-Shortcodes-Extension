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

if ( ! function_exists( 'sc_ab_render' ) ) {
	function sc_ab_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$catalog  = require __DIR__ . '/parts/socials.php';
		$design   = sc_get( 'design', $atts, 'card' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'card'; }

		$source = sc_get( 'source', $atts, 'current' );
		$uid    = 0;
		if ( $source === 'user' ) {
			$uid = (int) sc_get( 'user_id', $atts, 0 );
		} elseif ( $source === 'current' ) {
			$pid = get_the_ID();
			if ( $pid ) { $uid = (int) get_post_field( 'post_author', $pid ); }
			if ( ! $uid && function_exists( 'get_queried_object' ) ) {
				$qo = get_queried_object();
				if ( $qo instanceof WP_User ) { $uid = (int) $qo->ID; }
			}
		}
		$user = $uid ? get_userdata( $uid ) : false;

		$avatar_size = max( 32, (int) sc_get( 'avatar_size', $atts, 84 ) );

		$name = trim( (string) sc_get( 'name', $atts, '' ) );
		if ( $name === '' && $user ) { $name = $user->display_name; }
		$role = trim( (string) sc_get( 'role', $atts, '' ) );
		$bio  = trim( (string) sc_get( 'bio', $atts, '' ) );
		if ( $bio === '' && $user ) { $bio = (string) get_the_author_meta( 'description', $uid ); }

		$avatar_raw = sc_get( 'avatar', $atts, array() );
		$avatar_url = ( is_array( $avatar_raw ) && ! empty( $avatar_raw['url'] ) ) ? $avatar_raw['url'] : '';
		if ( $avatar_url === '' && $user ) { $avatar_url = get_avatar_url( $uid, array( 'size' => $avatar_size * 2 ) ); }

		if ( $name === '' && $bio === '' && $avatar_url === '' ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-ab__empty">' . esc_html__( 'No author found — pick a user or use Custom.', 'fw' ) . '</div>';
			}
			return '';
		}

		$archive  = $user ? get_author_posts_url( $uid ) : '';
		$show_posts = sc_get( 'show_posts', $atts, 'yes' ) === 'yes' && $archive !== '';
		$shape    = sc_get( 'avatar_shape', $atts, 'circle' );

		$socials = sc_get( 'socials', $atts, array() );
		if ( ! is_array( $socials ) ) { $socials = array(); }

		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$style_var  = '--ab-avatar:' . $avatar_size . 'px;';
		$style_var .= $var( 'accent_color', '--ab-accent' );
		$style_var .= $var( 'card_bg', '--ab-card-bg' );
		$style_var .= $var( 'name_color', '--ab-name' );
		$style_var .= $var( 'text_color', '--ab-text' );

		$classes = array(
			'fw-ab',
			'fw-ab--design-' . sanitize_html_class( $design ),
			'fw-ab--avatar-' . sanitize_html_class( $shape ),
		);

		$atts['base_class']       = 'author-box';
		$atts['unique_id_prefix'] = 'ab-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;

		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . '>';

		if ( $avatar_url !== '' ) {
			echo '<div class="fw-ab__avatar"><img src="' . esc_url( $avatar_url ) . '" alt="' . esc_attr( $name ) . '" loading="lazy" decoding="async" /></div>';
		}

		echo '<div class="fw-ab__body">';
		if ( $name !== '' ) {
			$name_html = $archive !== '' ? '<a href="' . esc_url( $archive ) . '">' . esc_html( $name ) . '</a>' : esc_html( $name );
			echo '<div class="fw-ab__name">' . $name_html . '</div>'; // phpcs:ignore
		}
		if ( $role !== '' ) { echo '<div class="fw-ab__role">' . esc_html( $role ) . '</div>'; }
		if ( $bio !== '' ) { echo '<div class="fw-ab__bio">' . wp_kses_post( wpautop( $bio ) ) . '</div>'; }

		if ( ! empty( $socials ) || $show_posts ) {
			echo '<div class="fw-ab__footer">';
			if ( ! empty( $socials ) ) {
				echo '<div class="fw-ab__socials">';
				foreach ( $socials as $s ) {
					$net = isset( $s['network'] ) ? $s['network'] : '';
					$url = isset( $s['url'] ) ? trim( (string) $s['url'] ) : '';
					if ( $url === '' || ! isset( $catalog[ $net ] ) ) { continue; }
					$href = ( $net === 'email' && strpos( $url, '@' ) !== false && strpos( $url, 'mailto:' ) !== 0 ) ? 'mailto:' . $url : $url;
					echo '<a class="fw-ab__social fw-ab__social--' . sanitize_html_class( $net ) . '" href="' . esc_url( $href ) . '"'
						. ( $net !== 'email' ? ' target="_blank" rel="noopener noreferrer"' : '' )
						. ' aria-label="' . esc_attr( $catalog[ $net ]['label'] ) . '">' . $catalog[ $net ]['icon'] . '</a>'; // phpcs:ignore
				}
				echo '</div>';
			}
			if ( $show_posts ) {
				echo '<a class="fw-ab__posts" href="' . esc_url( $archive ) . '">' . esc_html__( 'View all posts', 'fw' ) . ' <span aria-hidden="true">&rarr;</span></a>';
			}
			echo '</div>';
		}
		echo '</div>';

		echo '</div>';
		return ob_get_clean();
	}
}

echo sc_ab_render( $atts );
