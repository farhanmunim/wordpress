<?php
namespace SG_Security\Password_Service;

/**
 * Class that manages password reset services.
 */
class Password_Service {

	/**
	 * Update user meta, so users are forced to change their passwords upon next login.
	 *
	 * @since  1.0.0
	 */
	public function invalidate_passwords() {
		// Get all users.
		$users = get_users( array( 'fields' => array( 'ID' ) ) );

		// Loop and update the user meta.
		foreach ( $users as $user ) {
			update_user_meta( $user->ID, 'sg_security_force_password_reset', 1 );
		}
	}

	/**
	 * Check if the user has changed his password after a force reset.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $username The username - either email or username.
	 */
	public function force_password_reset( $username ) {
		// Try to get the user by username.
		$user = get_user_by( 'login', $username );

		// If not, try getting the user by email.
		if ( false === $user ) {
			$user = get_user_by( 'email', $username );
		}

		// Bail if we have no match.
		if ( false === $user ) {
			return $username;
		}

		// Get the current user if the user param is empty.
		if ( empty( $user ) ) {
			$user = wp_get_current_user();
		}

		// Bail if there is no user.
		if ( empty( $user->ID ) ) {
			return;
		}

		// Bail if the user has changed the password.
		if ( 1 !== (int) get_user_meta( $user->ID, 'sg_security_force_password_reset', true ) ) {
			return;
		}

		// Remove the auth cookie.
		wp_clear_auth_cookie();

		// Retirect to the reset url.
		wp_redirect( self::get_redirect_url( $user ) );
		exit;
	}

	/**
	 * Get the reset password url.
	 *
	 * @since  1.0.0
	 *
	 * @param  object $user WP_User obejct.
	 *
	 * @return string       The reset password url.
	 */
	public static function get_redirect_url( $user ) {
		return sprintf(
			site_url( 'wp-login.php?action=rp&key=%s&login=%s&sgsrp=1' ),
			get_password_reset_key( $user ),
			rawurlencode( $user->user_login )
		);
	}

	/**
	 * Redirect to password reset page.
	 *
	 * @since  1.0.0
	 */
	public function password_reset_redirect() {
		$user = wp_get_current_user();

		// Bail if there is no user.
		if ( empty( $user->ID ) ) {
			return;
		}

		// Bail if the user has changed the password.
		if ( 1 !== (int) get_user_meta( $user->ID, 'sg_security_force_password_reset', true ) ) { // phpcs:ignore
			return;
		}

		// Retirect to the reset url.
		wp_redirect( site_url( 'wp-login.php?action=rp&sgsrp=1' ) );
		exit;
	}

	/**
	 * Add a hidden field to the password reset form.
	 *
	 * @since 1.1.0
	 */
	public function hidden_login_field() {
		// Add the field only if you are on the password reset page forced by the plugin.
		if (
			isset( $_GET['sgsrp'] ) || // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			isset( $_POST['sgsrp_field'] ) // phpcs:ignore
		) {
			echo '<input type="hidden" value="1" id="sgsrp_field" name="sgsrp_field"/>';
		}
	}

	/**
	 * Validate the password.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_Error $errors WP_Errors object.
	 * @param WP_User  $user   WP_User object.
	 */
	public function validate_password( $errors, $user ) {
		// Bail if we are not on the forced password reset form.
		if ( ! isset( $_POST['sgsrp_field'] ) ) { // phpcs:ignore
			return;
		}

		// Return error if the entered password matches the previously used one.
		if (
			isset( $_POST['pass1'] ) && // phpcs:ignore
			wp_check_password( $_POST['pass1'], $user->user_pass ) // phpcs:ignore
		) {
			$errors->add( 'error', 'You can not use your old password.', '' );
		}
	}

	/**
	 * Remove the password reset meta.
	 *
	 * @since  1.0.0
	 *
	 * @param  object $user The user object.
	 */
	public function remove_password_reset_meta( $user ) {
		update_user_meta( $user->ID, 'sg_security_force_password_reset', 0 ); // phpcs:ignore
	}

	/**
	 * Add custom reset password message.
	 *
	 * @since 1.0.0
	 *
	 * @param string $message The custom login message.
	 */
	public function add_custom_login_message( $message ) {
		// Bail if we don't force the password reset.
		if ( ! isset( $_GET['sgsrp'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $message;
		}

		// Return the custom message.
		return '<p class="message reset-pass">' . esc_html__( 'The administrator of this site has requested that you change your password. Enter your new password below or generate one.', 'sg-security' ) . '</p>';
	}
}
