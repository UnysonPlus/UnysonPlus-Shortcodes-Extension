<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
} ?>

<?php
$atts['base_class']         = 'call-to-action';
$atts['unique_id_prefix']   = 'cta-';

// Build attributes for wrapper
$attr = sc_build_wrapper_attr( $atts );

// Append our custom classes
$attr['class'] = trim(
    ($attr['class'] ?? '') . ' fw-call-to-action'
);
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<div class="fw-action-content">
		<?php if (!empty($atts['title'])): ?>
		<h2><?php echo $atts['title']; ?></h2>
		<?php endif; ?>
		<p><?php echo $atts['message']; ?></p>
	</div>
	<div class="fw-action-btn">
		<a href="<?php echo esc_attr($atts['button_link']); ?>" class="btn btn-1" target="<?php echo esc_attr($atts['button_target']); ?>">
			<span><?php echo $atts['button_label']; ?></span>
		</a>
	</div>
</div>