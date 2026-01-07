<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 * @var string $content
 */

// Handle background color and image
$bg_color = ! empty( $atts['background_color'] )
    ? 'background-color:' . esc_attr( $atts['background_color'] ) . ';'
    : '';

$bg_image = ( ! empty( $atts['background_image'] ) && ! empty( $atts['background_image']['data']['icon'] ) )
    ? 'background-image:url(' . esc_url( $atts['background_image']['data']['icon'] ) . ');'
    : '';

// Background video
$bg_video_data_attr    = [];
$section_extra_classes = '';

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

    $data_name_attr      = version_compare( fw_ext('shortcodes')->manifest->get_version(), '1.3.9', '>=' )
        ? 'data-background-options'
        : 'data-wallpaper-options';

    $bg_video_data_attr[ $data_name_attr ] = fw_htmlspecialchars( json_encode( array(
        'source' => array( $filetype => $atts['video'] )
    ) ) );

    $section_extra_classes .= ' background-video';
}

// Inline style
$section_style = ( $bg_color || $bg_image )
    ? $bg_color . $bg_image
    : '';

// Container type
$container_class = ( isset( $atts['is_fullwidth'] ) && $atts['is_fullwidth'] )
    ? 'fw-container-fluid'
    : 'fw-container';

// Build attributes
$extra_attrs = [];

// Add style only if needed
if ( ! empty( $section_style ) ) {
    $extra_attrs['style'] = $section_style;
}

// Merge with video attributes
$extra_attrs = array_merge( $extra_attrs, $bg_video_data_attr );

$attr = sc_build_wrapper_attr( $atts );
?>

<section <?php echo fw_attr_to_html( $attr ); ?>>
    <div class="<?php echo esc_attr( $container_class ); ?>">
        <?php echo do_shortcode( $content ); ?>
    </div>
</section>
