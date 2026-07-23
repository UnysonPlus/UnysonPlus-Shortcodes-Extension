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

if ( ! function_exists( 'sc_pc_render' ) ) {
	function sc_pc_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$design   = sc_get( 'design', $atts, 'standard' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'standard'; }

		$post_type = sanitize_key( (string) sc_get( 'post_type', $atts, 'post' ) );
		if ( $post_type === '' || ! post_type_exists( $post_type ) ) { $post_type = 'post'; }
		$number = (int) sc_get( 'number', $atts, 9 );
		$number = $number > 0 ? min( 40, $number ) : 9;

		$q_args = array(
			'post_type'           => $post_type,
			'posts_per_page'      => $number,
			'orderby'             => sc_get( 'orderby', $atts, 'date' ),
			'order'               => sc_get( 'order', $atts, 'DESC' ) === 'ASC' ? 'ASC' : 'DESC',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		);
		$tax   = sanitize_key( (string) sc_get( 'taxonomy', $atts, 'category' ) );
		$terms = trim( (string) sc_get( 'terms', $atts, '' ) );
		if ( $terms !== '' && $tax !== '' && taxonomy_exists( $tax ) ) {
			$slugs = array_filter( array_map( 'trim', explode( ',', $terms ) ) );
			if ( ! empty( $slugs ) ) {
				$q_args['tax_query'] = array( array( 'taxonomy' => $tax, 'field' => 'slug', 'terms' => $slugs ) );
			}
		}

		$query = new WP_Query( $q_args );
		if ( ! $query->have_posts() ) {
			wp_reset_postdata();
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-pc__empty">' . esc_html__( 'No posts found for this query.', 'fw' ) . '</div>';
			}
			return '';
		}

		/* Card options. */
		$ratio    = sc_get( 'image_ratio', $atts, 'ratio-16-9' );
		$show_exc = sc_get( 'show_excerpt', $atts, 'yes' ) === 'yes';
		$exc_len  = max( 4, (int) sc_get( 'excerpt_length', $atts, 18 ) );
		$show_date= sc_get( 'show_date', $atts, 'yes' ) === 'yes';
		$show_meta= sc_get( 'show_meta', $atts, 'no' ) === 'yes';
		$readmore = trim( (string) sc_get( 'readmore', $atts, '' ) );

		/* Carousel options. */
		$per_view = max( 1, (int) sc_get( 'per_view', $atts, 3 ) );
		$autoplay = sc_get( 'autoplay', $atts, 'no' ) === 'yes';
		$loop     = sc_get( 'loop', $atts, 'yes' ) === 'yes';
		$arrows   = sc_get( 'arrows', $atts, 'yes' ) === 'yes';
		$dots     = sc_get( 'dots', $atts, 'yes' ) === 'yes';
		$gap_slug = preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) sc_get( 'gap', $atts, '4' ) ) );
		$gap_css  = $gap_slug === '' ? '0px' : 'var(--gap-' . $gap_slug . ', 1.5rem)';

		$show_nav = $query->post_count > $per_view;
		$splide_cfg = array(
			'type'         => ( $loop && $show_nav ) ? 'loop' : 'slide',
			'perPage'      => $per_view,
			'perMove'      => 1,
			'rewind'       => ! $loop,
			'arrows'       => ( $arrows && $show_nav ),
			'pagination'   => ( $dots && $show_nav ),
			'autoplay'     => $autoplay,
			'interval'     => 4500,
			'pauseOnHover' => true,
			'gap'          => $gap_css,
			'breakpoints'  => array( 992 => array( 'perPage' => max( 1, min( 2, $per_view ) ) ), 576 => array( 'perPage' => 1 ) ),
		);

		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$style_var  = $var( 'accent_color', '--pc-accent' );
		$style_var .= $var( 'card_bg', '--pc-card-bg' );
		$style_var .= $var( 'title_color', '--pc-title' );
		$style_var .= $var( 'text_color', '--pc-text' );

		$classes = array(
			'fw-pc',
			'fw-pc--design-' . sanitize_html_class( $design ),
			'fw-pc--' . sanitize_html_class( $ratio ),
		);

		$atts['base_class']       = 'post-carousel';
		$atts['unique_id_prefix'] = 'pc-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $style_var !== '' ) {
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;
		}

		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . '>';
		echo '<div class="splide fw-pc__carousel" role="group" aria-label="' . esc_attr__( 'Posts', 'fw' ) . '" data-splide="' . esc_attr( wp_json_encode( $splide_cfg ) ) . '">';
		echo '<div class="splide__track"><ul class="splide__list">';

		// Image Style preset (Components → Image Styles): the .fw-pc__media anchor doubles as .imgs-wrap.
			$imgs_cls = function_exists( 'sc_image_style_class' ) ? sc_image_style_class( $atts ) : '';
			$pc_media_cls = 'fw-pc__media' . ( $imgs_cls !== '' ? ' imgs-wrap ' . $imgs_cls : '' );

			while ( $query->have_posts() ) {
			$query->the_post();
			$permalink = get_permalink();
			$has_thumb = has_post_thumbnail();

			echo '<li class="splide__slide"><article class="fw-pc__card">';

			if ( $design !== 'minimal' && $has_thumb ) {
				echo '<a class="' . esc_attr( $pc_media_cls ) . '" href="' . esc_url( $permalink ) . '">' . get_the_post_thumbnail( get_the_ID(), 'large', array( 'class' => 'fw-pc__img', 'loading' => 'lazy' ) );
				if ( $design === 'overlay' ) { echo '<span class="fw-pc__scrim"></span>'; }
				echo '</a>';
			}

			echo '<div class="fw-pc__body">';
			if ( $show_meta ) {
				$cats = get_the_category_list( ', ' );
				if ( $cats ) { echo '<div class="fw-pc__cat">' . wp_kses_post( $cats ) . '</div>'; }
			}
			echo '<h4 class="fw-pc__title"><a href="' . esc_url( $permalink ) . '">' . esc_html( get_the_title() ) . '</a></h4>';
			if ( $show_date || $show_meta ) {
				echo '<div class="fw-pc__meta">';
				if ( $show_date ) { echo '<span class="fw-pc__date">' . esc_html( get_the_date() ) . '</span>'; }
				if ( $show_meta ) { echo '<span class="fw-pc__author">' . esc_html( get_the_author() ) . '</span>'; }
				echo '</div>';
			}
			if ( $design !== 'overlay' && $show_exc ) {
				echo '<p class="fw-pc__excerpt">' . esc_html( wp_trim_words( get_the_excerpt(), $exc_len, '…' ) ) . '</p>';
			}
			if ( $readmore !== '' ) {
				echo '<a class="fw-pc__more" href="' . esc_url( $permalink ) . '">' . esc_html( $readmore ) . ' <span aria-hidden="true">&rarr;</span></a>';
			}
			echo '</div>';

			echo '</article></li>';
		}
		wp_reset_postdata();

		echo '</ul></div></div></div>';
		return ob_get_clean();
	}
}

echo sc_pc_render( $atts );
