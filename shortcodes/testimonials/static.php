<?php if (!defined('FW')) die('Forbidden');

$shortcodes_extension = fw_ext('shortcodes');
wp_enqueue_style(
	'fw-shortcode-testimonials',
	$shortcodes_extension->get_declared_URI('/shortcodes/testimonials/static/css/styles.css'),
	array('font-awesome')
);
wp_enqueue_script(
	'fw-shortcode-testimonials-caroufredsel',
	$shortcodes_extension->get_declared_URI('/shortcodes/testimonials/static/js/jquery.carouFredSel-6.2.1-packed.js'),
	array('jquery'),
	false,
	true
);


/* Utility helpers */
if ( ! function_exists( 'sc_chunk' ) ) {
    function sc_chunk( $array, $size ) { return array_chunk( $array, $size ?: 1 ); }
}
if ( ! function_exists( 'sc_col_class' ) ) {
    function sc_col_class( $n ) {
        switch ( (int) $n ) {
            case 3: return 'col-12 col-md-4';
            case 2: return 'col-12 col-md-6';
            default: return 'col-12';
        }
    }
}
/* Rating renderer (Font Awesome 6) */
if ( ! function_exists( 'sc_render_rating' ) ) {
    function sc_render_rating( $rating ) {
        if ($rating === '' || $rating === null) return '';
        $rating = (float) $rating;
        if ($rating <= 0) return '';
        if ($rating > 5) $rating = 5.0;

        $full  = (int) floor($rating);
        $half  = ($rating - $full) >= 0.5 ? 1 : 0;
        if ($full === 5) $half = 0;
        $empty = 5 - $full - $half;

        $out  = '<span class="testimonial-rating d-inline-flex" aria-label="' . esc_attr( sprintf( __( 'Rated %s out of 5', 'fw' ), number_format($rating,1) ) ) . '">';
        for ($i=0; $i<$full; $i++)  $out .= '<i class="fa-solid fa-star text-warning" aria-hidden="true"></i>';
        if ($half) $out .= '<i class="fa-solid fa-star-half-stroke text-warning" aria-hidden="true"></i>';
        for ($i=0; $i<$empty; $i++) $out .= '<i class="fa-regular fa-star text-warning" aria-hidden="true"></i>';
        $out .= '</span>';
        return $out;
    }
}

if ( ! function_exists( 'sc_render_card' ) ) {
    function sc_render_card( $t, $args ) {
        $card_style      = $args['card_style'];
        $text_align      = $args['text_align'];
        $show_avatar     = $args['show_avatar'];
        $avatar_shape    = $args['avatar_shape'];
        $avatar_size     = $args['avatar_size'];
        $avatar_dim      = (int) $args['avatar_dim'];
        $show_rating     = $args['show_rating'];
        $avatar_position = $args['avatar_position']; // top|left|right|none

        $content     = isset( $t['content'] ) ? $t['content'] : '';
        $author_name = isset( $t['author_name'] ) ? $t['author_name'] : '';
        $author_job  = isset( $t['author_job'] ) ? $t['author_job'] : '';
        $site_name   = isset( $t['site_name'] ) ? $t['site_name'] : '';
        $site_url    = isset( $t['site_url'] ) ? $t['site_url'] : '';
        $rating_val  = isset( $t['rating'] ) ? $t['rating'] : '';

        $avatar_html = '';
        if ( $show_avatar && ! empty( $t['author_avatar']['url'] ) ) {
            $url = esc_url( $t['author_avatar']['url'] );
            // Center on mobile, normal flow desktop
            $avatar_html = '<div class="testimonial-avatar text-center flex-shrink-0 mx-auto mb-3">'
                . '<img src="' . $url . '" alt="' . esc_attr( $author_name ) . '" class="img-fluid '
                . esc_attr( $avatar_shape . ' ' . $avatar_size )
                . '" style="width:' . $avatar_dim . 'px;height:' . $avatar_dim . 'px;object-fit:cover;" />'
                . '</div>';
        }

        $rating_html = $show_rating && function_exists('sc_render_rating')
            ? sc_render_rating( $rating_val )
            : '';

        $site_html = '';
        if ( $site_name && $site_url ) {
            $site_html = '<span class="testimonial-site"><a href="' . esc_url( $site_url ) . '" rel="nofollow" target="_blank">' . esc_html( $site_name ) . '</a></span>';
        }

        $meta_parts  = array_filter( [
            $author_job ? '<span class="testimonial-job">' . esc_html( $author_job ) . '</span>' : '',
            $site_html
        ] );
        $author_meta = $meta_parts
            ? '<div class="testimonial-meta small text-muted">' . implode( ' <span class="sep">|</span> ', $meta_parts ) . '</div>'
            : '';

        $author_block = '';
        if ( $author_name ) {
            $author_block .= '<div class="testimonial-author fw-semibold">' . esc_html( $author_name ) . '</div>';
        }
        $author_block .= $author_meta;
        if ( $rating_html ) {
            $author_block .= '<div class="mt-2">' . $rating_html . '</div>';
        }

        $quote_html = '<blockquote class="testimonial-quote mb-3"><p class="mb-0">'
            . esc_html( $content ) . '</p></blockquote>' . $author_block;

        $classes = trim( 'testimonial-item ' . $card_style . ' ' . $text_align . ' avatar-pos-' . $avatar_position );
        $classes = preg_replace( '/\s+/', ' ', $classes );

        ob_start();

        // Stacked (top) or no avatar
        if ( ! $show_avatar || $avatar_position === 'top' ) {
            echo '<div class="' . esc_attr( $classes ) . ' d-flex flex-column w-100 text-center">';
            if ( $show_avatar && $avatar_position === 'top' ) {
                echo '<div>' . $avatar_html . '</div>';
            }
            echo '<div class="testimonial-body">' . $quote_html . '</div>';
            echo '</div>';
        }
        // Left / Right with mobile stacking
        elseif ( $avatar_position === 'left' || $avatar_position === 'right' ) {
            // Stack vertically on mobile (centered), horizontal from md up
            $row_classes = 'd-flex flex-column flex-md-row align-items-start gap-3 w-100';
            if ( $avatar_position === 'right' ) {
                $row_classes .= ' flex-md-row-reverse';
            }
            // Body text centered on mobile, original alignment (or start) on md+
            $body_text_classes = 'testimonial-body flex-grow-1 text-center text-md-start';
            echo '<div class="' . esc_attr( $classes ) . ' ' . $row_classes . '">';
            echo $avatar_html;
            echo '<div class="' . esc_attr( $body_text_classes ) . '">' . $quote_html . '</div>';
            echo '</div>';
        }

        return ob_get_clean();
    }
}
?>