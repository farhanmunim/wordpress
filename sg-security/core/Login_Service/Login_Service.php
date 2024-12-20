<?php
namespace SG_Security\Login_Service;

use SG_Security\Helper\Helper;

/**
 * Class that manages User's Log-in services.
 */
class Login_Service {

	/**
	 * The maximum allowed login attempts.
	 *
	 * @var integer
	 */
	public $login_attempts_limit = 0;

	/**
	 * Login attempts data
	 *
	 * @var array
	 */
	public $login_attempts_data = array(
		0 => 'OFF',
		3 => '3',
		5 => '5',
	);

	/**
	 * The constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->login_attempts_limit = get_option( 'sg_security_login_attempts', 0 );
	}

	/**
	 * Check if this ip has access to login page.
	 *
	 * @since  1.0.0
	 */
	public function restrict_login_to_ips() {
		// Get the list of allowed IP addresses.
		$allowed_ips = get_option( 'sg_login_access', array() );

		// Bail if the allowed ip list is empty.
		if ( empty( $allowed_ips ) ) {
			return true;
		}

		foreach ( $allowed_ips as $allowed_ip ) {
			if ( Helper::get_current_user_ip() === $allowed_ip ) {
				return true;
			}
			if ( false !== strpos( $allowed_ip, '/' ) && $this->ip_in_range( Helper::get_current_user_ip(), $allowed_ip ) ) {
				return true;
			}
		}

		// Update the total blocked logins counter.
		update_option( 'sg_security_total_blocked_logins', get_option( 'sg_security_total_blocked_logins', 0 ) + 1 );

		wp_die(
			esc_html__( 'You don’t have access to this page. Please contact the administrator of this website for further assistance.', 'sg-security' ),
			esc_html__( 'Restricted access', 'sg-security' ),
			array(
				'sgs_error' => true,
				'response'  => 403,
				'blocked_login' => true,
			)
		);
	}

	/**
	 * Restrict access to login for unsuccessfull attempts.
	 *
	 * @since  1.0.0
	 */
	public function maybe_block_login_access() {
		// Get the user ip.
		$user_ip = Helper::get_current_user_ip();
		// Get login attemps data.
		$login_attempts = get_option( 'sg_security_unsuccessful_login', array() );

		// Bail if the user doesn't have attempts.
		if ( empty( $login_attempts[ $user_ip ]['timestamp'] ) ) {
			return;
		}

		// Bail if ip, has reached login attempts limit.
		if ( $login_attempts[ $user_ip ]['timestamp'] > time() ) {

			// Update the total blocked logins counter.
			update_option( 'sg_security_total_blocked_logins', get_option( 'sg_security_total_blocked_logins', 0 ) + 1 );

			wp_die(
				esc_html__( 'You don’t have access to this page. Please contact the administrator of this website for further assistance.', 'sg-security' ),
				esc_html__( 'The access to that page has been restricted by the administrator of this website', 'sg-security' ),
				array(
					'sgs_error' => true,
					'response'  => 403,
				)
			);
		}

		// Reset the login attempts if the restriction time has ended and the user was banned for maximum amount of time.
		if (
			$login_attempts[ $user_ip ]['timestamp'] < time() &&
			$login_attempts[ $user_ip ]['attempts'] >= $this->login_attempts_limit * 3
		) {
			unset( $login_attempts[ $user_ip ] );
			update_option( 'sg_security_unsuccessful_login', $login_attempts );
		}
	}

	/**
	 * Add login attempt for specific ip address.
	 *
	 * @since 1.0.0
	 *
	 * @param string $error The login error.
	 */
	public function log_login_attempt( $error ) {
		global $errors;

		// Check for errors global since custom login urls plugin are not always returning it.
		if ( empty( $errors ) ) {
			return $error;
		}

		$err_codes = $errors->get_error_codes();
		// Invalid username.
		if (
			! in_array( 'invalid_username', $err_codes ) &&
			! in_array( 'incorrect_password', $err_codes )
		) {
			return $error;
		}

		// Get the current user ip.
		$user_ip = Helper::get_current_user_ip();
		// Get the login attempts data.
		$login_attempts = get_option( 'sg_security_unsuccessful_login', array() );

		// Add the ip to the list if it does not exist.
		if ( ! array_key_exists( $user_ip, $login_attempts ) ) {
			$login_attempts[ $user_ip ] = array(
				'attempts'  => 0,
				'timestamp' => '',
			);
		}

		// Increase the attempt count.
		$login_attempts[ $user_ip ]['attempts']++;

		// Check if we are reaching the limits.
		switch ( $login_attempts[ $user_ip ]['attempts'] ) {
			// Add a restriction time if we reach the limits.
			case $login_attempts[ $user_ip ]['attempts'] == $this->login_attempts_limit:
				// Set 1 hour limit.
				$login_attempts[ $user_ip ]['timestamp'] = time() + 3600;
				break;

			case $login_attempts[ $user_ip ]['attempts'] == $this->login_attempts_limit * 2:
				// Set 24 hours limit.
				$login_attempts[ $user_ip ]['timestamp'] = time() + 86400;
				break;

			case $login_attempts[ $user_ip ]['attempts'] > $this->login_attempts_limit * 3:
				// Set 7 days limit.
				$login_attempts[ $user_ip ]['timestamp'] = time() + 604800;
				break;

			// Do not set restriction if we do not reach any limits.
			default:
				$login_attempts[ $user_ip ]['timestamp'] = '';
				break;
		}

		// Update the login attempts data.
		update_option( 'sg_security_unsuccessful_login', $login_attempts );

		return $error;
	}

	/**
	 * Reset login attempts on successful login.
	 *
	 * @since  1.0.0
	 */
	public function reset_login_attempts() {
		// Get the current user ip.
		$user_ip = Helper::get_current_user_ip();
		// Get the login attempts data.
		$login_attempts = get_option( 'sg_security_unsuccessful_login', array() );

		// Bail if the IP doens't exists in the unsuccessful logins.
		if ( ! array_key_exists( $user_ip, $login_attempts ) ) {
			return;
		}

		// Remove the IP from the option.
		unset( $login_attempts[ $user_ip ] );

		// Update the option with the new value.
		update_option( 'sg_security_unsuccessful_login', $login_attempts );
	}

	/**
	 * Search an IP range for a given IP.
	 *
	 * @since  1.2.0
	 *
	 * @param  string $ip    The ip to be searched for.
	 * @param  string $range The range to be searched in.
	 *
	 * @return bool          True, if IP is contained in the range.
	 */
	public function ip_in_range( $ip, $range ) {
		$range = explode( '/', $range );
		// Get the netmask from the range.
		$netmask = $range[1];
		// Get the base range ip and convert to long.
		$start_ip = ip2long( $range[0] );
		// Get the count of the possible IPs.
		$ip_count = 1 << ( 32 - $netmask );

		// Iterate through all possible IPs and return true on match, false if not found.
		for ( $i = 0; $i < $ip_count - 1; $i++ ) {
			if ( long2ip( ( $start_ip + $i ) ) === $ip ) {
				return true;
			}
		}
		return false;
	}
}
