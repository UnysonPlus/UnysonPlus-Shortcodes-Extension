<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Newsletter shortcode class. Its only job is to register the admin-ajax
 * subscribe endpoint — `_init()` runs every request (shortcodes are
 * instantiated on init:11), so the endpoint exists even though the shortcode's
 * static.php only loads when the form is on a page.
 *
 * SECURITY: the notification recipient is ALWAYS the site admin (or the
 * `fw_newsletter_recipient` filter) — never a client-supplied address — so the
 * endpoint can't be abused as an open mail relay. A honeypot field deters bots.
 * Real list integrations should hook `fw_newsletter_subscribe` (or short-circuit
 * the admin email via `fw_newsletter_handled`).
 */
class FW_Shortcode_Newsletter extends FW_Shortcode {

	/** @internal */
	public function _init() {
		add_action( 'wp_ajax_fw_newsletter_subscribe', array( $this, '_ajax_subscribe' ) );
		add_action( 'wp_ajax_nopriv_fw_newsletter_subscribe', array( $this, '_ajax_subscribe' ) );
	}

	/** @internal */
	public function _ajax_subscribe() {
		check_ajax_referer( 'fw_newsletter', 'nonce' );

		// Honeypot — a filled hidden field means a bot. Pretend success.
		if ( ! empty( $_POST['fw_hp'] ) ) {
			wp_send_json_success( array( 'message' => __( 'Thanks!', 'fw' ) ) );
		}

		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$list  = isset( $_POST['list'] ) ? sanitize_text_field( wp_unslash( $_POST['list'] ) ) : '';

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'fw' ) ) );
		}

		/**
		 * Integrations (Mailchimp, etc.) hook here. Return a WP_Error from the
		 * `fw_newsletter_subscribe_result` filter to surface a failure to the user.
		 */
		do_action( 'fw_newsletter_subscribe', $email, $name, $list );
		$result = apply_filters( 'fw_newsletter_subscribe_result', true, $email, $name, $list );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		// Notify the site (uses the Mailer extension's SMTP through wp_mail).
		// `fw_newsletter_handled` lets an integration suppress this email.
		$handled = apply_filters( 'fw_newsletter_handled', false, $email, $name, $list );
		if ( ! $handled ) {
			$to = apply_filters( 'fw_newsletter_recipient', get_option( 'admin_email' ), $email, $list );
			if ( ! is_email( $to ) ) { $to = get_option( 'admin_email' ); }
			$source  = isset( $_POST['source'] ) ? esc_url_raw( wp_unslash( $_POST['source'] ) ) : '';
			$subject = sprintf( __( 'New newsletter signup: %s', 'fw' ), $email );
			$body    = sprintf(
				"Email: %s\nName: %s\nList: %s\nPage: %s\n",
				$email,
				$name !== '' ? $name : '-',
				$list !== '' ? $list : '-',
				$source !== '' ? $source : '-'
			);
			wp_mail( $to, $subject, $body );
		}

		wp_send_json_success( array( 'message' => __( 'Thanks! You are subscribed.', 'fw' ) ) );
	}
}

FW_Shortcode_Newsletter::class; // referenced by the loader via the class name
