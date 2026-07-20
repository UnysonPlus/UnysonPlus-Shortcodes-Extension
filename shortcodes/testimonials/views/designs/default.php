<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Classic (Slider / Grid / Single).
 *
 * The original testimonials output, unchanged. Branches on the Layout tab's
 * `layout_type.layout_choice`. All variables come from views/view.php (the
 * dispatcher) by scope. This is the design legacy instances fall back to.
 */
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
    <div class="<?php echo esc_attr( $container_cls ); ?>">

        <?php if ( empty( $testimonials ) ): ?>
            <div class="text-muted small"><?php esc_html_e( 'No testimonials found.', 'fw' ); ?></div>
        <?php else: ?>

            <?php if ( $layout_choice === 'grid' ): ?>
                <div class="fw-row <?php echo esc_attr( trim( $grid_columns . ' ' . $gutter ) ); ?>">
                    <?php foreach ( $testimonials as $t ): ?>
                        <div class="fw-col mb-4 d-flex">
                            <?php echo sc_render_card( $t, [
                                'card_style'      => $card_style,
                                'text_align'      => $text_align,
                                'show_avatar'     => $show_avatar,
                                'avatar_shape'    => $avatar_shape,
                                'avatar_size'     => $avatar_size,
                                'avatar_dim'      => $avatar_dim,
                                'show_rating'     => $show_rating,
                                'avatar_position' => $avatar_position,
                                'quote_color_class'       => $quote_class_extra,
                                'author_name_color_class' => $author_name_class_extra,
                                'author_job_color_class'  => $author_job_class_extra,
                                'site_link_color_class'   => $site_link_class_extra,
                                'quote_color_style'       => $quote_style_extra,
                                'author_name_color_style' => $author_name_style_extra,
                                'author_job_color_style'  => $author_job_style_extra,
                                'site_link_color_style'   => $site_link_style_extra,
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
                            'quote_color_class'       => $quote_class_extra,
                            'author_name_color_class' => $author_name_class_extra,
                            'author_job_color_class'  => $author_job_class_extra,
                            'site_link_color_class'   => $site_link_class_extra,
                        ] ); ?>
                    </div>
                </div>

            <?php else: /* carousel — driven by Splide (the plugin's bundled,
                            Bootstrap-free slider, shared with the Carousel
                            shortcode). Each testimonial is one slide; perPage
                            controls how many show at once. */ ?>
                <?php
                $show_nav = count( $testimonials ) > $items_per_slide;

                $splide_config = array(
                    'type'         => $carousel_wrap ? 'loop' : 'slide',
                    'perPage'      => $items_per_slide,
                    'perMove'      => 1,
                    'rewind'       => ! $carousel_wrap,
                    'arrows'       => ( $carousel_controls && $show_nav ),
                    'pagination'   => ( $carousel_indicators && $indicator_style !== 'none' && $show_nav ),
                    'autoplay'     => $carousel_autoplay,
                    'interval'     => $carousel_interval,
                    'pauseOnHover' => $carousel_pause_hover,
                    'pauseOnFocus' => true,
                    'gap'          => '1.5rem',
                    'breakpoints'  => array(
                        992 => array( 'perPage' => max( 1, min( 2, $items_per_slide ) ) ),
                        576 => array( 'perPage' => 1 ),
                    ),
                );

                // Indicator visual style (dots|lines) → modifier class consumed by CSS.
                $splide_modifier = ( $indicator_style === 'lines' ) ? ' testimonials-splide--lines' : ' testimonials-splide--dots';
                ?>
                <div class="splide testimonials-splide<?php echo esc_attr( $splide_modifier ); ?>"
                     role="group"
                     aria-label="<?php echo esc_attr( __( 'Testimonials', 'fw' ) ); ?>"
                     data-splide="<?php echo esc_attr( wp_json_encode( $splide_config ) ); ?>">
                    <div class="splide__track">
                        <ul class="splide__list">
                            <?php foreach ( $testimonials as $t ): ?>
                                <li class="splide__slide">
                                    <?php echo sc_render_card( $t, [
                                        'card_style'      => trim( $card_style . ' w-100' ),
                                        'text_align'      => $text_align,
                                        'show_avatar'     => $show_avatar,
                                        'avatar_shape'    => $avatar_shape,
                                        'avatar_size'     => $avatar_size,
                                        'avatar_dim'      => $avatar_dim,
                                        'show_rating'     => $show_rating,
                                        'avatar_position' => $avatar_position,
                                        'quote_color_class'       => $quote_class_extra,
                                        'author_name_color_class' => $author_name_class_extra,
                                        'author_job_color_class'  => $author_job_class_extra,
                                        'site_link_color_class'   => $site_link_class_extra,
                                    ] ); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>
