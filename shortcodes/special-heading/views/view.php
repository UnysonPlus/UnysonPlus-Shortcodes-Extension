<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 */

// Save original user inputs
$user_class = $atts['css_class'] ?? '';
$user_id    = $atts['css_id'] ?? '';

// Always set these before building attributes
$atts['base_class']       = 'heading';
$atts['unique_id_prefix'] = 'hd-';

// Prepend centered class if needed
if ( ! empty( $atts['centered'] ) && $atts['centered'] === 'yes' ) {
    $atts['css_class'] = 'text-center ' . $user_class;
} else {
    $atts['css_class'] = $user_class;
}

// Add heading-specific class
$atts['css_class'] = 'heading-' . ($atts['heading'] ?? 'h2') . ' ' . $atts['css_class'];

// Build wrapper attributes
$attr = sc_build_wrapper_attr( $atts );

// Determine if wrapper is needed (only if user explicitly set class or ID)
$use_wrapper = !empty($user_class) || !empty($user_id);
?>

<?php if ( $use_wrapper ) : ?>
    <div <?php echo fw_attr_to_html( $attr ); ?>>
<?php endif; ?>

<?php if ( ! empty( $atts['title'] ) ) : ?>
    <<?php echo esc_attr( $atts['heading'] ); ?> class="special-title">
        <?php echo esc_html( $atts['title'] ); ?>
    </<?php echo esc_attr( $atts['heading'] ); ?>>
<?php endif; ?>

<?php if ( ! empty( $atts['subtitle'] ) ) : ?>
    <div class="special-subtitle"><?php echo esc_html( $atts['subtitle'] ); ?></div>
<?php endif; ?>

<?php if ( $use_wrapper ) : ?>
    </div>
<?php endif; ?>
