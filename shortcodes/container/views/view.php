<?php if (!defined('FW')) die('Forbidden');

/**
 * @var array  $atts
 * @var string $content
 *
 * A Container renders a second `.fw-container` / `.fw-container-fluid` band inside a
 * section, as a SIBLING of the section's own container (the items-corrector lifts it
 * out so it is not nested). Its columns are grouped into `.fw-row`(s) by the corrector,
 * exactly like a section's columns — so the markup is `.fw-container[-fluid] > .fw-row > columns`.
 */
$is_fluid        = ! empty( $atts['is_fullwidth'] );
$container_class = $is_fluid ? 'fw-container-fluid' : 'fw-container';
?>
<div class="<?php echo esc_attr( $container_class ); ?>">
	<?php echo do_shortcode( $content ); ?>
</div>
