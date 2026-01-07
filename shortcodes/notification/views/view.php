<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
} 

$atts['base_class']       = 'alert';
$atts['unique_id_prefix'] = 'al-';

// Add alert type and dismissible class
$atts['css_class'] = (!empty($atts['type']) ? 'alert-' . esc_attr($atts['type']) : '');
if (!empty($atts['dismissible'])) {
    $atts['css_class'] .= ' alert-dismissible fade show';
}

$attr = sc_build_wrapper_attr($atts);
?>

<div <?php echo fw_attr_to_html($attr); ?> role="alert">
	<?php
	// Icons
	switch ($atts['type']) {
		case 'success':
			echo '<i class="fa-solid fa-circle-check alert-icon"></i> <strong>' . __('Success!', 'fw') . '</strong> ';
			break;
		case 'info':
			echo '<i class="fa-solid fa-circle-info alert-icon"></i> <strong>' . __('Information!', 'fw') . '</strong> ';
			break;
		case 'warning':
			echo '<i class="fa-solid fa-triangle-exclamation alert-icon"></i> <strong>' . __('Warning!', 'fw') . '</strong> ';
			break;
		case 'danger':
			echo '<i class="fa-solid fa-circle-xmark alert-icon"></i> <strong>' . __('Error!', 'fw') . '</strong> ';
			break;
		case 'primary':
			echo '<i class="fa-solid fa-bolt alert-icon"></i> <strong>' . __('Note!', 'fw') . '</strong> ';
			break;
		case 'secondary':
			echo '<i class="fa-solid fa-circle-dot alert-icon"></i> <strong>' . __('Note!', 'fw') . '</strong> ';
			break;
		case 'light':
			echo '<i class="fa-solid fa-lightbulb alert-icon"></i> <strong>' . __('Note!', 'fw') . '</strong> ';
			break;
		case 'dark':
			echo '<i class="fa-solid fa-moon alert-icon"></i> <strong>' . __('Note!', 'fw') . '</strong> ';
			break;
		default:
			echo '<i class="fa-solid fa-circle-info alert-icon"></i> ';
			break;
	}

	echo $atts['message'];

	// Add close button if dismissible
	if (!empty($atts['dismissible'])) {
		echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
	}
	?>
</div>
