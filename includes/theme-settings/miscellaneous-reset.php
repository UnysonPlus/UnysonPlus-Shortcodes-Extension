<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Miscellaneous → Reset Settings.
 *
 * A single button that posts the framework's built-in full-reset flag
 * (`_fw_reset_options`) — Unyson core handles the actual reset + confirm dialog, so
 * there is no handler to port. Works on any theme with a Theme Settings page.
 *
 * @var array $options Filled with the option schema (loaded via upw_ts_get_options()).
 */

$options = array(
	'misc_reset_settings' => array(
		'type'  => 'html-full',
		'label' => false,
		'html'  => '<p>' . esc_html__( 'This restores every option on every Theme Settings tab to its default value. This cannot be undone.', 'fw' ) . '</p>'
			. '<input type="submit" name="_fw_reset_options"'
			. ' value="' . esc_attr__( 'Reset All Theme Settings', 'fw' ) . '"'
			. ' class="button-secondary button-large fw-settings-form-reset-btn" />',
	),
);
