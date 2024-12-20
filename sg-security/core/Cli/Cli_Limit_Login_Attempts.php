<?php
namespace SG_Security\Cli;

use SG_Security\Login_Service\Login_Service;
use SG_Security\Options_Service\Options_Service;
/**
 * WP-CLI: wp sg limit-login-attempts value.
 *
 * Run the `wp sg limit-login-attempts value` command to use the limit login attempts functionality.
 *
 * @since 1.0.2
 * @package Cli
 * @subpackage Cli/Cli_Limit_Login_Attempts
 */

/**
 * Define the {@link Cli_Limit_Login_Attempts} class.
 *
 * @since 1.0.2
 */
class Cli_Limit_Login_Attempts {

	/**
	 * Login_Service class.
	 *
	 * @var Object.
	 */
	public $login_service;

	/**
	 * Enable specific setting for SG Security plugin.
	 *
	 * ## OPTIONS
	 * <value>
	 * : The new value of the setting.
	 */
	public function __invoke( $args ) {
		$this->login_service = new Login_Service();

		// Bail if value is not provided.
		if ( ! isset( $args[0] ) ) {
			return \WP_CLI::error( 'Please choose between' . implode( ', ', array_flip( $this->login_service->login_attempts_data ) ) );
		}

		// Bail if the provided value is not an integer.
		if ( false === is_numeric( $args[0] ) ) {
			\WP_CLI::error( 'Please choose one of the integers: ' . implode( ', ', array_flip( $this->login_service->login_attempts_data ) ) );
		}

		return $this->change_value( $args[0] );
	}

	/**
	 * Limits the number of login attempts.
	 *
	 * @since 1.0.2
	 *
	 * @param string $value The value which will define the login attempts.
	 */
	public function change_value( $value ) {
		// Bail if the provided value does not match the required ones.
		if ( ! array_key_exists( intval( $value ), $this->login_service->login_attempts_data ) ) {
			\WP_CLI::error( 'The value ' . $value . ' is not supported. Please choose between ' . implode( ', ', array_flip( $this->login_service->login_attempts_data ) ) );
		}

		// Updates the login limit in the database.
		$status = Options_Service::change_option( 'login_attempts', $value );

		// Bail if there is an error.
		if ( 0 === $status ) {
			return \WP_CLI::warning( 'The login limit is already set to ' . $value );
		}

		// Confirm that the limit was updated succesfully..
		\WP_CLI::success( 'Login limit updated succesfully!' );
	}
}
