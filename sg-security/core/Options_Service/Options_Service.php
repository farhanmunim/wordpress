<?php
namespace SG_Security\Options_Service;

/**
 * Options_Service class that handles all options checks.
 */
class Options_Service {

	/**
	 * The Database placeholder.
	 */
	public $wpdb;

	/**
	 * Check if a single boolean setting is enabled.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $key          Setting field key.
	 * @param  bool   $is_multisite Whether to check multisite option or regular option.
	 *
	 * @return boolean True if the setting is enabled, false otherwise.
	 */
	public static function is_enabled( $key, $is_multisite = false ) {
		$key   = self::add_key_prefix( $key );
		$value = false === $is_multisite ? get_option( $key ) : get_site_option( $key );

		if ( 1 === (int) $value ) {
			return true;
		}

		return false;
	}

	/**
	 * Enable a single boolean setting.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $key          Setting field key.
	 * @param  bool   $is_multisite Whether to check multisite option or regular option.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public static function enable_option( $key, $is_multisite = false ) {
		$key = self::add_key_prefix( $key );
		// Don't try to enable already enabled option.
		if ( self::is_enabled( $key, $is_multisite ) ) {
			return true;
		}

		// Update the option.
		$result = false === $is_multisite ? update_option( $key, 1 ) : update_site_option( $key, 1 );

		// Return the result.
		return $result;
	}

	/**
	 * Disable a single boolean setting.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $key Setting field key.
	 * @param  bool   $is_multisite Whether to check multisite option or regular option.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public static function disable_option( $key, $is_multisite = false ) {
		$key = self::add_key_prefix( $key );
		// Don't try to disable already disabled option.
		if ( ! self::is_enabled( $key, $is_multisite ) ) {
			return true;
		}

		// Update the option.
		$result = false === $is_multisite ? update_option( $key, 0 ) : update_site_option( $key, 0 );

		// Return the result.
		return $result;
	}

	/**
	 * Change an option.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $key Setting field key.
	 * @param  string $value Setting value.
	 * @param  bool   $is_multisite Whether to check multisite option or regular option.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public static function change_option( $key, $value, $is_multisite = false ) {
		$key = self::add_key_prefix( $key );

		// Update the option.
		$result = false === $is_multisite ? update_option( $key, $value ) : update_site_option( $key, $value );

		// Return the result.
		return intval( $result );
	}

	/**
	 * Adds a prefix to the option key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Prefixed key.
	 */
	public static function add_key_prefix( $key ) {
		return 'sg_security_' . $key;
	}

	/**
	 * Checks if the `option_key` parameter exists in rest data.
	 *
	 * @since  1.0.0
	 *
	 * @param  object $request Request data.
	 *
	 * @return string          The option key.
	 */
	private function validate_key( $request ) {
		$data = json_decode( $request->get_body(), true );

		// Bail if the option key is not set.
		if ( empty( $data['option_key'] ) ) {
			wp_send_json_error();
		}

		return $data['option_key'];
	}

	/**
	 * Provide all plugin options.
	 *
	 * @since  1.0.0
	 */
	public function fetch_options() {
		global $wpdb;
		global $blog_id;
		$this->wpdb = $wpdb;

		$prefix = $this->wpdb->get_blog_prefix( $blog_id );

		$options = array();

		$query = $this->wpdb->prepare(
			"SELECT REPLACE( option_name, 'sg_security_', '' ) AS name, option_value AS value
				FROM " . esc_sql( $prefix . 'options' ) . "
				WHERE option_name LIKE %s
			 ",
			'%' . $this->wpdb->esc_like( 'sg_security_' ) . '%'
		);

		$site_options = $this->wpdb->get_results( $query ); //phpcs:ignore

		foreach ( $site_options as $option ) {
			// Try to unserialize the value.
			$value = maybe_unserialize( $option->value );

			if (
				! is_array( $value ) &&
				null !== filter_var( $value, FILTER_VALIDATE_BOOLEAN )
			) {
				$value = intval( $value );
			}

			$options[ $option->name ] = $value;
		}

		return $options;
	}
}
