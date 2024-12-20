<?php
namespace SG_Security\Cli;

use SG_Security\Options_Service\Options_Service;
/**
 * WP-CLI: wp sg log {setting} value.
 *
 * Run the `wp sg log {setting} {option} {action} {value} {value}` command to change the settings of specific plugin functionality.
 *
 * @since 1.1.0
 * @package Cli
 * @subpackage Cli/Cli_Log
 */

/**
 * Define the {@link Cli_Log} class.
 *
 * @since 1.1.0
 */
class Cli_Log {

	/**
	 * IP.
	 *
	 * @var String.
	 */
	public $ip;

	/**
	 * Enable specific setting for SiteGround Security plugin.
	 *
	 * ## OPTIONS
	 *
	 * <setting>
	 * : Setting name.
	 * ---
	 * options:
	 *  - ua
	 *  - ip
	 * ---
	 *
	 * <action>
	 * : Action name.
	 * ---
	 * options:
	 *  - add
	 *  - remove
	 *  - list
	 * <name>
	 * : The name.
	 * [--ip=<ip>]
	 * : The IP.
	 */
	public function __invoke( $args, $assoc_args ) {
		// Build the method name.
		$method = $args[0] . '_' . $args[1];

		// Check if method exist.
		if ( true !== method_exists( $this, $method ) ) {
			return \WP_CLI::error( 'Non-existing method.' );
		}

		// Set the ip property since it is used for ip only.
		if ( array_key_exists( 'ip', $assoc_args ) ) {
			$this->ip = $assoc_args['ip'];
		}

		// Call the method and send arguments.
		call_user_func( array( $this, $method ), $args[2] );
	}

	/**
	 * Add ping service to the database.
	 *
	 * @since  1.1.0
	 *
	 * @param  string $service_name The service name.
	 *
	 * @return object WP_CLI success/error.
	 */
	public function ip_add( $service_name ) {
		// Bail if no IP.
		if ( empty( $this->ip ) ) {
			\WP_CLI::error( 'Please, provide an IP address. Usage wp sg log ip add <name> --ip=<ip>.' );
		}

		// Bail if the value is not a valid IP address.
		if ( ! filter_var( $this->ip, FILTER_VALIDATE_IP ) ) {
			\WP_CLI::error( 'Please, provide a valid IP address.' );
		}

		// Get the user ping services.
		$ping_services = get_option( 'sg_security_user_ping_services', array() );

		// Check if the name alreay exists.
		if ( array_key_exists( $service_name, $ping_services ) ) {
			// Check if the name and ip is in the list.
			if ( in_array( $this->ip, $ping_services[ $service_name ] ) ) {
				return \WP_CLI::success( 'IP and name already in the list.' );
			}

			// Add the IP to the existing record.
			array_push( $ping_services[ $service_name ], $this->ip );

		} else {
			// Add the Name and ip to to the services array.
			$ping_services[ $service_name ] = array(
				$this->ip,
			);
		}

		// Bail with error message if update is not successful.
		if ( false === update_option( 'sg_security_user_ping_services', $ping_services ) ) {
			\WP_CLI::error( 'Error while adding the record to the database.' );
		}

		return \WP_CLI::success( 'Record added successfully.' );
	}

	/**
	 * Remove ip from the ping services option.
	 *
	 * @since  1.1.0
	 *
	 * @param  string $service_name The service name.
	 *
	 * @return object WP_CLI success/error.
	 */
	public function ip_remove( $service_name ) {
		// Get the user ping services.
		$ping_services = get_option( 'sg_security_user_ping_services', array() );

		// Check if the key exists.
		if ( array_key_exists( $service_name, $ping_services ) ) {
			// Search for the array.
			$key = array_search( $this->ip, $ping_services[ $service_name ] );

			// Check if we have a match with the group and ip.
			if ( false !== $key ) {
				// Remove the value.
				unset( $ping_services[ $service_name ][ $key ] );
			}

			// Remove the record if no ip's exist in the group.
			if ( empty( $ping_services[ $service_name ] ) ) {
				unset( $ping_services[ $service_name ] );
			}
		}

		// Bail with error message if update is not successful.
		if ( false === update_option( 'sg_security_user_ping_services', $ping_services ) ) {
			\WP_CLI::error( 'Error while removing the record from the database.' );
		}

		return \WP_CLI::success( 'Record removed successfully.' );
	}

	/**
	 * Show all list of all ping services.
	 *
	 * @since  1.1.0
	 *
	 * @param  string $args       The service name.
	 */
	public function ip_list( $args ) {
		// Check for the magic word.
		if ( 'all' !== $args ) {
			\WP_CLI::error( 'Did you mean: wp sg log ip list all ?' );
		}

		// Get the values.
		$ping_services = get_option( 'sg_security_user_ping_services', array() );

		// Create the table data array.
		$table_data = array();
		foreach ( $ping_services as $name => $ip_address ) {

			// Populate the array.
			$table_data[] = array(
				'name' => $name,
				'ip'   => implode( ', ', $ip_address ),
			);
		}

		// Print the table.
		\WP_CLI\Utils\format_items( 'table', $table_data, array( 'name', 'ip' ) );
	}

	/**
	 * Add custom bot by user-agent.
	 *
	 * @since  1.1.0
	 *
	 * @param  string $user_agent User-agent.
	 *
	 * @return object WP_CLI success/error.
	 */
	public function ua_add( $user_agent ) {
		// Get the user crawlers.
		$crawlers = get_option( 'sg_security_user_crawlers', array() );

		// Check if user-agent already in the list.
		if ( in_array( $user_agent, $crawlers ) ) {
			\WP_CLI::error( 'User agent already in the list.' );
		}

		// Add the new crawler to the array.
		$crawlers[] = $user_agent;

		// Bail with error message if update is not successful.
		if ( false === update_option( 'sg_security_user_crawlers', $crawlers ) ) {
			\WP_CLI::error( 'Error while adding the record to the database.' );
		}

		return \WP_CLI::success( 'Record added successfully.' );
	}

	/**
	 * Remove custom bot by user-agent.
	 *
	 * @since  1.1.0
	 *
	 * @param  string $user_agent The service name.
	 *
	 * @return object WP_CLI success/error.
	 */
	public function ua_remove( $user_agent ) {
		// Get the user crawlers.
		$crawlers = get_option( 'sg_security_user_crawlers', array() );

		// Check if the ua name exists as a record.
		if ( in_array( $user_agent, $crawlers ) ) {

			// Check if crawler is in the list.
			$key = array_search( $user_agent, $crawlers );

			if ( false !== $key ) {
				// Remove the value.
				unset( $crawlers[ $key ] );
			}
		}

		// Bail with error message if update is not successful.
		if ( false === update_option( 'sg_security_user_crawlers', $crawlers ) ) {
			\WP_CLI::error( 'Error while removing the record from the database.' );
		}

		return \WP_CLI::success( 'Record removed successfully.' );
	}

	/**
	 * Show all list of all crawlers user-agent.
	 *
	 * @since  1.1.0
	 *
	 * @param  string $args The arguments name.
	 */
	public function ua_list( $args ) {
		// Check for the magic word.
		if ( 'all' !== $args ) {
			\WP_CLI::error( 'Did you mean: wp sg log ua list all ?' );
		}

		// Get the values.
		$crawlers = get_option( 'sg_security_user_crawlers', array() );

		// Create the table data array.
		$table_data = array();
		foreach ( $crawlers as $key => $user_agent ) {

			// Populate the array.
			$table_data[] = array(
				'name' => $user_agent,
			);
		}

		// Print the table.
		\WP_CLI\Utils\format_items( 'table', $table_data, array( 'name' ) );
	}
}
