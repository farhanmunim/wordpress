<?php
namespace SG_Security\Cli;

/**
 * WP-CLI: wp sg custom-login {action} value.
 *
 * Run the `wp sg custom-login {action} value` command to disable or get status of the Custom Login URL functionality.
 *
 * @since 1.3.3
 * @package Cli
 * @subpackage Cli/Cli_Custom_Login_Url
 */

/**
 * Define the {@link Cli_Custom_Login_Url} class.
 *
 * @since 1.3.3
 */
class Cli_Custom_Login_Url {
	/**
	 * Enable specific setting for SG Security plugin.
	 *
	 * ## OPTIONS
	 * <action>
	 * : Action name.
	 * options:
	 * - disable
	 * - status
	 */
	public function __invoke( $args ) {
		// Bail if no action is provided.
		if ( ! isset( $args[0] ) ) {
			return \WP_CLI::error( 'Please provide an action - status or disable. Per example "wp sg custom-login disable". This will disable the Custom Login URL functionality.' );
		}

		// Initiate the disable action.
		if ( 'disable' === $args[0] ) {
			return $this->disable();
		}

		// Initiate the retrieval of the status.
		if ( 'status' === $args[0] ) {
			return $this->status();
		}

		\WP_CLI::error( 'Incorrect setting, please provide an action' );
	}

	/**
	 * Disable Custom Login URL, if enabled.
	 *
	 * @since 1.3.3
	 */
	public function disable() {
		$login_type = get_option( 'sg_security_login_type', false );

		if ( false === $login_type || 'default' === $login_type ) {
			\WP_CLI::success( 'Custom Login URL is already disabled.' );
			return false;
		}

		$status = delete_option( 'sg_security_login_type' );

		if ( false !== $status ) {
			\WP_CLI::success( 'Custom Login URL is disabled.' );
		} else {
			\WP_CLI::error( 'Failed to disable Custom Login URL.' );
		}

	}
	/**
	 * Get the current status of the Custom Login URL.
	 *
	 * @since 1.3.3
	 */
	public function status() {
		$login_type = get_option( 'sg_security_login_type', false );

		if ( false === $login_type || 'default' === $login_type ) {
			\WP_CLI::success( 'Custom Login URL is disabled.' );
			return false;
		}

		$login_slug = get_option( 'sg_security_login_url', 'login' );

		\WP_CLI::success( 'Custom Login URL is set to: ' . \trailingslashit( home_url() ) . $login_slug );

	}
}
