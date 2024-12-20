<?php
namespace SG_Security\Cli;

use SG_Security\Options_Service\Options_Service;

/**
 * WP-CLI: wp sg login-access {setting} value.
 *
 * Run the `wp sg login-access value` command to add/remove specific IP address in order to allow access to backend.
 *
 * @since 1.0.2
 * @package Cli
 * @subpackage Cli/Cli_Login_Access
 */

/**
 * Define the {@link Cli_Login_Access} class.
 *
 * @since 1.0.2
 */
class Cli_Login_Access {
	/**
	 * Enable specific setting for SG Security plugin.
	 *
	 * ## OPTIONS
	 *
	 * options:
	 * - add
	 * - remove
	 * - list
	 * ---
	 * <action>
	 * : Setting name.
	 * <value>
	 * : IP to be whitelisted.
	 */
	public function __invoke( $args ) {
		// Bail if no action is provided.
		if ( ! isset( $args[0] ) ) {
			return \WP_CLI::error( 'Please provide an action to list/add/remove.' );
		}

		switch ( $args[0] ) {
			case 'add':
				$this->add_ip( $args );
				break;

			case 'remove':
				$this->remove_ip( $args );
				break;

			case 'list':
				$this->list_ips();
				break;
		}
	}

	/**
	 * Adds the provided IP address into the list of IPs that should be allowed to access the backend.
	 *
	 * @since 1.0.2
	 *
	 * @param string $args The value that will define the IP that should be allowed to access the backend.
	 */
	public function add_ip( $args ) {
		// Bail if a value is not provided.
		if ( ! isset( $args[1] ) ) {
			\WP_CLI::error( 'Please provide an IP address.' );
		}

		// Bail if the value is not a valid IP address.
		if ( ! $this->filter_ip( $args[1]) ) {
			\WP_CLI::error( 'Please, provide a valid IP address.' );
		}

		// Bail if the IP address already exists.
		if ( in_array( $args[1], get_option( 'sg_login_access', array() ) ) ) {
			\WP_CLI::error( 'The IP address: ' . $args[1] . ' already exists.' );
		}

		// Check which are the IPs present at the moment and add them into array.
		$current_ips = get_option( 'sg_login_access', array() );

		// Add the new IP address to the array.
		array_push( $current_ips, $args[1] );

		// Push the array into the option.
		update_option( 'sg_login_access', $current_ips );

		// Confirm that the IP was added successfully.
		\WP_CLI::success( 'The IP address: ' . $args[1] . ' was added successfully.' );
	}

	/**
	 * Removes the provided IP address from the list of IPs that should be allowed to access the backend.
	 *
	 * @since 1.0.2
	 *
	 * @param string $args The value that will define the IP that should be removed from the whitelisted IPs.
	 */
	public function remove_ip( $args ) {
		// Remove all IP addresses.
		if ( 'all' === $args[1] ) {

			// Push the array into the option.
			update_option( 'sg_login_access', array() );

			// Confirm that the IP was removed successfully.
			return \WP_CLI::success( 'All IP addresses were removed successfully.' );
		}

		// Bail if the value is not a valid IP address.
		if ( ! $this->filter_ip( $args[1]) ) {
			\WP_CLI::error( 'Please, provide a valid IP address.' );
		}

		// Check which are the IPs present at the moment and add them into an array.
		$current_ips = get_option( 'sg_login_access', array() );

		// Bail if the IP address is not present.
		if ( ! in_array( $args[1], $current_ips ) ) {
			\WP_CLI::error( 'The IP address: ' . $args[1] . ' does not exist.' );
		}

		// Find the position of the value in the current IPs and remove it.
		$key = array_search( $args[1], $current_ips );
			unset( $current_ips[ $key ] );

		// Push the array into the option.
		update_option( 'sg_login_access', $current_ips );

		// Confirm that the IP was removed successfully.
		\WP_CLI::success( 'The IP address: ' . $args[1] . ' was removed successfully.' );
	}

	/**
	 * Lists all of the IPs that are allowed to access the backend.
	 *
	 * @since 1.0.2
	 */
	public function list_ips() {
		// Check which are the IPs present at the moment and add them into an array.
		$list_arrays = get_option( 'sg_login_access', array() );

		// Bail if there are no IP addresses present at the moment.
		if ( empty( $list_arrays ) ) {
			return \WP_CLI::error( 'There are no IP addresses present at the moment.' );
		}

		// Check the current IP addresses and print them out.
		foreach ( $list_arrays as $ips ) {
			echo $ips . "\n"; // phpcs:ignore
		}
	}

	/**
	 * Filter, that checks if the passed variable is a valid IP address.
	 *
	 * @since 1.2.0
	 *
	 * @param string $ip The passed IP by the user.
	 *
	 * @return bool      True, if the IP is valid, otherwise - false.
	*/
	public function filter_ip( $ip ) {
		// Regex, that validates the IP, including wildcard.
		$ip = explode( '/', $ip );

		if ( ! filter_var( $ip[0], FILTER_VALIDATE_IP ) ) {
			return false;
		}

		if ( count( $ip ) > 2 ) {
			return false;
		}

		if ( isset( $ip[1] ) && ( intval( $ip[1] ) > 32 || intval( $ip[1] ) < 1 || ! is_numeric( $ip[1] ) ) ) {
			return false;
		}

		return true;
	}
}
