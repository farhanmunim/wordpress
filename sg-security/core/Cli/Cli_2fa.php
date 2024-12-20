<?php
namespace SG_Security\Cli;

use SG_Security\Sg_2fa\Sg_2fa;
/**
 * WP-CLI: wp sg 2fa {action} {setting} value.
 *
 * Run the `wp sg 2fa {action} {setting} value` command to reset 2FA setup for specific user.
 *
 * @since 1.1.1
 * @package Cli
 * @subpackage Cli/2fa
 */

/**
 * Define the {@link Cli_2fa} class.
 *
 * @since 1.1.1
 */
class Cli_2fa {
	/**
	 * Enable specific setting for SG Security plugin.
	 *
	 * ## OPTIONS
	 * <action>
	 * : Action name.
	 * default: reset
	 * options:
	 * - reset
	 * 
	 * <setting>
	 * : User ID or username.
	 * options:
	 * - id
	 * - username
	 * - all
	 * ---
	 * [<value>]
	 * : The user ID or username.
	 *
	 * ## EXAMPLES
	 *
	 * wp sg 2fa reset id 1
	 */
	public function __invoke( $args ) {
		// Bail if no action is provided.
		if ( ! isset( $args[0] ) ) {
			return \WP_CLI::error( 'Please provide an action - reset and the user ID/username/all. Per example "wp sg 2fa reset id 1". This will reset the 2fa setup for user with ID 1.' );
		}

		// Initiate the reset action.
		if ( 'reset' === $args[0] ) {
			return $this->reset( $args );
		}

		\WP_CLI::error( 'Incorrect setting, please user reset as an option and user ID/Username as a value!' );
	}

	/**
	 * Reset user 2FA.
	 *
	 * @since 1.1.1
	 */
	public function reset( $args ) {
		// Reset all users 2FA setup if all is selected.
		if ( 'all' === $args[1] ) {
			Sg_2fa::get_instance()->reset_all_users_2fa();
			// Return success message.
			return \WP_CLI::success( '2FA successfully reset for all users!' );
		}

		if ( empty( $args[2] ) ) {
			// Return error message if there is no such user.
			\WP_CLI::error( 'You need to define user ID or username.' );
		}

		// Get the user.
		$user = ( 'id' === $args[1] ) ? get_user_by( 'ID', $args[2] ) : get_user_by( 'login', $args[2] );

		if ( false === $user ) {
			// Return error message if there is no such user.
			\WP_CLI::error( 'User with such ' . $args[1] . ' does not exist!' );
		}

		// Reset the 2FA for the user.
		if ( Sg_2fa::get_instance()->reset_user_2fa( $user->ID ) ) {
			// Return success message.
			\WP_CLI::success( '2FA successfully reset for ' . $args[1] . ' ' . $args[2] . '!' );
		}
		else {
			// Return error message.
			\WP_CLI::error( 'Incorrect ' . $args[1] . '!' );
		}
	}
}
