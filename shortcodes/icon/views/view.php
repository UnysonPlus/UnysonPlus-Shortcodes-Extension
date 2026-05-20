<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 */

/*
|--------------------------------------------------------------------------
| Enqueue icon-v2 frontend CSS (only when icon is used)
|--------------------------------------------------------------------------
*/
if (
    ! empty( $atts['icon'] ) &&
    isset( fw()->backend->option_type( 'icon-v2' )->packs_loader )
) {
    fw()->backend->option_type( 'icon-v2' )->packs_loader->enqueue_frontend_css();
}

// Build wrapper attributes
$atts['base_class']       = 'icon';
$atts['unique_id_prefix'] = 'ic-';
$attr = sc_build_wrapper_attr( $atts );

// icon-v2 stores its value as an array: ['type' => 'icon-font'|'custom-upload'|'none', ...]
$icon      = is_array( $atts['icon'] ?? null ) ? $atts['icon'] : array();
$icon_type = $icon['type'] ?? '';
?>
<span <?php echo fw_attr_to_html( $attr ); ?>>
    <?php if ( $icon_type === 'icon-font' && ! empty( $icon['icon-class'] ) ) : ?>
        <i class="<?php echo esc_attr( $icon['icon-class'] ); ?>" aria-hidden="true"></i>
    <?php elseif ( $icon_type === 'custom-upload' && ! empty( $icon['url'] ) ) : ?>
        <img src="<?php echo esc_url( $icon['url'] ); ?>" alt="<?php echo esc_attr( $icon['alt'] ?? '' ); ?>" class="icon-image" loading="lazy">
    <?php endif; ?>

    <?php if ( ! empty( $atts['title'] ) ) : ?>
        <span class="list-title"><?php echo esc_html( $atts['title'] ); ?></span>
    <?php endif; ?>
</span>
