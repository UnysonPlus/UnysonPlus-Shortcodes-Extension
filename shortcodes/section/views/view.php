<?php if ( ! defined( 'FW' ) ) {
        die( 'Forbidden' );
}

/**
 * @var array $atts
 * @var string $content
 */

$bg_color = ! empty( $atts['background_color'] )
        ? 'background-color:' . esc_attr( $atts['background_color'] ) . ';'
        : '';

$bg_image = ( ! empty( $atts['background_image'] ) && ! empty( $atts['background_image']['data']['icon'] ) )
        ? 'background-image:url(' . esc_url( $atts['background_image']['data']['icon'] ) . '); background-size:cover; background-position:center;'
        : '';

$bg_video_data_attr    = [];
$section_extra_classes = '';

$variant = ( isset( $atts['variant'] ) && in_array( $atts['variant'], [ 'alt', 'light', 'dark' ], true ) )
        ? $atts['variant']
        : '';

if ( $variant !== '' ) {
        $section_extra_classes .= ' section--' . $variant;
}

// Per-section column-gap modifier classes — picked up by css-tokens.php's
// `.section--gap-{slug} .row` / `.section--gap-x-{slug} .row` /
// `.section--gap-y-{slug} .row` rules which override Bootstrap's
// --bs-gutter-x / --bs-gutter-y on every row inside this section.
// Empty atts (the "Use Default Gap" / "Use Section Gap" dropdown choices)
// emit nothing, so the site-wide Theme Settings default applies.
foreach ( [ 'gap' => 'section--gap-', 'gap_x' => 'section--gap-x-', 'gap_y' => 'section--gap-y-' ] as $att_key => $class_prefix ) {
        if ( empty( $atts[ $att_key ] ) ) { continue; }
        $slug = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $atts[ $att_key ] );
        if ( $slug === '' ) { continue; }
        $section_extra_classes .= ' ' . $class_prefix . strtolower( $slug );
}

if ( ! empty( $atts['video'] ) ) {
        $filetype  = wp_check_filetype( $atts['video'] );
        $filetypes = array(
                'mp4'  => 'mp4',
                'ogv'  => 'ogg',
                'webm' => 'webm',
                'jpg'  => 'poster',
        );

        $filetype = array_key_exists( (string) $filetype['ext'], $filetypes )
                ? $filetypes[ $filetype['ext'] ]
                : 'video';

        $data_name_attr = version_compare( fw_ext('shortcodes')->manifest->get_version(), '1.3.9', '>=' )
                ? 'data-background-options'
                : 'data-wallpaper-options';

        $bg_video_data_attr[ $data_name_attr ] = fw_htmlspecialchars( json_encode( array(
                'source' => array( $filetype => $atts['video'] )
        ) ) );

        $section_extra_classes .= ' background-video';
}

$section_style = $bg_color . $bg_image;

$container_class = ( isset( $atts['is_fullwidth'] ) && $atts['is_fullwidth'] )
        ? 'fw-container-fluid'
        : 'fw-container';

$attr = sc_build_wrapper_attr( $atts );

$bleed_data    = ! empty( $atts['bleed_layout'] ) ? $atts['bleed_layout'] : [];
$bleed_enabled = ! empty( $bleed_data['bleed_enabled'] ) && $bleed_data['bleed_enabled'] === 'yes';
$bleed_opts    = $bleed_enabled && ! empty( $bleed_data['yes'] ) ? $bleed_data['yes'] : [];

$bleed_url = '';
if ( ! empty( $bleed_opts['bleed_image'] ) ) {
        $bi = $bleed_opts['bleed_image'];
        if ( is_array( $bi ) ) {
                if ( ! empty( $bi['url'] ) ) {
                        $bleed_url = $bi['url'];
                } elseif ( ! empty( $bi['data']['icon'] ) ) {
                        $bleed_url = $bi['data']['icon'];
                } elseif ( ! empty( $bi['attachment_id'] ) && function_exists( 'wp_get_attachment_url' ) ) {
                        $bleed_url = wp_get_attachment_url( $bi['attachment_id'] );
                }
        } elseif ( is_numeric( $bi ) && function_exists( 'wp_get_attachment_url' ) ) {
                $bleed_url = wp_get_attachment_url( $bi );
        }
}

if ( empty( $bleed_url ) && ! empty( $atts['bleed_image'] ) ) {
        $bi_legacy = $atts['bleed_image'];
        if ( is_array( $bi_legacy ) ) {
                if ( ! empty( $bi_legacy['url'] ) ) {
                        $bleed_url = $bi_legacy['url'];
                } elseif ( ! empty( $bi_legacy['data']['icon'] ) ) {
                        $bleed_url = $bi_legacy['data']['icon'];
                } elseif ( ! empty( $bi_legacy['attachment_id'] ) && function_exists( 'wp_get_attachment_url' ) ) {
                        $bleed_url = wp_get_attachment_url( $bi_legacy['attachment_id'] );
                }
        } elseif ( is_numeric( $bi_legacy ) && function_exists( 'wp_get_attachment_url' ) ) {
                $bleed_url = wp_get_attachment_url( $bi_legacy );
        }
        if ( ! empty( $bleed_url ) ) {
                $bleed_enabled = true;
                $bleed_opts = [
                        'bleed_bg_color'        => ! empty( $atts['background_color'] ) ? $atts['background_color'] : '',
                        'bleed_image_position'  => ! empty( $atts['bleed_image_position'] ) ? $atts['bleed_image_position'] : 'center',
                        'bleed_image_side'      => ! empty( $atts['bleed_image_side'] ) ? $atts['bleed_image_side'] : 'right',
                        'bleed_image_ratio'     => ! empty( $atts['bleed_image_ratio'] ) ? $atts['bleed_image_ratio'] : '5-7',
                        'bleed_vertical_align'  => ! empty( $atts['bleed_vertical_align'] ) ? $atts['bleed_vertical_align'] : 'align-items-center',
                        'bleed_content_padding' => ! empty( $atts['bleed_content_padding'] ) ? $atts['bleed_content_padding'] : '3rem',
                        'bleed_mobile_stacking' => ! empty( $atts['bleed_mobile_stacking'] ) ? $atts['bleed_mobile_stacking'] : 'content-first',
                ];
        }
}

$has_bleed = $bleed_enabled && ! empty( $bleed_url );

if ( $has_bleed ) :

        $existing_class = ! empty( $attr['class'] ) ? $attr['class'] . ' ' : '';
        $bleed_extra    = trim( 'section--bleed ' . trim( $section_extra_classes ) );
        $attr['class']  = $existing_class . $bleed_extra;

        $bleed_side     = ! empty( $bleed_opts['bleed_image_side'] ) ? $bleed_opts['bleed_image_side'] : 'right';
        $bleed_ratio    = ! empty( $bleed_opts['bleed_image_ratio'] ) ? $bleed_opts['bleed_image_ratio'] : '5-7';
        $bleed_position = ! empty( $bleed_opts['bleed_image_position'] ) ? $bleed_opts['bleed_image_position'] : 'center';
        $bleed_valign_map = [
                'align-items-start'  => 'flex-start',
                'align-items-center' => 'center',
                'align-items-end'    => 'flex-end',
        ];
        $bleed_valign_raw = ! empty( $bleed_opts['bleed_vertical_align'] ) ? $bleed_opts['bleed_vertical_align'] : 'align-items-center';
        $bleed_justify    = isset( $bleed_valign_map[ $bleed_valign_raw ] ) ? $bleed_valign_map[ $bleed_valign_raw ] : 'center';
        $bleed_padding    = ! empty( $bleed_opts['bleed_content_padding'] ) ? $bleed_opts['bleed_content_padding'] : '3rem';
        $bleed_mobile     = ! empty( $bleed_opts['bleed_mobile_stacking'] ) ? $bleed_opts['bleed_mobile_stacking'] : 'content-first';

        $bleed_bg_color = ! empty( $bleed_opts['bleed_bg_color'] )
                ? 'background-color:' . esc_attr( $bleed_opts['bleed_bg_color'] ) . ';'
                : '';

        $ratio_parts = explode( '-', $bleed_ratio );
        $image_col   = isset( $ratio_parts[0] ) ? (int) $ratio_parts[0] : 5;
        $content_col = isset( $ratio_parts[1] ) ? (int) $ratio_parts[1] : 7;

        $bleed_alt = '';
        $bi_data = ! empty( $bleed_opts['bleed_image'] ) ? $bleed_opts['bleed_image'] : ( ! empty( $atts['bleed_image'] ) ? $atts['bleed_image'] : [] );
        $bleed_att_id = 0;
        if ( is_array( $bi_data ) && ! empty( $bi_data['attachment_id'] ) ) {
                $bleed_att_id = (int) $bi_data['attachment_id'];
        } elseif ( is_numeric( $bi_data ) ) {
                $bleed_att_id = (int) $bi_data;
        }
        if ( $bleed_att_id && function_exists( 'get_post_meta' ) ) {
                $bleed_alt = get_post_meta( $bleed_att_id, '_wp_attachment_image_alt', true );
        }

        $padding_style = $bleed_padding !== '0'
                ? 'padding-top:' . esc_attr( $bleed_padding ) . ';padding-bottom:' . esc_attr( $bleed_padding ) . ';'
                : '';

        $content_style = 'display:flex;flex-direction:column;justify-content:' . esc_attr( $bleed_justify ) . ';'
                . $padding_style;

        $content_order = '';
        $image_order   = '';

        if ( $bleed_side === 'right' ) {
                if ( $bleed_mobile === 'image-first' ) {
                        $content_order = ' order-2 order-md-1';
                        $image_order   = ' order-1 order-md-2';
                }
        } else {
                if ( $bleed_mobile === 'content-first' ) {
                        $image_order   = ' order-2';
                        $content_order = ' order-1';
                } else {
                        $image_order   = ' order-md-2';
                        $content_order = ' order-md-1';
                }
        }

        $image_pct   = round( ( $image_col / 12 ) * 100, 6 );
        $content_pct = round( ( $content_col / 12 ) * 100, 6 );

        if ( $bleed_side === 'right' ) {
                $bleed_img_style = 'right:0;width:' . $image_pct . '%;';
                $bleed_bg_inline = 'left:0;width:' . $content_pct . '%;' . $bleed_bg_color;
        } else {
                $bleed_img_style = 'left:0;width:' . $image_pct . '%;';
                $bleed_bg_inline = 'right:0;width:' . $content_pct . '%;' . $bleed_bg_color;
        }

        $section_attr_style = ! empty( $attr['style'] ) ? $attr['style'] : '';
        $attr['style'] = $section_attr_style;

?>

<section <?php echo fw_attr_to_html( $attr ); ?>>
        <?php if ( ! empty( $bleed_bg_color ) ) : ?>
                <div class="section__bleed-bg" style="<?php echo $bleed_bg_inline; ?>"></div>
        <?php endif; ?>
        <div class="section__bleed-img" style="<?php echo $bleed_img_style; ?>">
                <img src="<?php echo esc_url( $bleed_url ); ?>" alt="<?php echo esc_attr( $bleed_alt ); ?>" style="object-position:<?php echo esc_attr( $bleed_position ); ?>;" />
        </div>
        <div class="<?php echo esc_attr( $container_class ); ?>" style="position:relative;z-index:2;">
                <div class="row">
                        <?php if ( $bleed_side === 'left' ) : ?>
                                <div class="col-md-<?php echo esc_attr( $image_col . $image_order ); ?>">
                                </div>
                                <div class="col-md-<?php echo esc_attr( $content_col . $content_order ); ?>" style="<?php echo $content_style; ?>">
                                        <?php echo do_shortcode( $content ); ?>
                                </div>
                        <?php else : ?>
                                <div class="col-md-<?php echo esc_attr( $content_col . $content_order ); ?>" style="<?php echo $content_style; ?>">
                                        <?php echo do_shortcode( $content ); ?>
                                </div>
                                <div class="col-md-<?php echo esc_attr( $image_col . $image_order ); ?>">
                                </div>
                        <?php endif; ?>
                </div>
        </div>
</section>

<?php else : ?>

<?php
        if ( ! empty( $section_style ) ) {
                $existing_style = ! empty( $attr['style'] ) ? rtrim( $attr['style'], '; ' ) . '; ' : '';
                $attr['style'] = $existing_style . $section_style;
        }

        $attr = array_merge( $attr, $bg_video_data_attr );

        if ( ! empty( $section_extra_classes ) ) {
                $existing_class = ! empty( $attr['class'] ) ? $attr['class'] . ' ' : '';
                $attr['class'] = $existing_class . trim( $section_extra_classes );
        }
?>

<section <?php echo fw_attr_to_html( $attr ); ?>>
        <div class="<?php echo esc_attr( $container_class ); ?>">
                <?php echo do_shortcode( $content ); ?>
        </div>
</section>

<?php endif; ?>
