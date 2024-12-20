<?php
namespace SG_Security\Salt_Shaker;

use SiteGround_Helper\Helper_Service;

/**
 * Class that manages User's Log-out services.
 */
class Salt_Shaker {

	/**
	 * The salts we need to change in order to logout all users.
	 *
	 * @var array
	 */
	public $salts_array = array(
		'AUTH_KEY',
		'SECURE_AUTH_KEY',
		'LOGGED_IN_KEY',
		'NONCE_KEY',
		'AUTH_SALT',
		'SECURE_AUTH_SALT',
		'LOGGED_IN_SALT',
		'NONCE_SALT',
	);

	/**
	 * Regex for finding the salts we need to change.
	 *
	 * @var string
	 */
	public $regex = '/^define\((\s)?[\'"]SALT_CONSTANT[\'"](\s)?,(.*?)\);/';

	/**
	 * The path to the wp-config.
	 *
	 * @var string
	 */
	public $config_file = ABSPATH . 'wp-config.php';

	/**
	 * The path to the temporary wp-config we are creating.
	 *
	 * @var string
	 */
	public $tmp_config_file = ABSPATH . 'wp-config-tmp.php';

	/**
	 * The salt generator api.
	 *
	 * @var string
	 */
	public $wp_salt_api = 'https://api.wordpress.org/secret-key/1.1/salt/';

	/**
	 * The WordPress filesystem.
	 */
	public $wp_filesystem;

	public function __construct() {
		$this->wp_filesystem = Helper_Service::setup_wp_filesystem();
	}

	/**
	 * Check if the config exists.
	 *
	 * @since  1.0.0
	 *
	 * @return string|bool The path to the config file if exists. False otherwise.
	 */
	public function config_exist() {

		if ( file_exists( $this->config_file ) &&
			$this->wp_filesystem->is_writable( $this->config_file )
		) {
			return $this->config_file;

		}

		return false;
	}

	/**
	 * Get fresh salts from the API.
	 *
	 * @since  1.0.0
	 *
	 * @return bool|string False if we don't get a response, the fresh salts otherwise.
	 */
	public function get_fresh_salts() {
		// Get the salts from the salts generator.
		$api_salts = wp_remote_get( $this->wp_salt_api );

		// Bail if we don't get a response.
		if ( 200 !== wp_remote_retrieve_response_code( $api_salts ) ) {
			return false;
		}

		// Create the salts array.
		$new_salts = explode( "\n", wp_remote_retrieve_body( $api_salts ) );

		return $new_salts;
	}

	/**
	 * Start the change of salts.
	 *
	 * @since  1.0.0
	 *
	 * @return bool False if we fail one of the checks, true if we change the salts.
	 */
	public function change_salts() {
		// Bail if the config does not exist or is unwritable.
		if ( false === $this->config_exist() ) {
			return false;
		}

		// Get the fresh salts.
		$new_salts = $this->get_fresh_salts();

		// Bail if we don't get a response from the API.
		if ( false === $new_salts ) {
			return false;
		}

		// Check and save the file permissions.
		$config_permissions = fileperms( $this->config_file );

		// Open the File Stream.
		$reading_config = fopen( $this->config_file, 'r' );
		$writing_config = fopen( $this->tmp_config_file, 'w' );

		// Check if we can lock the files and set LOCK_EX to acquire an exclusive lock (writer).
		if (
			! flock( $reading_config, LOCK_EX ) ||
			! flock( $writing_config, LOCK_EX )
		) {
			echo 'Cant lock the file.';

			// Close the files, we are not editing since we cannot lock the file.
			fclose( $reading_config );
			fclose( $writing_config );

			return false;
		}

		// While the pointer is not at the end of the file do some salt check.
		while ( ! feof( $reading_config ) ) {

			// Get the contents of the line.
			$line = fgets( $reading_config );

			$line = $this->replace_salts( $line, $new_salts );

			// Write the new salt to the file.
			fputs( $writing_config, $line );
		}

		// Close the file and unlock it.
		fclose( $reading_config );
		fclose( $writing_config );

		// Rename the file.
		$this->wp_filesystem->move( $this->tmp_config_file, $this->config_file, true );

		// Keep the original permissions of wp-config.php.
		$this->wp_filesystem->chmod( $this->config_file, $config_permissions );

		return true;
	}

	/**
	 * Loop the salts, find them in the config file and replace them with the newly generated ones.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $line      A line from the wp-config.
	 * @param  array  $new_salts  The new salts generated from the API.
	 *
	 * @return string            The edited lines.
	 */
	public function replace_salts( $line, $new_salts ) {
		// Loop trough salts, find the ones we are trying to change.
		foreach ( $this->salts_array as $salt_key => $salt_value ) {

			// Build the regex and replace the Salt constant with the key.
			$regex = str_replace( 'SALT_CONSTANT', $salt_value, $this->regex );

			// If we have a match prepare the change of salts.
			if ( preg_match( $regex, $line, $match ) ) {
				// Change the line with the new salt.
				$line = $new_salts[ $salt_key ] . "\n";
			}
		}

		return $line;
	}
}
