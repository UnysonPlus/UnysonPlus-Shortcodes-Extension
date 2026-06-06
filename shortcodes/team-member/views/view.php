<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

if ( empty( $atts['image'] ) ) {
	$image = fw_get_framework_directory_uri('/static/img/no-image.png');
} else {
	$image = $atts['image']['url'];
}
?>
<div class="fw-team">
	<div class="fw-team-image"><img src="<?php echo esc_attr($image); ?>" alt="<?php echo esc_attr($atts['name']); ?>"/></div>
	<div class="fw-team-inner">
		<div class="fw-team-name">
			<h3><?php echo esc_html( $atts['name'] ); ?></h3>
			<span><?php echo esc_html( $atts['job'] ); ?></span>
		</div>
		<div class="fw-team-text">
			<p><?php echo wp_kses_post( $atts['desc'] ); ?></p>
		</div>
	</div>
</div>