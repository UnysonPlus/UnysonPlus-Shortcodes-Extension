<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * Theme Settings → Components → Element Designs.
 *
 * A bespoke client-side manager (not an addable-box settings form) because designs
 * are stored as files in the uploads catalog (framework/extensions/shortcodes/includes/
 * design-presets/design-library.php), not in the Theme Settings option. This returns a
 * single full-width html mount; element-designs-manager.js hydrates it via the
 * fw_design_lib_manage AJAX endpoint (import / edit / delete / browse). Exempt from the
 * metabox-holder convention, like the Shortcodes management page.
 */

$mount  = '<div class="upw-eldesigns" data-upw-eldesigns>';
$mount .= '<p class="upw-eldesigns__loading">' . esc_html__( 'Loading designs…', 'fw' ) . '</p>';
$mount .= '</div>';

$options = array(
	'sc_eldesigns_manager' => array(
		'type'  => 'html-full',
		'label' => false,
		'html'  => $mount,
	),
);
