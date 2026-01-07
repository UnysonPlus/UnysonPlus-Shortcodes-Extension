<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */

/* Wrapper base */
$atts['base_class']       = 'testimonials';
$atts['unique_id_prefix'] = 'ts-';
$attr = sc_build_wrapper_attr( $atts );

/* Helper getter */
if ( ! function_exists( 'sc_get' ) ) {
    function sc_get( $path, $atts, $default = '' ) {
        if ( function_exists( 'fw_akg' ) ) {
            $v = fw_akg( $path, $atts, null );
            if ( $v !== null ) return $v;
        }
        return $default;
    }
}

/* Content */
$title        = sc_get( 'title', $atts, sc_get( 'group/title', $atts, '' ) );
$testimonials = sc_get( 'testimonials', $atts, sc_get( 'group/testimonials', $atts, [] ) );
if ( ! is_array( $testimonials ) ) $testimonials = [];

/* Layout */
$layout_choice   = sc_get( 'layout_type/layout_choice', $atts, 'carousel' ); // carousel|grid|single
$grid_columns    = sc_get( 'layout_type/grid/grid_columns', $atts, 'row-cols-3' );
$gutter          = sc_get( 'gutter', $atts, '' );
$text_align      = sc_get( 'text_align', $atts, '' );
$container_cls   = sc_get( 'container_type', $atts, 'container' );
$container_cls   = $container_cls ?: 'container';
$items_per_slide = (int) sc_get( 'items_per_slide', $atts, 1 );
if ( $items_per_slide < 1 ) $items_per_slide = 1;

/* Style */
$card_style       = sc_get( 'card_style', $atts, '' );
$show_avatar      = true; /* default unless explicitly hidden */
$avatar_position  = sc_get( 'avatar_position', $atts, 'top' ); // top|left|right|none
if ( $avatar_position === 'none' ) $show_avatar = false;
$avatar_shape     = sc_get( 'avatar_shape', $atts, 'rounded-circle' );
$avatar_size      = sc_get( 'avatar_size', $atts, 'avatar-md' );
$show_rating      = sc_get( 'show_rating', $atts, 'yes' ) === 'yes';

/* Avatar dimensions (custom utility classes expected in CSS) */
$avatar_dim_map = [ 'avatar-sm' => 64, 'avatar-md' => 96, 'avatar-lg' => 128 ];
$avatar_dim     = isset( $avatar_dim_map[ $avatar_size ] ) ? $avatar_dim_map[ $avatar_size ] : 96;

/* Carousel behavior */
$carousel_autoplay    = sc_get( 'carousel_autoplay', $atts, 'yes' ) === 'yes';
$carousel_interval    = (int) sc_get( 'carousel_interval', $atts, 5000 );
$carousel_pause_hover = sc_get( 'carousel_pause_hover', $atts, 'yes' ) === 'yes';
$carousel_controls    = sc_get( 'carousel_controls', $atts, 'yes' ) === 'yes';
$carousel_indicators  = sc_get( 'carousel_indicators', $atts, 'yes' ) === 'yes';
$carousel_wrap        = sc_get( 'carousel_wrap', $atts, 'yes' ) === 'yes';
$indicator_style      = sc_get( 'carousel_indicator_style', $atts, 'dots' ); // dots|lines|none


/* Output */
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
    <div class="<?php echo esc_attr( $container_cls ); ?>">
        <?php if ( $title ): ?>
            <h3 class="testimonials-title <?php echo esc_attr( $text_align ); ?>"><?php echo esc_html( $title ); ?></h3>
        <?php endif; ?>

        <?php if ( empty( $testimonials ) ): ?>
            <div class="text-muted small"><?php esc_html_e( 'No testimonials found.', 'fw' ); ?></div>
        <?php else: ?>

            <?php if ( $layout_choice === 'grid' ): ?>
                <div class="row <?php echo esc_attr( trim( $grid_columns . ' ' . $gutter ) ); ?>">
                    <?php foreach ( $testimonials as $t ): ?>
                        <div class="col mb-4 d-flex">
                            <?php echo sc_render_card( $t, [
                                'card_style'      => $card_style,
                                'text_align'      => $text_align,
                                'show_avatar'     => $show_avatar,
                                'avatar_shape'    => $avatar_shape,
                                'avatar_size'     => $avatar_size,
                                'avatar_dim'      => $avatar_dim,
                                'show_rating'     => $show_rating,
                                'avatar_position' => $avatar_position,
                            ] ); ?>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php elseif ( $layout_choice === 'single' ): ?>
                <?php $t = $testimonials[0]; ?>
                <div class="d-flex flex-column align-items-center">
                    <div class="w-100" style="max-width:700px;">
                        <?php echo sc_render_card( $t, [
                            'card_style'      => $card_style,
                            'text_align'      => $text_align,
                            'show_avatar'     => $show_avatar,
                            'avatar_shape'    => $avatar_shape,
                            'avatar_size'     => $avatar_size,
                            'avatar_dim'      => $avatar_dim,
                            'show_rating'     => $show_rating,
                            'avatar_position' => $avatar_position,
                        ] ); ?>
                    </div>
                </div>

            <?php else: /* carousel */ ?>
                <?php
                $carousel_id = uniqid( 'testimonial-carousel-' );
                $groups      = sc_chunk( $testimonials, $items_per_slide );
                $col_class   = sc_col_class( $items_per_slide );

                // Map indicator style to a class (previously computed but not applied)
                $indicator_class = '';
                switch ( $indicator_style ) {
                    case 'dots':
                        $indicator_class = ' indicators-dots';
                        break;
                    case 'lines':
                        $indicator_class = ' indicators-lines';
                        break;
                    // 'none' leaves $indicator_class empty and indicators skipped below
                }
                ?>
                <div id="<?php echo esc_attr( $carousel_id ); ?>"
                     class="carousel slide carousel-dark"
                     data-bs-ride="<?php echo $carousel_autoplay ? 'carousel' : 'false'; ?>"
                     data-bs-interval="<?php echo $carousel_autoplay ? esc_attr( $carousel_interval ) : 'false'; ?>"
                     data-bs-pause="<?php echo $carousel_pause_hover ? 'hover' : 'false'; ?>"
                     data-bs-wrap="<?php echo $carousel_wrap ? 'true' : 'false'; ?>">

                    <?php if ( $carousel_indicators && $indicator_style !== 'none' && count( $groups ) > 1 ): ?>
                        <div class="carousel-indicators<?php echo esc_attr( $indicator_class ); ?>">
                            <?php foreach ( $groups as $i => $_group ): ?>
                                <button type="button"
                                        data-bs-target="#<?php echo esc_attr( $carousel_id ); ?>"
                                        data-bs-slide-to="<?php echo esc_attr( $i ); ?>"
                                        <?php if ( $i === 0 ) echo 'class="active" aria-current="true"'; ?>
                                        aria-label="<?php echo esc_attr( sprintf( __( 'Slide %d', 'fw' ), $i + 1 ) ); ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="carousel-inner">
                        <?php foreach ( $groups as $i => $group ): ?>
                            <div class="carousel-item<?php if ( $i === 0 ) echo ' active'; ?>">
                                <div class="px-3 px-md-4">
                                    <div class="row justify-content-center <?php echo esc_attr( $gutter ); ?>">
                                        <?php foreach ( $group as $t ): ?>
                                            <div class="<?php echo esc_attr( $col_class ); ?> mb-4 d-flex">
                                                <?php echo sc_render_card( $t, [
                                                    'card_style'      => $card_style . ' w-100',
                                                    'text_align'      => $text_align,
                                                    'show_avatar'     => $show_avatar,
                                                    'avatar_shape'    => $avatar_shape,
                                                    'avatar_size'     => $avatar_size,
                                                    'avatar_dim'      => $avatar_dim,
                                                    'show_rating'     => $show_rating,
                                                    'avatar_position' => $avatar_position,
                                                ] ); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ( $carousel_controls && count( $groups ) > 1 ): ?>
                        <button class="carousel-control-prev"
                                type="button"
                                data-bs-target="#<?php echo esc_attr( $carousel_id ); ?>"
                                data-bs-slide="prev"
                                style="width:auto;left:-1rem;">
                            <span aria-hidden="true" class="d-inline-flex align-items-center">
                                <i class="fa-solid fa-chevron-left fs-2 text-dark opacity-50"></i>
                            </span>
                            <span class="visually-hidden"><?php _e( 'Previous', 'fw' ); ?></span>
                        </button>
                        <button class="carousel-control-next"
                                type="button"
                                data-bs-target="#<?php echo esc_attr( $carousel_id ); ?>"
                                data-bs-slide="next"
                                style="width:auto;right:-1rem;">
                            <span aria-hidden="true" class="d-inline-flex align-items-center">
                                <i class="fa-solid fa-chevron-right fs-2 text-dark opacity-50"></i>
                            </span>
                            <span class="visually-hidden"><?php _e( 'Next', 'fw' ); ?></span>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>