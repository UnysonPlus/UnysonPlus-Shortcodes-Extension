<?php if ( ! defined( 'FW' ) ) {
        die( 'Forbidden' );
}

/**
 * @var array $atts
 */

if ( empty( $atts['image'] ) && empty( $atts['content'] ) ) {
        return;
}

$atts['base_class']       = 'image-content';
$atts['unique_id_prefix'] = 'ic-';
$atts['extra_attrs']      = [];

$attr = sc_build_wrapper_attr( $atts );

$layout         = ! empty( $atts['layout'] ) ? $atts['layout'] : 'image-left';
$column_ratio   = ! empty( $atts['column_ratio'] ) ? $atts['column_ratio'] : '4-8';
$vertical_align = ! empty( $atts['vertical_align'] ) ? $atts['vertical_align'] : 'align-items-center';
$gap            = ! empty( $atts['gap'] ) ? $atts['gap'] : 'g-4';
$image_fit      = ! empty( $atts['image_fit'] ) ? $atts['image_fit'] : 'contain';
$image_radius   = ! empty( $atts['image_radius'] ) ? $atts['image_radius'] : 'rounded-0';
$image_shadow   = ! empty( $atts['image_shadow'] ) ? $atts['image_shadow'] : '';

$ratio_parts = explode( '-', $column_ratio );
$image_col   = isset( $ratio_parts[0] ) ? (int) $ratio_parts[0] : 6;
$content_col = isset( $ratio_parts[1] ) ? (int) $ratio_parts[1] : 6;

$img_classes = [ 'img-fluid' ];
if ( $image_radius !== 'rounded-0' ) {
        $img_classes[] = $image_radius;
}
if ( ! empty( $image_shadow ) ) {
        $img_classes[] = $image_shadow;
}

$img_style = '';
if ( $image_fit === 'cover' ) {
        $img_classes[] = 'w-100';
        $img_style = 'object-fit:cover;height:100%;';
}

$alt = '';
if ( ! empty( $atts['image_alt'] ) ) {
        $alt = $atts['image_alt'];
} elseif ( ! empty( $atts['image']['attachment_id'] ) ) {
        $alt = get_post_meta( $atts['image']['attachment_id'], '_wp_attachment_image_alt', true );
}

$img_attr = [
        'src'   => ! empty( $atts['image']['url'] ) ? esc_url( $atts['image']['url'] ) : '',
        'alt'   => esc_attr( $alt ),
        'class' => esc_attr( implode( ' ', $img_classes ) ),
];
if ( ! empty( $img_style ) ) {
        $img_attr['style'] = esc_attr( $img_style );
}

$image_html = '';
if ( ! empty( $atts['image'] ) ) {
        $image_html = '<img ' . fw_attr_to_html( $img_attr ) . '/>';

        if ( ! empty( $atts['image_link'] ) ) {
                $target     = ! empty( $atts['image_link_target'] ) && in_array( $atts['image_link_target'], [ '_self', '_blank' ], true ) ? $atts['image_link_target'] : '_self';
                $image_html = '<a href="' . esc_url( $atts['image_link'] ) . '" target="' . esc_attr( $target ) . '">' . $image_html . '</a>';
        }
}

$mobile_order = ! empty( $atts['mobile_order'] ) ? $atts['mobile_order'] : 'image-first';

$image_order_classes   = '';
$content_order_classes = '';

if ( $layout === 'image-left' ) {
        if ( $mobile_order === 'content-first' ) {
                $image_order_classes   = ' order-2 order-md-1';
                $content_order_classes = ' order-1 order-md-2';
        }
} else {
        if ( $mobile_order === 'content-first' ) {
                $image_order_classes   = ' order-2';
                $content_order_classes = ' order-1';
        } else {
                $image_order_classes   = ' order-md-2';
                $content_order_classes = ' order-md-1';
        }
}

?>

<div <?php echo fw_attr_to_html( $attr ); ?>>
        <div class="row <?php echo esc_attr( $vertical_align . ' ' . $gap ); ?>">
                <div class="the-image col-md-<?php echo esc_attr( $image_col . $image_order_classes ); ?>">
                        <?php echo $image_html; ?>
                </div>
                <div class="the-content col-md-<?php echo esc_attr( $content_col . $content_order_classes ); ?>">
                        <?php if ( ! empty( $atts['content'] ) ) : ?>
                                <?php echo do_shortcode( $atts['content'] ); ?>
                        <?php endif; ?>
                </div>
        </div>
</div>
