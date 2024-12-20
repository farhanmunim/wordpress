<?php
namespace SG_Security\Helper;

use SG_Security;
use SiteGround_Helper\Helper_Service;
use SG_Security\Salt_Shaker\Salt_Shaker;
use \WP_Session_Tokens;

/**
 * Helper functions and main initialization class.
 */
class Helper {

	/**
	 * The Database placeholder.
	 */
	public static $wpdb;

	/**
	 * Get the current user's IP address.
	 *
	 * @since  1.0.0
	 *
	 * @return string The user's IP.
	 */
	public static function get_current_user_ip() {
		$keys = array( 'REMOTE_ADDR' );

		if (
			defined( 'SGS_HEADER' ) &&
			SGS_HEADER === 'X-Forwarded-For'
		) {
			array_unshift( $keys, 'HTTP_X_FORWARDED_FOR' );
		}

		foreach ( $keys as $key ) {
		   // Bail if the key doesn't exists.
		   if ( ! isset( $_SERVER[ $key ] ) ) {
			  continue;
		   }
		   // Bail if the IP is not valid.
		   if ( ! filter_var( $_SERVER[ $key ], FILTER_VALIDATE_IP ) ) { //phpcs:ignore
			  continue;
		   }
		   // Return the user's IP Address.
		   return preg_replace( '/^::1$/', '127.0.0.1', $_SERVER[ $key ] ); //phpcs:ignore
		}
		// Return the local IP by default.
		return '127.0.0.1';
	}

	/**
	 * Sets the server IP address.
	 *
	 * @since 1.1.0
	 */
	public static function set_server_ip() {
		update_option( 'sg_security_server_address', \gethostbyname( \gethostname() ) );
	}

	/**
	 * Get the path without home URL path.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $url The URL.
	 *
	 * @return string      The URL path.
	 */
	public static function get_url_path( $url ) {
		// Get the site URL parts.
		$url_parts = wp_parse_url( Helper_Service::get_site_url() );
		// Get the home path.
		$home_path = ! empty( $url_parts['path'] ) ? trailingslashit( $url_parts['path'] ) : '/';

		// Remove the query args from the URL.
		$url = explode( '?', preg_replace( '|//+|', '/', $url ) );
		// Get the URL path.
		$path = wp_parse_url( $url[0], PHP_URL_PATH );
		// Return the path without home path.
		return str_replace( $home_path, '', $path );

	}

	/**
	 * Set custom wp_die callback.
	 *
	 * @since  1.1.0
	 *
	 * @return array Array with the callable function for our custom wp_die.
	 */
	public function custom_wp_die_handler() {
		return array( $this, 'custom_wp_die_callback' );
	}

	/**
	 * Custom wp_die callback.
	 *
	 * @since  1.1.0
	 *
	 * @param  string $message The error message.
	 * @param  string $title   The error title.
	 * @param  array  $args    Array with additional args.
	 */
	public function custom_wp_die_callback( $message, $title, $args ) {
		// Call the default wp_die_handler if the custom param is not set or a WP_Error object is present.
		if ( is_object( $message ) || empty( $args['sgs_error'] ) ) {
			$args['exit'] = true;
			_default_wp_die_handler( $message, $title, $args );
		}

		// Include the error template.
		include SG_Security\DIR . '/templates/error.php';
		exit;
	}

	/**
	 * Checks if the table exists in the database.
	 *
	 * @since  1.2.0
	 *
	 * @param  string $table_name The name of the table
	 *
	 * @return boolean            True/False.
	 */
	public static function table_exists( $table_name ) {
		global $wpdb;
		self::$wpdb = $wpdb;

		// Bail if table doesn't exist.
		if ( self::$wpdb->get_var( self::$wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) !== $table_name ) { //phpcs:ignore
			return false;
		}

		return true;
	}

	/**
	 * Force user logout.
	 *
	 * @since  1.2.2
	 */
	public function logout_users() {
		// Init the salt shaker
		$this->salt_shaker = new Salt_Shaker();

		// Change salts
		$this->salt_shaker->change_salts();

		// Destroy all sessions.
		WP_Session_Tokens::destroy_all_for_all_users();
	}
}
